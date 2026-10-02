<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'path',
        'position',
    ];

    protected static function booted(): void
    {
        static::deleting(function (ProductImage $image) {
            if ($image->path && !str_starts_with($image->path, 'http')) {
                Storage::disk('public')->delete($image->path);
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute()
    {
        if (str_starts_with($this->path, 'http')) {
            return $this->path;
        }

        return asset('storage/' . $this->path);
    }
}
