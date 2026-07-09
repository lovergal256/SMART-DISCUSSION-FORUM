<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exclusion_lists', function (Blueprint $table) {
            $table->id('ExclusionID');
            $table->unsignedBigInteger('UserID');
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('ExcludedUserID');
            $table->foreign('ExcludedUserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->string('ContentType', 30);
            $table->integer('ContentID');
            $table->dateTime('ExclusionDate');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exclusion_lists');
    }
};