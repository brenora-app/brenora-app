<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('industry');
            $table->string('owner');
            $table->decimal('annual_revenue', 12, 2)->default(0);
            $table->unsignedTinyInteger('health_score')->default(0);
            $table->string('status')->default('healthy');
            $table->date('renewal_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
