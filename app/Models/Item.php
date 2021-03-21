<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'is_disabled'
    ];

    // Relationships

    public function reviews()
    {
        return $this->hasMany(ItemReview::class);
    }
}
