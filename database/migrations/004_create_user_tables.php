<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gallery_users', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('token');
        });

        Schema::create('gallery_users_gallery_image_tags', function (Blueprint $table) {
            $table->foreignUuid('user_id')->constrained('gallery_users', 'user_id');
            $table->foreignId('tag_id')->constrained('gallery_image_tags', 'tag_id');
            $table->primary(['user_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_users');
        Schema::dropIfExists('gallery_users_gallery_image_tags');
    }
};
