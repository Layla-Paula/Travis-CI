<?php

namespace SegWeb\Http\Controllers;

class Tools extends Controller {
    public static function contains($needle, $haystack) {
        return strpos($haystack, $needle) !== false;
    }

    public static function data($data){
        return date("d/m/Y H:i:s", strtotime($data));
    }

    public static function db_to_date($date){
		return implode('/',array_reverse(explode('-',$date)));
	}

	public static function db_to_date_time($date){
		return implode('/',array_reverse(explode('-',substr($date,0,10))))." ".substr($date,11,8);
	}
}
