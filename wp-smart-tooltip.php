<?php
/*
 * Plugin Name: Wp Smart Tooltip
 * Version: 1.0.0
 * Plugin URI:
 * Description: With this plugin, you can add tool tips any where in your site.
 * Author: Nurul Amin, Mohammad Saiful Islam
 * Author URI: http://nurul.ninja
 * Requires at least: 4.0
 * Tested up to:
 * License: GPL2
 * Text Domain: wpstt [find this text and replace with your text]
 * Domain Path: /lang/
 *
 */

class WpSmartToolTips {

    public $version = '1.0.0';
    public $text_domain = 'wpstt';
    public $db_version = '1.0.0';
    public $custom_post_name = 'wpsmart-tooltip';
    public $setting_options_name = 'wpstt_global_settings';
    public $global_setting = null;
    protected static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->init_actions();
        $this->define_constants();
        add_action('wp_enqueue_scripts', array($this, 'enqueue'));
        add_action('wp_head', array($this, 'render_global_style'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        $settings = get_option($this->setting_options_name);
        $this->global_setting = unserialize($settings);
    }

    function init_actions() {

        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('admin_menu', array($this, 'wpstt_admin_menu'));

        add_action('init', array($this, 'wpstt_register_post_type'));
        add_shortcode('wp-smart-tooltip', array($this, 'render_wpstt_short_code'));
        add_filter("manage_{$this->custom_post_name}_posts_columns", array($this, 'manage_custom_columns'));
        add_action("manage_{$this->custom_post_name}_posts_custom_column", array($this, 'manage_custom_columns_value'));
        add_action('wp_ajax_wpstt_settings_save', array($this, 'wpstt_settings_save'));
    }

    public function define_constants() {
        $this->define('WPSTT_VERSION', $this->version); // Chnage text 'YOUR_PLUGIN' 
        $this->define('WPSTT_DB_VERSION', $this->db_version); // Chnage text 'YOUR_PLUGIN' 
        $this->define('WPSTT_PATH', plugin_dir_path(__FILE__)); // Chnage text 'YOUR_PLUGIN' 
        $this->define('WPSTT_URL', plugins_url('', __FILE__)); // Chnage text 'YOUR_PLUGIN' 
    }

    public function define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    function load_textdomain() {
        load_plugin_textdomain($this->text_domain, false, dirname(plugin_basename(__FILE__)) . '/lang/');
    }

    function wpstt_admin_menu() {
        $capability = 'read'; //minimum level: subscriber 
        add_submenu_page('edit.php?post_type=' . $this->custom_post_name, __('How To USE', $this->text_domain), __('How to Use', $this->text_domain), $capability, __('how_to_use', $this->text_domain), array($this, 'manage_submenu_pages'));
        add_submenu_page('edit.php?post_type=' . $this->custom_post_name, __('WPSTT Settings', $this->text_domain), __('Setting', $this->text_domain), $capability, __('global_settings', $this->text_domain), array($this, 'manage_submenu_pages'));
    }

    public function activate() {
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    public function uninstall() {
        
    }

    function wpstt_register_post_type() {
        $name = "WP Smrat Tooltip";
        $labels = array(
            'name' => __($name, 'post type general name', $this->text_domain),
            'singular_name' => __($name, 'post type singular name', $this->text_domain),
            'add_new' => __('Add New', $name, $this->text_domain),
            'add_new_item' => __('Add New ' . $name, $this->text_domain),
            'edit_item' => __('Edit ' . $name, $this->text_domain),
            'new_item' => __('New ' . $name, $this->text_domain),
            'view_item' => __('View ' . $name, $this->text_domain),
            'search_items' => __('Search ' . $name, $this->text_domain),
            'not_found' => __('Nothing found', $this->text_domain),
            'not_found_in_trash' => __('Nothing found in Trash', $this->text_domain),
            'parent_item_colon' => __($name, $this->text_domain),
        );
        $post_type_agr = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'capability_type' => 'post',
            'menu_position' => false,
            'show_in_menu' => true,
            'supports' => array('title', 'editor'),
            'hierarchical' => false,
            'rewrite' => false,
            'query_var' => false,
            'show_in_nav_menus' => false,
        );
        register_post_type($this->custom_post_name, $post_type_agr);
    }

    function enqueue() {
        wp_enqueue_script('jquery');
        wp_enqueue_style('bootstrap_css', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');
        wp_enqueue_script('jquery-p-js', "https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js");
        wp_enqueue_script('jquery_ui', "https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js");
        wp_enqueue_script('wpsmarttooltip_front', plugins_url('assets/js/script.js', __FILE__), '', false, true);
        wp_enqueue_style('wpsmarttooltip_front', plugins_url('/assets/css/style.css', __FILE__));
    }

    function admin_enqueue() {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('wpsmarttooltip_backend', plugins_url('/assets/css/admin_style.css', __FILE__));
        wp_enqueue_script('wpsmarttooltip_backend', plugins_url('/assets/js/admin-script.js', __FILE__), array('wp-color-picker'), false, true);
        wp_localize_script('wpsmarttooltip_backend', 'WPSTT_Vars', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpstt_nonce'),
        ));
    }

    function manage_submenu_pages() {
        $page = $_GET['page'];
        $view_page = 'dehult.php';
        switch ($page) {

            case "how_to_use" :
                $view_page = "how_to_use.php";
                break;

            case "global_settings" :
                $view_page = "settings.php";
                break;

            default:
        }
        require ( WPSTT_PATH . '/view/' . $view_page );
    }

    function manage_custom_columns($columns) {


        $new_columns['wpstt_sc'] = "Short Code";
        $filtered_columns = array_merge($columns, $new_columns);


        return $filtered_columns;
    }

    function manage_custom_columns_value($column) {
        global $post;
        switch ($column) {
            case 'wpstt_sc' :
                echo "[wp-smart-tooltip id='{$post->ID}']";
                break;

            default :
                break;
        }
    }

    /**
     * Save settings
     */
    function wpstt_settings_save() {
        $post = $_POST;
        //check_ajax_referer('wpstt_nonce', $post['nonce']);
        parse_str($post['form_data'], $form_data);

        $form_data = serialize($form_data);

        update_option($this->setting_options_name, $form_data);

        echo "Save Success!!";

        die();
    }

    function render_wpstt_short_code($atts, $content=null) {

            
            
        
            
        $atts = array_change_key_case((array) $atts, CASE_LOWER);
            

        $a = shortcode_atts(array(
            'id' => '',
            'title' => '',
            
                ), $atts);

        extract($a);

            
        if ($id) {
            $post = get_post((int) $id);
            if (!$post) {
                return;
            }
            if ($this->custom_post_name !== $post->post_type) {
                return;
            }
            $title = $post->post_title;
            $content = $post->post_content;
        } else if ($title != '') {
            $title = $title;
            $content = $content;
        }
            
        $data = "<span class='wpstt_tooltips' data-placement='{$this->global_setting['popup_position']}' data-html='{$this->global_setting['support_html']}' data-toggle='tooltip'  title='{$content}'> {$title}</span>";
        return $data;
    }

    function render_global_style() {
        ?> 
        <style>
            .tooltip > .tooltip-inner {
                background-color: <?php echo $this->global_setting['bg_color'] ?>; 
                color: <?php echo $this->global_setting['text_color'] ?>;  
            }
            .tooltip.left > .tooltip-arrow{

            }
            <?php if( $this->global_setting['popup_width'] !== '') { ?>
            .tooltip-inner {
                max-width: <?php echo $this->global_setting['popup_width'] ?>;
                /* If max-width does not work, try using width instead */
                width: <?php echo $this->global_setting['popup_width'] ?>; 
            }
            <?php } ?>
        </style> 
        <?php
    }

}

function WpsttInit() {
    return WpSmartToolTips::instance();
}

//Class  instance.
$WpSmartToolTips = WpsttInit();

