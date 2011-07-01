<?php
if (! class_exists ( 'bv30v_application' )) :
	require dirname ( __FILE__ ) . '/base.php';
	class bv30v_application extends bv30v_base {
		public function setup_controllers() {
			$dirs = $this->loader ()->includepath ( array ('controllers' ) );
			foreach ( $dirs as $dir ) {
				$fs = new bv30v_fs ( $this, $dir );
				$controllers = $fs->dir ( '*.php' );
				foreach ( $controllers as $controller ) {
					$class = basename ( $controller, ".php" );
					if (! class_exists ( $class )) {
						include $controller;
						new $class ( $this );
					}
				}
			}
		}
		/*********************************************************************
		 * Settings
		 *********************************************************************/
		protected function xml_files() {
			return array ();
		}
		private $_xml = array ();
		private $_scope = null;
		public function scope($true = true) {
			$return = $this->_scope;
			if(!$true && null===$return)
			{
				$return = 'default';
			}
			return $return;
		}
		public function set_scope($scope = null) {
			$this->_scope = $scope;
		}
		public function xml_scopes() {
			if (isset ( $this->_xml [null] ['alternate'] )) {
				$return = array ();
				if (is_array ( $this->_xml [null] ['alternate'] )) {
					$return = array_keys ( $this->_xml [null] ['alternate'] );
				}
				if (! in_array ( 'default', $return )) {
					$return [] = 'default';
				}
				natsort ( $return );
				return $return;
			}
			return array ();
		}
		public function scopes() {
			return $this->xml_scopes ();
		}
		public function get_xml($key = null, $scope = null) {
			if($scope=='default')
			{
				$scope=null;
			}
			if (! isset ( $this->_xml [null] )) {
				$this->_xml [null] = bv30v_data_xml::settings ( $this->application ()->directory (), $this->xml_files () );
			}
			if (! isset ( $this->_xml [$scope] )) {
				$this->_xml [$scope] = bv30v_data_xml::merge_replace_recursive ( $this->_xml [null], $this->_xml [null] ['alternate'] );
			}
			if (null === $key) {
				return $this->_xml [$scope];
			}
			if (isset ( $this->_xml [$scope] [$key] )) {
				return $this->_xml [$scope] [$key];
			}
			return null;
		}
		// legacy
		public function all() {
			return $this->get_xml ( null, $this->scope () );
		}
		/*********************************************************************
		 * Settings Getter, Setters & unsetters
		 *********************************************************************/
		public function __get($key) {
			$method = '_' . $key;
			if (method_exists ( $this, $method )) {
				return $this->$method ();
			}
			$this->get_xml ( $key, $this->_scope );
			if (isset ( $this->_xml [$this->_scope] [$key] )) {
				return $this->_xml [$this->_scope] [$key];
			}
			return null;
		}
		public function __set($key, $value) {
			$method = '_' . $key;
			if (method_exists ( $this, $method )) {
				$this->$method ( $value );
				return;
			}
		}
		public function __isset($key) {
			$method = '_' . $key;
			if (method_exists ( $this, $method )) {
				return true;
			}
			$this->get_xml ( $key, $this->_scope );
			return isset ( $this->_xml [$this->_scope] [$key] );
		}
		public function __unset($key) {
		}
		/*********************************************************************
		 * 
		 *********************************************************************/
		
		private static $applications = array ();
		private $global = array ();
		public function applications() {
			return self::$applications;
		}
		public function version() {
			$return = $this->application ['version'] . '.v30v';
			if ($this->dodebug ()) {
				$return .= '.' . time ();
			}
			return $return;
		}
		public function directory() {
			return dirname ( $this->filename () );
		}
		public function siteuri($array = false) {
			$return = array ('protocol' => 'http://', 'uri' => 'test.com' );
			if (! $array) {
				return implode ( '', $return );
			}
			return $return;
		}
		private $_page = null;
		public function page() {
			if (null === $this->_page) {
				$this->set_page ();
			}
			return $this->_page;
		}
		public function set_page($page = null) {
			if (null === $page) {
				$this->_page = urldecode ( $this->relative_path () );
			} else {
				$this->_page = urldecode ( '/' . ltrim ( rtrim ( $page, '/' ), '/' ) );
			}
		}
		public function relative_path($uri = null) {
			if (null === $uri) {
				$uri = $_SERVER ['REQUEST_URI'];
			}
			$uri = explode ( '?', $uri );
			$uri = $uri [0];
			$uri = rtrim ( $uri, '/' );
			$project = dirname ( $this->filename () );
			$root_uri = $uri;
			while ( strpos ( $project, $root_uri ) === false ) {
				$root_uri = substr ( $root_uri, 0, strrpos ( $root_uri, '/' ) );
			}
			$uri = '/' . ltrim ( rtrim ( substr ( $uri, strlen ( $root_uri ) ), '/' ), '/' );
			return $uri;
		}
		private $_filename = null;
		public function filename() {
			return $this->_filename;
		}
		public function plugin_directory() {
			return dirname ( $this->_filename );
		}
		protected $handler = null;
		public function __construct($filename) {
			parent::__construct ( $this );
			$this->_filename = $filename;
			//load just enough classes to get the settings
			if (! class_exists ( 'bv30v_data_xml' )) {
				require_once $this->directory () . '/library/base/data/xml.php';
			}
			if (! class_exists ( 'bv30v_loader' )) {
				require_once $this->directory () . '/library/base/loader.php';
			}
			// get the settings
			$this->get_xml ();
			// load the classes specified in the classes
			$this->_loader = new bv30v_loader ( $this );
			foreach ( $this->_xml [null] ['application'] ['classes'] as $library ) {
				foreach ( ( array ) $library as $class ) {
					$class=trim($class);
					if(!empty($class))
					{
						$this->_loader->load_class ( $class );
					}
				}
			}
			$this->setup_controllers ();
			self::$applications [] = $this;
		}
		private $_loader = null;
		public function loader() {
			return $this->_loader;
		}
		protected function legacy($data) {
			return $data;
		}
		public function refresh() {
			$this->all ();
		}
	}

















endif;