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
        Schema::create('blacklists', function (Blueprint $table) {
                $table->string('BlacklistID',50)->primary();
                $table->string('UserID',50);
                $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
                $table->text('Reason');
                $table->date('StartDate');
                $table->date('EndDate');

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklists');
    }
};
