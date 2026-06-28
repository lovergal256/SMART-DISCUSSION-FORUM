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
        Schema::create('admins', function (Blueprint $table) {
            $table->string('AdminID',30)->primary();
            $table->string('UserID',50)->unique();
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->dateTime('AssignedDate');
            $table->string('Scope',50)->default('Forum Wide');


    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
