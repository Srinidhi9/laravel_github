<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'author',
        'published_date',
        'published_by',
        'number_of_copies_sold',
        'genre',
        'number_of_available_items',
        'language',
        'edition',
        'isbn',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $attributes = [
        'number_of_copies_sold' => 0,
        'number_of_available_items' => 1,
        'status' => 'available',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

