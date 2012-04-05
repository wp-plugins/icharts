<?php
class wv35v_action extends bv35v_action {
	public function SupportActionMeta($return) {
		if (! isset ( $this->application ()->wordpress->wpslug )) {
			$return ['hide'] = 1;
		} else {
			$return ['probono'] = true;
			$return ['title'] = 'Support Forum';
			$return ['url'] = "http://wordpress.org/tags/" . $this->application ()->wordpress->wpslug . "?forum_id=10";
			$return ['classes'] [] = 'v35v_forum';
			$return ['priority'] = 10;
		}
		return $return;
	}
	public function debugActionMeta($return) {
		if (! $this->dodebug ()) {
			$return ['hide'] = 1;
		}
		$return ['title'] = 'Debug';
		$return ['classes'] [] = 'v35v_debug';
		$return ['priority'] = 100;
		return $return;
	}
	public function debugAction() {
		if (isset ( $_POST ['debug_settings'] ['wipe'] ) && ! empty ( $_POST ['debug_settings'] ['wipe'] )) {
			$this->wipe ();
		}
		$this->view->debug_settings = $this->application ()->data ( '_debug_settings' )->post ( 'debug_settings' );
		if (isset ( $_POST ['debug_settings'] ['test'] ) && ! empty ( $_POST ['debug_settings'] ['test'] )) {
			$this->test_data ();
		}
		$return = $this->render_script ( 'common/debug.phtml' );
		return $return;
	}
	public function test_data() {
		$posts = $this->application ()->data ( '_test_data' )->posts;
		foreach ( $posts as $post ) {
			$check = get_page_by_title ( $post ['post_title'], OBJECT, $post ['post_type'] );
			if (! $check) {
				wp_insert_post ( $post );
			}
		}
	}
	public function wipe() {
		$this->application ()->table ( 'posts' )->truncate ();
		$this->application ()->table ( 'comments' )->truncate ();
		$this->application ()->table ( 'links' )->truncate ();
		$this->application ()->table ( 'postmeta' )->truncate ();
		$this->application ()->table ( 'terms' )->truncate ();
		$this->application ()->table ( 'term_taxonomy' )->truncate ();
		$this->application ()->table ( 'term_relationships' )->truncate ();
		$this->application ()->table ( 'commentmeta' )->truncate ();
	}
	public function PluginActionMeta($return) {
		if (! isset ( $this->application ()->wordpress->uri )) {
			$return ['hide'] = 1;
		} else {
			$return ['probono'] = true;
			$return ['title'] = 'Plugin Site';
			$return ['url'] = $this->application ()->wordpress->uri;
			$return ['classes'] [] = 'v35v_home';
			$return ['priority'] = 10;
		}
		return $return;
	}
	public function DonateActionMeta($return) {
		if (! isset ( $this->application ()->wordpress->donate_link )) {
			$return ['hide'] = 1;
		} else {
			$return ['link_name'] = $return ['title'];
			$return ['probono'] = true;
			$return ['url'] = $this->application ()->wordpress->donate_link;
			$return ['classes'] [] = 'v35v_donate';
			$return ['priority'] = 10;
		}
		return $return;
	}
	public function control_url($control) {
		$return = trim ( get_bloginfo ( 'url' ), '/' );
		if (get_option ( 'permalink_structure' ) == '') {
			$return .= '/?wppage=';
			$control = str_replace ( '?', '&', $control );
		} else {
			$return .= '/';
		}
		return $return . $control;
	
	}
	public function dashboard($srch_menu, $srch_sub = null) {
		global $menu;
		$srch_menu = array ($srch_menu, __ ( $srch_menu ) );
		$return = new stdClass ();
		$return->menu = false;
		$return->submenu = false;
		$return->url = false;
		foreach ( ( array ) $menu as $item ) {
			if (in_array ( $item [0], $srch_menu )) {
				$return->menu = $item;
				if (null !== $srch_sub) {
					global $submenu;
					$srch_sub = array ($srch_sub, __ ( $srch_sub ) );
					foreach ( ( array ) $submenu [$item [2]] as $sub_item ) {
						if ((in_array ( $sub_item [0], $srch_sub ))) {
							$return->submenu = $sub_item;
						}
					}
				}
			}
		}
		if (null !== $return->menu) {
			$return->url = $return->menu [2];
		}
		if (null !== $return->submenu) {
			$menu_a = explode ( '?', $return->submenu [2] );
			if (pathinfo ( $menu_a [0], PATHINFO_EXTENSION ) == 'php') {
				$return->url = $return->submenu [2];
			} else {
				$p_menu_a = explode ( '?', $return->menu [2] );
				if (pathinfo ( $p_menu_a [0], PATHINFO_EXTENSION ) == 'php') {
					$return->url = $return->menu [2];
					if (count ( $p_menu_a ) == 1) {
						$return->url .= '?';
					} else {
						$return->url .= '&';
					}
					$return->url .= 'page=' . $return->submenu [2];
				} else {
					$return->url = 'admin.php?page=' . $return->submenu [2];
				}
			}
		}
		return $return;
	}
	protected function basic_auth() {
		$credentials = array ();
		if (array_key_exists ( 'PHP_AUTH_USER', $_SERVER ) && array_key_exists ( 'PHP_AUTH_PW', $_SERVER )) {
			$credentials ['user_login'] = $_SERVER ['PHP_AUTH_USER'];
			$credentials ['user_password'] = $_SERVER ['PHP_AUTH_PW'];
		}
		$user = wp_signon ( $credentials );
		if (is_wp_error ( $user )) {
			header ( 'WWW-Authenticate: Basic realm="' . $_SERVER ['SERVER_NAME'] . '"' );
			header ( 'HTTP/1.0 401 Unauthorized' );
			die ();
		}
	}
	protected function set_view() {
		$this->view = new wv35v_view ( $this->application () );
	}
	protected function dispatch() {
		$this->view->args = array ();
		if (count ( func_get_args () ) > 0) {
			$this->view->args = func_get_args ();
		} else {
			$this->view->args [] = null;
		}
		if (is_array ( $this->view->selected )) {
			$args = $this->view->args;
			$return = call_user_func_array ( array ($this, $this->view->selected ['action'] ), $args );
			if (null !== $return) {
				$this->view->args [0] = $return;
			}
		}
		$return = $this->render_script ( $this->view->selected ['raw_title'] . '.phtml' );
		if (null !== $return) {
			$this->view->args [0] .= $return;
		}
		return $this->view->args [0];
	}
	/*******************************************************************
	 * Init Functions
	 *******************************************************************/
	public function __construct(&$application) {
		parent::__construct ( $application );
		//$this->debug('here');
		//add_action('plugins_loaded',array($this,'setup_wpactions'));
		$this->setup_wpactions ();
	}
	/*******************************************************************
	 * Setup Aciton Types
	 *******************************************************************/
	protected function setup_action() {
		// only setup for dashboard
		if (! is_admin ()) {
			return;
		}
		parent::setup_action ();
	}
	public function setup_wpactions() {
		$this->add_action_type ( 'wpaction', 'WPaction' );
		foreach ( ( array ) $this->get_actions ( 'wpaction' ) as $action ) {
			add_action ( $action ['raw_action_title'], $this->callback_filter ( $action ['action_callback'] ), $action ['priority'] );
		}
	}
	private function setup_wpfilters() {
		$this->add_action_type ( 'wpfilter', 'WPfilter' );
		foreach ( ( array ) $this->get_actions ( 'wpfilter' ) as $action ) {
			$numargs = 5;
			add_filter ( $action ['raw_action_title'], $this->callback_filter ( $action ['action_callback'] ), $action ['priority'], $numargs );
		}
	}
	private function setup_wppages() {
		// don't setup for dashboard
		if (is_admin ()) {
			return;
		}
		$this->add_action_type ( 'wppage', 'WPpage' );
		if (get_option ( 'permalink_structure' ) != '') {
			global $wp_rewrite;
			$flush = false;
			foreach ( ( array ) $this->get_actions ( 'wppage' ) as $action ) {
				if (! in_array ( 'index.php?wppage=' . $action ['slug'], $wp_rewrite->wp_rewrite_rules () )) {
					$flush = true;
				}
			}
			if ($flush) {
				$wp_rewrite->flush_rules ();
			}
		}
	}
	private function setup_wpnotices() {
		// only setup for dashboard
		if (! is_admin ()) {
			return;
		}
		$this->add_action_type ( 'wpnotice', 'WPnotice' );
		$output = '';
		foreach ( ( array ) $this->get_actions ( 'wpnotice' ) as $action ) {
			$this->view->class = $action ['alert'];
			$this->view->slug = $action ['slug'];
			$this->view->content = call_user_func_array ( array ($this, $action ['action'] ), array ('' ) );
			$output .= $this->render_script ( 'common/notices.phtml' );
		}
		if (! empty ( $output )) {
			echo $this->wrapper ( $output );
		}
	}
	public function admin_noticesWPactionA() {
		$this->setup_wpnotices ();
	}
	public function generate_rewrite_rulesWPaction($wp_rewrite) {
		$new_rules = array ();
		foreach ( ( array ) $this->get_actions ( 'wppage' ) as $action ) {
			$new_rules [$action ['slug']] = 'index.php?wppage=' . $action ['slug'];
		}
		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
	}
	public function query_varsWPfilter($qvars) {
		$qvars [] = 'wppage';
		return $qvars;
	}
	public function template_redirectWPaction() {
		global $wp_query;
		global $wp_rewrite;
		if ($wp_query->get ( 'wppage' )) {
			foreach ( ( array ) $this->get_actions ( 'wppage' ) as $action ) {
				//$this->debug ( $wp_query->get ( 'wppage' ) );
				$pages = $this->pages ( $action ['slug'] );
				//$this->debug ( $pages );
				if ($pages !== false) {
					$return = call_user_func_array ( array ($this, $action ['action'] ), array ($pages ) );
					// if the action did not refuse the page then stop die
					if ($return !== false) {
						die ();
					}
				}
			}
		}
	}
	protected function pages($slug) {
		$siteurl = $this->application ()->siteuri ( true );
		$host = trim ( $_SERVER ['HTTP_HOST'], '/' );
		$request_uri = explode ( '?', $_SERVER ['REQUEST_URI'] );
		$request_uri = trim ( $request_uri [0], '/' );
		if (isset ( $_GET ['wppage'] )) {
			$request_uri .= '/' . $_GET ['wppage'];
			$request_uri = trim ( $request_uri, '/' );
		}
		$page = urldecode ( strtolower ( $host . '/' . $request_uri ) );
		//print_r($siteurl);
		$pages = null;
		if (strpos ( $page, $siteurl ['uri'] ) === 0) {
			$pages = trim ( substr ( $page, strlen ( $siteurl ['uri'] ) ), '/' );
			$pages = explode ( '/', $pages );
		}
		$slug = explode ( '/', trim ( $slug, '/' ) );
		// get a possible matchine part of the requested uri
		if (is_array ( $pages )) {
			$match = array_slice ( $pages, 0, count ( $slug ) );
			if ($slug != $match) {
				return false;
			}
		} else {
			return false;
		}
		// its a match so calculate the pages after the permalink
		$pages = array_slice ( $pages, count ( $slug ) );
		return $pages;
	}
	public function setup() {
		foreach ( $this->get_actions ( 'wpmenu' ) as $menu ) {
			if ($menu ['menu'] != 'Sandbox' || $this->dodebug ()) {
				$page_title = __ ( $menu ['menu'] );
				$mnu = $this->dashboard ( $page_title )->menu;
				//if ($menu ['title'] == $menu ['menu']) {
				$menu_title = $menu ['title'];
				$capability = $menu ['capability'];
				$function = array ($this, 'callback' );
				$menu_slug = $menu ['slug'];
				if (false === $mnu) {
					/*
			 * positions in menu
			 * 0: $menu_title
			 * 1: $capability
			 * 2: $menu_slug
			 * 3: $page_title
			 * 4: class?
			 * 5: class?
			 * 6: icon_url
			 */
					$menu_slug = $page_title;
					$menu_title = $page_title;
					$icon_url = null;
					$position = null;
					add_menu_page ( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
					$parent_slug = $page_title;
				} else {
					$parent_slug = $mnu ['2'];
				}
				/**
				 * position in sub menu
				 * 
				 * key: $parent_slug.
				 * 0: $menu_title
				 * 1: $capability
				 * 2: $menu_slug
				 * 3: $page_title
				 */
				//$menu_slug='admin.php?page=test';
				add_submenu_page ( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
			}
		}
	}
	public function callback() {
		$page = urldecode ( $_GET ['page'] );
		$menu = false;
		foreach ( $this->get_actions ( 'wpmenu' ) as $item ) {
			if ($item ['slug'] = $page) {
				$menu = $item;
				break;
			}
		}
		if ($menu) {
			$pages = $this->get_actions ();
			foreach ( $pages as $page ) {
				if (! isset ( $_GET ['page2'] ) || $page ['slug'] == $_GET ['page2']) {
					break;
				}
			}
			$output = '';
			if (method_exists ( $this, $page ['action'] )) {
				$output = call_user_func_array ( array ($this, $page ['action'] ), array ($page, $menu, $pages ) );
			}
			$return = $this->render_script ( "{$menu['slug']}/{$page ['slug']}.phtml" );
			//$this->debug("{$menu['slug']}/{$page ['slug']}.phtml");
			if (null !== $return) {
				$output .= $return;
			}
			$classes = implode ( $page ['classes'] );
			$updated = $this->updated ();
			$output = "<form  id='v35v_form' action='' method='post' class='admin-menu {$classes}'>{$updated}{$output}</form>";
			$output = $this->icon ( $page, $menu ) . $this->menu ( $page, $menu, $pages ) . $output;
			echo $this->wrapper ( $output );
		}
	}
	public function icon($page, $menu) {
		$action = $page;
		if (! isset ( $action ['icon'] )) {
			$action = $menu;
		}
		if (isset ( $action ['icon'] )) {
			$icon = $action ['icon'];
		} else {
			$icons = array ('Dashboard' => 'icon-index', 'Posts' => 'icon-edit', 'Media' => 'icon-upload', 'Links' => 'icon-link-manager', 'Pages' => 'icon-edit-pages', 'Comments' => 'icon-edit-comments', 'Appearance' => 'icon-themes', 'Plugins' => 'icon-plugins', 'Users' => 'icon-users', 'Tools' => 'icon-tools', 'Settings' => 'icon-options-general' );
			if (isset ( $icons [$action ['menu']] )) {
				$icon = $icons [$action ['menu']];
			} else {
				$icon = $icons ['Settings'];
			}
		}
		return "	<div id='{$icon}' class='icon32'><br /></div>";
	}
	public function wrapper($page, $classes = array(), $attr = null, $tag = 'div') {
		$classes [] = 'wrap';
		$classes [] = 'v35v';
		$classes [] = 'v35v_' . $this->application ()->slug;
		//		$classes [] = $this->application ()->slug;
		$classes [] = $this->application ()->slug . '_admin';
		if ($this->application ()->css_class != "") {
			$classes [] = $this->application ()->css_class;
			$classes [] = $this->application ()->css_class . '_admin';
		}
		$classes = implode ( ' ', $classes );
		return "<{$tag} class='{$classes}'>{$page}</{$tag}>";
	}
	/*******************************************************************
	 * Default actions of all types but only the ones that need to be done for all classes
	 *******************************************************************/
	public function plugins_loadedWPactionA() {
		$this->setup_wpfilters ();
	}
	public function initWPactionA() {
		$this->setup_wppages ();
	}
	public function admin_menuWPactionA() {
		$this->setup_wpmenu ();
	}
	public function setup_wpmenu() {
		// only setup for dashboard
		if (! is_admin ()) {
			return;
		}
		$this->add_action_type ( 'wpmenu', 'WPmenu' );
		foreach ( ( array ) $this->get_actions ( 'wpmenu' ) as $action ) {
			$this->setup ( $action );
		}
	}
	public function menu($page, $menu, $pages) {
		$this->view->title = $menu ['title'];
		if ($menu ['title'] != $page ['title']) {
			$this->view->title .= '&raquo;' . $page ['title'];
		}
		$baseUrl = $this->dashboard ( $menu ['menu'], $menu ['title'] )->url;
		$this->view->items = $pages;
		$current = false;
		foreach ( $this->view->items as $key => $value ) {
			if ($value ['hide']) {
				unset ( $this->view->items [$key] );
			} else {
				if (empty ( $value ['url'] )) {
					$this->view->items [$key] ['url'] = $baseUrl . '&page2=' . $value ['slug'];
				}
				if ((! isset ( $_GET ['page2'] ) && ! $current) || substr ( $_SERVER ['REQUEST_URI'], - strlen ( $this->view->items [$key] ['url'] ) ) == $this->view->items [$key] ['url']) {
					$this->view->items [$key] ['classes'] [] = 'v35v_current';
					$current = true;
				}
				$this->view->items [$key] ['classes'] = implode ( ' ', $this->view->items [$key] ['classes'] );
			}
		}
		return $this->render_script ( 'common/menu.phtml' );
	}
	public function plugin_action_linksWPfilter($links, $file) {
		if ($file != plugin_basename ( $this->application ()->filename )) {
			return $links;
		}
		foreach ( $this->get_actions ( 'wpmenu' ) as $menu ) {
			$baseUrl = $this->dashboard ( $menu ['menu'], $menu ['title'] )->url;
			$actions = array_reverse ( ( array ) $this->get_actions ( 'action' ) );
			foreach ( $actions as $action ) {
				if (! empty ( $action ['link_name'] ) && ! $action ['hide']) {
					$url = $action ['url'];
					if (empty ( $url )) {
						$url = $baseUrl . '&page2=' . $action ['slug'];
					}
					$classes = implode ( ' ', $action ['classes'] );
					$link_url = "<a href='{$url}' class='{$classes}' title='{$action ['link_title']}'>{$action ['link_name']}</a>";
					array_unshift ( $links, $link_url );
				}
			}
		}
		return $links;
	}

/*******************************************************************
 * General functions share by this class type
 *******************************************************************/
}