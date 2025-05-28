<?php
/**
 * Sitemap Generator
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Sitemap_Generator
 */
class BasicSEO_Torwald45_Sitemap_Generator {
    
    /**
     * Plugin instance
     */
    private $plugin;
    
    /**
     * Posts per page for pagination
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
     * Generate main sitemap index
     */
    public function generate_sitemap_index() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Add post type sitemaps
        $post_types = get_post_types(array('public' => true));
        unset($post_types['attachment']); // Exclude attachments
        
        foreach ($post_types as $post_type) {
            $count = wp_count_posts($post_type);
            if ($count && $count->publish > 0) {
                $pages = ceil($count->publish / $this->posts_per_page);
                
                for ($page = 1; $page <= $pages; $page++) {
                    $sitemap_url = home_url("/sitemap-post-type-{$post_type}");
                    if ($page > 1) {
                        $sitemap_url .= "-{$page}";
                    }
                    $sitemap_url .= '.xml';
                    
                    $lastmod = $this->get_post_type_lastmod($post_type);
                    
                    $xml .= '  <sitemap>' . "\n";
                    $xml .= '    <loc>' . esc_url($sitemap_url) . '</loc>' . "\n";
                    if ($lastmod) {
                        $xml .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
                    }
                    $xml .= '  </sitemap>' . "\n";
                }
            }
        }
        
        // Add taxonomy sitemaps
        $taxonomies = get_taxonomies(array('public' => true));
        
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => true,
                'number' => 1
            ));
            
            if (!empty($terms) && !is_wp_error($terms)) {
                $term_count = wp_count_terms(array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => true
                ));
                
                $pages = ceil($term_count / $this->posts_per_page);
                
                for ($page = 1; $page <= $pages; $page++) {
                    $sitemap_url = home_url("/sitemap-taxonomy-{$taxonomy}");
                    if ($page > 1) {
                        $sitemap_url .= "-{$page}";
                    }
                    $sitemap_url .= '.xml';
                    
                    $xml .= '  <sitemap>' . "\n";
                    $xml .= '    <loc>' . esc_url($sitemap_url) . '</loc>' . "\n";
                    $xml .= '  </sitemap>' . "\n";
                }
            }
        }
        
        $xml .= '</sitemapindex>' . "\n";
        
        return $xml;
    }
    
    /**
     * Generate sitemap for specific post type
     */
    public function generate_post_type_sitemap($post_type, $page = 1) {
        if (!post_type_exists($post_type)) {
            return $this->generate_error_xml(__('Post type does not exist', 'basic-seo-torwald45'));
        }
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        $offset = ($page - 1) * $this->posts_per_page;
        
        $posts = get_posts(array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $this->posts_per_page,
            'offset' => $offset,
            'orderby' => 'modified',
            'order' => 'DESC',
            'meta_query' => array(
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
            )
        ));
        
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $url = get_permalink($post->ID);
                if ($url) {
                    $xml .= '  <url>' . "\n";
                    $xml .= '    <loc>' . esc_url($url) . '</loc>' . "\n";
                    $xml .= '    <lastmod>' . get_the_modified_date('c', $post->ID) . '</lastmod>' . "\n";
                    $xml .= '    <changefreq>' . $this->get_changefreq($post_type) . '</changefreq>' . "\n";
                    $xml .= '    <priority>' . $this->get_priority($post_type, $post->ID) . '</priority>' . "\n";
                    $xml .= '  </url>' . "\n";
                }
            }
        }
        
        $xml .= '</urlset>' . "\n";
        
        return $xml;
    }
    
    /**
     * Generate sitemap for taxonomy
     */
    public function generate_taxonomy_sitemap($taxonomy, $page = 1) {
        if (!taxonomy_exists($taxonomy)) {
            return $this->generate_error_xml(__('Taxonomy does not exist', 'basic-seo-torwald45'));
        }
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        $offset = ($page - 1) * $this->posts_per_page;
        
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
            'number' => $this->posts_per_page,
            'offset' => $offset,
            'orderby' => 'count',
            'order' => 'DESC'
        ));
        
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $url = get_term_link($term);
                if (!is_wp_error($url)) {
                    $xml .= '  <url>' . "\n";
                    $xml .= '    <loc>' . esc_url($url) . '</loc>' . "\n";
                    $xml .= '    <lastmod>' . $this->get_term_lastmod($term) . '</lastmod>' . "\n";
                    $xml .= '    <changefreq>' . $this->get_taxonomy_changefreq($taxonomy) . '</changefreq>' . "\n";
                    $xml .= '    <priority>' . $this->get_taxonomy_priority($taxonomy, $term) . '</priority>' . "\n";
                    $xml .= '  </url>' . "\n";
                }
            }
        }
        
        $xml .= '</urlset>' . "\n";
        
        return $xml;
    }
    
    /**
     * Generate error XML
     */
    private function generate_error_xml($message) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<error>' . "\n";
        $xml .= '  <message>' . esc_html($message) . '</message>' . "\n";
        $xml .= '</error>' . "\n";
        
        return $xml;
    }
    
    /**
     * Get change frequency for post type
     */
    private function get_changefreq($post_type) {
        $frequencies = array(
            'post' => 'weekly',
            'page' => 'monthly',
            'product' => 'weekly'
        );
        
        $default_freq = 'monthly';
        
        $freq = isset($frequencies[$post_type]) ? $frequencies[$post_type] : $default_freq;
        
        return apply_filters('basicseo_torwald45_sitemap_changefreq', $freq, $post_type);
    }
    
    /**
     * Get change frequency for taxonomy
     */
    private function get_taxonomy_changefreq($taxonomy) {
        $frequencies = array(
            'category' => 'weekly',
            'post_tag' => 'weekly',
            'product_cat' => 'weekly',
            'product_tag' => 'monthly'
        );
        
        $default_freq = 'monthly';
        
        $freq = isset($frequencies[$taxonomy]) ? $frequencies[$taxonomy] : $default_freq;
        
        return apply_filters('basicseo_torwald45_sitemap_taxonomy_changefreq', $freq, $taxonomy);
    }
    
    /**
     * Get priority for post
     */
    private function get_priority($post_type, $post_id = null) {
        $priorities = array(
            'page' => '0.8',
            'post' => '0.6',
            'product' => '0.7'
        );
        
        $default_priority = '0.5';
        
        $priority = isset($priorities[$post_type]) ? $priorities[$post_type] : $default_priority;
        
        // Higher priority for front page
        if ($post_id && get_option('page_on_front') == $post_id) {
            $priority = '1.0';
        }
        
        return apply_filters('basicseo_torwald45_sitemap_priority', $priority, $post_type, $post_id);
    }
    
    /**
     * Get priority for taxonomy term
     */
    private function get_taxonomy_priority($taxonomy, $term) {
        $priorities = array(
            'category' => '0.6',
            'post_tag' => '0.4',
            'product_cat' => '0.7',
            'product_tag' => '0.4'
        );
        
        $default_priority = '0.5';
        
        $priority = isset($priorities[$taxonomy]) ? $priorities[$taxonomy] : $default_priority;
        
        // Higher priority for terms with more posts
        if ($term->count > 10) {
            $priority = floatval($priority) + 0.1;
            $priority = min($priority, 1.0); // Cap at 1.0
            $priority = number_format($priority, 1);
        }
        
        return apply_filters('basicseo_torwald45_sitemap_taxonomy_priority', $priority, $taxonomy, $term);
    }
    
    /**
     * Get last modification date for post type
     */
    private function get_post_type_lastmod($post_type) {
        global $wpdb;
        
        $lastmod = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_modified_gmt FROM {$wpdb->posts} 
                 WHERE post_type = %s AND post_status = 'publish' 
                 ORDER BY post_modified_gmt DESC LIMIT 1",
                $post_type
            )
        );
        
        if ($lastmod) {
            return date('c', strtotime($lastmod));
        }
        
        return null;
    }
    
    /**
     * Get last modification date for term
     */
    private function get_term_lastmod($term) {
        // Try to get the latest post in this term
        $latest_post = get_posts(array(
            'numberposts' => 1,
            'orderby' => 'modified',
            'order' => 'DESC',
            'tax_query' => array(
                array(
                    'taxonomy' => $term->taxonomy,
                    'terms' => $term->term_id
                )
            )
        ));
        
        if (!empty($latest_post)) {
            return get_the_modified_date('c', $latest_post[0]->ID);
        }
        
        // Fallback to current date
        return date('c');
    }
    
    /**
     * Check if URL should be included in sitemap
     */
    private function should_include_url($post_id) {
        // Check if post is password protected
        if (post_password_required($post_id)) {
            return false;
        }
        
        // Check for noindex meta
        $noindex = get_post_meta($post_id, '_robots_noindex', true);
        if ($noindex === '1') {
            return false;
        }
        
        // Check exclude list
        $excluded_posts = $this->plugin->get_setting('sitemap.exclude_posts', array());
        if (in_array($post_id, $excluded_posts)) {
            return false;
        }
        
        return apply_filters('basicseo_torwald45_sitemap_include_url', true, $post_id);
    }
    
    /**
     * Get sitemap images for post
     */
    private function get_post_images($post_id) {
        $images = array();
        
        // Featured image
        if (has_post_thumbnail($post_id)) {
            $attachment_id = get_post_thumbnail_id($post_id);
            $image_url = wp_get_attachment_image_url($attachment_id, 'large');
            $image_title = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            
            if ($image_url) {
                $images[] = array(
                    'url' => $image_url,
                    'title' => $image_title ?: get_the_title($post_id),
                    'caption' => wp_get_attachment_caption($attachment_id)
                );
            }
        }
        
        // Images from content
        $post = get_post($post_id);
        if ($post && $post->post_content) {
            $content_images = $this->extract_images_from_content($post->post_content);
            $images = array_merge($images, $content_images);
        }
        
        // Limit to 10 images per post (Google recommendation)
        return array_slice($images, 0, 10);
    }
    
    /**
     * Extract images from post content
     */
    private function extract_images_from_content($content) {
        $images = array();
        
        if (empty($content)) {
            return $images;
        }
        
        // Find all img tags
        preg_match_all('/<img[^>]+>/i', $content, $matches);
        
        if (!empty($matches[0])) {
            foreach ($matches[0] as $img_tag) {
                // Extract src
                if (preg_match('/src=["\']([^"\']+)["\']/', $img_tag, $src_match)) {
                    $src = $src_match[1];
                    
                    // Extract alt
                    $alt = '';
                    if (preg_match('/alt=["\']([^"\']*)["\']/', $img_tag, $alt_match)) {
                        $alt = $alt_match[1];
                    }
                    
                    // Extract title
                    $title = '';
                    if (preg_match('/title=["\']([^"\']*)["\']/', $img_tag, $title_match)) {
                        $title = $title_match[1];
                    }
                    
                    $images[] = array(
                        'url' => $src,
                        'title' => $title ?: $alt,
                        'caption' => $alt
                    );
                }
            }
        }
        
        return $images;
    }
    
    /**
     * Add images to sitemap URL entry
     */
    private function add_images_to_url($xml, $post_id) {
        $images = $this->get_post_images($post_id);
        
        if (empty($images)) {
            return $xml;
        }
        
        foreach ($images as $image) {
            $xml .= '    <image:image xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
            $xml .= '      <image:loc>' . esc_url($image['url']) . '</image:loc>' . "\n";
            
            if (!empty($image['title'])) {
                $xml .= '      <image:title>' . esc_html($image['title']) . '</image:title>' . "\n";
            }
            
            if (!empty($image['caption'])) {
                $xml .= '      <image:caption>' . esc_html($image['caption']) . '</image:caption>' . "\n";
            }
            
            $xml .= '    </image:image>' . "\n";
        }
        
        return $xml;
    }
    
    /**
     * Validate sitemap entry
     */
    private function validate_sitemap_entry($url, $lastmod = null) {
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Validate lastmod format
        if ($lastmod && !$this->validate_lastmod_format($lastmod)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate lastmod date format
     */
    private function validate_lastmod_format($lastmod) {
        // Should be in W3C datetime format
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $lastmod);
    }
}
