<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE replies ADD COLUMN ParentReplyID VARCHAR(50) NULL, ALGORITHM=COPY, LOCK=SHARED");

        DB::statement("ALTER TABLE replies ADD CONSTRAINT replies_parentreplyid_foreign FOREIGN KEY (ParentReplyID) REFERENCES replies (ReplyID) ON DELETE SET NULL, ALGORITHM=COPY, LOCK=SHARED");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE replies DROP FOREIGN KEY replies_parentreplyid_foreign");
        DB::statement("ALTER TABLE replies DROP COLUMN ParentReplyID");
    }
};