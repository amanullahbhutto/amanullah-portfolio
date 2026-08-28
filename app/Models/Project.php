<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    public const TYPE_FULL_DEVELOPMENT = 'full_development';
    public const TYPE_MODIFICATION_ENHANCEMENT = 'modification_enhancement';

    protected $guarded = [];

    public static function types(): array
    {
        return [
            self::TYPE_FULL_DEVELOPMENT => [
                'label' => 'Full Development',
                'description' => 'Start se khud banaya',
                'badge' => 'Full Development',
                'badge_class' => 'type-full-dev',
            ],
            self::TYPE_MODIFICATION_ENHANCEMENT => [
                'label' => 'Modification / Enhancement',
                'description' => 'Pehle se bane project par kaam kiya',
                'badge' => 'Modification / Enhancement',
                'badge_class' => 'type-mod-enh',
            ],
        ];
    }

    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'images' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function visitorLogs(): HasMany
    {
        return $this->hasMany(VisitorLog::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->image ?: ($this->image_paths[0] ?? null));
    }

    public function getImageUrlsAttribute(): array
    {
        return array_values(array_filter(array_map(
            fn (string $path): ?string => $this->publicFileUrl($path),
            $this->image_paths
        )));
    }

    public function getImagePathsAttribute(): array
    {
        $paths = [];

        if (filled($this->image)) {
            $paths[] = $this->image;
        }

        foreach ($this->images ?? [] as $path) {
            if (filled($path)) {
                $paths[] = $path;
            }
        }

        return array_values(array_unique($paths));
    }

    private function publicFileUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        if (Str::startsWith($path, ['assets/', 'storage/'])) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }

    public function getProjectTypeLabelAttribute(): string
    {
        $type = $this->project_type ?: self::TYPE_FULL_DEVELOPMENT;
        return self::types()[$type]['label'] ?? 'Full Development';
    }

    public function getProjectTypeDescriptionAttribute(): string
    {
        $type = $this->project_type ?: self::TYPE_FULL_DEVELOPMENT;
        return self::types()[$type]['description'] ?? 'Start se khud banaya';
    }

    public function getProjectTypeBadgeClassAttribute(): string
    {
        $type = $this->project_type ?: self::TYPE_FULL_DEVELOPMENT;
        return self::types()[$type]['badge_class'] ?? 'type-full-dev';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
