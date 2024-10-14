<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryImageTag extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'tag_id';
    protected $fillable = [
        'tag_id',
        'tag_value',
    ];
}
