<?php
class wv35v_data_legacy extends bv35v_base {
	public function __construct(&$application) {
		parent::__construct ( $application );
		if (count ( $application->data ()->options ( false, true ) ) == 0) {
			$this->move ();
		}
	}
	public function move() {
	
	}
	public function data() {
		$sql = "SELECT `option_name`,`option_value` from `%s` WHERE option_name LIKE  '%s_%%';";
		$sql = sprintf ( $sql, $this->table ( 'options' )->name (), $this->application ()->slug );
		$results = $this->table ()->execute ( $sql );
		$data = array ();
		$len = strlen ( $this->application ()->slug ) + 1;
		foreach ( $results as $key => $value ) {
			$new_key = substr ( $value ['option_name'], $len );
			$data [$new_key] = $value ['option_value'];
			$data [$new_key] = base64_decode ( $data [$new_key] );
			$data [$new_key] = @gzuncompress ( $data [$new_key] );
			$data [$new_key] = unserialize ( $data [$new_key] );
			unset ( $results [$key] );
		}
		return $data;
	}
}