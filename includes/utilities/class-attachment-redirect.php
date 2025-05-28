<?php
/**
 * Attachment Redirect Handler
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Attachment_Redirect
 */
class BasicSEO_Torwald45_Attachment_Redirect {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('template_redirect', array($this, 'redirect_attachment_pages'), 1);
        add_filter('attachment_link', array($this, 'modify_attachment_link'), 10, 2);
        add_filter('wp_get_attachment_url', array($this, 'ensure_direct_file_access'), 10, 2);
    }
    
    /**
     * Redirect attachment pages to prevent duplicate content
     */
    public function redirect_attachment_pages() {
        // Only redirect on attachment pages
        if (!is_attachment()) {
            return;
        }
        
        global $post;
        
        if (!$post) {
            return;
        }
        
        $redirect_url = $this->get_redirect_url($post);
        
        if ($redirect_url) {
            // Use 301 permanent redirect for SEO
            wp_redirect($redirect_url, 301);
            exit;
        }
    }
    
    /**
     * Get redirect URL for attachment
     */
    private function get_redirect_url($attachment) {
        if (!$attachment) {
            return null;
        }
        
        // Priority 1: Redirect to parent post if exists
        if ($attachment->post_parent) {
            $parent_post = get_post($attachment->post_parent);
            
            if ($parent_post && $parent_post->post_status === 'publish') {
                return get_permalink($parent_post->ID);
            }
        }
        
        // Priority 2: Redirect to direct file URL for images
        if (wp_attachment_is_image($attachment->ID)) {
            $file_url = wp_get_attachment_url($attachment->ID);
            if ($file_url) {
                return $file_url;
            }
        }
        
        // Priority 3: Redirect to homepage as last resort
        return home_url('/');
    }
    
    /**
     * Modify attachment links to prevent attachment page URLs
     */
    public function modify_attachment_link($link, $attachment_id) {
        // Check if we should modify the link
        if (!$this->should_modify_attachment_link($attachment_id)) {
            return $link;
        }
        
        $attachment = get_post($attachment_id);
        
        if (!$attachment) {
            return $link;
        }
        
        // For images, link directly to the file
        if (wp_attachment_is_image($attachment_id)) {
            $file_url = wp_get_attachment_url($attachment_id);
            if ($file_url) {
                return $file_url;
            }
        }
        
        // For other attachments, link to parent post or homepage
        if ($attachment->post_parent) {
            $parent_post = get_post($attachment->post_parent);
            if ($parent_post && $parent_post->post_status === 'publish') {
                return get_permalink($parent_post->ID);
            }
        }
        
        return home_url('/');
    }
    
    /**
     * Ensure direct file access for attachment URLs
     */
    public function ensure_direct_file_access($url, $attachment_id) {
        // This filter ensures wp_get_attachment_url returns direct file URL
        // which is already the default behavior, but we keep it for consistency
        return $url;
    }
    
    /**
     * Check if attachment link should be modified
     */
    private function should_modify_attachment_link($attachment_id) {
        // Allow filtering to disable modification for specific attachments
        $should_modify = apply_filters('basicseo_torwald45_modify_attachment_link', true, $attachment_id);
        
        if (!$should_modify) {
            return false;
        }
        
        // Don't modify links in admin
        if (is_admin()) {
            return false;
        }
        
        // Don't modify during AJAX requests
        if (wp_doing_ajax()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Remove attachment pages from sitemaps
     */
    public function exclude_attachments_from_sitemap($posts, $post_type) {
        if ($post_type === 'attachment') {
            return array(); // Return empty array to exclude all attachments
        }
        
        return $posts;
    }
    
    /**
     * Add noindex meta to attachment pages (as backup)
     */
    public function add_attachment_noindex() {
        if (is_attachment()) {
            echo '<meta name="robots" content="noindex,nofollow">' . "\n";
        }
    }
    
    /**
     * Get attachment redirect statistics
     */
    public function get_redirect_stats() {
        global $wpdb;
        
        // Count total attachments
        $total_attachments = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
        );
        
        // Count attachments with parents
        $attachments_with_parents = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_type = 'attachment' AND post_parent > 0"
        );
        
        // Count orphaned attachments
        $orphaned_attachments = $total_attachments - $attachments_with_parents;
        
        return array(
            'total' => intval($total_attachments),
            'with_parents' => intval($attachments_with_parents),
            'orphaned' => intval($orphaned_attachments)
        );
    }
    
    /**
     * Get list of orphaned attachments
     */
    public function get_orphaned_attachments($limit = 50) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT ID, post_title, post_name, post_date 
             FROM {$wpdb->posts} 
             WHERE post_type = 'attachment' 
             AND post_parent = 0 
             ORDER BY post_date DESC 
             LIMIT %d",
            $limit
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Clean up attachment URLs in content
     */
    public function clean_attachment_urls_in_content($content) {
        if (empty($content)) {
            return $content;
        }
        
        // Find attachment page URLs in content
        $pattern = '/href=["\']([^"\']*\/attachment\/[^"\']*)["\'/i';
        
        $content = preg_replace_callback($pattern, function($matches) {
            $url = $matches[1];
            
            // Extract attachment ID from URL
            $attachment_id = url_to_postid($url);
            
            if ($attachment_id && get_post_type($attachment_id) === 'attachment') {
                $attachment = get_post($attachment_id);
                $new_url = $this->get_redirect_url($attachment);
                
                if ($new_url) {
                    return 'href="' . esc_url($new_url) . '"';
                }
            }
            
            return $matches[0]; // Return original if no replacement found
        }, $content);
        
        return $content;
    }
    
    /**
     * Handle attachment page requests in REST API
     */
    public function handle_rest_attachment_request($response, $post, $request) {
        if ($post->post_type !== 'attachment') {
            return $response;
        }
        
        // Add redirect information to REST response
        $redirect_url = $this->get_redirect_url($post);
        
        if ($redirect_url) {
            $response->data['redirect_url'] = $redirect_url;
            $response->data['redirect_type'] = '301';
        }
        
        return $response;
    }
    
    /**
     * Check if attachment page should be redirected
     */
    public function should_redirect_attachment($attachment_id) {
        // Allow filtering to disable redirect for specific attachments
        $should_redirect = apply_filters('basicseo_torwald45_redirect_attachment', true, $attachment_id);
        
        if (!$should_redirect) {
            return false;
        }
        
        // Don't redirect if viewing in admin
        if (is_admin()) {
            return false;
        }
        
        // Don't redirect during preview
        if (is_preview()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Log attachment redirects (for debugging)
     */
    private function log_redirect($attachment_id, $redirect_url, $reason) {
        // Only log if WP_DEBUG is enabled
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        $message = sprintf(
            'BasicSEO Attachment Redirect: ID %d redirected to %s (Reason: %s)',
            $attachment_id,
            $redirect_url,
            $reason
        );
        
        error_log($message);
    }
    
    /**
     * Get attachment redirect reason
     */
    private function get_redirect_reason($attachment) {
        if ($attachment->post_parent) {
            return 'parent_post';
        }
        
        if (wp_attachment_is_image($attachment->ID)) {
            return 'direct_file';
        }
        
        return 'homepage_fallback';
    }
}
