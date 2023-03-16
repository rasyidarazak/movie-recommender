<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $table = 'movies';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // Relasi dengan tabel ratings
    public function ratings()
    {
        return $this->hasMany('App\Models\Rating', 'movie_id', 'id');
    }
}
