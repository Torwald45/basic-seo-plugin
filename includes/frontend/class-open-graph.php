<?php
/**
 * Open Graph Handler
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Open_Graph
 */
class BasicSEO_Torwald45_Open_Graph {
    
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
        add_action('wp_head', array($this, 'output_open_graph_tags'), 5);
    }
    
    /**
     * Output Open Graph meta tags
     */
    public function output_open_graph_tags() {
        // Don't output in admin or during AJAX
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        // Don't output on search results, 404, etc.
        if (is_search() || is_404() || is_author() || is_date()) {
            return;
        }
        
        $this->output_basic_og_tags();
        $this->output_twitter_tags();
        $this->output_facebook_tags();
    }
    
    /**
     * Output basic Open Graph tags
     */
    private function output_basic_og_tags() {
        // Basic required OG tags
        echo '<meta property="og:type" content="' . esc_attr($this->get_og_type()) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($this->get_og_title()) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($this->get_og_description()) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_attr($this->get_og_url()) . '">' . "\n";
        
        // Site name
        $site_name = get_bloginfo('name');
        if ($site_name) {
            echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
        }
        
        // Locale
        $locale = $this->get_og_locale();
        if ($locale) {
            echo '<meta property="og:locale" content="' . esc_attr($locale) . '">' . "\n";
        }
        
        // Image
        $image = $this->get_og_image();
        if ($image) {
            echo '<meta property="og:image" content="' . esc_attr($image['url']) . '">' . "\n";
            
            if (isset($image['width']) && isset($image['height'])) {
                echo '<meta property="og:image:width" content="' . esc_attr($image['width']) . '">' . "\n";
                echo '<meta property="og:image:height" content="' . esc_attr($image['height']) . '">' . "\n";
            }
            
            if (isset($image['alt'])) {
                echo '<meta property="og:image:alt" content="' . esc_attr($image['alt']) . '">' . "\n";
            }
        }
        
        // Additional tags for articles
        if (is_singular('post')) {
            $this->output_article_tags();
        }
        
        // Additional tags for products
        if (is_singular('product') && class_exists('WooCommerce')) {
            $this->output_product_tags();
        }
    }
    
    /**
     * Output Twitter Card tags
     */
    private function output_twitter_tags() {
        $card_type = $this->plugin->get_setting('open_graph.twitter_card_type', 'summary');
        
        echo '<meta name="twitter:card" content="' . esc_attr($card_type) . '">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($this->get_og_title()) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($this->get_og_description()) . '">' . "\n";
        
        $image = $this->get_og_image();
        if ($image) {
            echo '<meta name="twitter:image" content="' . esc_attr($image['url']) . '">' . "\n";
            
            if (isset($image['alt'])) {
                echo '<meta name="twitter:image:alt" content="' . esc_attr($image['alt']) . '">' . "\n";
            }
        }
        
        // Twitter site handle (can be added via filter)
        $twitter_site = apply_filters('basicseo_torwald45_twitter_site', '');
        if ($twitter_site) {
            echo '<meta name="twitter:site" content="' . esc_attr($twitter_site) . '">' . "\n";
        }
    }
    
    /**
     * Output Facebook-specific tags
     */
    private function output_facebook_tags() {
        $app_id = $this->plugin->get_setting('open_graph.facebook_app_id');
        
        if ($app_id) {
            echo '<meta property="fb:app_id" content="' . esc_attr($app_id) . '">' . "\n";
        }
        
        // Facebook admins (can be added via filter)
        $fb_admins = apply_filters('basicseo_torwald45_facebook_admins', '');
        if ($fb_admins) {
            echo '<meta property="fb:admins" content="' . esc_attr($fb_admins) . '">' . "\n";
        }
    }
    
    /**
     * Output article-specific Open Graph tags
     */
    private function output_article_tags() {
        $post_id = get_queried_object_id();
        $post = get_post($post_id);
        
        if (!$post) {
            return;
        }
        
        // Published time
        echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c', $post_id)) . '">' . "\n";
        
        // Modified time
        echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c', $post_id)) . '">' . "\n";
        
        // Author
        $author = get_the_author_meta('display_name', $post->post_author);
        if ($author) {
            echo '<meta property="article:author" content="' . esc_attr($author) . '">' . "\n";
        }
        
        // Section (category)
        $categories = get_the_category($post_id);
        if ($categories && !is_wp_error($categories)) {
            $primary_category = $categories[0];
            echo '<meta property="article:section" content="' . esc_attr($primary_category->name) . '">' . "\n";
        }
        
        // Tags
        $tags = get_the_tags($post_id);
        if ($tags && !is_wp_error($tags)) {
            foreach (array_slice($tags, 0, 5) as $tag) { // Limit to 5 tags
                echo '<meta property="article:tag" content="' . esc_attr($tag->name) . '">' . "\n";
            }
        }
    }
    
    /**
     * Output product-specific Open Graph tags
     */
    private function output_product_tags() {
        if (!class_exists('WC_Product')) {
            return;
        }
        
        $post_id = get_queried_object_id();
        $product = wc_get_product($post_id);
        
        if (!$product) {
            return;
        }
        
        // Product price
        $price = $product->get_price();
        if ($price) {
            echo '<meta property="product:price:amount" content="' . esc_attr($price) . '">' . "\n";
            echo '<meta property="product:price:currency" content="' . esc_attr(get_woocommerce_currency()) . '">' . "\n";
        }
        
        // Product availability
        $availability = $product->is_in_stock() ? 'instock' : 'oos';
        echo '<meta property="product:availability" content="' . esc_attr($availability) . '">' . "\n";
        
        // Product condition (assume new)
        echo '<meta property="product:condition" content="new">' . "\n";
        
        // Product brand (can be added via filter)
        $brand = apply_filters('basicseo_torwald45_product_brand', '', $product);
        if ($brand) {
            echo '<meta property="product:brand" content="' . esc_attr($brand) . '">' . "\n";
        }
    }
    
    /**
     * Get Open Graph type
     */
    private function get_og_type() {
        if (is_front_page() || is_home()) {
            return 'website';
        }
        
        if (is_singular('post')) {
            return 'article';
        }
        
        if (is_singular('product')) {
            return 'product';
        }
        
        if (is_singular()) {
            return 'article';
        }
        
        return 'website';
    }
    
    /**
     * Get Open Graph title
     */
    private function get_og_title() {
        // Try to get custom SEO title first
        $title_handler = new BasicSEO_Torwald45_Title_Handler();
        $custom_title = $title_handler->get_custom_title();
        
        if ($custom_title) {
            return $custom_title;
        }
        
        // Fallback to WordPress title
        return wp_get_document_title();
    }
    
    /**
     * Get Open Graph description
     */
    private function get_og_description() {
        $meta_output = new BasicSEO_Torwald45_Meta_Output();
        $description = $meta_output->get_meta_description();
        
        if ($description) {
            return $description;
        }
        
        // Fallback to site tagline for homepage
        if (is_front_page() || is_home()) {
            return get_bloginfo('description');
        }
        
        return get_bloginfo('description');
    }
    
    /**
     * Get Open Graph URL
     */
    private function get_og_url() {
        if (is_front_page()) {
            return home_url('/');
        }
        
        global $wp;
        return home_url(add_query_arg(array(), $wp->request));
    }
    
    /**
     * Get Open Graph locale
     */
    private function get_og_locale() {
        $locale = get_locale();
        
        // Convert WordPress locale to Facebook locale format
        $locale_map = array(
            'en_US' => 'en_US',
            'en_GB' => 'en_GB',
            'pl_PL' => 'pl_PL',
            'de_DE' => 'de_DE',
            'es_ES' => 'es_ES',
            'fr_FR' => 'fr_FR',
            'it_IT' => 'it_IT',
            'ru_RU' => 'ru_RU'
        );
        
        if (isset($locale_map[$locale])) {
            return $locale_map[$locale];
        }
        
        // Default fallback
        return 'en_US';
    }
    
    /**
     * Get Open Graph image
     */
    private function get_og_image() {
        $image = null;
        
        // Single post/page/product
        if (is_singular()) {
            $post_id = get_queried_object_id();
            
            // Try featured image first
            if (has_post_thumbnail($post_id)) {
                $image_id = get_post_thumbnail_id($post_id);
                $image_data = wp_get_attachment_image_src($image_id, 'large');
                
                if ($image_data) {
                    $image = array(
                        'url' => $image_data[0],
                        'width' => $image_data[1],
                        'height' => $image_data[2],
                        'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true)
                    );
                }
            }
            
            // Fallback: first image in content
            if (!$image) {
                $post = get_post($post_id);
                $image = $this->get_first_content_image($post->post_content);
            }
        }
        
        // Category/taxonomy pages
        if (!$image && (is_category() || is_tax('product_cat'))) {
            $term = get_queried_object();
            
            if ($term && is_tax('product_cat')) {
                $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                if ($thumbnail_id) {
                    $image_data = wp_get_attachment_image_src($thumbnail_id, 'large');
                    if ($image_data) {
                        $image = array(
                            'url' => $image_data[0],
                            'width' => $image_data[1],
                            'height' => $image_data[2]
                        );
                    }
                }
            }
        }
        
        // Fallback to default image
        if (!$image) {
            $default_image = $this->plugin->get_setting('open_graph.default_image');
            if ($default_image) {
                $image = array('url' => $default_image);
            }
        }
        
        return $image;
    }
    
    /**
     * Get first image from post content
     */
    private function get_first_content_image($content) {
        if (empty($content)) {
            return null;
        }
        
        preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $matches);
        
        if (isset($matches[1])) {
            return array('url' => $matches[1]);
        }
        
        return null;
    }
    
    /**
     * Validate Open Graph image
     */
    private function validate_og_image($image_url) {
        // Basic validation - check if URL looks valid
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Check if it's an image file
        $ext = pathinfo(parse_url($image_url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        
        return in_array(strtolower($ext), $allowed_extensions);
    }
}
