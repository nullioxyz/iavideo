<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presets', function (Blueprint $table) {
            $table->string('preview_image_upload_path')->nullable()->after('preview_video_url');
            $table->string('preview_video_upload_path')->nullable()->after('preview_image_upload_path');
        });
    }

    public function down(): void
    {
        Schema::table('presets', function (Blueprint $table) {
            $table->dropColumn([
                'preview_image_upload_path',
                'preview_video_upload_path',
            ]);
        });
    }
};

