<?php
/**
 * Quick Edit Handler
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Quick_Edit
 */
class BasicSEO_Torwald45_Quick_Edit {
    
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
        // Quick edit fields
        add_action('quick_edit_custom_box', array($this, 'add_quick_edit_fields'), 10, 2);
        
        // Save quick edit data
        add_action('save_post', array($this, 'save_quick_edit'), 10, 1);
        
        // Add JavaScript for quick edit
        add_action('admin_footer-edit.php', array($this, 'add_quick_edit_javascript'));
        
        // Enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Add quick edit fields
     */
    public function add_quick_edit_fields($column_name, $post_type) {
        // Only add fields for supported post types and the right column
        if (!in_array($post_type, BasicSEO_Torwald45::get_supported_post_types()) || 
            $column_name !== 'basicseo_title') {
            return;
        }
        
        wp_nonce_field('basicseo_torwald45_quick_edit', 'basicseo_torwald45_quick_edit_nonce');
        ?>
        <fieldset class="inline-edit-col-right basicseo-quick-edit">
            <div class="inline-edit-col">
                <div class="inline-edit-group">
                    <label class="alignleft">
                        <span class="title"><?php _e('SEO Title', 'basic-seo-torwald45'); ?></span>
                        <span class="input-text-wrap">
                            <input type="text" 
                                   name="basicseo_custom_title" 
                                   class="basicseo-title-input ptitle" 
                                   value="" 
                                   maxlength="255" />
                        </span>
                    </label>
                </div>
                
                <div class="inline-edit-group">
                    <label class="alignleft">
                        <span class="title"><?php _e('Meta Description', 'basic-seo-torwald45'); ?></span>
                        <span class="input-text-wrap">
                            <textarea name="basicseo_meta_desc" 
                                      class="basicseo-desc-input" 
                                      rows="3" 
                                      maxlength="300"></textarea>
                        </span>
                    </label>
                </div>
                
                <div class="inline-edit-group basicseo-char-counters" style="display: none;">
                    <div class="basicseo-counter">
                        <small>
                            <?php _e('Title:', 'basic-seo-torwald45'); ?> 
                            <span class="basicseo-title-count">0</span>/60 
                            <?php _e('characters', 'basic-seo-torwald45'); ?>
                        </small>
                    </div>
                    <div class="basicseo-counter">
                        <small>
                            <?php _e('Description:', 'basic-seo-torwald45'); ?> 
                            <span class="basicseo-desc-count">0</span>/160 
                            <?php _e('characters', 'basic-seo-torwald45'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </fieldset>
        
        <style>
        .basicseo-quick-edit .inline-edit-group {
            margin-bottom: 10px;
        }
        
        .basicseo-quick-edit .title {
            width: 8em;
            display: inline-block;
            font-weight: 600;
        }
        
        .basicseo-quick-edit .input-text-wrap {
            display: block;
        }
        
        .basicseo-quick-edit input[type="text"],
        .basicseo-quick-edit textarea {
            width: 100%;
            margin-top: 2px;
        }
        
        .basicseo-char-counters {
            font-size: 11px;
            color: #646970;
        }
        
        .basicseo-counter {
            margin-bottom: 3px;
        }
        
        .basicseo-counter.warning {
            color: #d63638;
        }
        </style>
        <?php
    }
    
    /**
     * Save quick edit data
     */
    public function save_quick_edit($post_id) {
        // Security checks
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check if this is a quick edit request
        if (!isset($_POST['_inline_edit']) || 
            !wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check if post type is supported
        $post_type = get_post_type($post_id);
        if (!in_array($post_type, BasicSEO_Torwald45::get_supported_post_types())) {
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
            
            // Apply length control if enabled
            $length_control = $this->plugin->get_setting('meta_description.length_control', 'none');
            if ($length_control === 'auto_cut') {
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
    
    /**
     * Enqueue scripts
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'edit.php') {
            return;
        }
        
        global $current_screen;
        if (!$current_screen || 
            !in_array($current_screen->post_type, BasicSEO_Torwald45::get_supported_post_types())) {
            return;
        }
        
        // Inline script will be added in add_quick_edit_javascript()
    }
    
    /**
     * Add JavaScript for quick edit functionality
     */
    public function add_quick_edit_javascript() {
        global $current_screen;
        
        if (!$current_screen || 
            !in_array($current_screen->post_type, BasicSEO_Torwald45::get_supported_post_types())) {
            return;
        }
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Store original inline edit function
            var $wp_inline_edit = inlineEditPost.edit;
            
            // Override inline edit function
            inlineEditPost.edit = function(id) {
                // Call original function
                $wp_inline_edit.apply(this, arguments);
                
                var post_id = 0;
                if (typeof(id) == 'object') {
                    post_id = parseInt(this.getId(id));
                }
                
                if (post_id > 0) {
                    // Get the post row
                    var $row = $('#post-' + post_id);
                    var $editRow = $('#edit-' + post_id);
                    
                    // Get current SEO values from columns
                    var currentTitle = $row.find('td.column-basicseo_title .basicseo-title').text().trim();
                    var currentDesc = $row.find('td.column-basicseo_desc .basicseo-desc').text().trim();
                    
                    // Handle empty values (shown as em-dash)
                    if (currentTitle === '—' || currentTitle === '') {
                        currentTitle = '';
                    }
                    if (currentDesc === '—' || currentDesc === '') {
                        currentDesc = '';
                    }
                    
                    // Set values in quick edit form
                    $editRow.find('input[name="basicseo_custom_title"]').val(currentTitle);
                    $editRow.find('textarea[name="basicseo_meta_desc"]').val(currentDesc);
                    
                    // Show character counters
                    $editRow.find('.basicseo-char-counters').show();
                    
                    // Initialize character counters
                    updateCharacterCounters($editRow);
                    
                    // Bind character counter events
                    $editRow.find('.basicseo-title-input').on('input', function() {
                        updateCharacterCounters($editRow);
                    });
                    
                    $editRow.find('.basicseo-desc-input').on('input', function() {
                        updateCharacterCounters($editRow);
                    });
                }
            };
            
            // Character counter function
            function updateCharacterCounters($editRow) {
                var titleInput = $editRow.find('.basicseo-title-input');
                var descInput = $editRow.find('.basicseo-desc-input');
                var titleCounter = $editRow.find('.basicseo-title-count');
                var descCounter = $editRow.find('.basicseo-desc-count');
                
                var titleLength = titleInput.val().length;
                var descLength = descInput.val().length;
                
                titleCounter.text(titleLength);
                descCounter.text(descLength);
                
                // Add warning class if too long
                var titleCounterParent = titleCounter.closest('.basicseo-counter');
                var descCounterParent = descCounter.closest('.basicseo-counter');
                
                titleCounterParent.toggleClass('warning', titleLength > 60);
                descCounterParent.toggleClass('warning', descLength > 160);
            }
            
            // Handle bulk edit (disable SEO fields)
            $(document).on('click', '#bulk_edit', function() {
                var $bulkRow = $('#bulk-edit');
                $bulkRow.find('.basicseo-quick-edit').hide();
            });
            
            // Handle quick edit button click
            $(document).on('click', '.editinline', function() {
                // Show SEO fields for quick edit
                setTimeout(function() {
                    $('#bulk-edit, .inline-edit-row').find('.basicseo-quick-edit').show();
                }, 50);
            });
        });
        </script>
        <?php
    }
    
    /**
     * Get post SEO data for quick edit
     */
    public function get_post_seo_data($post_id) {
        return array(
            'title' => get_post_meta($post_id, BASICSEO_TORWALD45_POST_TITLE, true),
            'description' => get_post_meta($post_id, BASICSEO_TORWALD45_POST_DESC, true)
        );
    }
    
    /**
     * Validate quick edit data
     */
    private function validate_quick_edit_data($title, $description) {
        $errors = array();
        
        // Validate title length
        if (strlen($title) > 255) {
            $errors[] = __('SEO title is too long (maximum 255 characters)', 'basic-seo-torwald45');
        }
        
        // Validate description length
        if (strlen($description) > 500) {
            $errors[] = __('Meta description is too long (maximum 500 characters)', 'basic-seo-torwald45');
        }
        
        return $errors;
    }
}
