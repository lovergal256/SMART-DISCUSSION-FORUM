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
        Schema::create('answers', function (Blueprint $table) {
            $table->id('AnswerID');
            $table->foreignId('AttemptID')->constrained('attempts','AttemptID')->onDelete('cascade');
            $table->foreignId('QuestionID')->constrained('questions','QuestionID')->onDelete('cascade');
            $table->char('SelectedOption',1);
            $table->boolean('IsCorrect')->default(false);
            $table->decimal('MarksAwarded',5,2)->default(0);
            $table->dateTime('DateAnswered');

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};


