<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $smartColumns = [
            'course',
            'subject',
            'professor',
            'semester',
            'tags',
            'difficulty',
            'is_exam_focused',
        ];

        $columnsToDrop = array_values(array_filter(
            $smartColumns,
            fn (string $column) => Schema::hasColumn('study_resources', $column),
        ));

        if ($columnsToDrop === []) {
            return;
        }

        Schema::table('study_resources', function (Blueprint $table): void {
            if (Schema::hasColumn('study_resources', 'course') && Schema::hasColumn('study_resources', 'subject')) {
                $table->dropIndex(['course', 'subject']);
            }

            if (Schema::hasColumn('study_resources', 'file_type') && Schema::hasColumn('study_resources', 'semester')) {
                $table->dropIndex(['file_type', 'semester']);
            }

            if (Schema::hasColumn('study_resources', 'difficulty') && Schema::hasColumn('study_resources', 'is_exam_focused')) {
                $table->dropIndex(['difficulty', 'is_exam_focused']);
            }
        });

        Schema::table('study_resources', function (Blueprint $table) use ($columnsToDrop): void {
            $table->dropColumn($columnsToDrop);
        });
    }

    public function down(): void
    {
        Schema::table('study_resources', function (Blueprint $table): void {
            if (! Schema::hasColumn('study_resources', 'course')) {
                $table->string('course')->nullable();
            }

            if (! Schema::hasColumn('study_resources', 'subject')) {
                $table->string('subject')->nullable();
            }

            if (! Schema::hasColumn('study_resources', 'professor')) {
                $table->string('professor')->nullable();
            }

            if (! Schema::hasColumn('study_resources', 'semester')) {
                $table->string('semester', 40)->nullable();
            }

            if (! Schema::hasColumn('study_resources', 'tags')) {
                $table->json('tags')->nullable();
            }

            if (! Schema::hasColumn('study_resources', 'difficulty')) {
                $table->string('difficulty', 24)->nullable();
            }

            if (! Schema::hasColumn('study_resources', 'is_exam_focused')) {
                $table->boolean('is_exam_focused')->default(false);
            }
        });

        Schema::table('study_resources', function (Blueprint $table): void {
            $table->index(['course', 'subject']);
            $table->index(['file_type', 'semester']);
            $table->index(['difficulty', 'is_exam_focused']);
        });
    }
};
