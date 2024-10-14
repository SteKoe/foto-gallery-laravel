<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gallery_users_gallery_image_tags', function (Blueprint $table) {
            $table->addColumn('text', 'scope')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('gallery_users_gallery_image_tags', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
};
