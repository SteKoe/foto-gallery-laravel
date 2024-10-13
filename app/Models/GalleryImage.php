<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    use HasFactory;
    use HasUuids;

    protected $primaryKey = 'file_id';
    public $timestamps = false;
    protected $fillable = [
        'fileid',
        'displayname',
    ];

    public function tags()
    {
        return $this->belongsToMany(GalleryImageTag::class, 'gallery_image_gallery_image_tag', 'file_id', 'tag_id');
    }
}
