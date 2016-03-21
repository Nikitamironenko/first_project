<?php
/*
Author: Janek Niefeldt
Author URI: http://www.janek-niefeldt.de/
Description: Configuration of My Custom Widgets Plugin.
*/
include_once('my_custom_widget_functions.php');
include_once('my_custom_widget_meta.php');
?><?php
class MCW_ extends WP_Widget
{
	function MCW_(){
		$widget_ops = array('classname' => 'MCW_', 'description' => 'CustomWidget generated with MCW &raquo;' );
		$control_ops = array('width' => 45);
		$this->WP_Widget('MCW_', 'MCW: ', $widget_ops, $control_ops);
	}
	function widget($args, $instance){
		$args['name'] = '';
		MCW_eval_code($args);
	}
	function update($new_instance, $old_instance){
	  $new_instance['title'] = MCW_get_widget_info('', 'title');
		return $new_instance;
	}
	function form($instance){
    MCW_get_official_form('');	  
  }
}
	function MCW_Init() {
	  register_widget('MCW_');
	}
	add_action('widgets_init', 'MCW_Init');
?>