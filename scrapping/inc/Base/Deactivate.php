<?php
/**
 * @link              Pierre VPCrazy
 * @since             1.0.0
 * @package           Scrapping
*/

namespace Inc\Base;

class Deactivate
{
	public static function deactivate() {
		flush_rewrite_rules();
	}
} 