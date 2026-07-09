<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('replies', function (Blueprint $table) {
            $table->string('ParentReplyID', 50)->nullable();

            $table->foreign('ParentReplyID')
                  ->references('ReplyID')
                  ->on('replies')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('replies', function (Blueprint $table) {
            $table->dropForeign(['ParentReplyID']);
            $table->dropColumn('ParentReplyID');
        });
    }
};