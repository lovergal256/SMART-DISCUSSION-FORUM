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
        Schema::create('exclusions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('UserID');
            $table->unsignedBigInteger('ExcludedUserID');
            $table->unsignedBigInteger('GroupID');
            $table->foreign('UserID')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ExcludedUserID')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('GroupID')->references('GroupID')->on('groups')->onDelete('cascade');
            $table->unique(['UserID', 'ExcludedUserID', 'GroupID']);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exclusions');
    }
};
