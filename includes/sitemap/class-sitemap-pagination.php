<?php
/**
 * Sitemap Pagination Handler
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Sitemap_Pagination
 */
class BasicSEO_Torwald45_Sitemap_Pagination {
    
    /**
     * Plugin instance
     */
    private $plugin;
    
    /**
     * Default posts per page
     */
    private $posts_per_page;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = BasicSEO_Torwald45::get_instance();
        $this->posts_per_page = $this->plugin->get_setting('sitemap.posts_per_page', 1000);
    }
    
    /**
     * Calculate pagination for post type
     */
    public function calculate_post_type_pagination($post_type) {
        $count = wp_count_posts($post_type);
        $published_count = $count && isset($count->publish) ? $count->publish : 0;
        
        return array(
            'total_posts' => $published_count,
            'posts_per_page' => $this->posts_per_page,
            'total_pages' => $published_count > 0 ? ceil($published_count / $this->posts_per_page) : 0,
            'has_pagination' => $published_count > $this->posts_per_page
        );
    }
    
    /**
     * Calculate pagination for taxonomy
     */
    public function calculate_taxonomy_pagination($taxonomy) {
        $term_count = wp_count_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true
        ));
        
        if (is_wp_error($term_count)) {
            $term_count = 0;
        }
        
        return array(
            'total_terms' => $term_count,
            'terms_per_page' => $this->posts_per_page,
            'total_pages' => $term_count > 0 ? ceil($term_count / $this->posts_per_page) : 0,
            'has_pagination' => $term_count > $this->posts_per_page
        );
    }
    
    /**
     * Get paginated posts for post type
     */
    public function get_paginated_posts($post_type, $page = 1) {
        $offset = ($page - 1) * $this->posts_per_page;
        
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $this->posts_per_page,
            'offset' => $offset,
            'orderby' => 'modified',
            'order' => 'DESC',
            'no_found_rows' => true, // Performance optimization
            'update_post_meta_cache' => false, // Performance optimization
            'update_post_term_cache' => false, // Performance optimization
        );
        
        // Exclude password protected posts
        $args['has_password'] = false;
        
        // Exclude posts with noindex meta
        $args['meta_query'] = array(
            'relation' => 'OR',
            array(
                'key' => '_robots_noindex',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'key' => '_robots_noindex',
                'value' => '1',
                'compare' => '!='
            )
        );
        
        // Apply filters
        $args = apply_filters('basicseo_torwald45_sitemap_posts_args', $args, $post_type, $page);
        
        return get_posts($args);
    }
    
    /**
     * Get paginated terms for taxonomy
     */
    public function get_paginated_terms($taxonomy, $page = 1) {
        $offset = ($page - 1) * $this->posts_per_page;
        
        $args = array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
            'number' => $this->posts_per_page,
            'offset' => $offset,
            'orderby' => 'count',
            'order' => 'DESC'
        );
        
        // Apply filters
        $args = apply_filters('basicseo_torwald45_sitemap_terms_args', $args, $taxonomy, $page);
        
        return get_terms($args);
    }
    
    /**
     * Generate pagination URLs for post type
     */
    public function get_post_type_pagination_urls($post_type) {
        $pagination = $this->calculate_post_type_pagination($post_type);
        $urls = array();
        
        if ($pagination['total_pages'] > 0) {
            for ($page = 1; $page <= $pagination['total_pages']; $page++) {
                $url = home_url("/sitemap-post-type-{$post_type}");
                if ($page > 1) {
                    $url .= "-{$page}";
                }
                $url .= '.xml';
                
                $urls[] = array(
                    'url' => $url,
                    'page' => $page,
                    'is_first' => $page === 1,
                    'is_last' => $page === $pagination['total_pages']
                );
            }
        }
        
        return $urls;
    }
    
    /**
     * Generate pagination URLs for taxonomy
     */
    public function get_taxonomy_pagination_urls($taxonomy) {
        $pagination = $this->calculate_taxonomy_pagination($taxonomy);
        $urls = array();
        
        if ($pagination['total_pages'] > 0) {
            for ($page = 1; $page <= $pagination['total_pages']; $page++) {
                $url = home_url("/sitemap-taxonomy-{$taxonomy}");
                if ($page > 1) {
                    $url .= "-{$page}";
                }
                $url .= '.xml';
                
                $urls[] = array(
                    'url' => $url,
                    'page' => $page,
                    'is_first' => $page === 1,
                    'is_last' => $page === $pagination['total_pages']
                );
            }
        }
        
        return $urls;
    }
    
    /**
     * Validate page number
     */
    public function validate_page_number($post_type_or_taxonomy, $page, $is_taxonomy = false) {
        if ($page < 1) {
            return false;
        }
        
        if ($is_taxonomy) {
            $pagination = $this->calculate_taxonomy_pagination($post_type_or_taxonomy);
        } else {
            $pagination = $this->calculate_post_type_pagination($post_type_or_taxonomy);
        }
        
        return $page <= $pagination['total_pages'];
    }
    
    /**
     * Get pagination info for current request
     */
    public function get_current_pagination_info($post_type_or_taxonomy, $current_page, $is_taxonomy = false) {
        if ($is_taxonomy) {
            $pagination = $this->calculate_taxonomy_pagination($post_type_or_taxonomy);
            $type = 'taxonomy';
        } else {
            $pagination = $this->calculate_post_type_pagination($post_type_or_taxonomy);
            $type = 'post_type';
        }
        
        $start_item = (($current_page - 1) * $this->posts_per_page) + 1;
        $end_item = min($current_page * $this->posts_per_page, $pagination['total_' . ($is_taxonomy ? 'terms' : 'posts')]);
        
        return array(
            'type' => $type,
            'name' => $post_type_or_taxonomy,
            'current_page' => $current_page,
            'total_pages' => $pagination['total_pages'],
            'per_page' => $this->posts_per_page,
            'total_items' => $pagination['total_' . ($is_taxonomy ? 'terms' : 'posts')],
            'start_item' => $start_item,
            'end_item' => $end_item,
            'has_previous' => $current_page > 1,
            'has_next' => $current_page < $pagination['total_pages'],
            'previous_url' => $this->get_previous_page_url($post_type_or_taxonomy, $current_page, $is_taxonomy),
            'next_url' => $this->get_next_page_url($post_type_or_taxonomy, $current_page, $is_taxonomy)
        );
    }
    
    /**
     * Get previous page URL
     */
    private function get_previous_page_url($post_type_or_taxonomy, $current_page, $is_taxonomy = false) {
        if ($current_page <= 1) {
            return null;
        }
        
        $previous_page = $current_page - 1;
        $prefix = $is_taxonomy ? 'sitemap-taxonomy-' : 'sitemap-post-type-';
        
        $url = home_url("/{$prefix}{$post_type_or_taxonomy}");
        if ($previous_page > 1) {
            $url .= "-{$previous_page}";
        }
        $url .= '.xml';
        
        return $url;
    }
    
    /**
     * Get next page URL
     */
    private function get_next_page_url($post_type_or_taxonomy, $current_page, $is_taxonomy = false) {
        if ($is_taxonomy) {
            $pagination = $this->calculate_taxonomy_pagination($post_type_or_taxonomy);
        } else {
            $pagination = $this->calculate_post_type_pagination($post_type_or_taxonomy);
        }
        
        if ($current_page >= $pagination['total_pages']) {
            return null;
        }
        
        $next_page = $current_page + 1;
        $prefix = $is_taxonomy ? 'sitemap-taxonomy-' : 'sitemap-post-type-';
        
        $url = home_url("/{$prefix}{$post_type_or_taxonomy}-{$next_page}.xml");
        
        return $url;
    }
    
    /**
     * Get all pagination URLs for sitemap index
     */
    public function get_all_pagination_urls() {
        $all_urls = array();
        
        // Post type URLs
        $post_types = get_post_types(array('public' => true));
        unset($post_types['attachment']); // Exclude attachments
        
        foreach ($post_types as $post_type) {
            $urls = $this->get_post_type_pagination_urls($post_type);
            if (!empty($urls)) {
                $all_urls['post_types'][$post_type] = $urls;
            }
        }
        
        // Taxonomy URLs
        $taxonomies = get_taxonomies(array('public' => true));
        
        foreach ($taxonomies as $taxonomy) {
            $urls = $this->get_taxonomy_pagination_urls($taxonomy);
            if (!empty($urls)) {
                $all_urls['taxonomies'][$taxonomy] = $urls;
            }
        }
        
        return $all_urls;
    }
    
    /**
     * Optimize pagination for large sites
     */
    public function optimize_pagination_for_large_sites() {
        global $wpdb;
        
        // Get total number of published posts
        $total_posts = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_status = 'publish' 
             AND post_type IN ('post', 'page', 'product')"
        );
        
        // If site has more than 10,000 posts, reduce posts per page
        if ($total_posts > 10000) {
            $this->posts_per_page = min($this->posts_per_page, 500);
        }
        
        // If site has more than 50,000 posts, reduce further
        if ($total_posts > 50000) {
            $this->posts_per_page = min($this->posts_per_page, 250);
        }
        
        return $this->posts_per_page;
    }
    
    /**
     * Get pagination statistics
     */
    public function get_pagination_stats() {
        $stats = array(
            'total_sitemaps' => 0,
            'post_types' => array(),
            'taxonomies' => array()
        );
        
        // Post type stats
        $post_types = get_post_types(array('public' => true));
        unset($post_types['attachment']);
        
        foreach ($post_types as $post_type) {
            $pagination = $this->calculate_post_type_pagination($post_type);
            if ($pagination['total_pages'] > 0) {
                $stats['post_types'][$post_type] = $pagination;
                $stats['total_sitemaps'] += $pagination['total_pages'];
            }
        }
        
        // Taxonomy stats
        $taxonomies = get_taxonomies(array('public' => true));
        
        foreach ($taxonomies as $taxonomy) {
            $pagination = $this->calculate_taxonomy_pagination($taxonomy);
            if ($pagination['total_pages'] > 0) {
                $stats['taxonomies'][$taxonomy] = $pagination;
                $stats['total_sitemaps'] += $pagination['total_pages'];
            }
        }
        
        return $stats;
    }
    
    /**
     * Check if pagination is needed for post type
     */
    public function is_pagination_needed($post_type) {
        $pagination = $this->calculate_post_type_pagination($post_type);
        return $pagination['has_pagination'];
    }
    
    /**
     * Check if pagination is needed for taxonomy
     */
    public function is_taxonomy_pagination_needed($taxonomy) {
        $pagination = $this->calculate_taxonomy_pagination($taxonomy);
        return $pagination['has_pagination'];
    }
    
    /**
     * Get recommended posts per page based on site size
     */
    public function get_recommended_posts_per_page() {
        global $wpdb;
        
        $total_posts = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_status = 'publish'"
        );
        
        if ($total_posts < 1000) {
            return 1000;
        } elseif ($total_posts < 10000) {
            return 500;
        } elseif ($total_posts < 50000) {
            return 250;
        } else {
            return 100;
        }
    }
}
