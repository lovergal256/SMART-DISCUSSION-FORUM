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
        Schema::create('topics', function (Blueprint $table) {
            $table->id('TopicID');
            $table->foreignId('GroupID')
                  ->constrained('groups','GroupId')
                  ->onDelete('cascade');
            $table->string('UserID',50);
            $table->foreign('UserID')
            ->references('UserID')
            ->on('users')
            ->onDelete('cascade');
            $table->string('Title',150);
            $table->text('Description');
            $table->string('Status',20)
                 ->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
