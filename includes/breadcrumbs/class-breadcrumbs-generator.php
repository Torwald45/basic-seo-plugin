<?php
/**
 * Breadcrumbs Generator
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Breadcrumbs_Generator
 */
class BasicSEO_Torwald45_Breadcrumbs_Generator {
    
    /**
     * Plugin instance
     */
    private $plugin;
    
    /**
     * Breadcrumb items
     */
    private $breadcrumbs = array();
    
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
        add_action('wp_head', array($this, 'add_breadcrumb_css'));
    }
    
    /**
     * Generate breadcrumbs
     */
    public function generate() {
        // Don't show on home page
        if (is_front_page()) {
            return '';
        }
        
        $this->breadcrumbs = array();
        
        // Add home link
        $this->add_home_breadcrumb();
        
        // Add specific breadcrumbs based on page type
        if (is_single()) {
            $this->add_single_breadcrumbs();
        } elseif (is_page()) {
            $this->add_page_breadcrumbs();
        } elseif (is_category()) {
            $this->add_category_breadcrumbs();
        } elseif (is_tag()) {
            $this->add_tag_breadcrumbs();
        } elseif (is_tax()) {
            $this->add_taxonomy_breadcrumbs();
        } elseif (is_author()) {
            $this->add_author_breadcrumbs();
        } elseif (is_date()) {
            $this->add_date_breadcrumbs();
        } elseif (is_search()) {
            $this->add_search_breadcrumbs();
        } elseif (is_404()) {
            $this->add_404_breadcrumbs();
        } elseif (function_exists('is_shop') && is_shop()) {
            $this->add_shop_breadcrumbs();
        } elseif (function_exists('is_product_category') && is_product_category()) {
            $this->add_product_category_breadcrumbs();
        } elseif (function_exists('is_product_tag') && is_product_tag()) {
            $this->add_product_tag_breadcrumbs();
        }
        
        return $this->render_breadcrumbs();
    }
    
    /**
     * Add home breadcrumb
     */
    private function add_home_breadcrumb() {
        $home_text = $this->plugin->get_setting('breadcrumbs.home_text', 'Home');
        
        $this->breadcrumbs[] = array(
            'title' => $home_text,
            'url' => home_url('/'),
            'is_current' => false
        );
    }
    
    /**
     * Add breadcrumbs for single posts
     */
    private function add_single_breadcrumbs() {
        global $post;
        
        if (!$post) {
            return;
        }
        
        // Add post type archive if exists
        $post_type = get_post_type();
        $post_type_object = get_post_type_object($post_type);
        
        if ($post_type !== 'post' && $post_type_object && $post_type_object->has_archive) {
            $this->breadcrumbs[] = array(
                'title' => $post_type_object->labels->name,
                'url' => get_post_type_archive_link($post_type),
                'is_current' => false
            );
        }
        
        // Add categories for posts
        if ($post_type === 'post') {
            $categories = get_the_category();
            if (!empty($categories)) {
                $category = $categories[0]; // Use primary category
                $this->add_category_hierarchy($category);
            }
        }
        
        // Add WooCommerce product categories
        if ($post_type === 'product' && function_exists('wc_get_product_terms')) {
            $product_cats = wc_get_product_terms($post->ID, 'product_cat', array('orderby' => 'parent'));
            if (!empty($product_cats)) {
                $this->add_product_category_hierarchy($product_cats[0]);
            }
        }
        
        // Add current post
        $this->breadcrumbs[] = array(
            'title' => get_the_title(),
            'url' => '',
            'is_current' => true
        );
    }
    
    /**
     * Add breadcrumbs for pages
     */
    private function add_page_breadcrumbs() {
        global $post;
        
        if (!$post) {
            return;
        }
        
        // Add parent pages
        if ($post->post_parent) {
            $ancestors = get_post_ancestors($post->ID);
            $ancestors = array_reverse($ancestors);
            
            foreach ($ancestors as $ancestor_id) {
                $this->breadcrumbs[] = array(
                    'title' => get_the_title($ancestor_id),
                    'url' => get_permalink($ancestor_id),
                    'is_current' => false
                );
            }
        }
        
        // Add current page
        $this->breadcrumbs[] = array(
            'title' => get_the_title(),
            'url' => '',
            'is_current' => true
        );
    }
    
    /**
     * Add breadcrumbs for categories
     */
    private function add_category_breadcrumbs() {
        $category = get_queried_object();
        
        if ($category) {
            $this->add_category_hierarchy($category, true);
        }
    }
    
    /**
     * Add breadcrumbs for tags
     */
    private function add_tag_breadcrumbs() {
        $tag = get_queried_object();
        
        if ($tag) {
            $this->breadcrumbs[] = array(
                'title' => __('Tags', 'basic-seo-torwald45'),
                'url' => '',
                'is_current' => false
            );
            
            $this->breadcrumbs[] = array(
                'title' => $tag->name,
                'url' => '',
                'is_current' => true
            );
        }
    }
    
    /**
     * Add breadcrumbs for taxonomy pages
     */
    private function add_taxonomy_breadcrumbs() {
        $term = get_queried_object();
        
        if ($term) {
            $taxonomy = get_taxonomy($term->taxonomy);
            
            if ($taxonomy) {
                $this->breadcrumbs[] = array(
                    'title' => $taxonomy->labels->name,
                    'url' => '',
                    'is_current' => false
                );
                
                $this->breadcrumbs[] = array(
                    'title' => $term->name,
                    'url' => '',
                    'is_current' => true
                );
            }
        }
    }
    
    /**
     * Add breadcrumbs for author pages
     */
    private function add_author_breadcrumbs() {
        $author = get_queried_object();
        
        if ($author) {
            $this->breadcrumbs[] = array(
                'title' => __('Authors', 'basic-seo-torwald45'),
                'url' => '',
                'is_current' => false
            );
            
            $this->breadcrumbs[] = array(
                'title' => $author->display_name,
                'url' => '',
                'is_current' => true
            );
        }
    }
    
    /**
     * Add breadcrumbs for date archives
     */
    private function add_date_breadcrumbs() {
        $year = get_query_var('year');
        $month = get_query_var('monthnum');
        $day = get_query_var('day');
        
        if ($year) {
            $this->breadcrumbs[] = array(
                'title' => $year,
                'url' => $day || $month ? get_year_link($year) : '',
                'is_current' => !$month && !$day
            );
        }
        
        if ($month) {
            $this->breadcrumbs[] = array(
                'title' => date_i18n('F', mktime(0, 0, 0, $month, 1)),
                'url' => $day ? get_month_link($year, $month) : '',
                'is_current' => !$day
            );
        }
        
        if ($day) {
            $this->breadcrumbs[] = array(
                'title' => $day,
                'url' => '',
                'is_current' => true
            );
        }
    }
    
    /**
     * Add breadcrumbs for search results
     */
    private function add_search_breadcrumbs() {
        $search_query = get_search_query();
        
        $this->breadcrumbs[] = array(
            'title' => sprintf(__('Search results for: %s', 'basic-seo-torwald45'), $search_query),
            'url' => '',
            'is_current' => true
        );
    }
    
    /**
     * Add breadcrumbs for 404 pages
     */
    private function add_404_breadcrumbs() {
        $this->breadcrumbs[] = array(
            'title' => __('Page not found', 'basic-seo-torwald45'),
            'url' => '',
            'is_current' => true
        );
    }
    
    /**
     * Add breadcrumbs for WooCommerce shop
     */
    private function add_shop_breadcrumbs() {
        if (!function_exists('wc_get_page_id')) {
            return;
        }
        
        $shop_page_id = wc_get_page_id('shop');
        if ($shop_page_id) {
            $this->breadcrumbs[] = array(
                'title' => get_the_title($shop_page_id),
                'url' => '',
                'is_current' => true
            );
        }
    }
    
    /**
     * Add breadcrumbs for WooCommerce product categories
     */
    private function add_product_category_breadcrumbs() {
        $term = get_queried_object();
        
        if ($term) {
            // Add shop page
            if (function_exists('wc_get_page_id')) {
                $shop_page_id = wc_get_page_id('shop');
                if ($shop_page_id) {
                    $this->breadcrumbs[] = array(
                        'title' => get_the_title($shop_page_id),
                        'url' => get_permalink($shop_page_id),
                        'is_current' => false
                    );
                }
            }
            
            $this->add_product_category_hierarchy($term, true);
        }
    }
    
    /**
     * Add breadcrumbs for WooCommerce product tags
     */
    private function add_product_tag_breadcrumbs() {
        $term = get_queried_object();
        
        if ($term) {
            // Add shop page
            if (function_exists('wc_get_page_id')) {
                $shop_page_id = wc_get_page_id('shop');
                if ($shop_page_id) {
                    $this->breadcrumbs[] = array(
                        'title' => get_the_title($shop_page_id),
                        'url' => get_permalink($shop_page_id),
                        'is_current' => false
                    );
                }
            }
            
            $this->breadcrumbs[] = array(
                'title' => $term->name,
                'url' => '',
                'is_current' => true
            );
        }
    }
    
    /**
     * Add category hierarchy
     */
    private function add_category_hierarchy($category, $include_current = false) {
        if (!$category) {
            return;
        }
        
        $hierarchy = array();
        $current_cat = $category;
        
        // Build hierarchy from current category up to root
        while ($current_cat) {
            array_unshift($hierarchy, $current_cat);
            $current_cat = $current_cat->parent ? get_category($current_cat->parent) : null;
        }
        
        // Add categories to breadcrumbs
        foreach ($hierarchy as $index => $cat) {
            $is_last = $index === count($hierarchy) - 1;
            $is_current = $include_current && $is_last;
            
            $this->breadcrumbs[] = array(
                'title' => $cat->name,
                'url' => $is_current ? '' : get_category_link($cat->term_id),
                'is_current' => $is_current
            );
        }
    }
    
    /**
     * Add product category hierarchy
     */
    private function add_product_category_hierarchy($category, $include_current = false) {
        if (!$category) {
            return;
        }
        
        $hierarchy = array();
        $current_cat = $category;
        
        // Build hierarchy from current category up to root
        while ($current_cat) {
            array_unshift($hierarchy, $current_cat);
            $current_cat = $current_cat->parent ? get_term($current_cat->parent, 'product_cat') : null;
        }
        
        // Add categories to breadcrumbs
        foreach ($hierarchy as $index => $cat) {
            $is_last = $index === count($hierarchy) - 1;
            $is_current = $include_current && $is_last;
            
            $this->breadcrumbs[] = array(
                'title' => $cat->name,
                'url' => $is_current ? '' : get_term_link($cat),
                'is_current' => $is_current
            );
        }
    }
    
    /**
     * Render breadcrumbs HTML
     */
    private function render_breadcrumbs() {
        if (empty($this->breadcrumbs)) {
            return '';
        }
        
        $separator = $this->plugin->get_setting('breadcrumbs.separator', ' &raquo; ');
        $output = '<nav class="basicseo-breadcrumbs" aria-label="' . esc_attr__('Breadcrumb navigation', 'basic-seo-torwald45') . '">';
        $output .= '<ol class="breadcrumb-list">';
        
        foreach ($this->breadcrumbs as $index => $crumb) {
            $output .= '<li class="breadcrumb-item' . ($crumb['is_current'] ? ' current' : '') . '">';
            
            if (!$crumb['is_current'] && !empty($crumb['url'])) {
                $output .= '<a href="' . esc_url($crumb['url']) . '">' . esc_html($crumb['title']) . '</a>';
            } else {
                $output .= '<span>' . esc_html($crumb['title']) . '</span>';
            }
            
            if ($index < count($this->breadcrumbs) - 1) {
                $output .= '<span class="separator">' . $separator . '</span>';
            }
            
            $output .= '</li>';
        }
        
        $output .= '</ol>';
        $output .= '</nav>';
        
        return $output;
    }
    
    /**
     * Add basic CSS for breadcrumbs
     */
    public function add_breadcrumb_css() {
        ?>
        <style>
        .basicseo-breadcrumbs {
            margin: 10px 0;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .basicseo-breadcrumbs .breadcrumb-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .basicseo-breadcrumbs .breadcrumb-item {
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
        }
        
        .basicseo-breadcrumbs .breadcrumb-item a {
            color: #007bff;
            text-decoration: none;
        }
        
        .basicseo-breadcrumbs .breadcrumb-item a:hover {
            text-decoration: underline;
        }
        
        .basicseo-breadcrumbs .breadcrumb-item.current span {
            color: #6c757d;
        }
        
        .basicseo-breadcrumbs .separator {
            margin: 0 5px;
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .basicseo-breadcrumbs {
                font-size: 12px;
            }
            
            .basicseo-breadcrumbs .separator {
                margin: 0 3px;
            }
        }
        </style>
        <?php
    }
    
    /**
     * Get breadcrumbs as array (for developers)
     */
    public function get_breadcrumbs_array() {
        $this->generate();
        return $this->breadcrumbs;
    }
    
    /**
     * Get JSON-LD structured data for breadcrumbs
     */
    public function get_breadcrumbs_json_ld() {
        if (empty($this->breadcrumbs)) {
            $this->generate();
        }
        
        if (empty($this->breadcrumbs)) {
            return '';
        }
        
        $structured_data = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array()
        );
        
        foreach ($this->breadcrumbs as $index => $crumb) {
            $item = array(
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['title']
            );
            
            if (!empty($crumb['url'])) {
                $item['item'] = $crumb['url'];
            }
            
            $structured_data['itemListElement'][] = $item;
        }
        
        return json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Check if breadcrumbs should be shown on current page
     */
    public function should_show_breadcrumbs() {
        // Don't show on home page
        if (is_front_page()) {
            return false;
        }
        
        // Check settings for specific page types
        if (is_single('post') && !$this->plugin->get_setting('breadcrumbs.show_on_posts')) {
            return false;
        }
        
        if (is_page() && !$this->plugin->get_setting('breadcrumbs.show_on_pages')) {
            return false;
        }
        
        if (is_single('product') && !$this->plugin->get_setting('breadcrumbs.show_on_products')) {
            return false;
        }
        
        return true;
    }
}
