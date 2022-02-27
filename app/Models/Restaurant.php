<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = ['id', 'name', 'address', 'city', 'state', 'country', 'phone_number', 'lat', 'lng', 'place_id', 'created_at', 'created_by'];
    public $timestamps = false;
    use HasFactory;
}
