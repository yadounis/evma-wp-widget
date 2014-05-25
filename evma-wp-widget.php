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
      $widget_ops     = array('classname' => 'EvWidget', 'description' => 'Display events from Ev.ma');
      $this->WP_Widget('EvWidget', 'Events by Ev.ma (widget)', $widget_ops);
      //For ajax request
      add_action('wp_ajax_loadMore', array($this, 'loadMore'));
      add_action('wp_ajax_nopriv_loadMore', array($this, 'loadMore'));
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
      extract($args, EXTR_SKIP);
      echo $before_widget;
      echo '<div id="EventsByEvma">';
      $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
      if (!empty($title)) {
        echo $before_title . $title . $after_title;
      }
      $events = $this->_getEvents($instance['api_key'], $instance['category'], $instance['total_events']);
      if ($events) {
        echo '<div id="EventsByEvma_EventsList">';
        echo $events;
        if ($instance['display_more']) {
          echo '<div id="EventsByEvma_ShowMore"><a href="#" data-offset="'.($instance['total_events']++).'">Afficher plus</a></div>';
        }
        echo '</div>';
      }
      else {
        echo '<div id="EventsByEvma_EmptyList"></div>';
      }
      echo '</div>';
      echo $after_widget;
      //JS
      wp_enqueue_script('evma-wp-widget', WP_PLUGIN_URL . '/evma-wp-widget/scripts.js');
    }

    function loadMore()
    {
      $instance = get_option('widget_evwidget')[2];
      $instance['offset'] = intval($_GET['offset']);
      $events = $this->_getEvents($instance['api_key'], $instance['category'], $instance['total_events'], $instance['offset']);
      echo $events;
      echo '<div id="EventsByEvma_ShowMore"><a href="#" data-offset="'.($instance['total_events']+$instance['offset']).'">Afficher plus</a></div>';
      exit();
    }

    function _getEvents($api_key = null, $category = null, $count = 10, $offset = 0)
    {
      $html = '';
      $events = get_transient('evwidget_data');
      if ($events === false) {
        //Params
        $params = array();
        if ($category) {
          $params['category'] = $category;
        }
        $params['count']  = $count;
        $params['offset'] = $offset;
        //Uri
        $uri = "http://ev.ma/api/v1/events/search.json?" . http_build_query($params);
        //Options
        $options = array(
          "http" => array(
            "method" => "GET",
            "header" => array(
              "Authorization: TRUEREST access_token=" . $api_key,
              "Content-Type: application/json",
            )
          )
        );
        $context = stream_context_create($options);
        //Make the request
        $response = file_get_contents($uri, false, $context);
        //Event array
        $events = json_decode($response, true);
        //Cache for 12 hours
        set_transient('evwidget_data', $events, 60*60*12);
      }
      if ($events) {
        $html = '';
        foreach ($events['events'] as $event) {
          $html .= '<div class="EventsByEvma_SingleEvent">
            <h4><a href="'. $event['Event']['short_link'] .'" target="_blank">'. $event['Event']['name'] .'</a></h4>
            <div class="EventsByEvma-Meta">
              <span class="EventsByEvma-Date">Le '. date('d F', $event['Event']['start_timestamp']) .'</span>
              <span class="EventsByEvma-Time">à '. date('H:i', $event['Event']['start_timestamp']) .'</span>';
          $html .= (isset($event['Venue']['City'])) ? '<span class="EventsByEvma-Location"> - '. $event['Venue']['City']['name'] .'</span>' : '';
          $html .= '</div></div>';
        }
      }
      return $html;
    }
   
  }

  add_action('widgets_init', create_function('', 'return register_widget("EvWidget");'));

?>