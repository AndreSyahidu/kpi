<?php
/**
 * Application Constants
 * Central location for all magic numbers and configuration values
 */
class KPI_Dashboard_Constants {

    // ========================================================================
    // AUTHENTICATION & SECURITY
    // ========================================================================

    /** JWT access token expiration time in seconds (15 minutes) */
    const ACCESS_TOKEN_EXPIRY = 900;

    /** JWT refresh token expiration time in seconds (7 days) */
    const REFRESH_TOKEN_EXPIRY = 604800;

    /** Maximum login attempts before account lockout */
    const MAX_LOGIN_ATTEMPTS = 5;

    /** Account lockout duration in seconds (15 minutes) */
    const LOCKOUT_DURATION = 900;

    /** Minimum password length */
    const MIN_PASSWORD_LENGTH = 8;

    // ========================================================================
    // PAGINATION & LIMITS
    // ========================================================================

    /** Default items per page */
    const DEFAULT_PAGE_SIZE = 50;

    /** Maximum items per page */
    const MAX_PAGE_SIZE = 100;

    /** Default current page */
    const DEFAULT_PAGE = 1;

    // ========================================================================
    // FILE UPLOAD
    // ========================================================================

    /** Maximum file size in MB */
    const MAX_FILE_SIZE_MB = 10;

    /** Maximum file size in bytes */
    const MAX_FILE_SIZE_BYTES = 10485760; // 10 * 1024 * 1024

    /** Allowed file extensions for attachments */
    const ALLOWED_FILE_EXTENSIONS = ['pdf', 'png', 'jpg', 'jpeg', 'xlsx', 'xls', 'csv'];

    /** Allowed MIME types */
    const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/png',
        'image/jpeg',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'text/csv',
    ];

    // ========================================================================
    // INPUT VALIDATION
    // ========================================================================

    /** Minimum search query length */
    const MIN_SEARCH_LENGTH = 2;

    /** Maximum search query length */
    const MAX_SEARCH_LENGTH = 100;

    /** Minimum username length */
    const MIN_USERNAME_LENGTH = 3;

    /** Maximum username length */
    const MAX_USERNAME_LENGTH = 60;

    /** Maximum full name length */
    const MAX_NAME_LENGTH = 200;

    /** Maximum description length */
    const MAX_DESCRIPTION_LENGTH = 5000;

    /** Maximum notes length */
    const MAX_NOTES_LENGTH = 1000;

    // ========================================================================
    // KPI SPECIFIC
    // ========================================================================

    /** KPI chart types */
    const KPI_CHART_TYPES = ['line', 'bar', 'area', 'pie', 'doughnut', 'scatter'];

    /** KPI input frequencies */
    const KPI_INPUT_FREQUENCIES = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];

    /** KPI metric types */
    const KPI_METRIC_TYPES = ['number', 'percentage', 'ratio', 'custom', 'boolean'];

    /** KPI calculation methods */
    const KPI_CALCULATION_METHODS = ['sum', 'average', 'count', 'formula', 'auto'];

    /** KPI target types */
    const KPI_TARGET_TYPES = ['minimum', 'maximum', 'exact', 'range'];

    /** Default KPI weight */
    const DEFAULT_KPI_WEIGHT = 1;

    /** Maximum future data entry in years */
    const MAX_FUTURE_YEARS = 1;

    // ========================================================================
    // STATUS & ROLES
    // ========================================================================

    /** Data entry statuses */
    const DATA_STATUSES = ['draft', 'pending', 'approved', 'rejected'];

    /** User roles */
    const ROLES = [
        'super_admin' => 4,
        'dept_head' => 3,
        'manager' => 2,
        'staff' => 1,
    ];

    /** Active/Inactive flags */
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    // ========================================================================
    // NOTIFICATIONS
    // ========================================================================

    /** Notification types */
    const NOTIFICATION_TYPES = ['reminder', 'approval', 'alert', 'info', 'success', 'warning', 'error'];

    /** Notification severity levels */
    const NOTIFICATION_SEVERITY = ['info', 'warning', 'critical', 'success'];

    /** Maximum notifications to fetch at once */
    const NOTIFICATION_FETCH_LIMIT = 50;

    // ========================================================================
    // ANALYTICS & REPORTS
    // ========================================================================

    /** Default time range for analytics in days */
    const DEFAULT_ANALYTICS_RANGE_DAYS = 30;

    /** Minimum data points required for trend analysis */
    const MIN_DATA_POINTS_FOR_TREND = 3;

    /** Anomaly detection threshold (standard deviations) */
    const ANOMALY_DETECTION_THRESHOLD = 2;

    /** Forecasting periods to predict ahead */
    const FORECAST_PERIODS = 3;

    // ========================================================================
    // CACHE
    // ========================================================================

    /** Cache TTL in seconds */
    const CACHE_TTL_USER_DATA = 300;        // 5 minutes
    const CACHE_TTL_STATIC_DATA = 900;      // 15 minutes
    const CACHE_TTL_KPI_DEFINITIONS = 600;  // 10 minutes
    const CACHE_TTL_DASHBOARD_STATS = 60;   // 1 minute
    const CACHE_TTL_REPORTS = 1800;         // 30 minutes

    // ========================================================================
    // EXPORT
    // ========================================================================

    /** Available export formats */
    const EXPORT_FORMATS = ['csv', 'xlsx', 'pdf'];

    /** Default export format */
    const DEFAULT_EXPORT_FORMAT = 'xlsx';

    /** Maximum records to export at once */
    const MAX_EXPORT_RECORDS = 10000;

    // ========================================================================
    // ERROR CODES
    // ========================================================================

    const ERROR_INVALID_CREDENTIALS = 'invalid_credentials';
    const ERROR_NOT_AUTHENTICATED = 'not_authenticated';
    const ERROR_SESSION_EXPIRED = 'session_expired';
    const ERROR_ACCOUNT_LOCKED = 'account_locked';
    const ERROR_TOO_MANY_ATTEMPTS = 'too_many_attempts';
    const ERROR_FORBIDDEN = 'forbidden';
    const ERROR_PERMISSION_DENIED = 'permission_denied';
    const ERROR_VALIDATION_ERROR = 'validation_error';
    const ERROR_NOT_FOUND = 'not_found';
    const ERROR_ALREADY_EXISTS = 'already_exists';
    const ERROR_DUPLICATE_ENTRY = 'duplicate_entry';
    const ERROR_INTERNAL_ERROR = 'internal_error';

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    /**
     * Check if role has sufficient permissions
     *
     * @param string $user_role Current user's role
     * @param string $required_role Required role for action
     * @return bool
     */
    public static function has_higher_role($user_role, $required_role) {
        $hierarchy = self::ROLES;

        $user_level = $hierarchy[$user_role] ?? 0;
        $required_level = $hierarchy[$required_role] ?? 0;

        return $user_level >= $required_level;
    }

    /**
     * Validate file extension
     *
     * @param string $filename
     * @return bool
     */
    public static function is_valid_file_extension($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, self::ALLOWED_FILE_EXTENSIONS);
    }

    /**
     * Validate file size
     *
     * @param int $size_bytes File size in bytes
     * @return bool
     */
    public static function is_valid_file_size($size_bytes) {
        return $size_bytes <= self::MAX_FILE_SIZE_BYTES;
    }

    /**
     * Validate data entry status
     *
     * @param string $status
     * @return bool
     */
    public static function is_valid_status($status) {
        return in_array($status, self::DATA_STATUSES);
    }

    /**
     * Get cache TTL for specific data type
     *
     * @param string $type Cache type (user_data, static_data, kpi_definitions, dashboard_stats, reports)
     * @return int TTL in seconds
     */
    public static function get_cache_ttl($type) {
        $ttls = [
            'user_data' => self::CACHE_TTL_USER_DATA,
            'static_data' => self::CACHE_TTL_STATIC_DATA,
            'kpi_definitions' => self::CACHE_TTL_KPI_DEFINITIONS,
            'dashboard_stats' => self::CACHE_TTL_DASHBOARD_STATS,
            'reports' => self::CACHE_TTL_REPORTS,
        ];

        return $ttls[$type] ?? 300; // Default 5 minutes
    }

    /**
     * Get pagination defaults
     *
     * @return array
     */
    public static function get_pagination_defaults() {
        return [
            'page' => self::DEFAULT_PAGE,
            'per_page' => self::DEFAULT_PAGE_SIZE,
            'max_per_page' => self::MAX_PAGE_SIZE,
        ];
    }

    /**
     * Format file size in human-readable format
     *
     * @param int $bytes
     * @return string
     */
    public static function format_file_size($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }
}
