<?php

/**
* Azadify_CDN_Settings
*
* @since 0.0.1
*/

class Azadify_CDN_Settings
{


	/**
	* register settings
	*
	* @since   0.0.1
	* @change  0.0.1
	*/

	public static function register_settings()
	{
		register_setting(
			'azadify_cdn',
			'azadify_cdn',
			array(
				__CLASS__,
				'validate_settings'
			)
		);
	}


	/**
	* validation of settings
	*
	* @since   0.0.1
	* @change  0.0.1
	*
	* @param   array  $data  array with form data
	* @return  array         array with validated values
	*/

	public static function validate_settings($data)
	{
		return array(
			'url'		=> esc_url($data['url']),
			'dirs'		=> esc_attr($data['dirs']),
			'ext'		=> esc_attr($data['ext']),
			'excludes'	=> esc_attr($data['excludes']),
			'https'		=> (int)($data['https']),
			'clearcache' => (int)($data['clearcache'])
		);
	}


	/**
	* add settings page
	*
	* @since   0.0.1
	* @change  0.0.1
	*/

	public static function add_settings_page()
	{
		$page = add_options_page(
			'Azadify CDN',
			'Azadify CDN',
			'manage_options',
			'azadify_cdn',
			array(
				__CLASS__,
				'settings_page'
			)
		);
	}


	/**
	* settings page
	*
	* @since   0.0.1
	* @change  0.0.1
	*
	* @return  void
	*/

	public static function settings_page()
	{ ?>
		<div class="wrap">
			<h2>
				<?php _e("Azadify CDN Settings", "cdn"); ?>
			</h2>
			
			<?php $options = Azadify_CDN::get_options(); ?>
			
			<?php 
			if($_POST['disable_azadify_cdn'] == 1) {
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
				Azadify_CDN::azadify_cdn_htaccess(false);
				echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>CDN disabled! You can easily re-enable it at any time.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			} elseif($_POST['clear_cdn'] == 1) {
				$options = wp_parse_args(
					get_option('azadify_cdn'),
					array(
						'url' => get_option('home'),
						'dirs' => '',
						'ext' => '',
						'excludes' => '',
						'https' => 1,
						'clearcache' => mt_rand(1,10000)
					)
				);
				$options['clearcache'] = mt_rand(1,10000);
				update_option(
					'azadify_cdn',
					$options
				);
				echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>CDN cleared! If you are using a caching plugin, you will need to clear that cache now.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			} elseif($_POST['activate_cdn'] == 1) {
				$cdn = Azadify_CDN::activate_azadify_cdn();
				if ($cdn['status'] == 'success') {
					$options = wp_parse_args(
						get_option('azadify_cdn'),
						array(
							'url' => '//'.$cdn['cdn'],
							'dirs' => '',
							'ext' => '',
							'excludes' => '',
							'https' => 1
						)
					);
					$options['clearcache'] = mt_rand(1,10000);
					$options['url'] = '//'.$cdn['cdn'];
					update_option(
						'azadify_cdn',
						$options
					);
					Azadify_CDN::azadify_cdn_htaccess(true,$cdn['cdn']);
					echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>Success! CDN is now enabled. If you are using a caching plugin, you will need to clear that cache now.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
				} elseif ($cdn['status'] == 'oossuccess') {
					update_option(
						'azadify_cdn',
						array(
							'url' => 'http://example.com',
							'dirs' => '',
							'ext' => '',
							'excludes' => '',
							'https' => '1',
							'clearcache' => mt_rand(1,10000)
						)
					);
					echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>Thank you for registering for Azadify CDN! Your CDN is not quite ready yet -- it will be available in 24-hours. We will send you an email when your CDN is ready.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
				} elseif ($cdn['status'] == 'fail') {
					echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>Looks like you haven\'t registered your domain for Azadify CDN yet. Head over to <a href="http://azadify.com/cdn/wordpress.php" target="_blank">Azadify CDN</a> to get started.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
				} else {
					echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>Something went wrong. Please try again. If you keep getting errors, <a href="mailto:cdn@azadify.com">contact us</a> for help.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
				}
			} elseif ($_POST['azadify_cdn_url']) {
				echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>Settings changed! If you are using a caching plugin, you will need to clear that cache now.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			}
			?>
			
			<?php if ('http://example.com' == $options['url']) { ?>
				<p class="description">
					<?php _e("Your CDN will be ready soon. We will send you an email when it is ready. Impatient? You can manually check on your CDN by clicking the button below.", "cdn"); ?>
				</p>
				
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					<table class="form-table" style="display:none;">
						<tr valign="top">
							<td>
								<input type="hidden" name="activate_cdn" id="activate_cdn" value="1">
							</td>                         
						</tr>
					</table>
					
					<?php submit_button('Activate CDN'); ?>

				</form>
			<?php } elseif ((get_option('home') == $options['url'] || $_POST['disable_azadify_cdn'] == 1) && $_POST['activate_cdn'] != 1) { ?>
				<p class="description">
					<?php _e("Your CDN is <span style='color:red'>inactive</a>.", "cdn"); ?>
				</p>
				
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					<table class="form-table" style="display:none;">
						<tr valign="top">
							<td>
								<input type="hidden" name="activate_cdn" id="activate_cdn" value="1">
							</td>                         
						</tr>
					</table>
					
					<?php submit_button('Activate CDN'); ?>

				</form>
			
			<?php } elseif ($_POST['azadify_cdn_advanced'] == 1 && $cdn['status'] != 'fail') { ?>

				<form method="post" action="options.php">
					<?php settings_fields('azadify_cdn'); ?>

					<table class="form-table">
						<input type="hidden" name="azadify_cdn[clearcache]" id="azadify_cdn_clearcache" value="<?php echo $options['clearcache']; ?>">
						<tr valign="top" style="display:none;">
							<th scope="row">
								<?php _e("CDN URL", "cdn"); ?>
							</th>
							<td>
								<fieldset>
									<label for="azadify_cdn_url">
										<input type="hidden" name="azadify_cdn[url]" id="azadify_cdn_url" value="<?php echo $options['url']; ?>" size="64" class="regular-text code" />
										<?php _e("", "cdn"); ?>
									</label>

									<p class="description">
										<?php _e("This is your CDN. Don't change it.", "cdn"); ?>
									</p>
								</fieldset>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e("Include Custom Directories", "cdn"); ?>
							</th>
							<td>
								<fieldset>
									<label for="azadify_cdn_dirs">
										<input type="text" name="azadify_cdn[dirs]" id="azadify_cdn_dirs" value="<?php echo $options['dirs']; ?>" size="64" class="regular-text code" />
									</label>
									
									<p>
										<?php _e("The following are already included: <code>wp-content,wp-includes</code>", "cdn"); ?>
									</p>

									<p class="description">
										<?php _e("Enter custom directories you want served via CDN. Enter the directories separated by", "cdn"); ?> <code>,</code>
									</p>
								</fieldset>
							</td>
						</tr>
						
						<tr valign="top">
							<th scope="row">
								<?php _e("Include Custom Extensions", "cdn"); ?>
							</th>
							<td>
								<fieldset>
									<label for="azadify_cdn_dirs">
										<input type="text" name="azadify_cdn[ext]" id="azadify_cdn_ext" value="<?php echo $options['ext']; ?>" size="64" class="regular-text code" />
									</label>
									
									<p>
										<?php _e("The following are already included: <code>.css,.js,.jpg,.jpeg,.gif,.png,.bmp,.webp,.ico,.cur,.svg,.svgz,.eot,.otf,.ttc,.ttf,.woff,.woff2</code>", "cdn"); ?>
									</p>

									<p class="description">
										<?php _e("Enter custom extensions you want served via CDN. Enter the extensions separated by", "cdn"); ?> <code>,</code>
									</p>
								</fieldset>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e("Exclude Custom Files or Extensions", "cdn"); ?>
							</th>
							<td>
								<fieldset>
									<label for="azadify_cdn_excludes">
										<input type="text" name="azadify_cdn[excludes]" id="azadify_cdn_excludes" value="<?php echo $options['excludes']; ?>" size="64" class="regular-text code" />
									</label>
									
									<!--<p>
										<?php //_e("The following are already excluded: <code>.php,.xml</code>", "cdn"); ?>
									</p>-->

									<p class="description">
										<?php _e("Enter full file names (ex: jquery-min.js) or file extensions (ex: .png). Enter the files or extensions separated by", "cdn"); ?> <code>,</code>
									</p>
								</fieldset>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e("CDN HTTPS", "cdn"); ?>
							</th>
							<td>
								<fieldset>
									<label for="azadify_cdn_https">
										<input type="checkbox" name="azadify_cdn[https]" id="azadify_cdn_https" value="1" <?php checked(1, $options['https']) ?> />
										<?php _e("Enable CDN for HTTPS connections (default: enabled).", "cdn"); ?>
									</label>

									<p class="description">
										<?php _e("", "cdn"); ?>
									</p>
								</fieldset>
							</td>
						</tr>
					</table>

					<?php submit_button(); ?>
				</form>
				
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					<table class="form-table" style="display:none;">
						<tr valign="top">
							<td>
								<input type="hidden" name="clear_cdn" id="clear_cdn" value="1">
							</td>                         
						</tr>
					</table>
					
					<?php submit_button('Purge CDN Cache'); ?>

				</form>
				
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					<table class="form-table" style="display:none;">
						<tr valign="top">
							<td>
								<input type="hidden" name="disable_azadify_cdn" id="disable_azadify_cdn" value="1">
							</td>                         
						</tr>
					</table>
					
					<?php submit_button('Disable CDN'); ?>

				</form>
			<?php } elseif ($cdn['status'] != 'fail') { ?>
				<p class="description">
					<?php _e("Your CDN is <span style='color:green'>enabled</a>.", "cdn"); ?>
				</p>
				
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					<table class="form-table" style="display:none;">
						<tr valign="top">
							<td>
								<input type="hidden" name="clear_cdn" id="clear_cdn" value="1">
							</td>                         
						</tr>
					</table>
					
					<?php submit_button('Purge CDN Cache'); ?>

				</form>
				
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					<table class="form-table" style="display:none;">
						<tr valign="top">
							<td>
								<input type="hidden" name="azadify_cdn_advanced" id="azadify_cdn_advanced" value="1">
							</td>                         
						</tr>
					</table>
					
					<?php submit_button('Advanced Settings'); ?>

				</form>
				
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					<table class="form-table" style="display:none;">
						<tr valign="top">
							<td>
								<input type="hidden" name="disable_azadify_cdn" id="disable_azadify_cdn" value="1">
							</td>                         
						</tr>
					</table>
					
					<?php submit_button('Disable CDN'); ?>

				</form>
			<?php } ?>
		</div><?php
	}
}
