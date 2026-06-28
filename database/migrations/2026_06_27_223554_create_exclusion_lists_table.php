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
        Schema::create('exclusion_lists', function (Blueprint $table) {
            $table->id('ExclusionID');
            $table->string('UserID',50);
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->string('ExcludedUserID',50);
            $table->foreign('ExcludedUserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->string('ContentType',30);
            $table->integer('ContentID');
            $table->dateTime('ExclusionDate');
                
                    

          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exclusion_lists');
    }
};
