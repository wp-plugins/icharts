<?php
class bv35v_request extends bv35v_base {
	public function is_post()
	{
		return ($_SERVER ['REQUEST_METHOD'] == 'POST');
	}
}