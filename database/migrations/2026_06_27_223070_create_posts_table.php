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
        Schema::create('posts', function (Blueprint $table) {
            $table->string('PostID',50)->primary();
            $table->foreignId('TopicID')
                ->constrained('topics','TopicID')
                ->onDelete('cascade');
            $table->string('UserID',50);
            $table->foreign('UserID',50)
                  ->references('userID')
                  ->on('users')
                  ->onDelete('cascade');
            $table->text('content');
            $table->dateTime('DatePosted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
