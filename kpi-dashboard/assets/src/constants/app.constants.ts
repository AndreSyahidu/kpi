/**
 * Application Constants
 * Central location for all magic numbers and configuration values
 */

// ============================================================================
// AUTHENTICATION & SECURITY
// ============================================================================

export const AUTH = {
  /** JWT access token expiration time in seconds (15 minutes) */
  ACCESS_TOKEN_EXPIRY: 900,

  /** JWT refresh token expiration time in days */
  REFRESH_TOKEN_EXPIRY_DAYS: 7,

  /** Maximum login attempts before account lockout */
  MAX_LOGIN_ATTEMPTS: 5,

  /** Account lockout duration in minutes */
  LOCKOUT_DURATION_MINUTES: 15,

  /** Minimum password length */
  MIN_PASSWORD_LENGTH: 8,

  /** Password must contain: uppercase, lowercase, number */
  PASSWORD_REQUIREMENTS: {
    minLength: 8,
    requireUppercase: true,
    requireLowercase: true,
    requireNumber: true,
    requireSpecialChar: false,
  },
} as const;

// ============================================================================
// PAGINATION & LIMITS
// ============================================================================

export const PAGINATION = {
  /** Default items per page */
  DEFAULT_PAGE_SIZE: 50,

  /** Maximum items per page */
  MAX_PAGE_SIZE: 100,

  /** Available page size options */
  PAGE_SIZE_OPTIONS: [10, 25, 50, 100],

  /** Default current page */
  DEFAULT_PAGE: 1,
} as const;

// ============================================================================
// FILE UPLOAD
// ============================================================================

export const FILE_UPLOAD = {
  /** Maximum file size in MB */
  MAX_FILE_SIZE_MB: 10,

  /** Maximum file size in bytes */
  MAX_FILE_SIZE_BYTES: 10 * 1024 * 1024,

  /** Allowed file types for attachments */
  ALLOWED_FILE_TYPES: ['pdf', 'png', 'jpg', 'jpeg', 'xlsx', 'xls', 'csv'],

  /** Allowed MIME types */
  ALLOWED_MIME_TYPES: [
    'application/pdf',
    'image/png',
    'image/jpeg',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-excel',
    'text/csv',
  ],
} as const;

// ============================================================================
// INPUT VALIDATION
// ============================================================================

export const VALIDATION = {
  /** Minimum search query length */
  MIN_SEARCH_LENGTH: 2,

  /** Maximum search query length */
  MAX_SEARCH_LENGTH: 100,

  /** Minimum username length */
  MIN_USERNAME_LENGTH: 3,

  /** Maximum username length */
  MAX_USERNAME_LENGTH: 60,

  /** Maximum full name length */
  MAX_NAME_LENGTH: 200,

  /** Maximum description length */
  MAX_DESCRIPTION_LENGTH: 5000,

  /** Maximum notes length */
  MAX_NOTES_LENGTH: 1000,

  /** Decimal places for numeric values */
  DECIMAL_PLACES: {
    default: 2,
    percentage: 2,
    ratio: 4,
    currency: 2,
  },
} as const;

// ============================================================================
// DATE & TIME
// ============================================================================

export const DATE_FORMAT = {
  /** Display format for dates */
  DISPLAY: 'MMM DD, YYYY',

  /** Display format for date and time */
  DISPLAY_WITH_TIME: 'MMM DD, YYYY HH:mm',

  /** API/Database format (ISO 8601) */
  API: 'YYYY-MM-DD',

  /** API format with time */
  API_WITH_TIME: 'YYYY-MM-DD HH:mm:ss',

  /** Short format */
  SHORT: 'MM/DD/YYYY',

  /** Time only */
  TIME: 'HH:mm',
} as const;

export const TIME = {
  /** Debounce delay for search input in milliseconds */
  SEARCH_DEBOUNCE_MS: 500,

  /** Toast notification duration in milliseconds */
  TOAST_DURATION_MS: 3000,

  /** Auto-save delay in milliseconds */
  AUTO_SAVE_DELAY_MS: 2000,

  /** Polling interval for real-time updates in milliseconds */
  POLLING_INTERVAL_MS: 30000, // 30 seconds

  /** Request timeout in milliseconds */
  REQUEST_TIMEOUT_MS: 120000, // 2 minutes
} as const;

// ============================================================================
// KPI SPECIFIC
// ============================================================================

export const KPI = {
  /** Default chart type */
  DEFAULT_CHART_TYPE: 'line',

  /** Available chart types */
  CHART_TYPES: ['line', 'bar', 'area', 'pie', 'doughnut', 'scatter'],

  /** KPI input frequencies */
  INPUT_FREQUENCIES: ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'],

  /** KPI metric types */
  METRIC_TYPES: ['number', 'percentage', 'ratio', 'custom', 'boolean'],

  /** KPI calculation methods */
  CALCULATION_METHODS: ['sum', 'average', 'count', 'formula', 'auto'],

  /** Target types */
  TARGET_TYPES: ['minimum', 'maximum', 'exact', 'range'],

  /** Default weight for KPIs */
  DEFAULT_WEIGHT: 1,

  /** Maximum future data entry in years */
  MAX_FUTURE_YEARS: 1,
} as const;

// ============================================================================
// UI CONSTANTS
// ============================================================================

export const UI = {
  /** Sidebar width when expanded */
  SIDEBAR_WIDTH: 280,

  /** Sidebar width when collapsed */
  SIDEBAR_WIDTH_COLLAPSED: 64,

  /** Header height */
  HEADER_HEIGHT: 64,

  /** Default card elevation */
  CARD_ELEVATION: 1,

  /** Animation duration in milliseconds */
  ANIMATION_DURATION: 300,

  /** Z-index values */
  Z_INDEX: {
    drawer: 1200,
    modal: 1300,
    snackbar: 1400,
    tooltip: 1500,
  },
} as const;

// ============================================================================
// NOTIFICATIONS
// ============================================================================

export const NOTIFICATIONS = {
  /** Maximum notifications to fetch at once */
  FETCH_LIMIT: 50,

  /** Notification types */
  TYPES: ['reminder', 'approval', 'alert', 'info', 'success', 'warning', 'error'],

  /** Notification severity levels */
  SEVERITY: ['info', 'warning', 'critical', 'success'],

  /** Mark all as read batch size */
  MARK_READ_BATCH_SIZE: 100,
} as const;

// ============================================================================
// ANALYTICS & REPORTS
// ============================================================================

export const ANALYTICS = {
  /** Default time range for analytics in days */
  DEFAULT_RANGE_DAYS: 30,

  /** Available time ranges */
  TIME_RANGES: {
    '7d': 7,
    '30d': 30,
    '90d': 90,
    '1y': 365,
  },

  /** Minimum data points required for trend analysis */
  MIN_DATA_POINTS_FOR_TREND: 3,

  /** Anomaly detection sensitivity (standard deviations) */
  ANOMALY_THRESHOLD: 2,

  /** Forecasting periods to predict ahead */
  FORECAST_PERIODS: 3,
} as const;

// ============================================================================
// STATUS & ROLES
// ============================================================================

export const STATUS = {
  /** Data entry statuses */
  DATA: ['draft', 'pending', 'approved', 'rejected'],

  /** Boolean flags */
  ACTIVE: 1,
  INACTIVE: 0,
} as const;

export const ROLES = {
  /** User roles */
  SUPER_ADMIN: 'super_admin',
  DEPT_HEAD: 'dept_head',
  MANAGER: 'manager',
  STAFF: 'staff',

  /** Role hierarchy (higher number = more permissions) */
  HIERARCHY: {
    super_admin: 4,
    dept_head: 3,
    manager: 2,
    staff: 1,
  },
} as const;

// ============================================================================
// ERROR CODES
// ============================================================================

export const ERROR_CODES = {
  // Authentication
  INVALID_CREDENTIALS: 'invalid_credentials',
  NOT_AUTHENTICATED: 'not_authenticated',
  SESSION_EXPIRED: 'session_expired',
  ACCOUNT_LOCKED: 'account_locked',
  TOO_MANY_ATTEMPTS: 'too_many_attempts',

  // Authorization
  FORBIDDEN: 'forbidden',
  PERMISSION_DENIED: 'permission_denied',

  // Validation
  VALIDATION_ERROR: 'validation_error',
  INVALID_INPUT: 'invalid_input',
  MISSING_REQUIRED_FIELD: 'required_field',

  // Resources
  NOT_FOUND: 'not_found',
  ALREADY_EXISTS: 'already_exists',
  DUPLICATE_ENTRY: 'duplicate_entry',

  // Server
  INTERNAL_ERROR: 'internal_error',
  SERVICE_UNAVAILABLE: 'service_unavailable',
} as const;

// ============================================================================
// EXPORT TYPES
// ============================================================================

export const EXPORT = {
  /** Available export formats */
  FORMATS: ['csv', 'xlsx', 'pdf'],

  /** Default export format */
  DEFAULT_FORMAT: 'xlsx',

  /** Maximum records to export at once */
  MAX_RECORDS: 10000,
} as const;

// ============================================================================
// CACHE
// ============================================================================

export const CACHE = {
  /** Cache TTL in seconds */
  TTL: {
    /** User data cache */
    USER_DATA: 300, // 5 minutes

    /** Department/Position data */
    STATIC_DATA: 900, // 15 minutes

    /** KPI definitions */
    KPI_DEFINITIONS: 600, // 10 minutes

    /** Dashboard stats */
    DASHBOARD_STATS: 60, // 1 minute

    /** Reports */
    REPORTS: 1800, // 30 minutes
  },
} as const;

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Convert MB to bytes
 */
export function mbToBytes(mb: number): number {
  return mb * 1024 * 1024;
}

/**
 * Convert seconds to milliseconds
 */
export function secondsToMs(seconds: number): number {
  return seconds * 1000;
}

/**
 * Check if role has sufficient permissions
 */
export function hasHigherRole(userRole: string, requiredRole: string): boolean {
  return (ROLES.HIERARCHY[userRole as keyof typeof ROLES.HIERARCHY] || 0) >=
         (ROLES.HIERARCHY[requiredRole as keyof typeof ROLES.HIERARCHY] || 0);
}
