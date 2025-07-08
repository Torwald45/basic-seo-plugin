<?php
/**
 * Settings Page (Help)
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Settings_Page
 */
class BasicSEO_Torwald45_Settings_Page {
    
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
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_init', array($this, 'handle_form_submission'));
    }
    
    /**
     * Handle form submission
     */
    public function handle_form_submission() {
        if (!isset($_POST['basicseo_torwald45_settings_nonce']) || 
            !wp_verify_nonce($_POST['basicseo_torwald45_settings_nonce'], 'basicseo_torwald45_settings')) {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Meta description settings
        if (isset($_POST['meta_description_length_control'])) {
            $this->plugin->update_setting(
                'meta_description.length_control', 
                sanitize_text_field($_POST['meta_description_length_control'])
            );
        }
        
        if (isset($_POST['meta_description_max_length'])) {
            $this->plugin->update_setting(
                'meta_description.max_length', 
                intval($_POST['meta_description_max_length'])
            );
        }
        
        // Breadcrumb settings
        if (isset($_POST['breadcrumbs_home_text'])) {
            $this->plugin->update_setting(
                'breadcrumbs.home_text', 
                sanitize_text_field($_POST['breadcrumbs_home_text'])
            );
        }
        
        if (isset($_POST['breadcrumbs_separator'])) {
            $this->plugin->update_setting(
                'breadcrumbs.separator', 
                sanitize_text_field($_POST['breadcrumbs_separator'])
            );
        }
        
        $breadcrumb_locations = array(
            'show_on_posts' => isset($_POST['breadcrumbs_show_on_posts']),
            'show_on_pages' => isset($_POST['breadcrumbs_show_on_pages']),
            'show_on_products' => isset($_POST['breadcrumbs_show_on_products'])
        );
        
        foreach ($breadcrumb_locations as $key => $value) {
            $this->plugin->update_setting("breadcrumbs.{$key}", $value);
        }
        
        // Sitemap settings
        if (isset($_POST['sitemap_posts_per_page'])) {
            $this->plugin->update_setting(
                'sitemap.posts_per_page', 
                intval($_POST['sitemap_posts_per_page'])
            );
        }
        
        // Open Graph settings
        if (isset($_POST['open_graph_default_image'])) {
            $this->plugin->update_setting(
                'open_graph.default_image', 
                esc_url_raw($_POST['open_graph_default_image'])
            );
        }
        
        if (isset($_POST['open_graph_facebook_app_id'])) {
            $this->plugin->update_setting(
                'open_graph.facebook_app_id', 
                sanitize_text_field($_POST['open_graph_facebook_app_id'])
            );
        }
        
        if (isset($_POST['open_graph_twitter_card_type'])) {
            $this->plugin->update_setting(
                'open_graph.twitter_card_type', 
                sanitize_text_field($_POST['open_graph_twitter_card_type'])
            );
        }
        
        // Show success message
        add_action('admin_notices', array($this, 'show_success_notice'));
    }
    
    /**
     * Show success notice
     */
    public function show_success_notice() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Settings saved successfully!', 'basic-seo-torwald45'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Display settings page
     */
    public function display() {
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('basicseo_torwald45_settings', 'basicseo_torwald45_settings_nonce'); ?>
            
            <div class="basicseo-settings-container">
                
                <!-- Meta Description Settings -->
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Meta Description Settings', 'basic-seo-torwald45'); ?></h2>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Length Control', 'basic-seo-torwald45'); ?></th>
                                <td>
                                    <select name="meta_description_length_control">
                                        <option value="none" <?php selected($this->plugin->get_setting('meta_description.length_control'), 'none'); ?>>
                                            <?php _e('No limit', 'basic-seo-torwald45'); ?>
                                        </option>
                                        <option value="warning" <?php selected($this->plugin->get_setting('meta_description.length_control'), 'warning'); ?>>
                                            <?php _e('Show warning', 'basic-seo-torwald45'); ?>
                                        </option>
                                        <option value="auto_cut" <?php selected($this->plugin->get_setting('meta_description.length_control'), 'auto_cut'); ?>>
                                            <?php _e('Auto cut', 'basic-seo-torwald45'); ?>
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Maximum Length', 'basic-seo-torwald45'); ?></th>
                                <td>
                                    <input type="number" name="meta_description_max_length" 
                                           value="<?php echo esc_attr($this->plugin->get_setting('meta_description.max_length', 160)); ?>" 
                                           min="50" max="300" />
                                    <p class="description"><?php _e('Recommended: 150-160 characters', 'basic-seo-torwald45'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Breadcrumb Settings -->
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Breadcrumb Settings', 'basic-seo-torwald45'); ?></h2>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Home Text', 'basic-seo-torwald45'); ?></th>
                                <td>
                                    <input type="text" name="breadcrumbs_home_text" 
                                           value="<?php echo esc_attr($this->plugin->get_setting('breadcrumbs.home_text', 'Home')); ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Separator', 'basic-seo-torwald45'); ?></th>
                                <td>
                                    <select name="breadcrumbs_separator">
                                        <option value=" &raquo; " <?php selected($this->plugin->get_setting('breadcrumbs.separator'), ' &raquo; '); ?>>&raquo;</option>
                                        <option value=" / " <?php selected($this->plugin->get_setting('breadcrumbs.separator'), ' / '); ?>>/</option>
                                        <option value=" > " <?php selected($this->plugin->get_setting('breadcrumbs.separator'), ' > '); ?>>&gt;</option>
                                        <option value=" | " <?php selected($this->plugin->get_setting('breadcrumbs.separator'), ' | '); ?>>|</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Show On', 'basic-seo-torwald45'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="breadcrumbs_show_on_posts" value="1" 
                                               <?php checked($this->plugin->get_setting('breadcrumbs.show_on_posts')); ?> />
                                        <?php _e('Posts', 'basic-seo-torwald45'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="breadcrumbs_show_on_pages" value="1" 
                                               <?php checked($this->plugin->get_setting('breadcrumbs.show_on_pages')); ?> />
                                        <?php _e('Pages', 'basic-seo-torwald45'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="breadcrumbs_show_on_products" value="1" 
                                               <?php checked($this->plugin->get_setting('breadcrumbs.show_on_products')); ?> />
                                        <?php _e('Products', 'basic-seo-torwald45'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Sitemap Settings -->
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Sitemap Settings', 'basic-seo-torwald45'); ?></h2>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Posts per page', 'basic-seo-torwald45'); ?></th>
                                <td>
                                    <input type="number" name="sitemap_posts_per_page" 
                                           value="<?php echo esc_attr($this->plugin->get_setting('sitemap.posts_per_page', 1000)); ?>" 
                                           min="100" max="50000" />
                                    <p class="description"><?php _e('Maximum number of URLs per sitemap page', 'basic-seo-torwald45'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Open Graph Settings -->
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Open Graph Settings', 'basic-seo-torwald45'); ?></h2>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Default Image URL', 'basic-seo-torwald45'); ?></th>
                                <td>
                                    <input type="url" name="open_graph_default_image" class="large-text"
                                           value="<?php echo esc_attr($this->plugin->get_setting('open_graph.default_image')); ?>" />
                                    <p class="description"><?php _e('Used when no featured image is set', 'basic-seo-torwald45'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Facebook App ID', 'basic-seo-torwald45'); ?></th>
                                <td>
                                    <input type="text" name="open_graph_facebook_app_id" 
                                           value="<?php echo esc_attr($this->plugin->get_setting('open_graph.facebook_app_id')); ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Twitter Card Type', 'basic-seo-torwald45'); ?></th>
                                <td>
                                    <select name="open_graph_twitter_card_type">
                                        <option value="summary" <?php selected($this->plugin->get_setting('open_graph.twitter_card_type'), 'summary'); ?>>
                                            <?php _e('Summary', 'basic-seo-torwald45'); ?>
                                        </option>
                                        <option value="summary_large_image" <?php selected($this->plugin->get_setting('open_graph.twitter_card_type'), 'summary_large_image'); ?>>
                                            <?php _e('Summary Large Image', 'basic-seo-torwald45'); ?>
                                        </option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
            </div>
            
            <?php submit_button(__('Save Changes', 'basic-seo-torwald45')); ?>
        </form>
        
        <style>
        .basicseo-settings-container .postbox {
            margin-bottom: 20px;
        }
        .basicseo-settings-container .postbox h2 {
            padding: 8px 12px;
            margin: 0;
            line-height: 1.4;
        }
        .basicseo-settings-container .inside {
            margin: 0;
            padding: 0 12px 12px;
        }
        </style>
        <?php
    }
}
