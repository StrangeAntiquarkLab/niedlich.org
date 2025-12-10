<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Species extends Model
{
    use HasFactory;

    protected $table = 'species';

    protected $fillable = ['name', 'display_name'];

    public function media()
    {
        return $this->hasMany(Media::class);
    }
}
