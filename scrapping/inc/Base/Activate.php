<?php
/**
 * @link              Pierre VPCrazy
 * @since             1.0.0
 * @package           Scrapping
*/

namespace Inc\Base; 

class Activate
{
    public static function activate()
    {
        flush_rewrite_rules();
    }
}