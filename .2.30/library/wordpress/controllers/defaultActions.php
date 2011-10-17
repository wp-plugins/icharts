<?php
class defaultActions extends wv30v_action {
	/*******************************************************************
	 * Default actions of all types 
	 *******************************************************************/
	/*******************************************************************
	 * Routines used by the default actions
	 *******************************************************************/
	public function admin_print_stylesWPaction() {
		$this->wp_print_stylesWPaction ();
	}
	public function wp_print_stylesWPaction() {
		wp_enqueue_style ( 'jquery-ui_smoothness', $this->application ()->pluginuri () . '/library/public/css/smoothness/jquery-ui-1.8.13.custom.css', null, $this->application ()->version () );
		wp_enqueue_style ( 'v30v_style_css', $this->application ()->pluginuri () . '/library/public/css/style.css', null, $this->application ()->version () );
	}
	public function admin_print_scriptsWPaction() {
		$this->wp_print_scriptsWPaction();
	}
	public function wp_print_scriptsWPaction() {
		wp_enqueue_script ( 'jquery' );
		wp_enqueue_script ( 'jquery-ui-sortable' );
		wp_enqueue_script ( 'jquery-form' );
		wp_enqueue_script ( 'jquery-ui-dialog' );
		wp_enqueue_script ( 'v30v_script_js', $this->application ()->pluginuri () . '/library/public/js/script.js', null, $this->application ()->version () );
		$data = array ('dodebug' => $this->dodebug() );
		wp_localize_script ( 'v30v_script_js', 'v30v_data', $data );
	}
	public function initWPaction() {
	}
}