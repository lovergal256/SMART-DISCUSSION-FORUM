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
        Schema::create('group_admins', function (Blueprint $table) {
            $table->string('AdminID',30)->primary();
            $table->foreignId('GroupID')->constrained('groups','GroupID')->onDelete('cascade');
            $table->string('UserID',50);
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_admins');
    }
};
