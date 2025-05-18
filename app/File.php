<?php

namespace SegWeb;

use Illuminate\Database\Eloquent\Model;

class File extends Model {
    protected $filltable = [
        'user_id',
        'file_path',
        'original_file_name',
        'type',
        'repository_id'
    ];
    protected $guarded = ['id', 'created_at', 'update_at'];

    protected $table = 'files';
}
