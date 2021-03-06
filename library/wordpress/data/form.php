<?php
class wv35v_data_form extends wv35v_action {
	private static $_active = 'default';
	public function active($name = null) {
		if (null !== $name) {
			self::$_active = $name;
		}
		return self::$_active;
	}
	public function __construct($application, $name = null) {
		if (null === $name) {
			$name = 'default';
		}
		$this->_form = $name;
		parent::__construct ( $application );
	}
	private $_form = null;
	public function form() {
		return $this->_form;
	}
	public function &data() {
		return parent::data ( $this->form () );
	}
	public function forms($show_hidden = false) {
		if ($this->request ()->is_post ()) {
			$src = '';
			$dst = '';
			$del = array ();
			if (isset ( $_POST ['source_setting'] )) {
				$src = $_POST ['source_setting'];
			}
			if (isset ( $_POST ['new_form'] )) {
				$dst = $_POST ['new_form'];
			}
			if (isset ( $_POST ['delete_setting'] )) {
				$del = $_POST ['delete_setting'];
			}
			if (! empty ( $src ) && ! empty ( $dst )) {
				$this->data ()->copy ( $dst, $src );
			}
			foreach ( $del as $d ) {
				$this->data ()->delete ( $d );
			}
		}
		$options = $this->data ()->options ( $show_hidden );
		$tables = $this->table ()->show_tables ( "{$this->application()->slug}_%" );
		$forms = array ();
		$new = array ('name' => null, 'table' => null, 'count' => '' );
		foreach ( $options as $option ) {
			$forms [$option] = $new;
			$forms [$option] ['name'] = $option;
		}
		foreach ( $tables as $table ) {
			$option = $this->table_name_to_option ( $table );
			if (! isset ( $forms [$option] )) {
				$forms [$option] = $new;
			}
			$forms [$option] ['table'] = $table;
			$forms [$option] ['count'] = $this->table ( $table )->count ();
		}
		return $forms;
	}
	public function table_name_to_option($table) {
		$return = explode ( "{$this->application()->slug}_", $table );
		return $return [1];
	}
}