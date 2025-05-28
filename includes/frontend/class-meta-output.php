<?php
/**
 * Meta Output Handler
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Meta_Output
 */
class BasicSEO_Torwald45_Meta_Output {
    
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
        add_action('wp_head', array($this, 'output_meta_tags'), 2);
    }
    
    /**
     * Output meta tags in head
     */
    public function output_meta_tags() {
        // Don't output in admin or during AJAX
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        // Don't output on search results, 404, etc.
        if (is_search() || is_404() || is_author() || is_date()) {
            return;
        }
        
        $this->output_basic_meta_tags();
    }
    
    /**
     * Output basic meta tags (description, keywords, etc.)
     */
    private function output_basic_meta_tags() {
        // Meta description
        $description = $this->get_meta_description();
        if ($description) {
            echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        }
        
        // Meta keywords (if available)
        $keywords = $this->get_meta_keywords();
        if ($keywords) {
            echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
        }
        
        // Robots meta
        $robots = $this->get_robots_meta();
        if ($robots) {
            echo '<meta name="robots" content="' . esc_attr($robots) . '">' . "\n";
        }
        
        // Generator tag (optional)
        if (apply_filters('basicseo_torwald45_show_generator', false)) {
            echo '<meta name="generator" content="Basic SEO Plugin Torwald45 ' . BASICSEO_TORWALD45_VERSION . '">' . "\n";
        }
    }
    
    /**
     * Get meta description for current page
     */
    private function get_meta_description() {
        // WooCommerce shop page
        if (function_exists('is_shop') && is_shop()) {
            return $this->get_shop_description();
        }
        
        // Single posts, pages, products
        if (is_singular()) {
            return $this->get_singular_description();
        }
        
        // Category/taxonomy pages
        if (is_category() || is_tag() || is_tax()) {
            return $this->get_taxonomy_description();
        }
        
        // WooCommerce product categories
        if (is_tax('product_cat')) {
            return $this->get_product_category_description();
        }
        
        // Homepage
        if (is_front_page() || is_home()) {
            return $this->get_homepage_description();
        }
        
        return null;
    }
    
    /**
     * Get description for WooCommerce shop page
     */
    private function get_shop_description() {
        if (!function_exists('wc_get_page_id')) {
            return null;
        }
        
        $shop_page_id = wc_get_page_id('shop');
        if ($shop_page_id <= 0) {
            return null;
        }
        
        $custom_desc = get_post_meta($shop_page_id, BASICSEO_TORWALD45_POST_DESC, true);
        
        if (!empty($custom_desc)) {
            return $custom_desc;
        }
        
        // Fallback to page excerpt or content
        $shop_page = get_post($shop_page_id);
        if ($shop_page) {
            if (!empty($shop_page->post_excerpt)) {
                return wp_trim_words($shop_page->post_excerpt, 25, '...');
            }
            
            $content = strip_tags($shop_page->post_content);
            if (!empty($content)) {
                return wp_trim_words($content, 25, '...');
            }
        }
        
        return null;
    }
    
    /**
     * Get description for singular content
     */
    private function get_singular_description() {
        $post_id = get_queried_object_id();
        
        if (!$post_id) {
            return null;
        }
        
        // Check if post type is supported
        $post_type = get_post_type($post_id);
        if (!in_array($post_type, BasicSEO_Torwald45::get_supported_post_types())) {
            return null;
        }
        
        $custom_desc = get_post_meta($post_id, BASICSEO_TORWALD45_POST_DESC, true);
        
        if (!empty($custom_desc)) {
            return $custom_desc;
        }
        
        // Fallback to post excerpt or content
        $post = get_post($post_id);
        if ($post) {
            if (!empty($post->post_excerpt)) {
                return wp_trim_words($post->post_excerpt, 25, '...');
            }
            
            $content = strip_tags($post->post_content);
            if (!empty($content)) {
                return wp_trim_words($content, 25, '...');
            }
        }
        
        return null;
    }
    
    /**
     * Get description for taxonomy pages
     */
    private function get_taxonomy_description() {
        $term = get_queried_object();
        
        if (!$term || !isset($term->term_id)) {
            return null;
        }
        
        // Check if taxonomy is supported
        if (!in_array($term->taxonomy, BasicSEO_Torwald45::get_supported_taxonomies())) {
            return null;
        }
        
        $custom_desc = get_term_meta($term->term_id, BASICSEO_TORWALD45_TERM_DESC, true);
        
        if (!empty($custom_desc)) {
            return $custom_desc;
        }
        
        // Fallback to term description
        if (!empty($term->description)) {
            return wp_trim_words(strip_tags($term->description), 25, '...');
        }
        
        return null;
    }
    
    /**
     * Get description for WooCommerce product category
     */
    private function get_product_category_description() {
        $term = get_queried_object();
        
        if (!$term || !isset($term->term_id) || $term->taxonomy !== 'product_cat') {
            return null;
        }
        
        $custom_desc = get_term_meta($term->term_id, BASICSEO_TORWALD45_TERM_DESC, true);
        
        if (!empty($custom_desc)) {
            return $custom_desc;
        }
        
        // Fallback to term description
        if (!empty($term->description)) {
            return wp_trim_words(strip_tags($term->description), 25, '...');
        }
        
        return null;
    }
    
    /**
     * Get description for homepage
     */
    private function get_homepage_description() {
        // Try to get from static front page
        if (is_front_page() && !is_home()) {
            $page_id = get_option('page_on_front');
            if ($page_id) {
                $custom_desc = get_post_meta($page_id, BASICSEO_TORWALD45_POST_DESC, true);
                if (!empty($custom_desc)) {
                    return $custom_desc;
                }
            }
        }
        
        // Fallback to site tagline
        $tagline = get_bloginfo('description');
        return !empty($tagline) ? $tagline : null;
    }
    
    /**
     * Get meta keywords (basic implementation)
     */
    private function get_meta_keywords() {
        // Note: Meta keywords are largely ignored by search engines
        // This is included for completeness but not actively used
        
        if (is_singular()) {
            $post_id = get_queried_object_id();
            $tags = get_the_tags($post_id);
            
            if ($tags && !is_wp_error($tags)) {
                $keywords = array();
                foreach ($tags as $tag) {
                    $keywords[] = $tag->name;
                }
                return implode(', ', $keywords);
            }
        }
        
        return null;
    }
    
    /**
     * Get robots meta directives
     */
    private function get_robots_meta() {
        $directives = array();
        
        // Default directives
        $directives[] = 'index';
        $directives[] = 'follow';
        
        // Specific page rules
        if (is_search()) {
            $directives = array('noindex', 'follow');
        } elseif (is_404()) {
            $directives = array('noindex', 'nofollow');
        } elseif (is_attachment()) {
            $directives = array('noindex', 'nofollow');
        }
        
        // Allow filtering
        $directives = apply_filters('basicseo_torwald45_robots_meta', $directives);
        
        return !empty($directives) ? implode(', ', $directives) : null;
    }
    
    /**
     * Check if current page has meta description
     */
    public function has_meta_description() {
        return !empty($this->get_meta_description());
    }
    
    /**
     * Get description length for SEO analysis
     */
    public function get_description_length($post_id = null) {
        if ($post_id) {
            $description = get_post_meta($post_id, BASICSEO_TORWALD45_POST_DESC, true);
        } else {
            $description = $this->get_meta_description();
        }
        
        return $description ? strlen($description) : 0;
    }
    
    /**
     * Validate description length
     */
    public function is_description_length_optimal($post_id = null) {
        $length = $this->get_description_length($post_id);
        
        // Optimal description length is typically 120-160 characters
        return $length >= 120 && $length <= 160;
    }
    
    /**
     * Get description recommendations
     */
    public function get_description_recommendations($post_id = null) {
        $recommendations = array();
        $length = $this->get_description_length($post_id);
        
        if ($length === 0) {
            $recommendations[] = __('Add a meta description', 'basic-seo-torwald45');
        } elseif ($length < 120) {
            $recommendations[] = __('Description is too short - consider adding more details', 'basic-seo-torwald45');
        } elseif ($length > 160) {
            $recommendations[] = __('Description is too long - may be truncated in search results', 'basic-seo-torwald45');
        }
        
        return $recommendations;
    }
    
    /**
     * Get structured data for current page
     */
    public function get_structured_data() {
        // This method can be extended to include JSON-LD structured data
        // For now, it's a placeholder for future functionality
        
        $data = array();
        
        if (is_singular()) {
            $post_id = get_queried_object_id();
            $post = get_post($post_id);
            
            if ($post) {
                $data['@context'] = 'https://schema.org';
                $data['@type'] = $this->get_schema_type($post->post_type);
                $data['name'] = get_the_title($post_id);
                $data['url'] = get_permalink($post_id);
                
                $description = $this->get_meta_description();
                if ($description) {
                    $data['description'] = $description;
                }
            }
        }
        
        return apply_filters('basicseo_torwald45_structured_data', $data);
    }
    
    /**
     * Get schema.org type for post type
     */
    private function get_schema_type($post_type) {
        $types = array(
            'post' => 'Article',
            'page' => 'WebPage',
            'product' => 'Product'
        );
        
        return isset($types[$post_type]) ? $types[$post_type] : 'Thing';
    }
}
