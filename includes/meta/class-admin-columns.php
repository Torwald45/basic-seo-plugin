<?php
/**
 * Admin Columns Handler
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Admin_Columns
 */
class BasicSEO_Torwald45_Admin_Columns {

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
        // Add columns for supported post types
        $post_types = BasicSEO_Torwald45::get_supported_post_types();

        foreach ($post_types as $post_type) {
            add_filter("manage_{$post_type}_posts_columns", array($this, 'add_columns'), 20);
            add_action("manage_{$post_type}_posts_custom_column", array($this, 'column_content'), 10, 2);
        }

        // Legacy support for general post/page filters
        add_filter('manage_posts_columns', array($this, 'add_columns'), 20);
        add_filter('manage_pages_columns', array($this, 'add_columns'), 20);
        add_action('manage_posts_custom_column', array($this, 'column_content'), 10, 2);
        add_action('manage_pages_custom_column', array($this, 'column_content'), 10, 2);

        // Add CSS for columns
        add_action('admin_head', array($this, 'add_column_styles'));
    }

    /**
     * Add SEO columns to admin lists
     */
    public function add_columns($columns) {
        // Check if columns already added to prevent duplicates
        if (isset($columns['basicseo_title'])) {
            return $columns;
        }

        // Insert SEO columns after title column
        $new_columns = array();

        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;

            // Add SEO columns after title
            if ($key === 'title') {
                $new_columns['basicseo_title'] = __('Meta Title', 'basic-seo-torwald45');
                $new_columns['basicseo_desc'] = __('Meta Description', 'basic-seo-torwald45');
            }
        }

        return $new_columns;
    }

    /**
     * Display content in columns
     */
    public function column_content($column_name, $post_id) {
        static $displayed = array();

        // Prevent duplicate output
        $key = $post_id . '-' . $column_name;
        if (isset($displayed[$key])) {
            return;
        }
        $displayed[$key] = true;

        switch ($column_name) {
            case 'basicseo_title':
                $this->display_title_column($post_id);
                break;

            case 'basicseo_desc':
                $this->display_description_column($post_id);
                break;
        }
    }

    /**
     * Display title column content
     */
    private function display_title_column($post_id) {
        $title = get_post_meta($post_id, BASICSEO_TORWALD45_POST_TITLE, true);

        if ($title) {
            echo '<span class="basicseo-title" title="' . esc_attr($title) . '">';
            echo esc_html(wp_trim_words($title, 8, '...'));
            echo '</span>';
        } else {
            echo '<span class="basicseo-missing">—</span>';
        }
    }

    /**
     * Display description column content
     */
    private function display_description_column($post_id) {
        $desc = get_post_meta($post_id, BASICSEO_TORWALD45_POST_DESC, true);

        if ($desc) {
            echo '<span class="basicseo-desc" title="' . esc_attr($desc) . '">';
            echo esc_html(wp_trim_words($desc, 10, '...'));
            echo '</span>';
        } else {
            echo '<span class="basicseo-missing">—</span>';
        }
    }

    /**
     * Add CSS styles for columns
     */
    public function add_column_styles() {
        global $current_screen;

        if (!$current_screen || $current_screen->base !== 'edit') {
            return;
        }

        // Check if we're on a supported post type
        if (!in_array($current_screen->post_type, BasicSEO_Torwald45::get_supported_post_types())) {
            return;
        }
        ?>
        <style>
        .column-basicseo_title,
        .column-basicseo_desc {
            width: 15%;
        }

        .basicseo-title,
        .basicseo-desc {
            display: block;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .basicseo-missing {
            color: #a7aaad;
        }

        /* Mobile responsiveness */
        @media screen and (max-width: 782px) {
            .column-basicseo_title,
            .column-basicseo_desc {
                display: none;
            }
        }

        /* Improve readability on dark themes */
        .wp-core-ui .basicseo-title,
        .wp-core-ui .basicseo-desc {
            color: inherit;
        }
        </style>
        <?php
    }

    /**
     * Get column data for post
     */
    public function get_post_column_data($post_id) {
        return array(
            'title' => get_post_meta($post_id, BASICSEO_TORWALD45_POST_TITLE, true),
            'description' => get_post_meta($post_id, BASICSEO_TORWALD45_POST_DESC, true)
        );
    }
}
