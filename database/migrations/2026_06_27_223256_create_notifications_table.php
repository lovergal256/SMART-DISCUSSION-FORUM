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
        Schema::create('notifications', function (Blueprint $table) {
            $table->string('NotificationID')->primary();
            $table->string('UserID',50);
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->text('Message');
            $table->string('Type',50);
            $table->string('Status',20)->default('Unread');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
