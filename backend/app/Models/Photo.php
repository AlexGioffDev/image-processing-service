<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    protected $fillable = [
        'user_id',
        'path',
        'disk',
        'photo_folder',
        'original_name',
        'parent_photo_id',
        'transformations'
    ];

    protected $appends = ['url'];

    protected $casts = [
        'transformations' => 'array'
    ];

    // Relazione con User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relazione con la foto originale (per le trasformazioni)
    public function parent()
    {
        return $this->belongsTo(Photo::class, 'parent_photo_id');
    }

    // Relazione con tutte le versioni trasformate
    public function transformedVersions()
    {
        return $this->hasMany(Photo::class, 'parent_photo_id');
    }

    // URL della foto
    public function getUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    // Check se è una foto originale o trasformata
    public function isOriginal()
    {
        return is_null($this->parent_photo_id);
    }

    // Check se è una trasformazione
    public function isTransformed()
    {
        return !is_null($this->parent_photo_id);
    }

    public function getPhotoOriginal()
    {
        return $this->isTransformed() ? $this->parent : $this;
    }
}
