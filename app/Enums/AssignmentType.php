<?php

namespace App\Enums;

enum AssignmentType: string
{
    case Web      = 'web';
    case Document = 'document';
    case Image    = 'image';
    case General  = 'general';

    public function label(): string
    {
        return match($this) {
            self::Web      => 'Web Project',
            self::Document => 'Dokumen',
            self::Image    => 'Gambar',
            self::General  => 'File Umum',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Web      => 'fa-globe',
            self::Document => 'fa-file-alt',
            self::Image    => 'fa-image',
            self::General  => 'fa-file',
        };
    }

    public function defaultMaxMb(): int
    {
        return match($this) {
            self::Web      => 10,
            self::Document => 25,
            self::Image    => 5,
            self::General  => 20,
        };
    }

    public function allowedMimes(): array
    {
        return match($this) {
            self::Web => [
                'application/zip',
                'application/x-zip-compressed',
                'application/rar',
                'application/x-rar-compressed',
            ],
            self::Document => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            self::Image => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/svg+xml',
                'image/webp',
                // PSD tidak punya MIME standar, validasi via ekstensi saja
            ],
            self::General => [], // dinamis dari DB
        };
    }

    public function allowedExtensions(): array
    {
        return match($this) {
            self::Web      => ['zip', 'rar'],
            self::Document => ['pdf', 'docx', 'doc', 'pptx', 'ppt', 'xlsx', 'xls'],
            self::Image    => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'psd'],
            self::General  => [], // dari DB
        };
    }

    public function canPreview(): bool
    {
        return match($this) {
            self::Web      => true,  // iframe full
            self::Document => true,  // PDF embed, lain download
            self::Image    => true,  // img tag
            self::General  => false, // download saja
        };
    }

    public function hasPublicGallery(): bool
    {
        return true; // semua tipe punya galeri publik
    }

    /**
     * Warna badge Tailwind untuk display di galeri/views.
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::Web      => 'indigo',
            self::Document => 'blue',
            self::Image    => 'emerald',
            self::General  => 'gray',
        };
    }
}
