<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagSynonym extends Model
{
    use HasFactory;

    protected $table = 'tag_synonyms';

    protected $fillable = ['tag_id', 'synonym'];

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
