<?php
/**
 * Uninstall Basic SEO Plugin Torwald45
 *
 * @package BasicSEOTorwald45
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Clean up plugin data on uninstall
 */
class BasicSEO_Torwald45_Uninstaller {
    
    /**
     * Run uninstall process
     */
    public static function uninstall() {
        // Check if user wants to keep data
        $keep_data = get_option('basicseo_torwald45_keep_data_on_uninstall', false);
        
        if (!$keep_data) {
            self::remove_plugin_data();
        }
        
        // Always remove plugin options
        self::remove_plugin_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Remove all plugin data
     */
    private static function remove_plugin_data() {
        global $wpdb;
        
        // Remove post meta
        $wpdb->delete(
            $wpdb->postmeta,
            array(
                'meta_key' => 'basicseo_torwald45_post_title'
            )
        );
        
        $wpdb->delete(
            $wpdb->postmeta,
            array(
                'meta_key' => 'basicseo_torwald45_post_desc'
            )
        );
        
        // Remove term meta
        $wpdb->delete(
            $wpdb->termmeta,
            array(
                'meta_key' => 'basicseo_torwald45_term_title'
            )
        );
        
        $wpdb->delete(
            $wpdb->termmeta,
            array(
                'meta_key' => 'basicseo_torwald45_term_desc'
            )
        );
        
        // Clean up orphaned meta
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'basicseo_torwald45_%'");
        $wpdb->query("DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE 'basicseo_torwald45_%'");
    }
    
    /**
     * Remove plugin options
     */
    private static function remove_plugin_options() {
        // Remove main settings
        delete_option('basicseo_torwald45_settings');
        delete_option('basicseo_torwald45_version');
        delete_option('basicseo_torwald45_keep_data_on_uninstall');
        
        // Remove any other plugin options
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'basicseo_torwald45_%'");
        
        // Clean up autoload options
        wp_cache_delete('alloptions', 'options');
    }
}

// Run the uninstaller
BasicSEO_Torwald45_Uninstaller::uninstall();
