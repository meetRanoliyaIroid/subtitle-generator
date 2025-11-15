<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_name',
        'video_path',
        'subtitle_path',
        'subtitle_languages',
        'video_with_subtitles_path',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'subtitle_languages' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
