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
        Schema::create('attempts', function (Blueprint $table) {
            $table->id('AttemptID');
            $table->string('UserID',50);
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade')
            $table->foreignId('QuizID')->constrained('quizzes','QuizID')->onDelete('cascade')
            $table->dateTime('StartTime');
            $table->dateTime('EndTime');
            $table->string('Status',20)->default('Completed');
            $table->decimal('Score',5,2)->default(0);
            $table->dateTime('AttemptDate');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
