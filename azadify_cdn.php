<?php
/*
Plugin Name: Azadify CDN
Text Domain: cdn
Plugin URI: http://azadify.com/cdn/wordpress.php
Description: Use Azadify's 100% free CDN
Version: 1.2.2
Author: Azadify
Author URI: http://azadify.com/cdn
GPLv2 or later
*/

/*

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/


/* Check & Quit */
defined('ABSPATH') OR exit;


/* constants */
define('AZADIFY_CDN_FILE', __FILE__);
define('AZADIFY_CDN_DIR', dirname(__FILE__));
define('AZADIFY_CDN_BASE', plugin_basename(__FILE__));
define('AZADIFY_CDN_MIN_WP', '3.8');


/* loader */
add_action(
	'plugins_loaded',
	array(
		'Azadify_CDN',
		'instance'
	)
);


/* uninstall */
register_uninstall_hook(
	__FILE__,
	array(
		'Azadify_CDN',
		'handle_uninstall_hook'
	)
);

register_deactivation_hook(
	__FILE__,
	array(
		'Azadify_CDN',
		'handle_deactivation_hook'
	)
);


/* activation */
register_activation_hook(
	__FILE__,
	array(
		'Azadify_CDN',
		'handle_activation_hook'
	)
);


/* autoload init */
spl_autoload_register('AZADIFY_CDN_autoload');

/* autoload funktion */
function AZADIFY_CDN_autoload($class) {
	if ( in_array($class, array('Azadify_CDN', 'Azadify_CDN_Rewriter', 'Azadify_CDN_Settings')) ) {
		require_once(
			sprintf(
				'%s/inc/%s.class.php',
				AZADIFY_CDN_DIR,
				strtolower($class)
			)
		);
	}
}
