
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->string('AdminID', 30)->primary();
            $table->unsignedBigInteger('UserID')->unique();
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->dateTime('AssignedDate');
            $table->string('Scope', 50)->default('Forum Wide');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};