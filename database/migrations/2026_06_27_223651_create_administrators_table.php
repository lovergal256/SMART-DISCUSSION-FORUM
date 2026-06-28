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
        Schema::create('administrators', function (Blueprint $table) {
           $table->string('AdministratorID',30)->primary();
           $table->string('UserID',50)->unique();
           $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
           $table->string('AccessLevel',20)->default('Full');
           $table->dateTime('DateAssigned');
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administrators');
    }
};
