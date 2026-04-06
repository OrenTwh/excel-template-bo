<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Nationality extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'nationalities';

    protected $fillable = [
        'title',
        'name',
        'symbol',
        'icon',
        'status',
        'image',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getIconPathAttribute(){
        return $this->attributes['icon'] ? asset( $this->attributes['icon'] ) : null;
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'd/m/Y H:i:s' );
    }

    protected static $logAttributes = [
        'title',
        'name',
        'symbol',
        'icon',
        'image',
        'status',
    ];

    protected static $logName = 'Nationality';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} Nationality";
    }
}
