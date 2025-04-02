<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'collection_name',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'order_column',
    ];

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_name);
    }
    public function getPathAttribute()
    {
        return storage_path('app/public/' . $this->file_name);
    }
}
