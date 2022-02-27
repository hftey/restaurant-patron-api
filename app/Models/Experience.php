<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    protected $fillable = ['id', 'place_id', 'rating', 'comment', 'updated_at', 'created_at', 'created_by', 'done', 'points'];
    public $timestamps = false;
    use HasFactory;
}
