<?php

namespace SegWeb;

use Illuminate\Database\Eloquent\Model;

class TermTypes extends Model {
    protected $filltable = [
        'term_type',
        'color',
    ];
    protected $guarded = ['id', 'created_at', 'update_at'];

    protected $table = 'term_types';
}
