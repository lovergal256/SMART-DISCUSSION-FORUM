
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participation_scorres', function (Blueprint $table) {
            $table->string('ScoreID', 50)->primary();
            $table->unsignedBigInteger('UserID');
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('GroupID');
            $table->foreign('GroupID')->references('GroupID')->on('groups')->onDelete('cascade');
            $table->integer('PostsCount')->default(0);
            $table->integer('RepliesCount')->default(0);
            $table->decimal('Score', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participation_scorres');
    }
};