<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('category_post', 'pivot_field')) {
            return;
        }

        Schema::table('category_post', function (Blueprint $table): void {
            $table->string('pivot_field')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('category_post', function (Blueprint $table): void {
            $table->dropColumn('pivot_field');
        });
    }
};
