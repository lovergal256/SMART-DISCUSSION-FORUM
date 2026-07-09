<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_scores', function (Blueprint $table) {
            $table->id('QuizScoreID');
            $table->unsignedBigInteger('UserID');
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('QuizID');
            $table->foreign('QuizID')->references('QuizID')->on('quizzes')->onDelete('cascade');
            $table->decimal('Score', 5, 2);
            $table->dateTime('DateRecorded');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_scores');
    }
};