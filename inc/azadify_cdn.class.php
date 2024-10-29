<?php

/**
* CDN_Enabler
*
* @since 0.0.1
*/

class Azadify_CDN
{


	/**
	* pseudo-constructor
	*
	* @since   0.0.1
	* @change  0.0.1
	*/

	public static function instance()
	{
		new self();
	}


	/**
	* constructor
	*
	* @since   0.0.1
	* @change  0.0.1
	*/

	public function __construct()
	{

        /* CDN rewriter hook */
        add_action(
            'template_redirect',
            array(
                __CLASS__,
                'handle_rewrite_hook'
            )
        );

		/* Filter */
		if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) OR (defined('DOING_CRON') && DOING_CRON) OR (defined('DOING_AJAX') && DOING_AJAX) OR (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) ) {
			//return;
		}

		/* BE only */
		if ( ! is_admin() ) {
			//return;
		}

		/* Hooks */
		add_action(
			'admin_init',
			array(
				'Azadify_CDN_Settings',
				'register_settings'
			)
		);
		add_action(
			'admin_menu',
			array(
				'Azadify_CDN_Settings',
				'add_settings_page'
			)
		);
        add_filter(
            'plugin_action_links_' .AZADIFY_CDN_BASE,
            array(
                __CLASS__,
                'add_action_link'
            )
        );

        /* admin notices */
        add_action(
            'all_admin_notices',
            array(
                __CLASS__,
                'azadify_cdn_requirements_check'
            )
        );
		
		add_action(
            'admin_init',
            array(
                __CLASS__,
                'azadify_cdn_update_check'
            )
        );
		
	}



	/**
	* add action links
	*
	* @since   0.0.1
	* @change  0.0.1
	*
	* @param   array  $data  alreay existing links
	* @return  array  $data  extended array with links
	*/

	public static function add_action_link($data)
	{
		// check permission
		if ( ! current_user_can('manage_options') ) {
			return $data;
		}

		return array_merge(
			$data,
			array(
				sprintf(
					'<a href="%s">%s</a>',
					add_query_arg(
						array(
							'page' => 'azadify_cdn'
						),
						admin_url('options-general.php')
					),
					__("Settings")
				)
			)
		);
	}


	/**
	* run uninstall hook
	*
	* @since   0.0.1
	* @change  0.0.1
	*/

	public static function handle_uninstall_hook()
	{
        delete_option('azadify_cdn');
	}
	
	public static function handle_deactivation_hook()
	{
		$options = wp_parse_args(
			get_option('azadify_cdn'),
			array(
				'url' => get_option('home'),
				'dirs' => '',
				'ext' => '',
				'excludes' => '',
				'https' => 1
			)
		);
		$options['clearcache'] = mt_rand(1,10000);
		$options['url'] = get_option('home');
		update_option(
			'azadify_cdn',
			$options
		);
	}


	/**
	* run activation hook
	*
	* @since   0.0.1
	* @change  1.0.2
	*/

	public static function handle_activation_hook() {
        add_option(
            'azadify_cdn',
            array(
                'url' => get_option('home'),
                'dirs' => '',
				'ext' => '',
                'excludes' => '',
                'https' => '1',
				'clearcache' => mt_rand(1,10000)
            )
        );
	}


	/**
	* check plugin requirements
	*
	* @since   0.0.1
	* @change  0.0.1
	*/

	public static function azadify_cdn_requirements_check() {
		// WordPress version check
		if ( version_compare($GLOBALS['wp_version'], CDN_ENABLER_MIN_WP.'alpha', '<') ) {
			show_message(
				sprintf(
					'<div class="error"><p>%s</p></div>',
					sprintf(
						__("Azadify CDN is optimized for WordPress %s. Please disable the plugin or upgrade your WordPress installation (recommended).", "cdn"),
						AZADIFY_CDN_MIN_WP
					)
				)
			);
		}
	}


	/**
	* return plugin options
	*
	* @since   0.0.1
	* @change  1.0.2
	*
	* @return  array  $diff  data pairs
	*/

	public static function get_options()
	{
		return wp_parse_args(
			get_option('azadify_cdn'),
			array(
                'url' => get_option('home'),
                'dirs' => '',
				'ext' => '',
                'excludes' => '',
                'https' => 1,
				'clearcache' => 1
			)
		);
	}
	
	public static function azadify_cdn_update_check() {
		if (get_option('azadify_cdn_version') != '1.2.2') {
			$options = self::get_options();
			if (get_option('home') == $options['url'] || $options['url'] == 'http://example.com') {
				self::azadify_cdn_htaccess(false);
			} else {
				self::azadify_cdn_htaccess(true);
			}
			$options['clearcache'] = mt_rand(1,10000);
			update_option(
				'azadify_cdn',
				$options
			);
			update_option('azadify_cdn_version','1.2.2',false);
		}
	}
	
	public static function activate_azadify_cdn() {
		if (function_exists('curl_version')) {
			$ch = curl_init('https://azadify.com/cdn/api/api_v1.php?site='.str_replace('www.','',parse_url(get_site_url(), PHP_URL_HOST)));
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
			$cdn = curl_exec($ch);
		} else {
			$cdn = file_get_contents('https://azadify.com/cdn/api/api_v1.php?site='.str_replace('www.','',parse_url(get_site_url(), PHP_URL_HOST)));
		}
		$cdn = json_decode($cdn, true);
		return $cdn;
	}
	
	public static function azadify_cdn_htaccess($status,$cdn = null) {
		if ($GLOBALS['is_apache']) {
			$htaccess = get_home_path().'.htaccess';
			
			if (is_writable($htaccess)) {
				$current = file_get_contents($htaccess);
				$current = preg_replace('/# BEGIN Azadify CDN(.*)# END Azadify CDN/isU','',$current);
				$current = str_replace("\n\n","\n",$current);
				
				$new = NULL;
				if ($status === true) {
					$new .= '# BEGIN Azadify CDN' . PHP_EOL;
					$new .= '<IfModule mod_setenvif.c>' . PHP_EOL;
					$new .= '<IfModule mod_headers.c>' . PHP_EOL;
					$new .= '<FilesMatch "\.(cur|gif|png|bmp|jpe?g|svgz?|ico|webp)$">' . PHP_EOL;
					$new .= 'SetEnvIf Origin ":" IS_CORS' . PHP_EOL;
					$new .= 'Header set Access-Control-Allow-Origin "*" env=IS_CORS' . PHP_EOL;
					$new .= '</FilesMatch>' . PHP_EOL;
					$new .= '</IfModule>' . PHP_EOL;
					$new .= '</IfModule>' . PHP_EOL;
					$new .= '<FilesMatch "\.(eot|otf|tt[cf]|woff2?)$">' . PHP_EOL;
					$new .= '<IfModule mod_headers.c>' . PHP_EOL;
					$new .= 'Header set Access-Control-Allow-Origin "*"' . PHP_EOL;
					$new .= '</IfModule>' . PHP_EOL;
					$new .= '</FilesMatch>' . PHP_EOL;
					$new .= '# END Azadify CDN' . PHP_EOL . PHP_EOL;
				}
				$new = $new.$current;
				
				file_put_contents($htaccess,$new);
			}
		}
	}


    /**
	* run rewrite hook
	*
	* @since   0.0.1
	* @change  1.0.2
	*/

    public static function handle_rewrite_hook()
    {
        $options = self::get_options();

        // check if origin equals cdn url
        if (get_option('home') == $options['url'] || $options['url'] == 'http://example.com') {
    		return;
    	}

        $excludes = array_map('trim', explode(',', $options['excludes']));

    	$rewriter = new Azadify_CDN_Rewriter(
    		str_replace(array('http:','https:'),'',get_option('home')),
    		$options['url'],
    		$options['dirs'],
			$options['ext'],
    		$excludes,
    		$options['https'],
			$options['clearcache']
    	);
    	ob_start(
            array(&$rewriter, 'rewrite')
        );
    }
}
