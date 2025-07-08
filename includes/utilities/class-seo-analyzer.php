<?php
/**
 * SEO Data Analyzer
 *
 * @package BasicSEOTorwald45
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BasicSEO_Torwald45_SEO_Analyzer
 */
class BasicSEO_Torwald45_SEO_Analyzer {
    
    /**
     * Get posts missing SEO data (title, description, and featured image)
     */
    public static function get_posts_missing_seo($post_type = 'post', $limit = 20) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT DISTINCT p.ID, p.post_title, p.post_type 
            FROM {$wpdb->posts} p 
            WHERE p.post_type = %s 
            AND p.post_status = 'publish' 
            AND (
                p.ID NOT IN (
                    SELECT pm1.post_id 
                    FROM {$wpdb->postmeta} pm1 
                    WHERE pm1.meta_key = %s AND pm1.meta_value != ''
                )
                OR p.ID NOT IN (
                    SELECT pm2.post_id 
                    FROM {$wpdb->postmeta} pm2 
                    WHERE pm2.meta_key = %s AND pm2.meta_value != ''
                )
                OR p.ID NOT IN (
                    SELECT pm3.post_id 
                    FROM {$wpdb->postmeta} pm3 
                    WHERE pm3.meta_key = '_thumbnail_id' AND pm3.meta_value != ''
                )
            )
            ORDER BY p.post_modified DESC 
            LIMIT %d",
            $post_type,
            BASICSEO_TORWALD45_POST_TITLE,
            BASICSEO_TORWALD45_POST_DESC,
            $limit
        );
        
        $posts = $wpdb->get_results($query);
        
        // Add missing info for each post
        foreach ($posts as $post) {
            $post->missing_title = empty(get_post_meta($post->ID, BASICSEO_TORWALD45_POST_TITLE, true));
            $post->missing_description = empty(get_post_meta($post->ID, BASICSEO_TORWALD45_POST_DESC, true));
            $post->missing_featured_image = !has_post_thumbnail($post->ID);
        }
        
        return $posts;
    }
    
    /**
     * Get SEO statistics for posts
     */
    public static function get_post_seo_stats($post_type = 'post') {
        global $wpdb;
        
        $total_posts = wp_count_posts($post_type);
        $published_count = $total_posts->publish;
        
        // Count posts with SEO title
        $posts_with_title = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID) 
                FROM {$wpdb->posts} p 
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                WHERE p.post_type = %s 
                AND p.post_status = 'publish' 
                AND pm.meta_key = %s 
                AND pm.meta_value != ''",
                $post_type,
                BASICSEO_TORWALD45_POST_TITLE
            )
        );
        
        // Count posts with SEO description
        $posts_with_desc = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID) 
                FROM {$wpdb->posts} p 
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                WHERE p.post_type = %s 
                AND p.post_status = 'publish' 
                AND pm.meta_key = %s 
                AND pm.meta_value != ''",
                $post_type,
                BASICSEO_TORWALD45_POST_DESC
            )
        );
        
        // Count posts with featured image
        $posts_with_image = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID) 
                FROM {$wpdb->posts} p 
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                WHERE p.post_type = %s 
                AND p.post_status = 'publish' 
                AND pm.meta_key = '_thumbnail_id' 
                AND pm.meta_value != ''",
                $post_type
            )
        );
        
        // Count posts with all three: SEO title, description AND featured image
        $posts_with_all = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID) 
                FROM {$wpdb->posts} p 
                INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id 
                INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id 
                INNER JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id 
                WHERE p.post_type = %s 
                AND p.post_status = 'publish' 
                AND pm1.meta_key = %s AND pm1.meta_value != ''
                AND pm2.meta_key = %s AND pm2.meta_value != ''
                AND pm3.meta_key = '_thumbnail_id' AND pm3.meta_value != ''",
                $post_type,
                BASICSEO_TORWALD45_POST_TITLE,
                BASICSEO_TORWALD45_POST_DESC
            )
        );
        
        return array(
            'total' => intval($published_count),
            'with_title' => intval($posts_with_title),
            'with_description' => intval($posts_with_desc),
            'with_featured_image' => intval($posts_with_image),
            'with_all' => intval($posts_with_all),
            'missing_seo' => intval($published_count) - intval($posts_with_all)
        );
    }
    
    /**
     * Get posts with missing title only
     */
    public static function get_posts_without_title($post_type = 'post', $limit = 20) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT p.ID, p.post_title, p.post_type 
            FROM {$wpdb->posts} p 
            WHERE p.post_type = %s 
            AND p.post_status = 'publish' 
            AND p.ID NOT IN (
                SELECT pm.post_id 
                FROM {$wpdb->postmeta} pm 
                WHERE pm.meta_key = %s AND pm.meta_value != ''
            )
            ORDER BY p.post_modified DESC 
            LIMIT %d",
            $post_type,
            BASICSEO_TORWALD45_POST_TITLE,
            $limit
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get posts with missing description only
     */
    public static function get_posts_without_description($post_type = 'post', $limit = 20) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT p.ID, p.post_title, p.post_type 
            FROM {$wpdb->posts} p 
            WHERE p.post_type = %s 
            AND p.post_status = 'publish' 
            AND p.ID NOT IN (
                SELECT pm.post_id 
                FROM {$wpdb->postmeta} pm 
                WHERE pm.meta_key = %s AND pm.meta_value != ''
            )
            ORDER BY p.post_modified DESC 
            LIMIT %d",
            $post_type,
            BASICSEO_TORWALD45_POST_DESC,
            $limit
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get posts with missing featured image only
     */
    public static function get_posts_without_featured_image($post_type = 'post', $limit = 20) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT p.ID, p.post_title, p.post_type 
            FROM {$wpdb->posts} p 
            WHERE p.post_type = %s 
            AND p.post_status = 'publish' 
            AND p.ID NOT IN (
                SELECT pm.post_id 
                FROM {$wpdb->postmeta} pm 
                WHERE pm.meta_key = '_thumbnail_id' AND pm.meta_value != ''
            )
            ORDER BY p.post_modified DESC 
            LIMIT %d",
            $post_type,
            $limit
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get overall SEO health score
     */
    public static function get_seo_health_score() {
        $post_types = BasicSEO_Torwald45::get_supported_post_types();
        $total_posts = 0;
        $posts_with_seo = 0;
        
        foreach ($post_types as $post_type) {
            $stats = self::get_post_seo_stats($post_type);
            $total_posts += $stats['total'];
            $posts_with_seo += $stats['with_all'];
        }
        
        if ($total_posts === 0) {
            return 100; // No posts = perfect score
        }
        
        return round(($posts_with_seo / $total_posts) * 100, 1);
    }
    
    /**
     * Get SEO recommendations
     */
    public static function get_seo_recommendations() {
        $recommendations = array();
        $post_types = BasicSEO_Torwald45::get_supported_post_types();
        
        foreach ($post_types as $post_type) {
            $stats = self::get_post_seo_stats($post_type);
            
            if ($stats['total'] > 0 && $stats['missing_seo'] > 0) {
                $post_type_object = get_post_type_object($post_type);
                $post_type_name = $post_type_object ? $post_type_object->labels->name : $post_type;
                
                $recommendations[] = array(
                    'type' => 'missing_seo',
                    'message' => sprintf(
                        __('%d %s are missing SEO data', 'basic-seo-torwald45'),
                        $stats['missing_seo'],
                        $post_type_name
                    ),
                    'action_url' => admin_url("edit.php?post_type={$post_type}"),
                    'priority' => $stats['missing_seo'] > 10 ? 'high' : 'medium'
                );
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Check if post has complete SEO data
     */
    public static function has_complete_seo($post_id) {
        $title = get_post_meta($post_id, BASICSEO_TORWALD45_POST_TITLE, true);
        $desc = get_post_meta($post_id, BASICSEO_TORWALD45_POST_DESC, true);
        $has_thumbnail = has_post_thumbnail($post_id);
        
        return !empty($title) && !empty($desc) && $has_thumbnail;
    }
    
    /**
     * Get SEO completeness percentage for a post type
     */
    public static function get_completeness_percentage($post_type = 'post') {
        $stats = self::get_post_seo_stats($post_type);
        
        if ($stats['total'] === 0) {
            return 100;
        }
        
        return round(($stats['with_all'] / $stats['total']) * 100, 1);
    }
}
