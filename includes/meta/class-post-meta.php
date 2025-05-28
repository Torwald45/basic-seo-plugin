<?php
/**
 * Post Meta Boxes Handler
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Post_Meta
 */
class BasicSEO_Torwald45_Post_Meta {
    
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
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        $post_types = BasicSEO_Torwald45::get_supported_post_types();
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'basicseo_torwald45_meta_box',
                __('SEO Settings', 'basic-seo-torwald45'),
                array($this, 'meta_box_callback'),
                $post_type,
                'normal',
                'high'
            );
        }
    }
    
    /**
     * Meta box callback
     */
    public function meta_box_callback($post) {
        wp_nonce_field('basicseo_torwald45_meta_box', 'basicseo_torwald45_meta_box_nonce');
        
        $custom_title = get_post_meta($post->ID, BASICSEO_TORWALD45_POST_TITLE, true);
        $meta_desc = get_post_meta($post->ID, BASICSEO_TORWALD45_POST_DESC, true);
        
        $length_control = $this->plugin->get_setting('meta_description.length_control', 'none');
        $max_length = $this->plugin->get_setting('meta_description.max_length', 160);
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="basicseo_custom_title">
                        <?php _e('Title Tag', 'basic-seo-torwald45'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" 
                           id="basicseo_custom_title" 
                           name="basicseo_custom_title" 
                           value="<?php echo esc_attr($custom_title); ?>" 
                           class="large-text" 
                           placeholder="<?php echo esc_attr(get_the_title($post->ID)); ?>" />
                    <p class="description">
                        <?php _e('This will replace the default title in search results and browser tab.', 'basic-seo-torwald45'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="basicseo_meta_desc">
                        <?php _e('Meta Description', 'basic-seo-torwald45'); ?>
                    </label>
                </th>
                <td>
                    <textarea id="basicseo_meta_desc" 
                              name="basicseo_meta_desc" 
                              rows="4" 
                              class="large-text"
                              <?php if ($length_control === 'auto_cut') echo 'maxlength="' . esc_attr($max_length) . '"'; ?>
                              placeholder="<?php _e('Enter meta description for search engines...', 'basic-seo-torwald45'); ?>"
                    ><?php echo esc_textarea($meta_desc); ?></textarea>
                    
                    <?php if ($length_control !== 'none'): ?>
                        <p class="description">
                            <span id="basicseo-char-count">0</span> / <?php echo esc_html($max_length); ?> 
                            <?php _e('characters', 'basic-seo-torwald45'); ?>
                            <span id="basicseo-length-warning" style="color: #d63638; display: none;">
                                <?php _e('Warning: Description is too long for optimal SEO', 'basic-seo-torwald45'); ?>
                            </span>
                        </p>
                    <?php endif; ?>
                    
                    <p class="description">
                        <?php _e('Description for search engine results. Recommended length: 150-160 characters.', 'basic-seo-torwald45'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Enqueue scripts
     */
    public function enqueue_scripts($hook) {
        global $post;
        
        if (in_array($hook, array('post.php', 'post-new.php')) && 
            in_array($post->post_type, BasicSEO_Torwald45::get_supported_post_types())) {
            
            $length_control = $this->plugin->get_setting('meta_description.length_control', 'none');
            
            if ($length_control !== 'none') {
                wp_add_inline_script('jquery', $this->get_character_counter_script());
            }
        }
    }
    
    /**
     * Get character counter script
     */
    private function get_character_counter_script() {
        $max_length = $this->plugin->get_setting('meta_description.max_length', 160);
        $length_control = $this->plugin->get_setting('meta_description.length_control', 'none');
        
        return "
        jQuery(document).ready(function($) {
            var textarea = $('#basicseo_meta_desc');
            var counter = $('#basicseo-char-count');
            var warning = $('#basicseo-length-warning');
            var maxLength = " . intval($max_length) . ";
            var lengthControl = '" . esc_js($length_control) . "';
            
            function updateCounter() {
                var length = textarea.val().length;
                counter.text(length);
                
                if (length > maxLength && lengthControl === 'warning') {
                    warning.show();
                    counter.css('color', '#d63638');
                } else {
                    warning.hide();
                    counter.css('color', length > maxLength * 0.9 ? '#f56e28' : '#50575e');
                }
            }
            
            textarea.on('input', updateCounter);
            updateCounter();
        });
        ";
    }
    
    /**
     * Save meta data
     */
    public function save_meta($post_id) {
        // Security checks
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!isset($_POST['basicseo_torwald45_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['basicseo_torwald45_meta_box_nonce'], 'basicseo_torwald45_meta_box')) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save title
        if (isset($_POST['basicseo_custom_title'])) {
            $custom_title = sanitize_text_field($_POST['basicseo_custom_title']);
            if (!empty($custom_title)) {
                update_post_meta($post_id, BASICSEO_TORWALD45_POST_TITLE, $custom_title);
            } else {
                delete_post_meta($post_id, BASICSEO_TORWALD45_POST_TITLE);
            }
        }
        
        // Save description
        if (isset($_POST['basicseo_meta_desc'])) {
            $meta_desc = sanitize_textarea_field($_POST['basicseo_meta_desc']);
            
            // Auto-cut if enabled
            if ($this->plugin->get_setting('meta_description.length_control') === 'auto_cut') {
                $max_length = $this->plugin->get_setting('meta_description.max_length', 160);
                $meta_desc = substr($meta_desc, 0, $max_length);
            }
            
            if (!empty($meta_desc)) {
                update_post_meta($post_id, BASICSEO_TORWALD45_POST_DESC, $meta_desc);
            } else {
                delete_post_meta($post_id, BASICSEO_TORWALD45_POST_DESC);
            }
        }
    }
}
