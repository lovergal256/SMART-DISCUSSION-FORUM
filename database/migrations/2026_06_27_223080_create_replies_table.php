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
        Schema::create('replies', function (Blueprint $table) {
            $table->string('ReplyID',50)->primary();
            $table->string('PostID',50);
            $table->foreign('PostID')
                  ->references('PostID')
                  ->on('posts')
                  ->onDelete('cascade');
            $table->string('UserID',50);
            $table->foreign('UserId'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replies');
    }
};


