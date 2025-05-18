<?php

namespace SegWeb;

use Illuminate\Database\Eloquent\Model;

class FileResults extends Model {
    protected $filltable = [
        'file_id',
        'line_number',
        'term_id'
    ];
    protected $guarded = ['id', 'created_at', 'update_at'];

    protected $table = 'file_results';
}
