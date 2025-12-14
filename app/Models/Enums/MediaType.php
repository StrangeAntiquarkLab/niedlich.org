<?php
namespace App\Models\Enums;

enum MediaType: string
{
    case PICTURE = 'pic';
    case GIF = 'gif';
    case WEBM = 'webm';
    case MP4 = 'mp4';

    public function label(): string
    {
        return match ($this) {
            self::PICTURE => 'Picture',
            self::GIF => 'Animated GIF',
            self::WEBM => 'WebM Video',
            self::MP4 => 'MP4 Video',
        };
    }

    public function isImage(): bool
    {
        return in_array($this, [self::PICTURE, self::GIF]);
    }

    public function isVideo(): bool
    {
        return in_array($this, [self::WEBM, self::MP4]);
    }

    public static function mimeToMediaType(MimeType $mimeType): MediaType
    {
        return match ($mimeType) {
            MimeType::GIF => MediaType::GIF,
            MimeType::WEBM => MediaType::WEBM,
            MimeType::MP4 => MediaType::MP4,
            default => MediaType::PICTURE,
        };
    }
}
