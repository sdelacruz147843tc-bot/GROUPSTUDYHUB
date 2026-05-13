<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_resources', function (Blueprint $table) {
            $table->string('file_type', 24)->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);

            $table->index(['download_count', 'rating_average']);
        });
    }

    public function down(): void
    {
        Schema::table('study_resources', function (Blueprint $table) {
            $table->dropIndex(['download_count', 'rating_average']);

            $table->dropColumn([
                'file_type',
                'download_count',
                'rating_average',
                'rating_count',
            ]);
        });
    }
};
