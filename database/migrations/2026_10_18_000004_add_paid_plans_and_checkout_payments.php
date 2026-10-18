<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('paid_plan', 40)->nullable()->after('trial_ends_at');
            $table->timestamp('paid_until')->nullable()->after('paid_plan')->index();
        });

        Schema::create('checkout_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan', 40);
            $table->string('billing_period', 20);
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3);
            $table->string('status', 24)->index();
            $table->string('paypal_order_id')->nullable()->unique();
            $table->string('paypal_capture_id')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_payments');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['paid_until']);
            $table->dropColumn(['paid_plan', 'paid_until']);
        });
    }
};
