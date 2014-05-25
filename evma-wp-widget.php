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
      $instance = wp_parse_args((array) $instance);
      ?>
        <p>
          <label for="<?= $this->get_field_id('title'); ?>">
            Titre:
            <input class="widefat" id="<?= $this->get_field_id('title'); ?>" name="<?= $this->get_field_name('title'); ?>" type="text" value="<?= attribute_escape($instance['title']); ?>" />
          </label>
        </p>
        <p>
          <label for="<?= $this->get_field_id('api_key'); ?>">
            API key:
            <input class="widefat" id="<?= $this->get_field_id('api_key'); ?>" name="<?= $this->get_field_name('api_key'); ?>" type="text" value="<?= attribute_escape($instance['api_key']); ?>" />
          </label>
        </p>
        <p>
          <label for="<?= $this->get_field_id('category'); ?>">
            Catégorie:
            <select id="<?= $this->get_field_id('category'); ?>" name="<?= $this->get_field_name('category'); ?>" class="widefat">
              <option <?php if(attribute_escape($instance['category']) == 0): echo "selected"; endif; ?> value="0">Toutes les catégories</option>
              <option <?php if(attribute_escape($instance['category']) == 1): echo "selected"; endif; ?> value="1">Art &amp; Culture</option>
              <option <?php if(attribute_escape($instance['category']) == 2): echo "selected"; endif; ?> value="2">Business</option>
              <option <?php if(attribute_escape($instance['category']) == 3): echo "selected"; endif; ?> value="3">Divertissement</option>
              <option <?php if(attribute_escape($instance['category']) == 4): echo "selected"; endif; ?> value="4">Sciences &amp; Tech</option>
              <option <?php if(attribute_escape($instance['category']) == 5): echo "selected"; endif; ?> value="5">Sports</option>
              <option <?php if(attribute_escape($instance['category']) == 6): echo "selected"; endif; ?> value="6">Autres</option>
            </select>
          </label>
        </p>
        <p>
          <label for="<?= $this->get_field_id('total_events'); ?>">
            Nombre d'événements à afficher:
            <input class="widefat" id="<?= $this->get_field_id('total_events'); ?>" name="<?= $this->get_field_name('total_events'); ?>" type="text" value="<?= attribute_escape($instance['total_events']); ?>" />
          </label>
        </p>
        <p>
          <input class="checkbox" type="checkbox" id="<?= $this->get_field_id('display_more'); ?>" name="<?= $this->get_field_name('display_more'); ?>" value="1" <?php if(attribute_escape($instance['display_more']) == 1): echo "checked"; endif; ?>>
          <label for="<?= $this->get_field_id('display_more'); ?>">Afficher le lien "Afficher plus" ?</label>
        </p>
      <?php
    }
   
    function update($new_instance, $old_instance)
    {
      $instance = $old_instance;
      $instance = array(
        'title'        => $new_instance['title'],
        'api_key'      => $new_instance['api_key'],
        'category'     => intval($new_instance['category']),
        'total_events' => intval($new_instance['total_events']),
        'display_more' => intval($new_instance['display_more']),
      );
      delete_transient('evwidget_data');
      return $instance;
    }
   
    function widget($args, $instance)
    {

    }
   
  }

  add_action('widgets_init', create_function('', 'return register_widget("EvWidget");'));

?>