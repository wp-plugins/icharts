<?php
class wv35v_config extends bv35v_action {
	public function __construct(&$application) {
		if (! $this->dodebug () || basename ( $application->filename ) == 'application') {
			return;
		}
		parent::__construct ( $application );
		$this->index ();
		$this->readme ();
	}
	private function index() {
		$this->view->version = $this->version ();
		$this->view->load = $this->load ();
		$page = $this->render_script ( 'config/plugin.phtml', false );
		$page = '<?php ' . $page;
		$this->write ( $this->application ()->filename , $page );
	}
	private function readme() {
		$readme = dirname ( $this->application ()->filename  ) . '/readme/readme.txt';
		if (! file_exists ( $readme )) {
			return;
		}
		$page = $this->render_script ( 'config/readme.phtml', false );
		$oldpage = file_get_contents ( $readme );
		$newpage = explode ( '== Description ==', $oldpage );
		$newpage [0] = $page;
		$newpage = implode ( '== Description ==', $newpage );
		$this->write ( $readme, $newpage );
	}
	private function load() {
		$return = $this->application ()->classes->application;
		$dir = $this->application()->directory.'application/';
		foreach ( $this->application ()->folders as $key => $value ) {
			if (strpos($key,'_')!==0) {
				$start = "{$key}v35v";
				if (strpos ( $return, $start ) === 0) {
					$return = str_replace ( $start, '', $return );
					$dir = $value;
					break;
				}
			}
		}
		$return = str_replace($this->application()->directory,'',$dir).str_replace ( '_', '/', $return );
		return $return;
	}
	private function write($name, $contents) {
		$oldcontents = file_get_contents ( $name );
		if ($oldcontents != $contents) {
			file_put_contents ( $name, $contents );
		}
	}
	private function version() {
		$return = $this->application ()->version;
		$version = '';
		if (strpos ( $return, 'a' )) {
			$return = str_replace ( 'a', '', $return );
			$version = '.&alpha;';
		}
		if (strpos ( $return, 'b' )) {
			$return = str_replace ( 'b', '', $return );
			$version = '.&beta;';
		}
		if (strpos ( $return, 'c' )) {
			$return = str_replace ( 'c', '', $return );
			$version = '.&gamma;';
		}
		$return .= '.' . str_replace ( 'v', '', 'v35v' ) . $version;
		$plugin = $this->application ()->filename ;
		return $return;
	}
	public function old() {
		if (isset ( $this->view->options ['tags'] )) 

		{
			foreach ( $this->view->options ['tags'] as $key => $value ) {
				if (empty ( $value )) {
					unset ( $this->view->options ['tags'] [$key] );
				}
			}
			array_unique ( $this->view->options ['tags'] );
			sort ( $this->view->options ['tags'] );
		}
	}
}