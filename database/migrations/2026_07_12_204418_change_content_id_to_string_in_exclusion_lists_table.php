<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exclusion_lists', function (Blueprint $table) {
            $table->string('ContentID', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('exclusion_lists', function (Blueprint $table) {
            $table->integer('ContentID')->change();
        });
    }
};