<?php

/*
 * Plugin Name: Wp Smart Tooltip
 * Version: 1.0.0
 * Plugin URI:
 * Description: With this plugin, you can add tool tips any where in your site.
 * Author: Nurul Amin, Mohammad Saiful Islam
 * Author URI: http://nurulamin.me
 * Requires at least: 4.0
 * Tested up to:
 * License: GPL2
 * Text Domain: wpstt [find this text and replace with your text]
 * Domain Path: /lang/
 *
 */

class WpSmartToolTips{
    public $version             = '1.0.0';
    public $text_domain         = 'wpstt';   
    public $db_version          = '1.0.0';
    public $custom_post_name         = 'wpsmart-tooltip'; 
    protected static $_instance = null;


    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    
    public function __construct()
    {
        $this->init_actions();

        add_action('wp_enqueue_scripts',array($this,'enqueue'));
       
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook(__FILE__,array($this,'deactivate'));
    }


    function init_actions() {
        
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action('init',array($this,'wpstt_register_post_type'));
        add_shortcode( 'wp-smart-tooltip', array( $this, 'render_wpstt_short_code'));


    }

    function load_textdomain() {
        load_plugin_textdomain( $this->text_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
    }


    public function activate(){
              flush_rewrite_rules();
    }

    public function deactivate(){
        flush_rewrite_rules();
    }
    public function uninstall(){

    }
    function wpstt_register_post_type() {
        $name = "WP Smrat Tooltip" ;   
        $labels        = array(
            'name'               => __( $name , 'post type general name', $this->text_domain ),
            'singular_name'      => __( $name, 'post type singular name', $this->text_domain ),
            'add_new'            => __( 'Add New', $name, $this->text_domain ),
            'add_new_item'       => __( 'Add New '.$name, $this->text_domain ),
            'edit_item'          => __( 'Edit '.$name, $this->text_domain ),
            'new_item'           => __( 'New ' .$name, $this->text_domain ),
            'view_item'          => __( 'View '. $name, $this->text_domain ),
            'search_items'       => __( 'Search ' .$name, $this->text_domain ),
            'not_found'          => __( 'Nothing found', $this->text_domain ),
            'not_found_in_trash' => __( 'Nothing found in Trash', $this->text_domain ),
            'parent_item_colon'  => __( $name, $this->text_domain ),
        );
        $post_type_agr = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'capability_type' => 'post',
            'menu_position' => false,
            'show_in_menu'  => true,
            'supports'      => array( 'title', 'editor' ),
            'hierarchical'  => false,
            'rewrite'       => false,
            'query_var'     => false,
            'show_in_nav_menus' => false,
        );
        register_post_type( $this->custom_post_name, $post_type_agr );  
    }
    function enqueue(){
        wp_enqueue_script( 'jquery');
        wp_enqueue_style( 'bootstrap_css', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css' );
        wp_enqueue_script('jquery-p-js',"https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js");
        wp_enqueue_script('jquery_ui',"https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js");
        wp_enqueue_script( 'wpsmarttooltip_front', plugins_url( 'assets/js/script.js', __FILE__ ), '', false, true );
        wp_enqueue_style( 'wpsmarttooltip_front', plugins_url( '/assets/css/style.css', __FILE__ ) );  

    }
   

    function render_wpstt_short_code($atts){
        $a = shortcode_atts( array(
            'id'=>'',
        ), $atts );
        extract($a);

        $post = get_post( (int)$id);
        if(!$post){
            return; 
        }
        $title = $post->post_title;
        $content=$post->post_content;

        $data="<span class='wp-tooltip' data-toggle='tooltip' title='{$content}'> {$title} </span>";
        return $data;
    }
}

 
function WpsttInit() {
    return WpSmartToolTips::instance();
}
//Class  instance.
$WpSmartToolTips = WpsttInit();

