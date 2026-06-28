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
        Schema::create('users', function (Blueprint $table) {
            $table->string('UserID',50)->primary();
            $table->string('FullName',100);
            $table->string('Email',100)->unique();
            $table->string('Password',255);
            $table->dateTime('DateJoined');
            $table->dateTime('LastActiveDate')->nullable();
            $table->foreignId('RoleID')->default(1)->constrained('roles','RoleID');

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
