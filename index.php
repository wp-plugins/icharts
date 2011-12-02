<?php /*
Plugin Name: iCharts
Plugin URI: http://wordpress.org/extend/plugins/icharts/
Description: Easily Insert iCharts into your post.
Author: dcoda
Author URI: 
Version: 0.4.43
License: GPLv2 or later
*/
@require_once  dirname ( __FILE__ ) . '/library/wordpress/application.php';
if (class_exists("wv43v_application"))
{
	new wv43v_application ( __FILE__);
}