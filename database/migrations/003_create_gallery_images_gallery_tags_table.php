<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gallery_image_gallery_image_tag', function (Blueprint $table) {
            $table->foreignUuid('file_id')->constrained('gallery_images', 'file_id');
            $table->foreignId('tag_id')->constrained('gallery_image_tags', 'tag_id');
            $table->primary(['file_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_image_to_gallery_image_tag');
    }
};
