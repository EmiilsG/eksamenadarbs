<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    public const MAX_IMAGES = 6;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'category',
        'image',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Product $product) {
            $product->images()->get()->each->delete();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('position')->orderBy('id');
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return 'https://picsum.photos/seed/placeholder/600/400';
        }

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }

    public function gallery()
    {
        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            return $this->images->pluck('url');
        }

        return collect([$this->image_url]);
    }

    public function remainingImageSlots()
    {
        return max(0, self::MAX_IMAGES - $this->images()->count());
    }

    public function storeImage($file, $position = null)
    {
        $path = $file->store('products', 'public');

        $this->images()->create([
            'path' => $path,
            'position' => $position ?? $this->images()->count(),
        ]);

        if (!$this->image) {
            $this->update(['image' => $path]);
        }

        return $path;
    }

    public function makeMainImage(ProductImage $image)
    {
        $ordered = $this->images()
            ->get()
            ->sortBy(fn (ProductImage $item) => $item->id === $image->id ? 0 : 1)
            ->values();

        foreach ($ordered as $position => $item) {
            $item->update(['position' => $position]);
        }

        $this->update(['image' => $image->path]);
    }

    public function deleteImage(ProductImage $image)
    {
        $wasMain = $this->image === $image->path;

        $image->delete();

        if ($wasMain) {
            $this->update(['image' => $this->images()->value('path')]);
        }
    }

    public function deleteStoredImage($path)
    {
        if ($path && !str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
