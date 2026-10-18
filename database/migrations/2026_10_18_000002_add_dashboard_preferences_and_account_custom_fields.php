<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('dashboard_preferences')->nullable();
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->json('custom_fields')->nullable();
        });

        Schema::create('account_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('key', 80)->unique();
            $table->string('type', 20);
            $table->json('options')->nullable();
            $table->boolean('required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_custom_fields');

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('dashboard_preferences');
        });
    }
};
