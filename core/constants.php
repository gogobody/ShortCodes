<?php

function ty_cookie_constants(){
    if ( ! defined( 'COOKIEHASH' ) ) {
        $siteurl = Helper::options()->siteUrl;
        if ( $siteurl ) {
            define( 'COOKIEHASH', md5( $siteurl ) );
        } else {
            define( 'COOKIEHASH', '' );
        }
    }
}
