<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->contst;
            $table->foreignId('event_id')->constrained();
            $table->text('message'); // Текст отзыва
            $table->tinyInteger('rating')->default(1)->between(1, 5); // Оценка от 1 до 5

            // Уникальный индекс: один пользователь может оставить отзыв только один раз
            $table->unique(['user_id', 'event_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
