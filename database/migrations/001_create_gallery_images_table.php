<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->uuid('file_id')->primary();
            $table->string('fileid');
            $table->string('displayname')->default('displayname');
            $table->string('href')->default('href');
            $table->string('name')->default('name');
            $table->string('slug')->default('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
    }
};
