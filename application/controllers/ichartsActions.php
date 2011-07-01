<?php
class ichartsActions extends wv30v_action {
	public function iChartsWPmenuMeta($return) {
		$return ['title'] = 'iCharts';
		return $return;
	}
	public function getting_startedActionMeta($return) {
		$return ['link_name'] = $return ['title'];
		$return ['priority'] = 2;
		$return ['classes'] [] = 'v30v_info';
		return $return;
	}
	public function ichartsActionMeta($return) {
		$return ['title'] = 'iCharts';
		$return ['link_name'] = $return ['title'];
		$return ['priority'] = 3;
		$return ['url'] = 'http://icharts.net';
		$return ['classes'] [] = 'v30v_icharts';
		return $return;
	}
	function mce_buttonsWPfilter($buttons) {
		array_push ( $buttons, "separator", "icharts" );
		return $buttons;
	}
	function mce_external_pluginsWPfilter($plugin_array) {
		$plugin_array ['icharts'] = $this->application ()->pluginuri () . '/application/public/js/editor_plugin.js';
		return $plugin_array;
	}
	public function admin_print_stylesWPaction() {
		wp_enqueue_style ( 'icharts_style_css', $this->application ()->pluginuri () . '/application/public/css/style.css', null, $this->application ()->version () );
	}
	public function admin_print_scriptsWPaction() {
		wp_enqueue_script ( 'jquery' );
		wp_enqueue_script ( 'v30v_script_js' );
		wp_enqueue_script ( 'icharts_script_js', $this->application ()->pluginuri () . '/application/public/js/script.js', null, $this->application ()->version () );
		$data = array ('popup_url' => $this->control_url ( 'icharts/popup' ) );
		wp_localize_script ( 'icharts_script_js', 'icharts_data', $data );
	}
	public function popupWPpageMeta($return) {
		$return ['slug'] = 'icharts/popup';
		return $return;
	}
	public function popupWPpage() {
		echo $this->render_script ( 'icharts/popup.phtml' );
	}
	public function the_contentWPfilter($content) {
		$content = $this->marker ( 'ichart', $content );
		return $content;
	}
	public function ichart_Marker($match) {
		if (isset ( $match ['attributes'] ['url'] )) {
			$url = $match ['attributes'] ['url'];
			$embed = get_option ( 'ichart_' . $url );
			$embed = '';
			if ($embed == '') {
				$embed = $this->method1 ( $url );
				if ($embed === false) {
					$embed = $this->method2 ( $url );
				}
				if ($embed != "") {
					update_option ( 'ichart_' . $url, $embed );
				}
			}
			return $embed;
		} else {
			return '';
		}
	}
	private function method1($url) {
		$id = $url;
		$id = split ( 'sp=', $id );
		$id = split ( '=', $id [1] );
		$id = $id [0] . '=';
		// doesn't always work first attempt dso give it 2 tries
		$embed = $this->get_embed_code ( $id );
		if ($embed == '') {
			$embed = $this->get_embed_code ( $id );
		}
		if ($embed != '') {
			return $embed;
		}
		return false;
	}
	public function get_embed_code($id) {
		$url = "http://accounts.icharts.net/portal/app";
		$data = array ();
		$data ['page'] = 'TeamChartDetail';
		$data ['sp'] = $id;
		$data ['service'] = 'external';
		$http = new bv30v_http ( $url );
		$http->data ( $data );
		$page = $http->get ();
		// roughly find the embed code
		$page = substr ( $page, strpos ( $page, $id ) - 200, 700 );
		// clean it up
		$page = split ( '<input value="', $page );
		$return = '';
		if (count ( $page ) > 1) {
			$page = $page [1];
			$page = split ( '"', $page );
			$return = $page [0];
		}
		return html_entity_decode ( $return );
	}
	public function method2($url) {
		$data = array ();
		$url = explode ( '?', $url );
		// split the querly line into data is one is there
		if (count ( $url ) == 2) {
			$queries = explode ( '&', $url [1] );
			foreach ( $queries as $query ) {
				$datum = explode ( '=', $query );
				$data [$datum [0]] = $datum [1];
			}
		}
		$url = $url [0];
		$http = new bv30v_http ( $url );
		$http->data ( $data );
		$page = $http->get ();
		// roughly find the object code
		$pos = strpos ( $page, "Powered By: <a href = 'http://www.icharts.net'>iCharts" );
		if ($pos === false) {
			$pos = strpos ( $page, "Powered By: <a href = 'http://www.ichartsbusiness.com'>iCharts" );
		}
		$page = substr ( $page, $pos - 700, 1400 );
		$page = split ( '<object', $page );
		$return = '';
		if (count ( $page ) > 1) {
			$page = "<object" . $page [1];
			$page = split ( '</object>', $page );
			$page = $page [0] . "</object>";
			$return = $page;
		}
		return $return;
	}

}