<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'buyer_id',
        'seller_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function otherParticipant(User $user)
    {
        return $this->buyer_id === $user->id ? $this->seller : $this->buyer;
    }

    public static function findOrStart(?int $productId, int $buyerId, int $sellerId): self
    {
        $conversation = static::query()
            ->where('buyer_id', $buyerId)
            ->where('seller_id', $sellerId)
            ->when(
                $productId === null,
                fn ($query) => $query->whereNull('product_id'),
                fn ($query) => $query->where('product_id', $productId)
            )
            ->first();

        if ($conversation) {
            return $conversation;
        }

        return static::create([
            'product_id' => $productId,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'last_message_at' => now(),
        ]);
    }
}
