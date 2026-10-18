<?php

namespace App\Http\Controllers;

use App\Models\CheckoutPayment;
use App\Services\PayPalCheckout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use RuntimeException;

class PayPalCheckoutController extends Controller
{
    public function store(Request $request, PayPalCheckout $checkout): JsonResponse
    {
        $validated = $request->validate([
            'plan' => ['required', Rule::in(['launch', 'growth'])],
            'period' => ['required', Rule::in(['monthly', 'annual'])],
        ]);

        if (! $checkout->isConfigured()) {
            return response()->json(['message' => 'Online checkout is temporarily unavailable. Please contact our team.'], 503);
        }

        $plan = config('pricing.plans.'.$validated['plan']);
        $amount = $plan[$validated['period']];
        $payment = CheckoutPayment::create([
            'user_id' => $request->user()->id,
            'plan' => $validated['plan'],
            'billing_period' => $validated['period'],
            'amount' => $amount,
            'currency' => config('pricing.currency'),
            'status' => 'creating',
        ]);

        try {
            $order = $checkout->createOrder(
                $amount,
                config('pricing.currency'),
                $plan['name'].' plan ('.$validated['period'].')',
                (string) $payment->id,
            );
        } catch (RuntimeException $exception) {
            $payment->update(['status' => 'failed']);

            return response()->json(['message' => $exception->getMessage()], 502);
        }

        if (! is_string($order['id'] ?? null) || ($order['status'] ?? null) !== 'CREATED') {
            Log::error('Payment provider returned an invalid order response.', ['payment_id' => $payment->id]);
            $payment->update(['status' => 'failed']);

            return response()->json(['message' => 'Checkout could not be started. Please try again.'], 502);
        }

        $payment->update([
            'paypal_order_id' => $order['id'],
            'status' => 'created',
        ]);

        return response()->json(['id' => $order['id'], 'payment_id' => $payment->id]);
    }

    public function capture(Request $request, CheckoutPayment $payment, PayPalCheckout $checkout): JsonResponse
    {
        abort_unless($payment->user_id === $request->user()->id, 404);

        if ($payment->status === 'completed') {
            return response()->json(['status' => 'completed']);
        }

        if ($payment->status !== 'created' || ! $payment->paypal_order_id) {
            return response()->json(['message' => 'This checkout is no longer available. Please start again.'], 409);
        }

        try {
            $order = $checkout->captureOrder($payment->paypal_order_id);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 502);
        }

        $purchase = Arr::get($order, 'purchase_units.0');
        $capture = Arr::get($purchase, 'payments.captures.0');
        $expectedAmount = number_format((float) $payment->amount, 2, '.', '');
        $expectedReference = (string) $payment->id;

        if (($order['id'] ?? null) !== $payment->paypal_order_id
            || ($order['status'] ?? null) !== 'COMPLETED'
            || Arr::get($purchase, 'custom_id') !== $expectedReference
            || Arr::get($purchase, 'amount.value') !== $expectedAmount
            || Arr::get($purchase, 'amount.currency_code') !== $payment->currency
            || Arr::get($capture, 'status') !== 'COMPLETED'
            || Arr::get($capture, 'amount.value') !== $expectedAmount
            || Arr::get($capture, 'amount.currency_code') !== $payment->currency
            || ! is_string(Arr::get($capture, 'id'))) {
            Log::warning('Payment capture response did not match the expected checkout.', [
                'payment_id' => $payment->id,
                'order_id' => $payment->paypal_order_id,
            ]);

            return response()->json(['message' => 'Payment could not be verified. Please contact our team before retrying.'], 422);
        }

        DB::transaction(function () use ($payment, $capture, $request): void {
            $lockedPayment = CheckoutPayment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($lockedPayment->status === 'completed') {
                return;
            }

            $lockedPayment->update([
                'status' => 'completed',
                'paypal_capture_id' => $capture['id'],
                'paid_at' => now(),
            ]);

            $user = $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);
            $startsAt = $user->paid_until?->isFuture() ? $user->paid_until : now();
            $paidUntil = $lockedPayment->billing_period === 'annual'
                ? $startsAt->copy()->addYear()
                : $startsAt->copy()->addMonth();

            $user->forceFill([
                'paid_plan' => $lockedPayment->plan,
                'paid_until' => $paidUntil,
            ])->save();
        });

        return response()->json(['status' => 'completed']);
    }
}
