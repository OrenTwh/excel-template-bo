<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'module',
        'name',
        'file',
        'type',
        'status',
    ];

    public function getPathAttribute() {
        return $this->attributes['file'] ? asset( 'storage/' . $this->attributes['file'] ) : null;
    }
}
