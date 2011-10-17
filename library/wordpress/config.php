<?php
class wv30v_config extends bv30v_action {
	public function __construct($application) {
		if (! $this->dodebug () || basename ( $application->filename () ) == 'application') {
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
		$this->write ( $this->application ()->filename (), $page );
	}
	private function readme() {
		$readme = dirname ( $this->application ()->filename () ) . '/readme/readme.txt';
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
		$return = $this->settings ()->application ['class'];
		$found = false;
		foreach ( $this->application ()->loader ()->subfolders as $key => $value ) {
			if (null !== $value) {
				$start = "{$value}v30v";
				if (strpos ( $return, $start ) === 0) {
					$return = str_replace ( $start, $key, $return );
					$found = true;
					break;
				}
			}
		}
		if (! $found) {
			$return = 'application/' . $return;
		}
		$return = str_replace ( '_', '/', $return );
		return $return;
	}
	private function write($name, $contents) {
		$oldcontents = file_get_contents ( $name );
		if ($oldcontents != $contents) {
			file_put_contents ( $name, $contents );
		}
	}
	private function version() {
		$return = $this->settings ()->application ['version'];
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
		$return .= '.' . str_replace ( 'v', '', 'v30v' ) . $version;
		$plugin = $this->application ()->filename ();
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