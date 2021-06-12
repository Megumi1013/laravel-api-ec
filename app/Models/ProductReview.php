<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'stars',
    ];

    // Relationships

    public function item()
    {
        return $this->belongsTo(Product::class);
    }
}
