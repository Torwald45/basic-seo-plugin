<?php
/**
 * Term Meta Fields Handler
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Term_Meta
 */
class BasicSEO_Torwald45_Term_Meta {
    
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
        // WooCommerce category fields
        if (class_exists('WooCommerce')) {
            add_action('product_cat_add_form_fields', array($this, 'add_category_fields'));
            add_action('product_cat_edit_form_fields', array($this, 'edit_category_fields'));
            add_action('created_product_cat', array($this, 'save_category_fields'));
            add_action('edited_product_cat', array($this, 'save_category_fields'));
            
            // Category columns
            add_filter('manage_edit-product_cat_columns', array($this, 'add_category_columns'));
            add_filter('manage_product_cat_custom_column', array($this, 'category_column_content'), 10, 3);
        }
        
        // Regular taxonomies (categories, tags)
        $taxonomies = BasicSEO_Torwald45::get_supported_taxonomies();
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy !== 'product_cat') { // Skip product_cat as it's handled above
                add_action("{$taxonomy}_add_form_fields", array($this, 'add_term_fields'));
                add_action("{$taxonomy}_edit_form_fields", array($this, 'edit_term_fields'));
                add_action("created_{$taxonomy}", array($this, 'save_term_fields'));
                add_action("edited_{$taxonomy}", array($this, 'save_term_fields'));
            }
        }
    }
    
    /**
     * Add WooCommerce category fields
     */
    public function add_category_fields() {
        $this->render_add_fields();
    }
    
    /**
     * Add regular taxonomy fields
     */
    public function add_term_fields() {
        $this->render_add_fields();
    }
    
    /**
     * Render add form fields
     */
    private function render_add_fields() {
        ?>
        <div class="form-field">
            <label for="basicseo_custom_title"><?php _e('SEO Title', 'basic-seo-torwald45'); ?></label>
            <input type="text" name="basicseo_custom_title" id="basicseo_custom_title" />
            <p class="description"><?php _e('Custom title for search results and browser tab.', 'basic-seo-torwald45'); ?></p>
        </div>
        <div class="form-field">
            <label for="basicseo_meta_desc"><?php _e('Meta Description', 'basic-seo-torwald45'); ?></label>
            <textarea name="basicseo_meta_desc" id="basicseo_meta_desc" rows="4"></textarea>
            <p class="description"><?php _e('Description for search engine results.', 'basic-seo-torwald45'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Edit WooCommerce category fields
     */
    public function edit_category_fields($term) {
        $this->render_edit_fields($term);
    }
    
    /**
     * Edit regular taxonomy fields
     */
    public function edit_term_fields($term) {
        $this->render_edit_fields($term);
    }
    
    /**
     * Render edit form fields
     */
    private function render_edit_fields($term) {
        $custom_title = get_term_meta($term->term_id, BASICSEO_TORWALD45_TERM_TITLE, true);
        $meta_desc = get_term_meta($term->term_id, BASICSEO_TORWALD45_TERM_DESC, true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="basicseo_custom_title"><?php _e('SEO Title', 'basic-seo-torwald45'); ?></label>
            </th>
            <td>
                <input type="text" 
                       name="basicseo_custom_title" 
                       id="basicseo_custom_title" 
                       value="<?php echo esc_attr($custom_title); ?>" 
                       class="large-text" />
                <p class="description"><?php _e('Custom title for search results and browser tab.', 'basic-seo-torwald45'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="basicseo_meta_desc"><?php _e('Meta Description', 'basic-seo-torwald45'); ?></label>
            </th>
            <td>
                <textarea name="basicseo_meta_desc" 
                          id="basicseo_meta_desc" 
                          rows="4" 
                          class="large-text"><?php echo esc_textarea($meta_desc); ?></textarea>
                <p class="description"><?php _e('Description for search engine results.', 'basic-seo-torwald45'); ?></p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save WooCommerce category fields
     */
    public function save_category_fields($term_id) {
        $this->save_term_meta($term_id);
    }
    
    /**
     * Save regular taxonomy fields
     */
    public function save_term_fields($term_id) {
        $this->save_term_meta($term_id);
    }
    
    /**
     * Save term meta data
     */
    private function save_term_meta($term_id) {
        // Check permissions
        if (!current_user_can('manage_categories')) {
            return;
        }
        
        // Save title
        if (isset($_POST['basicseo_custom_title'])) {
            $title = sanitize_text_field($_POST['basicseo_custom_title']);
            if (!empty($title)) {
                update_term_meta($term_id, BASICSEO_TORWALD45_TERM_TITLE, $title);
            } else {
                delete_term_meta($term_id, BASICSEO_TORWALD45_TERM_TITLE);
            }
        }
        
        // Save description
        if (isset($_POST['basicseo_meta_desc'])) {
            $desc = sanitize_textarea_field($_POST['basicseo_meta_desc']);
            if (!empty($desc)) {
                update_term_meta($term_id, BASICSEO_TORWALD45_TERM_DESC, $desc);
            } else {
                delete_term_meta($term_id, BASICSEO_TORWALD45_TERM_DESC);
            }
        }
    }
    
    /**
     * Add category columns
     */
    public function add_category_columns($columns) {
        $new_columns = array();
        
        // Preserve existing columns in order
        if (isset($columns['cb'])) {
            $new_columns['cb'] = $columns['cb'];
        }
        if (isset($columns['thumb'])) {
            $new_columns['thumb'] = $columns['thumb'];
        }
        
        $new_columns['name'] = $columns['name'];
        
        // Add our SEO columns
        $new_columns['basicseo_title'] = __('SEO Title', 'basic-seo-torwald45');
        $new_columns['basicseo_desc'] = __('Meta Description', 'basic-seo-torwald45');
        
        // Preserve remaining standard columns
        if (isset($columns['description'])) {
            $new_columns['description'] = $columns['description'];
        }
        if (isset($columns['slug'])) {
            $new_columns['slug'] = $columns['slug'];
        }
        if (isset($columns['count'])) {
            $new_columns['count'] = $columns['count'];
        }
        
        return $new_columns;
    }
    
    /**
     * Category column content
     */
    public function category_column_content($content, $column_name, $term_id) {
        switch ($column_name) {
            case 'basicseo_title':
                $title = get_term_meta($term_id, BASICSEO_TORWALD45_TERM_TITLE, true);
                return $title ? esc_html($title) : '—';
                
            case 'basicseo_desc':
                $desc = get_term_meta($term_id, BASICSEO_TORWALD45_TERM_DESC, true);
                return $desc ? esc_html(wp_trim_words($desc, 10, '...')) : '—';
        }
        return $content;
    }
    
    /**
     * Get term SEO data
     */
    public function get_term_seo_data($term_id) {
        return array(
            'title' => get_term_meta($term_id, BASICSEO_TORWALD45_TERM_TITLE, true),
            'description' => get_term_meta($term_id, BASICSEO_TORWALD45_TERM_DESC, true)
        );
    }
    
    /**
     * Check if term has complete SEO data
     */
    public function has_complete_seo($term_id) {
        $seo_data = $this->get_term_seo_data($term_id);
        return !empty($seo_data['title']) && !empty($seo_data['description']);
    }
}
