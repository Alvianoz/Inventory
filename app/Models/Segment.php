<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'image_path',
    ];

    protected $appends = ['image_url'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        return asset('assets/default.png');
    }
}