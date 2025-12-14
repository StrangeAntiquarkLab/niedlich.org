<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Enums\MediaType;
use App\Models\Enums\MimeType;

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

    protected $casts = [
        'type' => MediaType::class,
        'mime_type' => MimeType::class
    ];

    /**
     * Relationships
     */
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'media_tags');
    }

    /**
     * Scope queries for filtering
     */
    public function scopeImages($query)
    {
        return $query->whereIn('type', [MediaType::PICTURE, MediaType::GIF]);
    }

    public function scopeVideos($query)
    {
        return $query->whereIn('type', [MediaType::WEBM, MediaType::MP4]);
    }

    public function scopeForSpecies($query, Species|int $species)
    {
        return $query->where('species_id', $species instanceof Species ? $species->id : $species);
    }

    /**
     * Return the display URL of the media.
     *
     * If the media is a CF asset, return the URL to the asset.
     * Otherwise, return the URL set on the media.
     */
    public function getDisplayUrlAttribute(): string | false
    {
        if ($this->cf_id) {
            return "https://imagedelivery.net/{$_ENV['CF_ACC_HASH']}/".$this->cf_id."/public";
        }
        return $this->url ?? false;
    }

    public function getThumbnailUrlAttribute(): string | false
    {
        if ($this->cf_id) {
            return "https://imagedelivery.net/{$_ENV['CF_ACC_HASH']}/".$this->cf_id."/thumbnail";
        }
        return $this->url ?? false;
    }

    /**
     * Checks for Media- and Mime-Type
     */
    public function isImage(): bool
    {
        return in_array($this->type, [MediaType::PICTURE, MediaType::GIF]);
    }

    public function isVideo(): bool
    {
        return in_array($this->type, [MediaType::WEBM, MediaType::MP4]);
    }

    public static function validateMimeType($file): ?MimeType
    {
        $mime = $file->getMimeType();
        if (in_array($mime, MimeType::allowedTypes())) {
            return MimeType::tryFrom($mime);
        }
        return null;
    }

}
