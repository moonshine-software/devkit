<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            if (! Schema::hasColumn('comments', 'imageable_type')) {
                $table->nullableMorphs('imageable');
            }
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            if (Schema::hasColumn('comments', 'imageable_type')) {
                $table->dropMorphs('imageable');
            }
        });
    }
};
