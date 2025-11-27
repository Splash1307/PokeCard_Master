<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'series_id'];

    // Un set appartient à une série
    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    // Un set a plusieurs cartes
    public function cards()
    {
        return $this->hasMany(Card::class);
    }
}
