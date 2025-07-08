<?php
/**
 * Admin Initialization
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Admin_Init
 */
class BasicSEO_Torwald45_Admin_Init {
    
    /**
     * Plugin instance
     */
    private $plugin;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = BasicSEO_Torwald45::get_instance();
        $this->init_hooks();
        $this->load_admin_components();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_filter('plugin_action_links_' . BASICSEO_TORWALD45_PLUGIN_BASENAME, array($this, 'add_action_links'));
    }
    
    /**
     * Load admin components
     */
    private function load_admin_components() {
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'admin/class-settings-page.php';
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'admin/class-dashboard-page.php';
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Add submenu under Settings
        add_options_page(
            __('Basic SEO Settings', 'basic-seo-torwald45'),
            __('Basic SEO', 'basic-seo-torwald45'),
            'manage_options',
            'basic-seo-torwald45',
            array($this, 'admin_page_callback')
        );
    }
    
    /**
     * Admin page callback
     */
    public function admin_page_callback() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <nav class="nav-tab-wrapper">
                <a href="?page=basic-seo-torwald45&tab=dashboard" 
                   class="nav-tab <?php echo $active_tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Dashboard', 'basic-seo-torwald45'); ?>
                </a>
                <a href="?page=basic-seo-torwald45&tab=help" 
                   class="nav-tab <?php echo $active_tab === 'help' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Help', 'basic-seo-torwald45'); ?>
                </a>
            </nav>
            
            <div class="tab-content">
                <?php
                switch ($active_tab) {
                    case 'help':
                        $this->display_help_tab();
                        break;
                    case 'dashboard':
                    default:
                        $this->display_dashboard_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display dashboard tab
     */
    private function display_dashboard_tab() {
        $dashboard = new BasicSEO_Torwald45_Dashboard_Page();
        $dashboard->display();
    }
    
    /**
     * Display help tab
     */
    private function display_help_tab() {
        $settings = new BasicSEO_Torwald45_Settings_Page();
        $settings->display();
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_basic-seo-torwald45') {
            return;
        }
        
        wp_enqueue_style(
            'basic-seo-torwald45-admin',
            BASICSEO_TORWALD45_PLUGIN_URL . 'admin/assets/admin.css',
            array(),
            BASICSEO_TORWALD45_VERSION
        );
        
        wp_enqueue_script(
            'basic-seo-torwald45-admin',
            BASICSEO_TORWALD45_PLUGIN_URL . 'admin/assets/admin.js',
            array('jquery'),
            BASICSEO_TORWALD45_VERSION,
            true
        );
        
        wp_localize_script(
            'basic-seo-torwald45-admin',
            'basicSeoTorwald45Admin',
            array(
                'nonce' => wp_create_nonce('basic_seo_torwald45_admin'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'strings' => array(
                    'saving' => __('Saving...', 'basic-seo-torwald45'),
                    'saved' => __('Settings saved!', 'basic-seo-torwald45'),
                    'error' => __('Error saving settings!', 'basic-seo-torwald45'),
                )
            )
        );
    }
    
    /**
     * Add plugin action links
     */
    public function add_action_links($links) {
        $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=basic-seo-torwald45')) . '">' . 
                        __('Settings', 'basic-seo-torwald45') . '</a>';
        
        array_unshift($links, $settings_link);
        
        return $links;
    }
}
