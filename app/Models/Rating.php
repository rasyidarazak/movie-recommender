<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'ratings';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // Relasi dengan tabel movies
    public function movie()
    {
        return $this->belongsTo('App\Models\Movie', 'movie_id', 'id');
    }

    // Relasi dengan tabel users
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
