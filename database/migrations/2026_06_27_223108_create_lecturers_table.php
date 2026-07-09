<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecturers', function (Blueprint $table) {
            $table->unsignedBigInteger('LecturerID')->autoIncrement();
            $table->unsignedBigInteger('UserID');
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->string('Department', 100)->nullable();
            $table->date('DateEmployed')->nullable();
            $table->string('Status', 20)->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturers');
    }
};