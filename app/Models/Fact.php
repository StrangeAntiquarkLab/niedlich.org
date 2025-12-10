<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fact extends Model
{
    use HasFactory;

    protected $fillable = [
        'species_id',
        'tag_id',
        'creator',
        'title',
        'fact',
    ];

    public function species()
    {
        return $this->belongsTo(Species::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
