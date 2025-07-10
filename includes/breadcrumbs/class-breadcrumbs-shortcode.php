<?php
/**
 * Breadcrumbs Shortcode Handler
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Breadcrumbs_Shortcode
 */
class BasicSEO_Torwald45_Breadcrumbs_Shortcode {
    
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
        add_shortcode('basicseo-breadcrumb', array($this, 'breadcrumb_shortcode'));
        
        // Add shortcode to Gutenberg
        add_action('init', array($this, 'register_gutenberg_block'));
    }
    
    /**
     * Breadcrumb shortcode handler
     * 
     * Usage: [basicseo-breadcrumb]
     * Usage with parameters: [basicseo-breadcrumb separator=" > " home_text="Start"]
     */
    public function breadcrumb_shortcode($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'separator' => $this->plugin->get_setting('breadcrumbs.separator', ' &raquo; '),
            'home_text' => $this->plugin->get_setting('breadcrumbs.home_text', 'Home'),
            'show_home' => 'true',
            'show_current' => 'true',
            'class' => 'basicseo-breadcrumbs',
            'schema' => 'true',
            'before' => '',
            'after' => '',
            'max_depth' => '0' // 0 = no limit
        ), $atts, 'basicseo-breadcrumb');
        
        // Convert string booleans to actual booleans
        $show_home = filter_var($atts['show_home'], FILTER_VALIDATE_BOOLEAN);
        $show_current = filter_var($atts['show_current'], FILTER_VALIDATE_BOOLEAN);
        $schema = filter_var($atts['schema'], FILTER_VALIDATE_BOOLEAN);
        $max_depth = intval($atts['max_depth']);
        
        // Generate breadcrumbs
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/breadcrumbs/class-breadcrumbs-generator.php';
        $generator = new BasicSEO_Torwald45_Breadcrumbs_Generator();
        
        // Don't show if conditions not met
        if (!$generator->should_show_breadcrumbs()) {
            return '';
        }
        
        // Temporarily override settings for this shortcode
        $original_separator = $this->plugin->get_setting('breadcrumbs.separator');
        $original_home_text = $this->plugin->get_setting('breadcrumbs.home_text');
        
        $this->plugin->update_setting('breadcrumbs.separator', $atts['separator']);
        $this->plugin->update_setting('breadcrumbs.home_text', $atts['home_text']);
        
        // Generate breadcrumbs array
        $breadcrumbs = $generator->get_breadcrumbs_array();
        
        // Restore original settings
        $this->plugin->update_setting('breadcrumbs.separator', $original_separator);
        $this->plugin->update_setting('breadcrumbs.home_text', $original_home_text);
        
        if (empty($breadcrumbs)) {
            return '';
        }
        
        // Apply filters
        if (!$show_home && !empty($breadcrumbs)) {
            array_shift($breadcrumbs); // Remove first item (home)
        }
        
        if (!$show_current && !empty($breadcrumbs)) {
            array_pop($breadcrumbs); // Remove last item (current page)
        }
        
        // Apply max depth
        if ($max_depth > 0 && count($breadcrumbs) > $max_depth) {
            $breadcrumbs = array_slice($breadcrumbs, 0, $max_depth);
        }
        
        if (empty($breadcrumbs)) {
            return '';
        }
        
        // Render breadcrumbs
        $output = $atts['before'];
        $output .= $this->render_custom_breadcrumbs($breadcrumbs, $atts);
        $output .= $atts['after'];
        
        // Add JSON-LD schema if requested
        if ($schema) {
            $output .= $this->add_breadcrumb_schema($breadcrumbs);
        }
        
        return $output;
    }
    
    /**
     * Render custom breadcrumbs with shortcode attributes
     */
    private function render_custom_breadcrumbs($breadcrumbs, $atts) {
        $output = '<nav class="' . esc_attr($atts['class']) . '" aria-label="' . esc_attr__('Breadcrumb navigation', 'basic-seo-torwald45') . '">';
        $output .= '<ol class="breadcrumb-list">';
        
        foreach ($breadcrumbs as $index => $crumb) {
            $output .= '<li class="breadcrumb-item' . ($crumb['is_current'] ? ' current' : '') . '">';
            
            if (!$crumb['is_current'] && !empty($crumb['url'])) {
                $output .= '<a href="' . esc_url($crumb['url']) . '">' . esc_html($crumb['title']) . '</a>';
            } else {
                $output .= '<span>' . esc_html($crumb['title']) . '</span>';
            }
            
            if ($index < count($breadcrumbs) - 1) {
                $output .= '<span class="separator">' . $atts['separator'] . '</span>';
            }
            
            $output .= '</li>';
        }
        
        $output .= '</ol>';
        $output .= '</nav>';
        
        return $output;
    }
    
    /**
     * Add JSON-LD schema for breadcrumbs
     */
    private function add_breadcrumb_schema($breadcrumbs) {
        $structured_data = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array()
        );
        
        foreach ($breadcrumbs as $index => $crumb) {
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
        
        $json_ld = json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        return '<script type="application/ld+json">' . $json_ld . '</script>';
    }
    
    /**
     * Register Gutenberg block for breadcrumbs
     */
    public function register_gutenberg_block() {
        if (!function_exists('register_block_type')) {
            return;
        }
        
        wp_register_script(
            'basicseo-breadcrumbs-block',
            BASICSEO_TORWALD45_PLUGIN_URL . 'admin/assets/breadcrumbs-block.js',
            array('wp-blocks', 'wp-element', 'wp-editor'),
            BASICSEO_TORWALD45_VERSION
        );
        
        register_block_type('basicseo/breadcrumbs', array(
            'editor_script' => 'basicseo-breadcrumbs-block',
            'render_callback' => array($this, 'render_gutenberg_block'),
            'attributes' => array(
                'separator' => array(
                    'type' => 'string',
                    'default' => ' &raquo; '
                ),
                'homeText' => array(
                    'type' => 'string',
                    'default' => 'Home'
                ),
                'showHome' => array(
                    'type' => 'boolean',
                    'default' => true
                ),
                'showCurrent' => array(
                    'type' => 'boolean',
                    'default' => true
                ),
                'schema' => array(
                    'type' => 'boolean',
                    'default' => true
                )
            )
        ));
    }
    
    /**
     * Render Gutenberg block
     */
    public function render_gutenberg_block($attributes) {
        $shortcode_atts = array(
            'separator' => $attributes['separator'] ?? ' &raquo; ',
            'home_text' => $attributes['homeText'] ?? 'Home',
            'show_home' => $attributes['showHome'] ?? true ? 'true' : 'false',
            'show_current' => $attributes['showCurrent'] ?? true ? 'true' : 'false',
            'schema' => $attributes['schema'] ?? true ? 'true' : 'false'
        );
        
        return $this->breadcrumb_shortcode($shortcode_atts);
    }
    
    /**
     * Add breadcrumbs to widget
     */
    public function breadcrumb_widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $shortcode_atts = array(
            'separator' => $instance['separator'] ?? ' &raquo; ',
            'home_text' => $instance['home_text'] ?? 'Home',
            'show_home' => $instance['show_home'] ?? true ? 'true' : 'false',
            'show_current' => $instance['show_current'] ?? true ? 'true' : 'false',
            'schema' => $instance['schema'] ?? false ? 'true' : 'false' // Schema usually not needed in widgets
        );
        
        echo $this->breadcrumb_shortcode($shortcode_atts);
        
        echo $args['after_widget'];
    }
    
    /**
     * Get shortcode help text
     */
    public function get_shortcode_help() {
        return array(
            'shortcode' => '[basicseo-breadcrumb]',
            'description' => __('Display breadcrumb navigation on your pages', 'basic-seo-torwald45'),
            'parameters' => array(
                'separator' => array(
                    'description' => __('Separator between breadcrumb items', 'basic-seo-torwald45'),
                    'default' => ' &raquo; ',
                    'example' => 'separator=" > "'
                ),
                'home_text' => array(
                    'description' => __('Text for the home link', 'basic-seo-torwald45'),
                    'default' => 'Home',
                    'example' => 'home_text="Start"'
                ),
                'show_home' => array(
                    'description' => __('Whether to show the home link', 'basic-seo-torwald45'),
                    'default' => 'true',
                    'example' => 'show_home="false"'
                ),
                'show_current' => array(
                    'description' => __('Whether to show the current page', 'basic-seo-torwald45'),
                    'default' => 'true',
                    'example' => 'show_current="false"'
                ),
                'class' => array(
                    'description' => __('CSS class for the breadcrumb container', 'basic-seo-torwald45'),
                    'default' => 'basicseo-breadcrumbs',
                    'example' => 'class="my-breadcrumbs"'
                ),
                'schema' => array(
                    'description' => __('Whether to include JSON-LD schema markup', 'basic-seo-torwald45'),
                    'default' => 'true',
                    'example' => 'schema="false"'
                ),
                'before' => array(
                    'description' => __('HTML to display before the breadcrumbs', 'basic-seo-torwald45'),
                    'default' => '',
                    'example' => 'before="<div class=\'navigation\'>"'
                ),
                'after' => array(
                    'description' => __('HTML to display after the breadcrumbs', 'basic-seo-torwald45'),
                    'default' => '',
                    'example' => 'after="</div>"'
                ),
                'max_depth' => array(
                    'description' => __('Maximum number of breadcrumb items to show (0 = no limit)', 'basic-seo-torwald45'),
                    'default' => '0',
                    'example' => 'max_depth="5"'
                )
            ),
            'examples' => array(
                '[basicseo-breadcrumb]' => __('Basic breadcrumbs', 'basic-seo-torwald45'),
                '[basicseo-breadcrumb separator=" / " home_text="Start"]' => __('Custom separator and home text', 'basic-seo-torwald45'),
                '[basicseo-breadcrumb show_home="false"]' => __('Hide home link', 'basic-seo-torwald45'),
                '[basicseo-breadcrumb max_depth="3"]' => __('Limit to 3 breadcrumb levels', 'basic-seo-torwald45')
            )
        );
    }
    
    /**
     * Template function for themes
     */
    public static function display_breadcrumbs($args = array()) {
        $shortcode = new self();
        echo $shortcode->breadcrumb_shortcode($args);
    }
    
    /**
     * Check if breadcrumbs are available for current page
     */
    public function are_breadcrumbs_available() {
        require_once BASICSEO_TORWALD45_PLUGIN_DIR . 'includes/breadcrumbs/class-breadcrumbs-generator.php';
        $generator = new BasicSEO_Torwald45_Breadcrumbs_Generator();
        
        return $generator->should_show_breadcrumbs();
    }
}
