<?php
/**
 * Dashboard Page
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_Dashboard_Page
 */
class BasicSEO_Torwald45_Dashboard_Page {
    
    /**
     * Plugin instance
     */
    private $plugin;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = BasicSEO_Torwald45::get_instance();
    }
    
    /**
     * Display dashboard page
     */
    public function display() {
        $post_types = BasicSEO_Torwald45::get_supported_post_types();
        ?>
        
        <div class="basicseo-dashboard">
            
            <!-- Module Settings -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('Module Settings', 'basic-seo-torwald45'); ?></h2>
                <div class="inside">
                    <div class="basicseo-modules-info">
                        
                        <div class="module-info">
                            <h3><?php _e('Meta Tags', 'basic-seo-torwald45'); ?></h3>
                            <p><?php _e('Plugin automatically adds custom title tags and meta descriptions to posts/pages. Edit them in meta boxes below the editor.', 'basic-seo-torwald45'); ?></p>
                        </div>
                        
                        <div class="module-info">
                            <h3><?php _e('XML Sitemap', 'basic-seo-torwald45'); ?></h3>
                            <p><?php _e('Available at /sitemap.xml - automatically generated from all public posts/pages/products with pagination support.', 'basic-seo-torwald45'); ?></p>
                        </div>
                        
                        <div class="module-info">
                            <h3><?php _e('Breadcrumbs', 'basic-seo-torwald45'); ?></h3>
                            <p><?php _e('Use shortcode [breadcrumbs] in content or call do_shortcode(\'[breadcrumbs]\') in your theme template files.', 'basic-seo-torwald45'); ?></p>
                        </div>
                        
                        <div class="module-info">
                            <h3><?php _e('Open Graph', 'basic-seo-torwald45'); ?></h3>
                            <p><?php _e('Automatic meta tags for Facebook/Twitter sharing. Uses featured image, custom meta title/description, and fallback settings.', 'basic-seo-torwald45'); ?></p>
                        </div>
                        
                        <div class="module-info">
                            <h3><?php _e('Canonical URLs', 'basic-seo-torwald45'); ?></h3>
                            <p><?php _e('Automatic rel=canonical tags added to all pages to prevent duplicate content issues.', 'basic-seo-torwald45'); ?></p>
                        </div>
                        
                        <div class="module-info">
                            <h3><?php _e('Redirect Attachments', 'basic-seo-torwald45'); ?></h3>
                            <p><?php _e('Redirects attachment pages to parent posts to prevent duplicate content and improve SEO.', 'basic-seo-torwald45'); ?></p>
                        </div>
                        
                    </div>
                </div>
            </div>
            
            <!-- Missing SEO Data -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('Posts Missing Title and Description', 'basic-seo-torwald45'); ?></h2>
                <div class="inside">
                    <?php 
                    $has_missing_posts = false;
                    
                    foreach ($post_types as $post_type): 
                        $posts_missing_seo = BasicSEO_Torwald45_SEO_Analyzer::get_posts_missing_seo($post_type, 10);
                        $post_type_object = get_post_type_object($post_type);
                        $post_type_name = $post_type_object ? $post_type_object->labels->name : $post_type;
                        
                        if (!empty($posts_missing_seo)):
                            $has_missing_posts = true;
                        ?>
                        <h4><?php echo esc_html($post_type_name); ?></h4>
                        <div class="basicseo-missing-list">
                            <?php foreach ($posts_missing_seo as $post): ?>
                                <div class="missing-item">
                                    <span class="post-title">
                                        <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>" target="_blank">
                                            <?php echo esc_html($post->post_title ?: __('(no title)', 'basic-seo-torwald45')); ?>
                                        </a>
                                    </span>
                                    <span class="post-status">
                                        <?php 
                                        $missing_items = array();
                                        if ($post->missing_title) {
                                            $missing_items[] = __('title', 'basic-seo-torwald45');
                                        }
                                        if ($post->missing_description) {
                                            $missing_items[] = __('description', 'basic-seo-torwald45');
                                        }
                                        if ($post->missing_featured_image) {
                                            $missing_items[] = __('featured image', 'basic-seo-torwald45');
                                        }
                                        
                                        if (!empty($missing_items)) {
                                            echo sprintf(__('Missing: %s', 'basic-seo-torwald45'), implode(', ', $missing_items));
                                        }
                                        ?>
                                    </span>
                                    <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>" class="button button-small">
                                        <?php _e('Edit', 'basic-seo-torwald45'); ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (count($posts_missing_seo) >= 10): ?>
                                <p class="description">
                                    <a href="<?php echo esc_url(admin_url("edit.php?post_type={$post_type}")); ?>">
                                        <?php _e('View all posts', 'basic-seo-torwald45'); ?> &raquo;
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <?php if (!$has_missing_posts): ?>
                        <p class="basicseo-success">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php _e('All posts have complete SEO data (title, description, and featured image)!', 'basic-seo-torwald45'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('Quick Links', 'basic-seo-torwald45'); ?></h2>
                <div class="inside">
                    <div class="basicseo-quick-links">
                        <a href="<?php echo esc_url(home_url('/sitemap.xml')); ?>" target="_blank" class="button">
                            <?php _e('View Sitemap', 'basic-seo-torwald45'); ?>
                        </a>
                        <a href="https://github.com/Torwald45/basic-seo-plugin/blob/main/README.md" target="_blank" class="button">
                            <?php _e('Documentation', 'basic-seo-torwald45'); ?>
                        </a>
                    </div>
                </div>
            </div>
            
        </div>
        
        <style>
        .basicseo-dashboard .postbox {
            margin-bottom: 20px;
        }
        
        .basicseo-dashboard .postbox h2 {
            padding: 8px 12px;
            margin: 0;
            line-height: 1.4;
        }
        
        .basicseo-dashboard .inside {
            margin: 0;
            padding: 0 12px 12px;
        }
        
        .basicseo-modules-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .module-info {
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 15px;
            background: #fff;
        }
        
        .module-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #1d2327;
            font-weight: 600;
        }
        
        .module-info p {
            margin: 0;
            font-size: 13px;
            line-height: 1.4;
            color: #646970;
        }
        
        .basicseo-missing-list {
            margin-bottom: 20px;
        }
        
        .missing-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f1;
        }
        
        .missing-item:last-child {
            border-bottom: none;
        }
        
        .post-title {
            flex: 1;
        }
        
        .post-status {
            color: #dc3232;
            font-size: 12px;
            min-width: 200px;
        }
        
        .basicseo-success {
            text-align: center;
            color: #46b450;
            font-weight: 500;
            padding: 20px;
        }
        
        .basicseo-success .dashicons {
            margin-right: 5px;
        }
        
        .basicseo-quick-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        </style>
        <?php
    }
}
