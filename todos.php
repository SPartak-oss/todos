<?php
/*
Plugin Name: Todos
Plugin URI: https://kwork.ru/user/sh_it
Description: Custom API Plugin
Version: 1.0
Author: sh it
Author URI:  https://kwork.ru/user/sh_it
License: GPLv2 or later
Text Domain: todos 
Domain Path: /lang
*/

//Проверка константа 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'TODOS_PATH', plugin_dir_path( __FILE__ ) ); // констант

if (!class_exists( 'todosPropertyCpt' ) ) { // проверка класса
    require TODOS_PATH . "inc/class-todosplugincpt.php"; // путь к файлу
 }

 // файл шорт кода
 require TODOS_PATH . "inc/class-todos-shortcodes.php"; // путь к файлу


// класс активации деактивации
class todos{
    function  register() {
        add_action('admin_menu',[$this,'add_menu_item']); // страница админа
    }

    public function add_menu_item(){
        add_menu_page(
            esc_html__('Custom API Plugin','todos'),
            esc_html__('Custom API Plugin','todos'),
            'manage_options',
            'custom-api-plugin',
            [$this,'custom_api_plugin_admin_page'],
            'dashicons-update',
            6,
        );
    }

    public function custom_api_plugin_admin_page(){
        require_once TODOS_PATH .'admin/api.php';
    }
    

    //активация
    static function activation () {
        flush_rewrite_rules(); //обновления чпу
    }
    //деактивации
    static function deactivation () {
        flush_rewrite_rules();//обновления чпу
    }

}

if ( class_exists( 'todos' ) ) { // проверка класса
    $todos = new todos();// вызов класса
    $todos->register();
}

register_activation_hook(__FILE__,array( $todos,'activation'));//хук активации
register_deactivation_hook(__FILE__,array( $todos,'activation'));//хук де активации

