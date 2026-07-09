<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  public function up(): void
{
    // Step 1: add DiscussionID only if it doesn't already exist
    if (!Schema::hasColumn('topics', 'DiscussionID')) {
        Schema::table('topics', function (Blueprint $table) {
            $table->foreignId('DiscussionID')
                  ->nullable()
                  ->after('TopicID')
                  ->constrained('discussions', 'DiscussionID')
                  ->onDelete('cascade');
        });
    }

    // Step 2: create a placeholder discussion for existing test topics
    $firstUserId = DB::table('users')->value('UserID') ?? 1;

    $discussionId = DB::table('discussions')->insertGetId([
        'Title' => 'Migrated Test Discussion',
        'Description' => 'Auto-created to hold existing test topics after migration.',
        'UserID' => $firstUserId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Step 3: backfill existing topics with that discussion id
    DB::table('topics')->whereNull('DiscussionID')->update([
        'DiscussionID' => $discussionId,
    ]);

    // Step 4: drop the old GroupID column if it still exists
    if (Schema::hasColumn('topics', 'GroupID')) {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropForeign(['GroupID']);
            $table->dropColumn('GroupID');
        });
    }

    // Step 5: make DiscussionID required now that all rows have a value
    Schema::table('topics', function (Blueprint $table) {
        $table->foreignId('DiscussionID')->nullable(false)->change();
    });
}  
    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropForeign(['DiscussionID']);
            $table->dropColumn('DiscussionID');

            $table->foreignId('GroupID')
                  ->constrained('groups', 'GroupId')
                  ->onDelete('cascade');
        });
    }
};