<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gallery_users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);
        });

        Schema::table('gallery_users_gallery_image_tags', function (Blueprint $table) {
            $table->uuid('id')->default(DB::raw('(UUID())'));
            $table->dropForeign(['tag_id']);
            $table->dropForeign(['user_id']);
            $table->dropPrimary();
            $table->primary(['id']);

            $table->foreign('tag_id')
                ->references('tag_id')
                ->on('gallery_image_tags');

            $table->foreign('user_id')
                ->references('user_id')
                ->on('gallery_users');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
