<?php
namespace App\Models\Enums;

enum MimeType: string
{
    case GIF = 'image/gif';
    case MP4 = 'video/mp4';
    case WEBM = 'video/webm';
    case JPG = 'image/jpg';
    case JPEG = 'image/jpeg';
    case PNG = 'image/png';
    case WEBP = 'image/webp';

    public static function allowedTypes(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->value, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->value, 'video/');
    }

    public function extension(): string
    {
        return match($this) {
            self::GIF => 'gif',
            self::MP4 => 'mp4',
            self::WEBM => 'webm',
            self::JPG => 'jpg',
            self::JPEG => 'jpg',
            self::PNG => 'png',
            self::WEBP => 'webp',
        };
    }

    public function isAnimated(): bool
    {
        // WebP can be animated too, but we only allow non-animated WebP files to be submitted
        return match($this) {
            self::GIF => true,
            self::WEBP => false, // Assuming non-animated as that is currently the only way we allow them to be submitted.
            default => false,
        };
    }
}
