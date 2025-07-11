<?php
/**
 * Plugin Name: Basic WP/Woo SEO Plugin
 * Plugin URI: https://github.com/Torwald45/basic-seo-plugin
 * Description: Lightweight SEO plugin for WordPress and WooCommerce with custom title tags, meta descriptions, sitemaps, breadcrumbs, and Open Graph support.
 * Version: 1.0.7
 * Author: Torwald45
 * Author URI: https://github.com/Torwald45
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: basic-seo-torwald45
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.7
 * Requires PHP: 8.1
 * Network: false
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BASICSEO_TORWALD45_VERSION', '1.0.7');
define('BASICSEO_TORWALD45_PLUGIN_FILE', __FILE__);
define('BASICSEO_TORWALD45_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BASICSEO_TORWALD45_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BASICSEO_TORWALD45_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Meta keys constants
define('BASICSEO_TORWALD45_POST_TITLE', 'basicseo_torwald45_post_title');
define('BASICSEO_TORWALD45_POST_DESC', 'basicseo_torwald45_post_desc');
define('BASICSEO_TORWALD45_TERM_TITLE', 'basicseo_torwald45_term_title');
define('BASICSEO_TORWALD45_TERM_DESC', 'basicseo_torwald45_term_desc');

/**
 * Main plugin class
 */
class BasicSEO_Torwald45 {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Plugin settings
     */
    private $settings = array();
    private $admin;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_textdomain();
        $this->load_settings();
        $this->load_includes();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Load plugin textdomain
     */
    private function load_textdomain() {
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }
    
    /**
     * Load plugin textdomain callback
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'basic-seo-torwald45',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Load plugin settings
     */
    private function load_settings() {
        $defaults = array(
            'modules' => array(
                'meta_tags' => true,
                'sitemap' => true,
                'breadcrumbs' => true,
                'open_graph' => true,
                'canonical' => true,
                'attachment_redirect' => true
            ),
            'sitemap' => array(
                'post_types' => array(),
                'taxonomies' => array(),
                'exclude_posts' => array(),
                'posts_per_page' => 1000
            ),
            'breadcrumbs' => array(
                'home_text' => 'Home',
                'separator' => ' &raquo; ',
                'show_on_posts' => false,
                'show_on_pages' => false,
                'show_on_products' => false
            ),
            'open_graph' => array(
                'default_image' => '',
                'facebook_app_id' => '',
                'twitter_card_type' => 'summary'
            ),
            'meta_description' => array(
                'length_control' => 'none', // none, warning, auto_cut
                'max_length' => 160
            )
        );
        
        $this->settings = wp_parse_args(
            get_option('basicseo_torwald45_settings', array()), 
            $defaults
        );
    }
    
    /**
     * Get setting value
     */
    public function get_setting($key, $default = null) {
        $keys = explode('.', $key);
        $value = $this->settings;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
    
    /**
     * Update setting value
     */
    public function update_setting($key, $value) {
        $keys = explode('.', $key);
        $settings = &$this->settings;
        
        $last_key = array_pop($keys);
        foreach ($keys as $k) {
            if (!isset($settings[$k]) || !is_array($settings[$k])) {
                $settings[$k] = array();
            }
            $settings = &$settings[$k];
        }
        
        $settings[$last_key] = $value;
        update_option('basicseo_torwald45_settings', $this->settings);
    }
    
    /**
     * Load includes
     */
    private function load_includes() {
        // Core includes
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/utilities/class-seo-analyzer.php';
        
        // Admin includes
        if (is_admin()) {
            require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'admin/class-admin-init.php';
        }
    }
    
    /**
     * Initialize plugin
     */
    public function init() {

 // Initialize admin components
    if (is_admin() && !$this->admin) {
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'admin/class-admin-init.php';
        $this->admin = new BasicSEO_Torwald45_Admin_Init();
    }

        // Load components based on enabled modules
        if ($this->get_setting('modules.meta_tags')) {
            $this->load_meta_components();
        }
        
        if ($this->get_setting('modules.sitemap')) {
            $this->load_sitemap_components();
        }
        
        if ($this->get_setting('modules.breadcrumbs')) {
            $this->load_breadcrumbs_components();
        }
        
        if ($this->get_setting('modules.open_graph') || 
            $this->get_setting('modules.canonical') || 
            $this->get_setting('modules.meta_tags')) {
            $this->load_frontend_components();
        }
        
        if ($this->get_setting('modules.attachment_redirect')) {
            require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/utilities/class-attachment-redirect.php';
            new BasicSEO_Torwald45_Attachment_Redirect();
        }
    }
    
    /**
     * Load meta components
     */
    private function load_meta_components() {
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/meta/class-post-meta.php';
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/meta/class-term-meta.php';
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/meta/class-admin-columns.php';
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/meta/class-quick-edit.php';
        
        new BasicSEO_Torwald45_Post_Meta();
        new BasicSEO_Torwald45_Term_Meta();
        new BasicSEO_Torwald45_Admin_Columns();
        new BasicSEO_Torwald45_Quick_Edit();
    }
    
    /**
     * Load sitemap components
     */
    private function load_sitemap_components() {
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/sitemap/class-sitemap-generator.php';
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/sitemap/class-sitemap-handler.php';
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/sitemap/class-sitemap-pagination.php';
        
        new BasicSEO_Torwald45_Sitemap_Handler();
    }
    
    /**
     * Load breadcrumbs components
     */
    private function load_breadcrumbs_components() {
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/breadcrumbs/class-breadcrumbs-generator.php';
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/breadcrumbs/class-breadcrumbs-shortcode.php';
        
        new BasicSEO_Torwald45_Breadcrumbs_Generator();
        new BasicSEO_Torwald45_Breadcrumbs_Shortcode();
    }
    
    /**
     * Load frontend components
     */
    private function load_frontend_components() {
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/frontend/class-title-handler.php';
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/frontend/class-meta-output.php';
        
        new BasicSEO_Torwald45_Title_Handler();
        new BasicSEO_Torwald45_Meta_Output();
        
        if ($this->get_setting('modules.open_graph')) {
            require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/frontend/class-open-graph.php';
            new BasicSEO_Torwald45_Open_Graph();
        }
        
        if ($this->get_setting('modules.canonical')) {
            require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/frontend/class-canonical.php';
            new BasicSEO_Torwald45_Canonical();
        }
    }
    
/**
 * Initialize admin
 */
public function admin_init() {
    // Admin initialization handled by admin_menu hook
}
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default settings if not exist
        if (!get_option('basicseo_torwald45_settings')) {
            update_option('basicseo_torwald45_settings', $this->settings);
        }
        
        // Flush rewrite rules for sitemap
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Get supported post types
     */
    public static function get_supported_post_types() {
        return apply_filters('basicseo_torwald45_post_types', array(
            'page',
            'post',
            'product'
        ));
    }
    
    /**
     * Get supported taxonomies
     */
    public static function get_supported_taxonomies() {
        return apply_filters('basicseo_torwald45_taxonomies', array(
            'category',
            'post_tag',
            'product_cat',
            'product_tag'
        ));
    }

} // END CLASS BasicSEO_Torwald45

/**
 * Initialize plugin
 */
function basicseo_torwald45_init() {
    return BasicSEO_Torwald45::get_instance();
}

// Start the plugin
basicseo_torwald45_init();
