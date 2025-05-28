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
        
        // Count posts with both SEO title AND description
        $posts_with_both = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID) 
                FROM {$wpdb->posts} p 
                INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id 
                INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id 
                WHERE p.post_type = %s 
                AND p.post_status = 'publish' 
                AND pm1.meta_key = %s AND pm1.meta_value != ''
                AND pm2.meta_key = %s AND pm2.meta_value != ''",
                $post_type,
                BASICSEO_TORWALD45_POST_TITLE,
                BASICSEO_TORWALD45_POST_DESC
            )
        );
        
        return array(
            'total' => intval($published_count),
            'with_title' => intval($posts_with_title),
            'with_description' => intval($posts_with_desc),
            'with_both' => intval($posts_with_both),
            'without_seo' => intval($published_count) - intval($posts_with_both)
        );
    }
    
    /**
     * Get posts without SEO data
     */
    public static function get_posts_without_seo($post_type = 'post', $limit = 20) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT p.ID, p.post_title, p.post_type 
            FROM {$wpdb->posts} p 
            WHERE p.post_type = %s 
            AND p.post_status = 'publish' 
            AND p.ID NOT IN (
                SELECT DISTINCT pm1.post_id 
                FROM {$wpdb->postmeta} pm1 
                INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id 
                WHERE pm1.meta_key = %s AND pm1.meta_value != ''
                AND pm2.meta_key = %s AND pm2.meta_value != ''
            )
            ORDER BY p.post_modified DESC 
            LIMIT %d",
            $post_type,
            BASICSEO_TORWALD45_POST_TITLE,
            BASICSEO_TORWALD45_POST_DESC,
            $limit
        );
        
        return $wpdb->get_results($query);
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
     * Get overall SEO health score
     */
    public static function get_seo_health_score() {
        $post_types = BasicSEO_Torwald45::get_supported_post_types();
        $total_posts = 0;
        $posts_with_seo = 0;
        
        foreach ($post_types as $post_type) {
            $stats = self::get_post_seo_stats($post_type);
            $total_posts += $stats['total'];
            $posts_with_seo += $stats['with_both'];
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
            
            if ($stats['total'] > 0 && $stats['without_seo'] > 0) {
                $post_type_object = get_post_type_object($post_type);
                $post_type_name = $post_type_object ? $post_type_object->labels->name : $post_type;
                
                $recommendations[] = array(
                    'type' => 'missing_seo',
                    'message' => sprintf(
                        __('%d %s are missing SEO data', 'basic-seo-torwald45'),
                        $stats['without_seo'],
                        $post_type_name
                    ),
                    'action_url' => admin_url("edit.php?post_type={$post_type}"),
                    'priority' => $stats['without_seo'] > 10 ? 'high' : 'medium'
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
        
        return !empty($title) && !empty($desc);
    }
    
    /**
     * Get SEO completeness percentage for a post type
     */
    public static function get_completeness_percentage($post_type = 'post') {
        $stats = self::get_post_seo_stats($post_type);
        
        if ($stats['total'] === 0) {
            return 100;
        }
        
        return round(($stats['with_both'] / $stats['total']) * 100, 1);
    }
}
