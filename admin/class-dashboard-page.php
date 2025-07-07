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
        $health_score = BasicSEO_Torwald45_SEO_Analyzer::get_seo_health_score();
        $recommendations = BasicSEO_Torwald45_SEO_Analyzer::get_seo_recommendations();
        $post_types = BasicSEO_Torwald45::get_supported_post_types();
        ?>
        
        <div class="basicseo-dashboard">
            
            <!-- SEO Health Score -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('SEO Health Score', 'basic-seo-torwald45'); ?></h2>
                <div class="inside">
                    <div class="basicseo-health-score">
                        <div class="score-circle score-<?php echo $this->get_score_class($health_score); ?>">
                            <span class="score-number"><?php echo esc_html($health_score); ?>%</span>
                        </div>
                        <div class="score-description">
                            <p>
                                <?php if ($health_score >= 90): ?>
                                    <?php _e('Excellent! Your SEO setup is in great shape.', 'basic-seo-torwald45'); ?>
                                <?php elseif ($health_score >= 70): ?>
                                    <?php _e('Good! There are a few areas for improvement.', 'basic-seo-torwald45'); ?>
                                <?php elseif ($health_score >= 50): ?>
                                    <?php _e('Fair. Consider optimizing more content for better SEO.', 'basic-seo-torwald45'); ?>
                                <?php else: ?>
                                    <?php _e('Needs improvement. Many posts are missing SEO data.', 'basic-seo-torwald45'); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SEO Overview Stats -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('SEO Overview', 'basic-seo-torwald45'); ?></h2>
                <div class="inside">
                    <div class="basicseo-stats-grid">
                        <?php foreach ($post_types as $post_type): ?>
                            <?php 
                            $stats = BasicSEO_Torwald45_SEO_Analyzer::get_post_seo_stats($post_type);
                            $post_type_object = get_post_type_object($post_type);
                            $post_type_name = $post_type_object ? $post_type_object->labels->name : $post_type;
                            
                            if ($stats['total'] > 0):
                            ?>
                            <div class="stat-box">
                                <h3><?php echo esc_html($post_type_name); ?></h3>
                                <div class="stat-item">
                                    <span class="stat-number good"><?php echo esc_html($stats['with_both']); ?></span>
                                    <span class="stat-label"><?php _e('with complete SEO', 'basic-seo-torwald45'); ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number warning"><?php echo esc_html($stats['without_seo']); ?></span>
                                    <span class="stat-label"><?php _e('missing SEO data', 'basic-seo-torwald45'); ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number total"><?php echo esc_html($stats['total']); ?></span>
                                    <span class="stat-label"><?php _e('total', 'basic-seo-torwald45'); ?></span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo esc_attr($this->get_completeness_percentage($stats)); ?>%"></div>
                                </div>
                                <p class="completion-text">
                                    <?php echo esc_html($this->get_completeness_percentage($stats)); ?>% <?php _e('complete', 'basic-seo-torwald45'); ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Missing SEO Data -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('Posts Missing SEO Data', 'basic-seo-torwald45'); ?></h2>
                <div class="inside">
                    <?php foreach ($post_types as $post_type): ?>
                        <?php 
                        $posts_without_seo = BasicSEO_Torwald45_SEO_Analyzer::get_posts_without_seo($post_type, 10);
                        $post_type_object = get_post_type_object($post_type);
                        $post_type_name = $post_type_object ? $post_type_object->labels->name : $post_type;
                        
                        if (!empty($posts_without_seo)):
                        ?>
                        <h4><?php echo esc_html($post_type_name); ?></h4>
                        <div class="basicseo-missing-list">
                            <?php foreach ($posts_without_seo as $post): ?>
                                <div class="missing-item">
                                    <span class="post-title">
                                        <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>" target="_blank">
                                            <?php echo esc_html($post->post_title ?: __('(no title)', 'basic-seo-torwald45')); ?>
                                        </a>
                                    </span>
                                    <span class="post-status">
                                        <?php 
                                        $has_title = !empty(get_post_meta($post->ID, BASICSEO_TORWALD45_POST_TITLE, true));
                                        $has_desc = !empty(get_post_meta($post->ID, BASICSEO_TORWALD45_POST_DESC, true));
                                        
                                        if (!$has_title && !$has_desc) {
                                            _e('Missing title and description', 'basic-seo-torwald45');
                                        } elseif (!$has_title) {
                                            _e('Missing title', 'basic-seo-torwald45');
                                        } else {
                                            _e('Missing description', 'basic-seo-torwald45');
                                        }
                                        ?>
                                    </span>
                                    <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>" class="button button-small">
                                        <?php _e('Edit', 'basic-seo-torwald45'); ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (count($posts_without_seo) >= 10): ?>
                                <p class="description">
                                    <a href="<?php echo esc_url(admin_url("edit.php?post_type={$post_type}")); ?>">
                                        <?php _e('View all posts', 'basic-seo-torwald45'); ?> &raquo;
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <?php if (empty(array_filter($post_types, function($type) { 
                        return !empty(BasicSEO_Torwald45_SEO_Analyzer::get_posts_without_seo($type, 1)); 
                    }))): ?>
                        <p class="basicseo-success">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php _e('All posts have complete SEO data!', 'basic-seo-torwald45'); ?>
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
                        <a href="<?php echo esc_url(admin_url('options-general.php?page=basic-seo-torwald45&tab=settings')); ?>" class="button">
                            <?php _e('Plugin Settings', 'basic-seo-torwald45'); ?>
                        </a>
                        <a href="https://github.com/Torwald45/basic-seo/blob/main/README.md" target="_blank" class="button">
                            <?php _e('Documentation', 'basic-seo-torwald45'); ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Module Status -->
            <div class="postbox">
                <h2 class="hndle"><?php _e('Module Status', 'basic-seo-torwald45'); ?></h2>
                <div class="inside">
                    <div class="basicseo-modules-status">
                        <?php
                        $modules = array(
                            'meta_tags' => __('Meta Tags', 'basic-seo-torwald45'),
                            'sitemap' => __('XML Sitemap', 'basic-seo-torwald45'),
                            'breadcrumbs' => __('Breadcrumbs', 'basic-seo-torwald45'),
                            'open_graph' => __('Open Graph', 'basic-seo-torwald45'),
                            'canonical' => __('Canonical URLs', 'basic-seo-torwald45'),
                            'attachment_redirect' => __('Attachment Redirect', 'basic-seo-torwald45')
                        );
                        
                        foreach ($modules as $module_key => $module_name):
                            $is_enabled = $this->plugin->get_setting("modules.{$module_key}");
                        ?>
                            <div class="module-status <?php echo $is_enabled ? 'enabled' : 'disabled'; ?>">
                                <span class="status-indicator"></span>
                                <span class="module-name"><?php echo esc_html($module_name); ?></span>
                                <span class="status-text">
                                    <?php echo $is_enabled ? __('Enabled', 'basic-seo-torwald45') : __('Disabled', 'basic-seo-torwald45'); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
        </div>
        <?php
    }
    
    /**
     * Get score class based on health score
     */
    private function get_score_class($score) {
        if ($score >= 90) return 'excellent';
        if ($score >= 70) return 'good';
        if ($score >= 50) return 'fair';
        return 'poor';
    }
    
    /**
     * Get completeness percentage
     */
    private function get_completeness_percentage($stats) {
        if ($stats['total'] === 0) return 100;
        return round(($stats['with_both'] / $stats['total']) * 100, 1);
    }
}
