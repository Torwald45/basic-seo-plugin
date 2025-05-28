<?php
/**
 * Sitemap Handler
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Sitemap_Handler
 */
class BasicSEO_Torwald45_Sitemap_Handler {
    
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
        add_action('init', array($this, 'handle_sitemap_request'), 1);
        add_action('init', array($this, 'add_rewrite_rules'), 5);
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('parse_request', array($this, 'parse_sitemap_request'));
    }
    
    /**
     * Add rewrite rules for sitemaps
     */
    public function add_rewrite_rules() {
        // Main sitemap
        add_rewrite_rule(
            '^sitemap\.xml$',
            'index.php?basicseo_sitemap=index',
            'top'
        );
        
        // Post type sitemaps
        add_rewrite_rule(
            '^sitemap-post-type-([^.]+)\.xml$',
            'index.php?basicseo_sitemap=post_type&basicseo_sitemap_type=$matches[1]',
            'top'
        );
        
        // Post type sitemaps with pagination
        add_rewrite_rule(
            '^sitemap-post-type-([^.]+)-([0-9]+)\.xml$',
            'index.php?basicseo_sitemap=post_type&basicseo_sitemap_type=$matches[1]&basicseo_sitemap_page=$matches[2]',
            'top'
        );
        
        // Taxonomy sitemaps
        add_rewrite_rule(
            '^sitemap-taxonomy-([^.]+)\.xml$',
            'index.php?basicseo_sitemap=taxonomy&basicseo_sitemap_type=$matches[1]',
            'top'
        );
        
        // Taxonomy sitemaps with pagination
        add_rewrite_rule(
            '^sitemap-taxonomy-([^.]+)-([0-9]+)\.xml$',
            'index.php?basicseo_sitemap=taxonomy&basicseo_sitemap_type=$matches[1]&basicseo_sitemap_page=$matches[2]',
            'top'
        );
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'basicseo_sitemap';
        $vars[] = 'basicseo_sitemap_type';
        $vars[] = 'basicseo_sitemap_page';
        return $vars;
    }
    
    /**
     * Parse sitemap request
     */
    public function parse_sitemap_request($wp) {
        if (!isset($wp->query_vars['basicseo_sitemap'])) {
            return;
        }
        
        $sitemap_type = $wp->query_vars['basicseo_sitemap'];
        
        switch ($sitemap_type) {
            case 'index':
                $this->output_sitemap_index();
                break;
                
            case 'post_type':
                $post_type = isset($wp->query_vars['basicseo_sitemap_type']) ? 
                           sanitize_text_field($wp->query_vars['basicseo_sitemap_type']) : '';
                $page = isset($wp->query_vars['basicseo_sitemap_page']) ? 
                       intval($wp->query_vars['basicseo_sitemap_page']) : 1;
                $this->output_post_type_sitemap($post_type, $page);
                break;
                
            case 'taxonomy':
                $taxonomy = isset($wp->query_vars['basicseo_sitemap_type']) ? 
                          sanitize_text_field($wp->query_vars['basicseo_sitemap_type']) : '';
                $page = isset($wp->query_vars['basicseo_sitemap_page']) ? 
                       intval($wp->query_vars['basicseo_sitemap_page']) : 1;
                $this->output_taxonomy_sitemap($taxonomy, $page);
                break;
        }
    }
    
    /**
     * Handle legacy sitemap requests (fallback)
     */
    public function handle_sitemap_request() {
        $request_uri = $_SERVER['REQUEST_URI'];
        
        // Check if URL is related to sitemap
        if (strpos($request_uri, 'sitemap') === false) {
            return;
        }
        
        // Remove trailing slash only for sitemap URLs
        if (substr($request_uri, -1) === '/' && strpos($request_uri, 'sitemap') !== false) {
            wp_redirect(rtrim($request_uri, '/'), 301);
            exit;
        }
        
        // Handle direct XML requests (fallback if rewrite rules don't work)
        if (preg_match('/\/sitemap\.xml$/', $request_uri)) {
            $this->output_sitemap_index();
        } elseif (preg_match('/\/sitemap-post-type-([^.]+)\.xml$/', $request_uri, $matches)) {
            $this->output_post_type_sitemap($matches[1]);
        } elseif (preg_match('/\/sitemap-taxonomy-([^.]+)\.xml$/', $request_uri, $matches)) {
            $this->output_taxonomy_sitemap($matches[1]);
        }
    }
    
    /**
     * Output main sitemap index
     */
    private function output_sitemap_index() {
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/sitemap/class-sitemap-generator.php';
        $generator = new BasicSEO_Torwald45_Sitemap_Generator();
        
        $this->set_xml_headers();
        echo $generator->generate_sitemap_index();
        exit;
    }
    
    /**
     * Output post type sitemap
     */
    private function output_post_type_sitemap($post_type, $page = 1) {
        if (!post_type_exists($post_type)) {
            $this->output_404();
            return;
        }
        
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/sitemap/class-sitemap-generator.php';
        $generator = new BasicSEO_Torwald45_Sitemap_Generator();
        
        $this->set_xml_headers();
        echo $generator->generate_post_type_sitemap($post_type, $page);
        exit;
    }
    
    /**
     * Output taxonomy sitemap
     */
    private function output_taxonomy_sitemap($taxonomy, $page = 1) {
        if (!taxonomy_exists($taxonomy)) {
            $this->output_404();
            return;
        }
        
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/sitemap/class-sitemap-generator.php';
        $generator = new BasicSEO_Torwald45_Sitemap_Generator();
        
        $this->set_xml_headers();
        echo $generator->generate_taxonomy_sitemap($taxonomy, $page);
        exit;
    }
    
    /**
     * Set XML headers
     */
    private function set_xml_headers() {
        // Set content type
        header('Content-Type: application/xml; charset=utf-8');
        
        // Prevent caching in development
        if (defined('WP_DEBUG') && WP_DEBUG) {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        } else {
            // Cache for 1 hour in production
            header('Cache-Control: public, max-age=3600');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
        }
    }
    
    /**
     * Output 404 error for invalid sitemaps
     */
    private function output_404() {
        status_header(404);
        
        $this->set_xml_headers();
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<error>' . "\n";
        echo '  <message>' . __('Sitemap not found', 'basic-seo-torwald45') . '</message>' . "\n";
        echo '  <code>404</code>' . "\n";
        echo '</error>' . "\n";
        
        exit;
    }
    
    /**
     * Check if sitemap exists
     */
    public function sitemap_exists($sitemap_url) {
        $response = wp_remote_head($sitemap_url);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        return $response_code === 200;
    }
    
    /**
     * Get sitemap URLs
     */
    public function get_sitemap_urls() {
        $urls = array();
        
        // Main sitemap
        $urls['main'] = home_url('/sitemap.xml');
        
        // Post type sitemaps
        $post_types = get_post_types(array('public' => true));
        unset($post_types['attachment']); // Exclude attachments
        
        foreach ($post_types as $post_type) {
            $count = wp_count_posts($post_type);
            if ($count && $count->publish > 0) {
                $urls['post_types'][$post_type] = home_url("/sitemap-post-type-{$post_type}.xml");
            }
        }
        
        // Taxonomy sitemaps
        $taxonomies = get_taxonomies(array('public' => true));
        
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => true,
                'number' => 1
            ));
            
            if (!empty($terms) && !is_wp_error($terms)) {
                $urls['taxonomies'][$taxonomy] = home_url("/sitemap-taxonomy-{$taxonomy}.xml");
            }
        }
        
        return $urls;
    }
    
    /**
     * Ping search engines about sitemap
     */
    public function ping_search_engines() {
        $sitemap_url = home_url('/sitemap.xml');
        
        $search_engines = array(
            'google' => 'https://www.google.com/ping?sitemap=' . urlencode($sitemap_url),
            'bing' => 'https://www.bing.com/ping?sitemap=' . urlencode($sitemap_url)
        );
        
        $results = array();
        
        foreach ($search_engines as $engine => $ping_url) {
            $response = wp_remote_get($ping_url, array(
                'timeout' => 30,
                'user-agent' => 'Basic SEO Plugin Torwald45/' . BASICSEO_TORWALD45_VERSION
            ));
            
            if (is_wp_error($response)) {
                $results[$engine] = array(
                    'success' => false,
                    'error' => $response->get_error_message()
                );
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $results[$engine] = array(
                    'success' => $response_code === 200,
                    'response_code' => $response_code
                );
            }
        }
        
        return $results;
    }
    
    /**
     * Validate sitemap XML
     */
    public function validate_sitemap($xml_content) {
        // Basic XML validation
        $previous_use_errors = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml_content);
        $errors = libxml_get_errors();
        libxml_use_internal_errors($previous_use_errors);
        
        if ($doc === false || !empty($errors)) {
            return array(
                'valid' => false,
                'errors' => $errors
            );
        }
        
        return array('valid' => true);
    }
    
    /**
     * Get sitemap statistics
     */
    public function get_sitemap_stats() {
        $stats = array(
            'total_urls' => 0,
            'post_types' => array(),
            'taxonomies' => array()
        );
        
        // Count posts by type
        $post_types = get_post_types(array('public' => true));
        unset($post_types['attachment']);
        
        foreach ($post_types as $post_type) {
            $count = wp_count_posts($post_type);
            if ($count && $count->publish > 0) {
                $stats['post_types'][$post_type] = intval($count->publish);
                $stats['total_urls'] += intval($count->publish);
            }
        }
        
        // Count terms by taxonomy
        $taxonomies = get_taxonomies(array('public' => true));
        
        foreach ($taxonomies as $taxonomy) {
            $count = wp_count_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => true
            ));
            
            if ($count > 0) {
                $stats['taxonomies'][$taxonomy] = $count;
                $stats['total_urls'] += $count;
            }
        }
        
        return $stats;
    }
}
