<?php

class Mask {
    public static function hashGuest( $code ) {
        return hash('sha256', '*a,/18dizpfb' . $code . 'lP1cyr9;la]');
    }

    public static function validHash( $code, $hash ) {
    	$sha256  =  hash('sha256', '*a,/18dizpfb' . $code . 'lP1cyr9;la]');
        return $sha256 == $hash ? true : false;
    }    
}