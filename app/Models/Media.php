<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'species_id',
        'creator',
        'type',
        'cf_id',
        'url',
        'description',
        'source',
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
