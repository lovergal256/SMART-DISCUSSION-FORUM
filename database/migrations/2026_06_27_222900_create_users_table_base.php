
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->unsignedBigInteger('UserID')->autoIncrement();
            $table->string('FullName', 100);
            $table->string('Email', 100);
            $table->string('Password', 255);
            $table->dateTime('DateJoined');
            $table->dateTime('LastActiveDate')->nullable();
            $table->unsignedBigInteger('RoleID')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

