<?php

class Todos_Shortcodes {

    public $todosPlugin;
    public $managers;

    public function register(){
        add_action('init',[$this,'register_shortcode']);
    }

    public function register_shortcode(){
        add_shortcode('random_todos',[$this,'custom_api_plugin_random_todos']);
    }

    public function custom_api_plugin_random_todos($atts = array()){

        extract(shortcode_atts(array(
            'posts_per_page' => 5,
        ),$atts));
       
        $args = array(
			'post_type'      => 'todo',
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'orderby'        => 'rand',
			'meta_query'     => array(
				array(
					'key'     => 'completed',
					'value'   => 0,
					'compare' => '=',
				),
			),
		);

		$query = new WP_Query($args);

		if ($query->have_posts()) {
			$output = '<ul>';
			while ($query->have_posts()) {
				$query->the_post();
				$output .= '<li>' . get_the_title() . '</li>';
			}
			$output .= '</ul>';
			wp_reset_postdata();
			return $output;
		} else {
			return '<p>Нет незавершенных задач</p>';
		}
        
    }

}
$Todos_Shortcodes = new Todos_Shortcodes();
$Todos_Shortcodes->register();