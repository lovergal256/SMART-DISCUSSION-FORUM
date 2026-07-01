
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id('QuizID');
            $table->string('Title', 100);
            $table->dateTime('StartTime');
            $table->integer('Duration');
            $table->foreignId('GroupID')->constrained('groups', 'GroupID');
            $table->unsignedBigInteger('LecturerID')->nullable();
            $table->foreign('LecturerID')->references('LecturerID')->on('lecturers')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};