<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name'];

    // Une série a plusieurs sets
    public function sets()
    {
        return $this->hasMany(Set::class);
    }
}
