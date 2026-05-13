<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_folders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('color', 24)->default('#22c55e');
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::create('saved_resources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('study_resource_id')->constrained('study_resources')->cascadeOnDelete();
            $table->foreignId('resource_folder_id')->nullable()->constrained('resource_folders')->nullOnDelete();
            $table->timestamp('saved_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'study_resource_id']);
            $table->index(['user_id', 'resource_folder_id']);
        });

        Schema::create('resource_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('study_resource_id')->constrained('study_resources')->cascadeOnDelete();
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'study_resource_id']);
            $table->index(['user_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_views');
        Schema::dropIfExists('saved_resources');
        Schema::dropIfExists('resource_folders');
    }
};
