<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ebook extends Model
{
    protected $table = 'ebook';

    protected $fillable = [
        'title',
        'file_title',
        'pdf_path',
        'folder_path',
        'share_token',
        'share_expires_at',
        'share_enabled',
        'ggg',
    ];
}
