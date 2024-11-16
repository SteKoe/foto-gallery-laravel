<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GalleryUser extends Model
{
    use HasFactory;
    use HasUuids;

    protected $primaryKey = 'user_id';
    public $timestamps = false;
    protected $casts = [
        'is_admin' => 'boolean',
    ];
    protected $fillable = [
        'token',
        'is_admin',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(GalleryImageTag::class, 'gallery_users_gallery_image_tags', 'user_id', 'tag_id')->select('*');
    }
}
