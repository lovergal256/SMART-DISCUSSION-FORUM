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
        Schema::create('questions', function (Blueprint $table) {
            $table->id('QuestionID');
            $table->foreignId('QuizID')->constrained('quizzes','QuizID')->onDelete('cascade');
            $table->text('QuestionText');
            $table->string('OptionA',225);
            $table->string('OptionB',225);
            $table->string('OptionC',225)->nullable();
            $table->string('OptionD',225)->nullable();
            $table->char('CorrectOption',1);
            $table->integer('Marks');

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};


