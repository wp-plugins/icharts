<?php
if (! class_exists ( 'wv35v_application' )) :
	require_once dirname ( dirname ( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'base/application.php';
	class wv35v_application extends bv35v_application {
		/*********************************************************************
		 * Settings Getter, Setters & unsetters
		 *********************************************************************/
		public function &user($user_id = null) {
			return $this->cache ( 'wv35v_user', $user_id );
		}
		/*********************************************************************
		 * 
		 *********************************************************************/
		public function siteuri($array = false) {
			$siteurl = strtolower ( get_option ( 'siteurl' ) );
			$siteurl = explode ( '//', $siteurl );
			$protocol = $siteurl [0] . '//';
			$siteurl = $siteurl [1];
			$siteurl = explode ( '?', $siteurl );
			$siteurl = urldecode ( trim ( $siteurl [0], '/' ) );
			$return = array ('protocol' => $protocol, 'uri' => $siteurl );
			if (! $array) {
				$return = implode ( '', $return );
			}
			return $return;
		}
		public function pluginuri() {
			$return = substr ( $this->directory, strlen ( ABSPATH ) );
			$return = str_replace ( '\\', '/', $return );
			$return = $this->siteuri () . '/' . $return;
			return $return;
		}
		public function __construct($filename) {
			parent::__construct ( $filename );
			new wv35v_config ( $this );
			$this->legacy ();
		}
		public function relative_path($uri = null) {
			global $current_blog;
			if (null === $uri) {
				$uri = $_SERVER ['REQUEST_URI'];
			}
			//$uri = substr ( $uri , strlen ( $current_blog->path ) );
			$uri = substr ( $uri, strlen ( get_option ( 'site_url' ) ) );
			
			$uri = explode ( '?', $uri );
			$uri = $uri [0];
			$uri = rtrim ( $uri, '/' );
			$uri = '/' . rtrim ( $uri, '/' );
			return $uri;
		}
		public function &info() {
			return $this->cache ( 'wv35v_info' );
		}
		public function &mu() {
			return $this->cache ( 'wv35v_mu' );
		}
		
		public function &posts() {
			return $this->cache ( 'wv35v_posts' );
		}
		public function &legacy() {
			return $this->cache ( $this->classes->legacy );
		}
		public function &comments() {
			return $this->cache ( 'wv35v_comments' );
		}
	
	}










endif;