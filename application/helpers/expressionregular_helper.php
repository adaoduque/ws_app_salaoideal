<?php

function  isAlphaNumeric( $var ) {

    $regex = "/^[A-Za-z0-9ÁáÂâÀàÅåÃãÄäÉéÊêÈèËëðÍíÎîÌìÏïÓóÔôÒòÕõÖöÚúÛûÙùÜüÇçÑñ ]+$/";

    return (Boolean) preg_match( $regex, sqlInjection( $var ) );

}

function isEmail( $email ) {

    $regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/";

    return (Boolean) preg_match( $regex, $email );

}

function  sqlInjection( $var ) {

    $var = preg_replace("/'|from|select|insert|delete|where|drop table|show tables|#|\*|--|/", '', $var);

    return $var;

}