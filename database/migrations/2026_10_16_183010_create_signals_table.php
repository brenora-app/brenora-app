<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('source');
            $table->unsignedTinyInteger('score')->default(0);
            $table->dateTime('recorded_at');
            $table->text('summary');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signals');
    }
};
