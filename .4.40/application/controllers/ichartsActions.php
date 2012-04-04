<?php
class ichartsActions extends wv35v_action {
	public function iChartsWPmenuMeta($return) {
		$return ['title'] = 'iCharts';
		return $return;
	}
	public function getting_startedActionMeta($return) {
		$return ['link_name'] = $return ['title'];
		$return ['priority'] = 2;
		$return ['classes'] [] = 'v35v_info';
		return $return;
	}
	public function ichartsActionMeta($return) {
		$return ['title'] = 'iCharts';
		$return ['link_name'] = $return ['title'];
		$return ['priority'] = 3;
		$return ['url'] = 'http://icharts.net';
		$return ['classes'] [] = 'v35v_icharts';
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
		wp_enqueue_script ( 'v35v_script_js' );
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
				else { $embed = 'Cannot retrieve chart';}  /*Added by Megha - April 2 2012*/
			}
			return $embed;
		} else {
			return '';
		}
	}
	private function method1($url) {
		$id = $url;

/*Commented by Megha - Apr1 2012 START*/
//		$id = split ( 'sp=', $id );  
//		if (count ( $id ) == 1) {
//			return false;
//		}
//		$id = split ( '=', $id [1] );
//		$id = $id [0] . '=';
/*Commented by Megha - Apr1 2012 END*/

	/*Megha start*/
		$url_data = parse_url($url);
		$parameters = $url_data["query"];
		$url_params = preg_split('/[&=]/', $parameters);
		for ($i=0; $i<count($url_params); $i++){
 	           $url_param_data[$url_params[$i]] = $url_params[++$i];
  	      	}
		if (isset($url_param_data['sp'])) {
		   $id = $url_param_data['sp'];
		}
		elseif (isset($url_param_data['amp;sp'])) {
		   $id = $url_param_data['amp;sp'];
		}
		else{
			return false;
		}
	/*Megha end*/

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
		$http = new bv35v_http ( $url );
		$http->data ( $data );
		$page = $http->get ();
		// roughly find the embed code
		$pos = strpos ( $page, 'id="embedCodeText"' );
		$page = substr ( $page, $pos, 70000 );
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
		$http = new bv35v_http ( $url );
		$http->data ( $data );
		$page = $http->get ();

		// roughly find the object code
		//$pos = strpos ( $page, 'id="embedCodeText"' );
                //$pos = strpos ( $page, 'id="iChartEmbedCodeText"' );   /*Added by Megha - April 1 2012*/
		$pos = strpos ( $page, 'class="field-item even"' );      /*commented by Megha - April 1 2012*/
		/*Added by Megha - April 1 2012 Start*/
		if ($pos == ''){
			$pos = strpos ( $page, '<iframe src="http://accounts.icharts.net/' ); 
		}
		/*Added by Megha - April 1 2012 End*/
	
		//$page = substr ( $page, $pos - 700, 14000 ); 	/*commented by Megha - April 1 2012*/
		$page = substr ( $page, $pos - 700, 8000 ); 	/*Added by Megha - April 1 2012*/
		$page = split ( '<iframe', $page );
		$return = '';

		if (count ( $page ) > 1) {
			$page = "<iframe ".$page [1];
			$page = split ( '</iframe>', $page );
			$page = $page [0] . "</iframe>";
			$return = $page;
		}

		return $return;
	}

}