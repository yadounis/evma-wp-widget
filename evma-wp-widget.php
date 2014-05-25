<?php

  /*
  Plugin Name: Events by Ev.ma (widget)
  Plugin URI: https://github.com/yadounis/evma-wp-widget
  Description: A simple Wordpress widget to display events from Ev.ma.
  Author: Younes Adounis
  Version: 1.0
  Author URI: http://adounisyounes.com/
  */

  class EvWidget extends WP_Widget
  {

    function EvWidget()
    {
      $widget_ops = array('classname' => 'EvWidget', 'description' => 'Display events from Ev.ma');
      $this->WP_Widget('EvWidget', 'Events by Ev.ma', $widget_ops);
    }
   
    function form($instance)
    {

    }
   
    function update($new_instance, $old_instance)
    {
      $instance = $old_instance;
      return $instance;
    }
   
    function widget($args, $instance)
    {

    }
   
  }

  add_action('widgets_init', create_function('', 'return register_widget("EvWidget");'));

?>