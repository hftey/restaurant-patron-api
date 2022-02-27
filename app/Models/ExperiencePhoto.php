<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperiencePhoto extends Model
{
    protected $table = 'experiences_photos';
    protected $fillable = ['id', 'experiences_id', 'place_id', 'file_path', 'width', 'height', 'created_at', 'created_by'];
    use HasFactory;
}
