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
    Schema::table('replies', function (Blueprint $table) {
        $table->string('ReplyID', 50)->nullable();
        $table->string('PostID', 50)->nullable();
        $table->string('UserID', 50)->nullable();
        $table->text('Body')->nullable();
    });
}

public function down(): void
{
    Schema::table('replies', function (Blueprint $table) {
        $table->dropForeign(['PostID']);
        $table->dropForeign(['UserID']);
        $table->dropColumn(['ReplyID', 'PostID', 'UserID', 'Body']);
    });
}
};
