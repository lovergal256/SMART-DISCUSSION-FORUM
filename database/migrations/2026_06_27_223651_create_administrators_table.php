
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrators', function (Blueprint $table) {
            $table->string('AdministratorID', 30)->primary();
            $table->unsignedBigInteger('UserID')->unique();
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->string('AccessLevel', 20)->default('Full');
            $table->dateTime('DateAssigned');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrators');
    }
};