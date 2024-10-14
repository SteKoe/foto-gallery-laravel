<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_image_tags', function(Blueprint $table) {
            $table->id('tag_id');
            $table->string('tag_value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_image_tags');
    }
};
