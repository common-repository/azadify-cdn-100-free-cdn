<?php

/**
* Azadify_CDN_Rewriter
*
* @since 0.0.1
*/

class Azadify_CDN_Rewriter
{
	var $blog_url = null; // origin URL
	var $cdn_url = null; // CDN URL

	var $dirs = null; // included directories
	var $ext = null; // included directories
	var $excludes = array(); // excluded extensions
	var $https = false; // use CDN on HTTPS
	var $clearcache = false; // use CDN on HTTPS

    /**
	* constructor
	*
	* @since   0.0.1
	* @change  0.0.1
	*/

	function __construct($blog_url, $cdn_url, $dirs, $ext, array $excludes, $https, $clearcache) {
		$this->blog_url = $blog_url;
		$this->cdn_url = $cdn_url;
		$this->dirs	= $dirs;
		$this->ext = $ext;
		$this->excludes = $excludes;
		$this->https = $https;
		$this->clearcache = $clearcache;
	}


    /**
    * excludes assets that should not rewritten
    *
    * @since   0.0.1
    * @change  0.0.1
    *
    * @param   string  $asset  current asset
    * @return  boolean  true if need to be excluded
    */

	protected function exclude_asset(&$asset) {
		foreach ($this->excludes as $exclude) {
			if (!!$exclude && stripos($asset, $exclude) !== false) {
				return true;
			}
		}
		return false;
	}


    /**
    * rewrite url
    *
    * @since   0.0.1
    * @change  0.0.1
    *
    * @param   string  $asset  current asset
    * @return  string  updated url if not excluded
    */

    protected function rewrite_url($asset) {
		if ($this->exclude_asset($asset[0])) {
			return $asset[0];
		}
		$blog_url = $this->blog_url;
		
		if (strpos($asset[0],'?') !== false) {
			$asset[0] = str_replace('?','?'.$this->clearcache.'&',$asset[0]);
		} else {
			$asset[0] = $asset[0] . '?' . $this->clearcache;
		}

		$feed = NULL;
		if (is_feed()) {
			if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') {
				$feed = 'https:';
			} else {
				$feed = 'http:';
			}
		}
		
		if (strpos($asset[0], 'http://') !== false) {
			return str_replace('http:'.$blog_url, $feed.$this->cdn_url, $asset[0]);
		} elseif (strpos($asset[0], $blog_url) !== false) {
			return str_replace($blog_url, $feed.$this->cdn_url, $asset[0]);
		}

		return $this->cdn_url . $asset[0];
	}


    /**
    * get directory scope
    *
    * @since   0.0.1
    * @change  0.0.1
    *
    * @return  string  directory scope
    */

	protected function get_dir_scope() {
		$default_dirs = 'wp\-content|wp\-includes';
		
		if ($this->dirs == '') {
			return $default_dirs;
		}
		
		$input = explode(',', $this->dirs);

		if (count($input) < 1) {
			return $default_dirs;
		}

		return $default_dirs.'|'.implode('|', array_map('quotemeta', array_map('trim', $input)));
	}
	
	protected function get_ext_scope() {
		$default_ext = 'css|js|jpe?g|gif|png|bmp|webp|ico|cur|svgz?|eot|otf|tt[cf]|woff2?';
		
		if (!$this->ext) {
			return $default_ext;
		}
		
		$input = explode(',', str_replace('.','',$this->ext));

		if (count($input) < 1) {
			return $default_ext;
		}

		return $default_ext.'|'.implode('|', array_map('quotemeta', array_map('trim', $input)));
	}


    /**
    * rewrite url
    *
    * @since   0.0.1
    * @change  1.0.1
    *
    * @param   string  $html  current raw HTML doc
    * @return  string  updated HTML doc with CDN links
    */

	public function rewrite($html) {
		if (!$this->https && isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') {
			return $html;
		}

		$dirs = $this->get_dir_scope();
		$ext = $this->get_ext_scope();
        $blog_url = '(?:https?\:)?'.quotemeta($this->blog_url);
		
		$regex_rule = '#(?<=[(\"\'\s])';
		$regex_rule .= '(?:'.$blog_url.')?/?(?:\:443|\:80)?';
		$regex_rule .= '/?(?:((?:'.$dirs.')/[^\"\'\s)]+\.(?:'.$ext.')[^/\"\'\s)]*))(?=[\"\'\s)])#';
		
		$cdn_html = preg_replace_callback($regex_rule, array(&$this, 'rewrite_url'), $html);

		return $cdn_html;
	}
}
