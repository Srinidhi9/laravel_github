<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibrarianRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requested_by',
        'name',
        'email',
        'password',
        'phone_number',
        'status',
        'approved_by',
    ];

    // Relationships
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
