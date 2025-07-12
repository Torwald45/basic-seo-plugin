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
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="basicseo_custom_title">
                        <?php _e('Meta Title', 'basic-seo-torwald45'); ?>
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
                        <span id="basicseo-title-count">0</span> / 60 
                        <?php _e('characters', 'basic-seo-torwald45'); ?>
                        <span id="basicseo-title-warning" style="color: #d63638; display: none;">
                            <?php _e('Warning: Title is too long for optimal SEO', 'basic-seo-torwald45'); ?>
                        </span>
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
                              placeholder="<?php _e('Enter meta description for search engines...', 'basic-seo-torwald45'); ?>"
                    ><?php echo esc_textarea($meta_desc); ?></textarea>
                    
                    <p class="description">
                        <span id="basicseo-desc-count">0</span> / 160 
                        <?php _e('characters', 'basic-seo-torwald45'); ?>
                        <span id="basicseo-desc-warning" style="color: #d63638; display: none;">
                            <?php _e('Warning: Description is too long for optimal SEO', 'basic-seo-torwald45'); ?>
                        </span>
                    </p>
                </td>
            </tr>
        </table>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Character counters for both fields
            var titleInput = $('#basicseo_custom_title');
            var descTextarea = $('#basicseo_meta_desc');
            var titleCounter = $('#basicseo-title-count');
            var descCounter = $('#basicseo-desc-count');
            var titleWarning = $('#basicseo-title-warning');
            var descWarning = $('#basicseo-desc-warning');

            function updateTitleCounter() {
                var length = titleInput.val().length;
                titleCounter.text(length);

                if (length > 60) {
                    titleWarning.show();
                    titleCounter.css('color', '#d63638');
                } else {
                    titleWarning.hide();
                    titleCounter.css('color', length > 54 ? '#f56e28' : '#50575e');
                }
            }

            function updateDescCounter() {
                var length = descTextarea.val().length;
                descCounter.text(length);

                if (length > 160) {
                    descWarning.show();
                    descCounter.css('color', '#d63638');
                } else {
                    descWarning.hide();
                    descCounter.css('color', length > 144 ? '#f56e28' : '#50575e');
                }
            }

            // Bind events
            titleInput.on('input', updateTitleCounter);
            descTextarea.on('input', updateDescCounter);

            // Initialize counters
            updateTitleCounter();
            updateDescCounter();
        });
        </script>
        <?php
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts($hook) {
        // Only enqueue jQuery if not already loaded
        if (in_array($hook, array('post.php', 'post-new.php'))) {
            wp_enqueue_script('jquery');
        }
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
            $meta_desc = sanitize_text_field($_POST['basicseo_meta_desc']);

            if (!empty($meta_desc)) {
                update_post_meta($post_id, BASICSEO_TORWALD45_POST_DESC, $meta_desc);
            } else {
                delete_post_meta($post_id, BASICSEO_TORWALD45_POST_DESC);
            }
        }
    }
}
