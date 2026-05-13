<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_resource_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_resource_id')->constrained('study_resources')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('accuracy_rating');
            $table->unsignedTinyInteger('clarity_rating');
            $table->unsignedTinyInteger('usefulness_rating');
            $table->text('review_text')->nullable();
            $table->timestamps();

            $table->unique(['study_resource_id', 'user_id']);
            $table->index(['study_resource_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_resource_reviews');
    }
};
