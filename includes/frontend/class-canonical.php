<?php
/**
 * Canonical URLs Handler
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Canonical
 */
class BasicSEO_Torwald45_Canonical {
    
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
        // Remove WordPress default canonical (we'll add our own)
        remove_action('wp_head', 'rel_canonical');
        
        // Add our canonical
        add_action('wp_head', array($this, 'output_canonical'), 10);
    }
    
    /**
     * Output canonical link tag
     */
    public function output_canonical() {
        $canonical_url = $this->get_canonical_url();
        
        if ($canonical_url) {
            echo '<link rel="canonical" href="' . esc_url($canonical_url) . '">' . "\n";
        }
    }
    
    /**
     * Get canonical URL for current page
     */
    public function get_canonical_url() {
        // Don't output canonical on certain pages
        if (is_404() || is_search()) {
            return null;
        }
        
        $canonical_url = null;
        
        // Homepage
        if (is_front_page()) {
            $canonical_url = home_url('/');
        }
        // Blog homepage
        elseif (is_home()) {
            $canonical_url = get_permalink(get_option('page_for_posts'));
        }
        // Single posts, pages, products
        elseif (is_singular()) {
            $canonical_url = $this->get_singular_canonical();
        }
        // Category, tag, and taxonomy pages
        elseif (is_category() || is_tag() || is_tax()) {
            $canonical_url = $this->get_taxonomy_canonical();
        }
        // Author pages
        elseif (is_author()) {
            $canonical_url = $this->get_author_canonical();
        }
        // Date archives
        elseif (is_date()) {
            $canonical_url = $this->get_date_canonical();
        }
        // WooCommerce shop
        elseif (function_exists('is_shop') && is_shop()) {
            $canonical_url = $this->get_shop_canonical();
        }
        
        // Clean up URL and remove unwanted parameters
        if ($canonical_url) {
            $canonical_url = $this->clean_canonical_url($canonical_url);
        }
        
        // Allow filtering
        return apply_filters('basicseo_torwald45_canonical_url', $canonical_url);
    }
    
    /**
     * Get canonical URL for singular content
     */
    private function get_singular_canonical() {
        $post_id = get_queried_object_id();
        
        if (!$post_id) {
            return null;
        }
        
        // Get clean permalink
        $canonical_url = get_permalink($post_id);
        
        // Handle pagination for paginated posts
        $page = get_query_var('page');
        if ($page && $page > 1) {
            global $wp_rewrite;
            if ($wp_rewrite->using_permalinks()) {
                $canonical_url = trailingslashit($canonical_url) . user_trailingslashit($page, 'single_paged');
            } else {
                $canonical_url = add_query_arg('page', $page, $canonical_url);
            }
        }
        
        return $canonical_url;
    }
    
    /**
     * Get canonical URL for taxonomy pages
     */
    private function get_taxonomy_canonical() {
        $term = get_queried_object();
        
        if (!$term || !isset($term->term_id)) {
            return null;
        }
        
        $canonical_url = get_term_link($term);
        
        if (is_wp_error($canonical_url)) {
            return null;
        }
        
        // Handle pagination
        $paged = get_query_var('paged');
        if ($paged && $paged > 1) {
            global $wp_rewrite;
            if ($wp_rewrite->using_permalinks()) {
                $canonical_url = trailingslashit($canonical_url) . user_trailingslashit("page/{$paged}", 'paged');
            } else {
                $canonical_url = add_query_arg('paged', $paged, $canonical_url);
            }
        }
        
        return $canonical_url;
    }
    
    /**
     * Get canonical URL for author pages
     */
    private function get_author_canonical() {
        $author = get_queried_object();
        
        if (!$author || !isset($author->ID)) {
            return null;
        }
        
        $canonical_url = get_author_posts_url($author->ID);
        
        // Handle pagination
        $paged = get_query_var('paged');
        if ($paged && $paged > 1) {
            global $wp_rewrite;
            if ($wp_rewrite->using_permalinks()) {
                $canonical_url = trailingslashit($canonical_url) . user_trailingslashit("page/{$paged}", 'paged');
            } else {
                $canonical_url = add_query_arg('paged', $paged, $canonical_url);
            }
        }
        
        return $canonical_url;
    }
    
    /**
     * Get canonical URL for date archives
     */
    private function get_date_canonical() {
        $year = get_query_var('year');
        $month = get_query_var('monthnum');
        $day = get_query_var('day');
        
        if ($day) {
            $canonical_url = get_day_link($year, $month, $day);
        } elseif ($month) {
            $canonical_url = get_month_link($year, $month);
        } else {
            $canonical_url = get_year_link($year);
        }
        
        // Handle pagination
        $paged = get_query_var('paged');
        if ($paged && $paged > 1) {
            global $wp_rewrite;
            if ($wp_rewrite->using_permalinks()) {
                $canonical_url = trailingslashit($canonical_url) . user_trailingslashit("page/{$paged}", 'paged');
            } else {
                $canonical_url = add_query_arg('paged', $paged, $canonical_url);
            }
        }
        
        return $canonical_url;
    }
    
    /**
     * Get canonical URL for WooCommerce shop
     */
    private function get_shop_canonical() {
        if (!function_exists('wc_get_page_id')) {
            return null;
        }
        
        $shop_page_id = wc_get_page_id('shop');
        if ($shop_page_id <= 0) {
            return null;
        }
        
        $canonical_url = get_permalink($shop_page_id);
        
        // Handle pagination
        $paged = get_query_var('paged');
        if ($paged && $paged > 1) {
            global $wp_rewrite;
            if ($wp_rewrite->using_permalinks()) {
                $canonical_url = trailingslashit($canonical_url) . user_trailingslashit("page/{$paged}", 'paged');
            } else {
                $canonical_url = add_query_arg('paged', $paged, $canonical_url);
            }
        }
        
        return $canonical_url;
    }
    
    /**
     * Clean canonical URL by removing unwanted parameters
     */
    private function clean_canonical_url($url) {
        // Parse URL
        $parsed_url = parse_url($url);
        
        if (!$parsed_url) {
            return $url;
        }
        
        // Parameters to remove (tracking, session, etc.)
        $params_to_remove = array(
            // Analytics and tracking
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'gclid',
            'fbclid',
            'msclkid',
            '_ga',
            '_gl',
            
            // Social media
            'ref',
            'source',
            'campaign',
            
            // Session and cache
            'cache',
            'nocache',
            'ver',
            'version',
            '_',
            
            // WordPress specific
            'preview',
            'preview_id',
            'preview_nonce',
            
            // WooCommerce filters (optional - can be customized)
            'orderby',
            'min_price',
            'max_price'
        );
        
        // Allow filtering of parameters to remove
        $params_to_remove = apply_filters('basicseo_torwald45_canonical_remove_params', $params_to_remove);
        
        // Remove unwanted query parameters
        if (isset($parsed_url['query'])) {
            parse_str($parsed_url['query'], $query_params);
            
            foreach ($params_to_remove as $param) {
                unset($query_params[$param]);
            }
            
            // Rebuild query string
            if (!empty($query_params)) {
                $parsed_url['query'] = http_build_query($query_params);
            } else {
                unset($parsed_url['query']);
            }
        }
        
        // Remove fragment (everything after #)
        unset($parsed_url['fragment']);
        
        // Rebuild URL
        $clean_url = '';
        
        if (isset($parsed_url['scheme'])) {
            $clean_url .= $parsed_url['scheme'] . '://';
        }
        
        if (isset($parsed_url['host'])) {
            $clean_url .= $parsed_url['host'];
        }
        
        if (isset($parsed_url['port'])) {
            $clean_url .= ':' . $parsed_url['port'];
        }
        
        if (isset($parsed_url['path'])) {
            $clean_url .= $parsed_url['path'];
        }
        
        if (isset($parsed_url['query'])) {
            $clean_url .= '?' . $parsed_url['query'];
        }
        
        // Ensure proper trailing slash
        if (substr($clean_url, -1) !== '/' && !pathinfo($clean_url, PATHINFO_EXTENSION)) {
            $clean_url = trailingslashit($clean_url);
        }
        
        return $clean_url;
    }
    
    /**
     * Check if current URL matches canonical
     */
    public function is_canonical_correct() {
        $canonical_url = $this->get_canonical_url();
        $current_url = $this->get_current_url();
        
        return $canonical_url === $current_url;
    }
    
    /**
     * Get current page URL
     */
    private function get_current_url() {
        global $wp;
        
        $current_url = home_url(add_query_arg(array(), $wp->request));
        
        // Add query string if present
        if (!empty($_SERVER['QUERY_STRING'])) {
            $current_url .= '?' . $_SERVER['QUERY_STRING'];
        }
        
        return $current_url;
    }
    
    /**
     * Get canonical URL for specific post/page
     */
    public function get_post_canonical($post_id) {
        $old_post = get_queried_object();
        
        // Temporarily set the queried object
        global $wp_query;
        $wp_query->queried_object = get_post($post_id);
        $wp_query->queried_object_id = $post_id;
        
        $canonical = get_permalink($post_id);
        
        // Restore original queried object
        $wp_query->queried_object = $old_post;
        $wp_query->queried_object_id = $old_post ? $old_post->ID : 0;
        
        return $this->clean_canonical_url($canonical);
    }
    
    /**
     * Validate canonical URL
     */
    public function validate_canonical($url) {
        // Check if URL is valid
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Check if URL belongs to this site
        $site_url = parse_url(home_url());
        $canonical_url = parse_url($url);
        
        if ($site_url['host'] !== $canonical_url['host']) {
            return false;
        }
        
        return true;
    }
}
