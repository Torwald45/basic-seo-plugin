<?php
/**
 * Title Handler
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Title_Handler
 */
class BasicSEO_Torwald45_Title_Handler {
    
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
    public function init_hooks() {
        // Hook into WordPress title generation
        add_filter('pre_get_document_title', array($this, 'modify_document_title'), 10);
        add_filter('document_title_parts', array($this, 'modify_title_parts'), 10);
        
        // Fallback for older themes that don't support wp_head title
        add_action('wp_head', array($this, 'maybe_output_title'), 1);
    }
    
    /**
     * Modify document title before it's generated
     */
    public function modify_document_title($title) {
        // Don't modify in admin or during AJAX requests
        if (is_admin() || wp_doing_ajax()) {
            return $title;
        }
        
        $custom_title = $this->get_custom_title();
        
        if ($custom_title) {
            return $custom_title;
        }
        
        return $title;
    }
    
    /**
     * Modify title parts for more granular control
     */
    public function modify_title_parts($parts) {
        // Don't modify in admin
        if (is_admin() || wp_doing_ajax()) {
            return $parts;
        }
        
        $custom_title = $this->get_custom_title();
        
        if ($custom_title) {
            // Replace the main title part but keep site name if it exists
            $parts['title'] = $custom_title;
        }
        
        return $parts;
    }
    
    /**
     * Get custom title for current page
     */
    public function get_custom_title() {
        // WooCommerce shop page
        if (function_exists('is_shop') && is_shop()) {
            return $this->get_shop_title();
        }
        
        // Single posts, pages, products
        if (is_singular()) {
            return $this->get_singular_title();
        }
        
        // Category/taxonomy pages
        if (is_category() || is_tag() || is_tax()) {
            return $this->get_taxonomy_title();
        }
        
        // WooCommerce product categories
        if (is_tax('product_cat')) {
            return $this->get_product_category_title();
        }
        
        return null;
    }
    
    /**
     * Get title for WooCommerce shop page
     */
    public function get_shop_title() {
        if (!function_exists('wc_get_page_id')) {
            return null;
        }
        
        $shop_page_id = wc_get_page_id('shop');
        if ($shop_page_id <= 0) {
            return null;
        }
        
        $custom_title = get_post_meta($shop_page_id, BASICSEO_TORWALD45_POST_TITLE, true);
        
        return !empty($custom_title) ? $custom_title : null;
    }
    
    /**
     * Get title for singular content (posts, pages, products)
     */
    public function get_singular_title() {
        $post_id = get_queried_object_id();
        
        if (!$post_id) {
            return null;
        }
        
        // Check if post type is supported
        $post_type = get_post_type($post_id);
        if (!in_array($post_type, BasicSEO_Torwald45::get_supported_post_types())) {
            return null;
        }
        
        $custom_title = get_post_meta($post_id, BASICSEO_TORWALD45_POST_TITLE, true);
        
        return !empty($custom_title) ? $custom_title : null;
    }
    
    /**
     * Get title for taxonomy pages
     */
    public function get_taxonomy_title() {
        $term = get_queried_object();
        
        if (!$term || !isset($term->term_id)) {
            return null;
        }
        
        // Check if taxonomy is supported
        if (!in_array($term->taxonomy, BasicSEO_Torwald45::get_supported_taxonomies())) {
            return null;
        }
        
        $custom_title = get_term_meta($term->term_id, BASICSEO_TORWALD45_TERM_TITLE, true);
        
        return !empty($custom_title) ? $custom_title : null;
    }
    
    /**
     * Get title for WooCommerce product category
     */
    public function get_product_category_title() {
        $term = get_queried_object();
        
        if (!$term || !isset($term->term_id) || $term->taxonomy !== 'product_cat') {
            return null;
        }
        
        $custom_title = get_term_meta($term->term_id, BASICSEO_TORWALD45_TERM_TITLE, true);
        
        return !empty($custom_title) ? $custom_title : null;
    }
    
    /**
     * Maybe output title tag for older themes
     */
    public function maybe_output_title() {
        // Only output if theme doesn't support title-tag
        if (current_theme_supports('title-tag')) {
            return;
        }
        
        $title = wp_get_document_title();
        
        if ($title) {
            echo '<title>' . esc_html($title) . '</title>' . "\n";
        }
    }
    
    /**
     * Get fallback title for a post
     */
    public function get_fallback_title($post_id) {
        $post = get_post($post_id);
        
        if (!$post) {
            return '';
        }
        
        // Use post title as fallback
        $title = $post->post_title;
        
        // Add site name if not home page
        if (!is_front_page()) {
            $site_name = get_bloginfo('name');
            if ($site_name) {
                $separator = apply_filters('document_title_separator', '-');
                $title .= " {$separator} {$site_name}";
            }
        }
        
        return $title;
    }
    
    /**
     * Get fallback title for a term
     */
    public function get_fallback_term_title($term_id) {
        $term = get_term($term_id);
        
        if (!$term || is_wp_error($term)) {
            return '';
        }
        
        // Use term name as fallback
        $title = $term->name;
        
        // Add site name
        $site_name = get_bloginfo('name');
        if ($site_name) {
            $separator = apply_filters('document_title_separator', '-');
            $title .= " {$separator} {$site_name}";
        }
        
        return $title;
    }
    
    /**
     * Check if current page has custom title
     */
    public function has_custom_title() {
        return !empty($this->get_custom_title());
    }
    
    /**
     * Get title length for SEO analysis
     */
    public function get_title_length($post_id = null) {
        if ($post_id) {
            $title = get_post_meta($post_id, BASICSEO_TORWALD45_POST_TITLE, true);
        } else {
            $title = $this->get_custom_title();
        }
        
        return $title ? strlen($title) : 0;
    }
    
    /**
     * Validate title length
     */
    public function is_title_length_optimal($post_id = null) {
        $length = $this->get_title_length($post_id);
        
        // Optimal title length is typically 30-60 characters
        return $length >= 30 && $length <= 60;
    }
    
    /**
     * Get title recommendations
     */
    public function get_title_recommendations($post_id = null) {
        $recommendations = array();
        $length = $this->get_title_length($post_id);
        
        if ($length === 0) {
            $recommendations[] = __('Add a custom SEO title', 'basic-seo-torwald45');
        } elseif ($length < 30) {
            $recommendations[] = __('Title is too short - consider adding more descriptive keywords', 'basic-seo-torwald45');
        } elseif ($length > 60) {
            $recommendations[] = __('Title is too long - may be truncated in search results', 'basic-seo-torwald45');
        }
        
        return $recommendations;
    }
}
