<?php
abstract class wv30v_widget extends WP_Widget {
	private $_application;
	public $view;
	private $action;
	public function init($application)
	{
		$this->_application = $application;
		$this->action = new wv30v_action($application);
		$this->view = &$this->action->view;
	}
	public function application()
	{
		return $this->_application;
	}
	public function app()
	{
		return $this->_application;
	}
	public function settings()
	{
		return $this->application()->settings();
	}
	public function render_script($script, $html = true) {
		//return 'test';
		return $this->action->render_script($script, $html);
	}
}
	