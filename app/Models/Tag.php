<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'whitelist'];

    public function media()
    {
        return $this->belongsToMany(Media::class);
    }

    public function synonyms()
    {
        return $this->hasMany(TagSynonym::class);
    }
}
