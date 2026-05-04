<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_resources', function (Blueprint $table) {
            $table->index(['uploaded_at', 'created_at']);
            $table->index(['group_id', 'uploaded_at']);
        });

        Schema::table('discussions', function (Blueprint $table) {
            $table->index(['last_active_at', 'created_at']);
            $table->index(['group_id', 'last_active_at']);
        });

        Schema::table('study_sessions', function (Blueprint $table) {
            $table->index(['session_date', 'end_time']);
            $table->index(['created_at']);
            $table->index(['group_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::table('study_sessions', function (Blueprint $table) {
            $table->dropIndex(['session_date', 'end_time']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['group_id', 'session_date']);
        });

        Schema::table('discussions', function (Blueprint $table) {
            $table->dropIndex(['last_active_at', 'created_at']);
            $table->dropIndex(['group_id', 'last_active_at']);
        });

        Schema::table('study_resources', function (Blueprint $table) {
            $table->dropIndex(['uploaded_at', 'created_at']);
            $table->dropIndex(['group_id', 'uploaded_at']);
        });
    }
};
