<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasTranslations;

class AdministratorNotification extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'module_id',
        'title',
        'content',
        'system_title',
        'system_content',
        'meta_data',
        'image',
        'module',
        'type',
    ];

    public $translatable = [ 'title', 'content' ];
}
