<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class SearchTag extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'search_tags';

    protected $fillable = [
        'translations',
        'status',
        'icon',
    ];

    protected $casts = [
        'translations' => 'array',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getIconPathAttribute(){
        return $this->attributes['icon'] ? asset( $this->attributes['icon'] ) : null;
    }

    public function getTitleAttribute() {
        $translations = $this->translations ?? [];
        $locale = app()->getLocale();

        // Check if translation exists for current locale
        if (isset($translations[$locale]) && !empty($translations[$locale]['title'])) {
            return $translations[$locale]['title'];
        }

        // Fallback to English
        if (isset($translations['en']) && !empty($translations['en']['title'])) {
            return $translations['en']['title'];
        }

        // If no translation found, return first available title
        foreach ($translations as $lang => $data) {
            if (!empty($data['title'])) {
                return $data['title'];
            }
        }

        return '';
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'd/m/Y H:i:s' );
    }

    protected static $logAttributes = [
        'translations',
        'icon',
        'status',
    ];

    protected static $logName = 'search_tag';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} search tag";
    }
}