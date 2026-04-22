<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->after('email');
            $table->string('display_name')->nullable()->after('role');
            $table->string('avatar_url', 500)->nullable()->after('display_name');
            $table->text('bio')->nullable()->after('avatar_url');
            $table->string('theme')->default('forest')->after('bio');
            $table->string('surface_style')->default('soft')->after('theme');
            $table->string('interface_density')->default('comfortable')->after('surface_style');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'display_name',
                'avatar_url',
                'bio',
                'theme',
                'surface_style',
                'interface_density',
            ]);
        });
    }
};
