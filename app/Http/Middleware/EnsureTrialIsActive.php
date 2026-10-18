<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTrialIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $trialEndsAt = $request->user()?->trial_ends_at;
        $paidUntil = $request->user()?->paid_until;

        if ($trialEndsAt !== null && $trialEndsAt->isPast() && ($paidUntil === null || $paidUntil->isPast())) {
            return redirect()->to(route('home').'#pricing')
                ->with('trial_expired', true);
        }

        return $next($request);
    }
}
