<?php
class bv30v_loader extends bv30v_base {
	public function __construct($application) {
		parent::__construct ( $application );
		$this->subfolders = array_flip ( $application->application ['folders'] );
		$this->subfolders ['application'] = null;
		$this->subfolders ['application/models'] = null;
	}
	private function sanitize_path($path) {
		return rtrim ( $path, DIRECTORY_SEPARATOR );
	}
	private $includepath = null;
	public function includepath($folders = null, $reverse = false) {
		if (null === $this->includepath) {
			$this->includepath = array ();
			$path = $this->application ()->directory ();
			$this->includepath [] = $path;
			foreach ( array_keys ( $this->subfolders ) as $subfolder ) {
				$newfolder = $path . DIRECTORY_SEPARATOR . $subfolder;
				if (is_dir ( $newfolder )) {
					$this->includepath [] = $newfolder;
				}
			}
		}
		if (null !== $folders) {
			$dirs = array ();
			foreach ( $this->includepath as $path ) {
				foreach ( ( array ) $folders as $folder ) {
					$newfolder = $path . DIRECTORY_SEPARATOR . $this->sanitize_path ( $folder );
					if (is_dir ( $newfolder )) {
						$dirs [] = $newfolder;
					}
				}
			}
			if ($reverse) {
				$dirs = array_reverse ( $dirs );
			}
			return $dirs;
		}
		return $this->includepath;
	}
	public $subfolders = null;
	public function load_class($class, $dirs = null) {
		if (class_exists ( $class, false )) {
			return;
		}
		if (null === $dirs) {
			$dirs = $this->includepath ();
		}
		$file = str_replace ( '_', DIRECTORY_SEPARATOR, $class ) . '.php';
		foreach ( $this->subfolders as $key => $value ) {
			if (null !== $value) {
				$start = "{$value}v30v";
				if (strpos ( $file, $start ) === 0) {
					$file = str_replace ( $start, $key, $file );
					break;
				}
			}
		}
		$incPath = get_include_path ();
		if (is_string ( $dirs )) {
			set_include_path ( $dirs );
		} else {
			set_include_path ( implode ( PATH_SEPARATOR, $dirs ) );
		}
		@include_once $file;
		set_include_path ( $incPath );
		if (! class_exists ( $class, false )) {
			throw new Exception ( "File \"{$file}\" does not exist or class \"{$class}\" was not found in the file" );
		}
	}
	
	public function find_file($filename, $quiet = false, $include_path = null) {
		if (null === $include_path) {
			$include_path = $this->includepath ();
		}
		if (file_exists ( $filename )) {
			return $filename;
		}
		foreach ( $include_path as $dir ) {
			if (file_exists ( $this->sanitize_path ( $dir ) . DIRECTORY_SEPARATOR . $filename )) {
				return $this->sanitize_path ( $dir ) . DIRECTORY_SEPARATOR . $filename;
			}
		}
		if (! $quiet) {
			throw new Exception ( $filename . ' Not Found ' . print_r ( $include_path, true ) );
		}
		return false;
	}
	public function file($filename) {
		$filename = $this->find_file ( $filename );
		return file_get_contents ( $filename );
	}
}

