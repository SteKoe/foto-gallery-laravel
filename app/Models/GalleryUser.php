<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryUser extends Model
{
    use HasFactory;
    use HasUuids;

    protected $primaryKey = 'user_id';
    public $timestamps = false;

    public function tags()
    {
        return $this->belongsToMany(GalleryUsersGalleryImageTag::class, 'gallery_users', 'user_id', 'user_id');
    }
}
