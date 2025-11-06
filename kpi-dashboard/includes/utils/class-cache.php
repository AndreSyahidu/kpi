<?php
/**
 * Caching Utility
 */
class KPI_Dashboard_Cache {

    private static $prefix = 'kpi_dashboard_';
    private static $default_expiration = 3600; // 1 hour

    /**
     * Get cached value
     */
    public static function get($key) {
        $cache_key = self::$prefix . $key;
        return get_transient($cache_key);
    }

    /**
     * Set cached value
     */
    public static function set($key, $value, $expiration = null) {
        $cache_key = self::$prefix . $key;
        $expiration = $expiration ?? self::$default_expiration;
        return set_transient($cache_key, $value, $expiration);
    }

    /**
     * Delete cached value
     */
    public static function delete($key) {
        $cache_key = self::$prefix . $key;
        return delete_transient($cache_key);
    }

    /**
     * Delete all cache entries matching pattern
     */
    public static function delete_pattern($pattern) {
        global $wpdb;

        $pattern = self::$prefix . $pattern;

        $sql = $wpdb->prepare(
            "DELETE FROM $wpdb->options
            WHERE option_name LIKE %s",
            '%' . $wpdb->esc_like('_transient_' . $pattern) . '%'
        );

        return $wpdb->query($sql);
    }

    /**
     * Remember - Get from cache or execute callback and cache result
     */
    public static function remember($key, $callback, $expiration = null) {
        $cached = self::get($key);

        if ($cached !== false) {
            return $cached;
        }

        $value = $callback();
        self::set($key, $value, $expiration);

        return $value;
    }

    /**
     * Clear all KPI Dashboard cache
     */
    public static function clear_all() {
        return self::delete_pattern('');
    }

    /**
     * Cache department data
     */
    public static function get_departments() {
        return self::remember('departments', function() {
            return KPI_Dashboard_Department_Model::get_all(['is_active' => 1]);
        }, 3600);
    }

    /**
     * Cache KPI definitions
     */
    public static function get_kpis() {
        return self::remember('kpis', function() {
            return KPI_Dashboard_KPI_Model::get_all(['is_active' => 1]);
        }, 3600);
    }

    /**
     * Clear department cache
     */
    public static function clear_departments() {
        return self::delete('departments');
    }

    /**
     * Clear KPI cache
     */
    public static function clear_kpis() {
        return self::delete('kpis');
    }
}
