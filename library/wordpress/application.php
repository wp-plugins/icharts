<?php
if (! class_exists ( 'wv30v_application' )) :
	require_once dirname ( dirname ( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'base/application.php';
	class wv30v_application extends bv30v_application {
		/*********************************************************************
		 * Settings Getter, Setters & unsetters
		 *********************************************************************/
		public function __get($key) {
			$method = '_' . $key;
			if (method_exists ( $this, $method )) {
				return $this->$method ();
			}
			$this->get_meta ( $key, $this->scope () );
			if (isset ( $this->_meta [$this->scope ()] [$key] )) {
				return $this->_meta [$this->scope ()] [$key];
			}
			return parent::__get ( $key );
		}
		public function __isset($key) {
			$method = '_' . $key;
			if (method_exists ( $this, $method )) {
				return true;
			}
			$this->get_xml ( $key, $this->scope () );
			if (isset ( $this->_xml [$this->scope ()] [$key] )) {
				return true;
			}
			return parent::__isset ( $key );
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
		public function legacy_migrate($delete = true) {
			$options = $this->application ()->saved_data_locations ();
			$old_opts = $this->options_name ( false );
			foreach ( $options as $option ) {
				$this->set_options_name ( $option ['name'], null, false );
				$opt = $this->get ();
				if (! empty ( $opt )) {
					$this->_meta_obj->update ( $opt, null, $option ['name'] );
					if ($delete) {
						$this->delete ();
					}
				}
			}
			$this->set_options_name ( $old_opts, null, false );
		}
		public function pluginuri() {
			$return = substr ( $this->directory (), strlen ( ABSPATH ) );
			$return = str_replace ( '\\', '/', $return );
			$return = $this->siteuri () . '/' . $return;
			return $return;
		}
		private $_meta_obj = null;
		private $_tables = array ();
		public function table($tbl = null) {
			if (! isset ( $this->_tables [$tbl] )) {
				$this->_tables [$tbl] = new wv30v_data_table ( $tbl );
			}
			return $this->_tables [$tbl];
		}
		private $_meta = array ();
		public function meta_scopes() {
			return $this->_meta_obj->scopes ();
		}
		public function scopes() {
			$xml_scopes = $this->xml_scopes ();
			$scopes = $this->meta_scopes ();
			foreach ( $xml_scopes as $scope ) {
				$scopes [] = $scope;
			}
			$scopes = array_unique ( $scopes );
			natsort ( $scopes );
			return $scopes;
		}
		public function get_meta($key = null, $scope = null, $just_meta = false) {
			// check that meta has been set up
			if (null === $this->_meta_obj) {
				return;
			}
			if ($just_meta) {
				return $this->_meta_obj->get ( $key, $scope );
			}
			$xml = $this->get_xml ( $key, $scope );
			if (! isset ( $this->_meta [$scope] [$key] )) {
				$meta = $this->get_meta ( $key, $scope, true );
				$overide=false;
				if($key!='__overide__')
				{
					$overide = $this->get_meta('__overide__');
					$overide = in_array($key,(array)$overide);
				}
				if(!$overide)
				{
					$meta = bv30v_data_xml::merge_replace_recursive ( $xml, $meta );
				}
				$this->_meta [$scope] [$key] = $this->filterMeta($meta,$key);
			}
			return $this->_meta [$scope] [$key];
		}
		private function filterMeta($data,$filter)
		{
			$method = 'filterMeta_'.$filter;
			if(method_exists($this,$method))
			{
				$data = $this->$method($data);
			}
			return $data;
		}
		public function get_opt($key, $scope = null) {
			if (! isset ( $this->_opt [$scope] [$key] )) {
				$alt = $this->get_alt ( $key, $scope );
				$options_name = $this->application ()->options_name ( false );
				$this->set_options_name ( $key, null, false );
				$opt = $this->get ( $key );
				$this->set_options_name ( $options_name ['sub'], null, false );
				$this->_opt [$scope] [$key] = bv30v_data_xml::merge_replace_recursive ( $alt, $opt [$key] );
			}
			return $this->_opt [$scope] [$key];
		}
		public function __construct($filename) {
			parent::__construct ( $filename );
			$this->_meta_obj = new wv30v_data_meta ( $this->application ['name'], $this->application ['slug'], $this->dodebug () );
			$this->info = new wv30v_info ( $this );
			add_action ( "plugins_loaded", array ($this, "setup" ) );
			new wv30v_config ( $this );
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
		public function setup() {
			load_plugin_textdomain ( get_class ( $this ), false, dirname ( plugin_basename ( $this->application ()->filename () ) ) . "/languages/" );
		}
		private $info = null;
		public function info() {
			return $this->info;
		}
		//-----------------------------------------------------------------------------------------
		protected $options_name = null;
		public function set_options_name($sub = 'default', $base = null, $refresh = true) {
			if (null === $base) {
				$base = '';
				if (isset ( $this->options_name ['base'] )) {
					$base = $this->options_name ['base'];
				} else {
					$base = $this->application ['slug'];
				}
			}
			$this->options_name = array ('base' => $base, 'sub' => $sub );
			if ($refresh) {
				$this->refresh ();
			}
		}
		public function option($option = null, $refresh = true) {
			if (null !== $option) {
				$this->set_options_name ( $option, null, $refresh );
				$return = $option;
			} else {
				$return = $this->options_name ( false );
				$return = $return ['sub'];
			}
			return $return;
		}
		public function options_name($flat = true) {
			if (null === $this->options_name) {
				$this->set_options_name ();
			}
			$option = $this->options_name;
			if ($flat) {
				$option = implode ( '_', $this->options_name );
			}
			return $option;
		}
		protected $saved_data_locations = null;
		public function special_setting($name) {
			return (strpos ( $name, '_' ) === 0);
		}
		public function data_locations($exclude_hidden=true) {
			$table = new wv30v_data_table ();
			$tables = $table->show_tables ( $this->settings ()->application ['slug'] . '_' );
			natsort ( $tables );
			$scopes = $this->settings ()->scopes ();
			natsort ( $scopes );
			$locs = array ();
			foreach ( $scopes as $scope ) {
				if(!$exclude_hidden || strpos($scope,'_')!==0)
				{
					$locs [$scope] ['name'] = $scope;
					$locs [$scope] ['table'] = null;
					$locs [$scope] ['count'] = null;
				}
			}
			foreach ( $tables as $tab ) {
				$name = explode ( $this->settings ()->application ['slug'] . '_', $tab );
				$name = $name [1];
				if (! isset ( $locs [$name] ['name'] )) {
					$locs [$name] ['name'] = null;
				}
				$locs [$name] ['table'] = $tab;
				$locs [$name] ['count'] = $table->count ( $tab );
			}
			return $locs;
		}
		public function saved_data_locations($add_default = true) {
			if (is_null ( $this->saved_data_locations )) {
				$type = $this->options_name ( false );
				$type = $type ['base'];
				$options = new wv30v_data_table ( 'options' );
				$sql = "SELECT `option_name` from %s WHERE option_name LIKE  '%s_%%' AND option_value != '%s';";
				$sql = sprintf ( $sql, $options->name (), $type, $type );
				$results = $options->execute ( $sql );
				$saved_options = array ();
				foreach ( ( array ) $results as $key => $value ) {
					$new_form = substr ( $value ['option_name'], strlen ( $type ) + 1 );
					$saved_options [$new_form] = $new_form;
				}
				$return = array ();
				foreach ( $saved_options as $sub ) {
					if (! $this->special_setting ( $sub ) || $this->dodebug ()) {
						$return [' ' . $sub] = array ('name' => $sub, 'table' => '', 'records' => '', 'table_name' => '' );
					}
				}
				if ($add_default) {
					$return [' default'] ['name'] = 'default';
					$return [' default'] ['table'] = '';
					$return [' default'] ['records'] = '';
					$return [' default'] ['table_name'] = '';
				}
				//				$table = new wv30v_data_table (  );
				//				$tables = $table->list_tables ();
				//
				//				foreach ( $tables as $table ) {
				//					//$table['url'] = $this->control_url ( '/' . $this->settings->type() . '/' . $table ['name'] . '.csv' );
				//					if (isset ( $return [' ' . $table ['name']] )) {
				//						$return [' ' . $table ['name']] = $table;
				//						$return [' ' . $table ['name']] ['table_name'] = $table ['name'];
				//					} else {
				//						$table_name = $table ['name'];
				//						$table ['name'] = '';
				//						$return [$table ['table']] = $table;
				//						$return [$table ['table']] ['table_name'] = $table_name;
				//					}
				//				}
				ksort ( $return );
				$this->saved_data_locations = $return;
			}
			return $this->saved_data_locations;
		}
		public function update_global($key, $value) {
			$options_name = $this->options_name ( false );
			$this->set_options_name ( '_global', null, false );
			$this->set ( array ($key => $value ), $key );
			//$this->_settings [$key] = $value;
			$this->set_options_name ( $options_name ['sub'], null, false );
		}
		protected function get_data() {
			$return = parent::get_data ();
			$options_name = $this->options_name ( false );
			$this->set_options_name ( '_global', null, false );
			$options = $this->get ();
			if (is_array ( $options )) {
				$return [] = $options;
			}
			$this->set_options_name ( $options_name ['sub'], null, false );
			$options = $this->get ();
			if (is_array ( $options )) {
				$return [] = $options;
			}
			return $return;
		}
		protected function legacy_move($old, $section = null, $new = null) {
			$options_name = null;
			if (null !== $new) {
				$options_name = $this->options_name ( false );
				$this->set_options_name ( $new );
			}
			$old_data = $this->decode ( get_option ( $old ) );
			if (is_array ( $old_data )) {
				$new_data = $this->decode ( get_option ( $this->options_name () ) );
				if (null !== $section) {
					if (! isset ( $old_data [$section] )) {
						foreach ( $old_data as $key => $value ) {
							unset ( $new_data [$key] );
						}
						$old_data = array ($section => $old_data );
					}
				}
				if (! empty ( $new_data )) {
					$new_data = bv30v_data_xml::merge_replace_recursive ( $new_data, $old_data );
				} else {
					$new_data = $old_data;
				}
				if ($old != $this->options_name ()) {
					delete_option ( $old );
				}
				$this->set ( $new_data );
			}
			if (null !== $options_name) {
				$this->set_options_name ( $options_name ['sub'] );
			}
		}
		public function prepare(&$value) {
			$value = stripslashes ( $value );
		}
		protected function prepare_data($data) {
			return $data;
		}
		public function encode($data) {
			$data = serialize ( $data );
			$data = gzcompress ( $data, 9 );
			$data = base64_encode ( $data );
			return $data;
		}
		public function copy($to, $from = null) {
			if (null !== $from) {
				$this->set_options_name ( $from );
			}
			$data = $this->get ();
			$this->set_options_name ( $to );
			$this->set ( $data );
		}
		public function decode($data) {
			if (! is_array ( $data ) && ! empty ( $data )) {
				$data = base64_decode ( $data );
				$data = gzuncompress ( $data );
				$data = unserialize ( $data );
			}
			if (is_array ( $data )) {
				array_walk_recursive ( $data, array ($this, 'prepare' ) );
			}
			return $data;
		}
		public function get() {
			$data = get_option ( $this->options_name () );
			$data = $this->decode ( $data );
			return $data;
		}
		public function set($data, $key = null) {
			$this->_meta_obj->update ( $data, $key );
			
			return;
			$olddata = $data;
			if (null !== $option) {
				$olddata = $this->get ();
				if (! isset ( $data [$option] )) {
					$data [$option] = '';
				}
				$olddata [$option] = $data [$option];
			}
			$data = $this->encode ( $olddata );
			update_option ( $this->options_name (), $data );
		}
		public function delete($key=null,$scope=null) {
			$this->_meta_obj->delete($key,$scope);
		}
		public function delete_table($tables) {
			//			$tableObj = new wv30v_table ();
		//			foreach ( ( array ) $tables as $table ) {
		//				$tableObj->drop ( $table );
		//			}
		}
		public function old_post($option = null) {
			if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
				$post = $this->prepare_data ( $_POST );
				$this->set ( $post, $option );
				$this->refresh ();
			}
			$return = $this->all ();
			if (null !== $option) {
				$return = $return [$option];
			}
			return $return;
		}
		public function post($key = null) {
			if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
				$post = $this->prepare_data ( $_POST );
				//$this->debug ( $post );
				if (null !== $key) {
					$post = $post [$key];
				}
				$this->_meta_obj->update ( $post, $key );
			}
			$return = $this->get_meta ( $key );
			return $return;
		}
	
	}


endif;