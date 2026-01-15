<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->string('photo_folder')->nullable()->after('disk');
            $table->string('original_name')->nullable()->after('photo_folder');
            $table->foreignId('parent_photo_id')->nullable()->after('original_name')
                ->constrained('photos')->cascadeOnDelete();
            $table->json('transformations')->nullable()->after('parent_photo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropForeign(['parent_photo_id']);
            $table->dropColumn(['photo_folder', 'original_name', 'parent_photo_id', 'transformations']);
        });
    }
};
