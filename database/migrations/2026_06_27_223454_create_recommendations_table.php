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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id('RecommendationsID');
            $table->string('UserID',50);
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->bigInteger('TopicID')->unsigned();
            $table->foreign('TopicID')->references('TopicID')->on('topics')->onDelete('cascade');
            $table->decimal('RecommendationScore',5,2);
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
