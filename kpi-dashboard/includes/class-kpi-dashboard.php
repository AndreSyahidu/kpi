<?php
/**
 * The core plugin class
 */
class KPI_Dashboard {

    /**
     * The loader that's responsible for maintaining and registering all hooks
     */
    protected $loader;

    /**
     * The unique identifier of this plugin
     */
    protected $plugin_name;

    /**
     * The current version of the plugin
     */
    protected $version;

    /**
     * Define the core functionality of the plugin
     */
    public function __construct() {
        $this->version = KPI_DASHBOARD_VERSION;
        $this->plugin_name = 'kpi-dashboard';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_api_hooks();
    }

    /**
     * Load the required dependencies
     */
    private function load_dependencies() {
        // Core
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/class-loader.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/core/class-router.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/core/class-auth.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/core/class-session.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/core/class-permissions.php';

        // Database
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/database/class-db-schema.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/database/class-db-indexes.php';

        // Models
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/models/class-user.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/models/class-department.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/models/class-position.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/models/class-kpi.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/models/class-kpi-data.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/models/class-notification.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/models/class-audit-log.php';

        // Services
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/services/class-user-service.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/services/class-department-service.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/services/class-kpi-service.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/services/class-data-service.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/services/class-approval-service.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/services/class-notification-service.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/services/class-email-service.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/services/class-report-generator.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/services/class-analytics-service.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/services/class-validation-service.php';

        // API
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/api/class-api-base.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/api/class-auth-api.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/api/class-users-api.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/api/class-departments-api.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/api/class-positions-api.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/api/class-kpis-api.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/api/class-data-api.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/api/class-approvals-api.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/api/class-reports-api.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/api/class-analytics-api.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/api/class-notifications-api.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/api/class-settings-api.php';

        // Utils
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/utils/class-rate-limiter.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/utils/class-validator.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/utils/class-sanitizer.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/utils/class-logger.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/utils/class-cache.php';
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/utils/class-export.php';

        // Admin (minimal WP admin integration)
        require_once KPI_DASHBOARD_PLUGIN_DIR . 'includes/admin/class-admin-menu.php';

        $this->loader = new KPI_Dashboard_Loader();
    }

    /**
     * Register all hooks related to admin area
     */
    private function define_admin_hooks() {
        $admin_menu = new KPI_Dashboard_Admin_Menu($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_menu', $admin_menu, 'add_menu_items');
        $this->loader->add_action('admin_enqueue_scripts', $admin_menu, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $admin_menu, 'enqueue_scripts');
    }

    /**
     * Register all hooks related to public-facing functionality
     */
    private function define_public_hooks() {
        $router = new KPI_Dashboard_Router($this->get_plugin_name(), $this->get_version());

        // Custom routing for /kpi endpoint
        $this->loader->add_action('init', $router, 'add_rewrite_rules');
        $this->loader->add_action('template_redirect', $router, 'handle_kpi_route');
        $this->loader->add_filter('query_vars', $router, 'add_query_vars');

        // Enqueue frontend assets
        $this->loader->add_action('wp_enqueue_scripts', $router, 'enqueue_frontend_assets');
    }

    /**
     * Register all API endpoints
     */
    private function define_api_hooks() {
        // Register REST API endpoints
        $this->loader->add_action('rest_api_init', $this, 'register_api_endpoints');

        // Add CORS headers for API
        $this->loader->add_action('rest_api_init', $this, 'add_cors_headers');
    }

    /**
     * Register all REST API endpoints
     */
    public function register_api_endpoints() {
        $api_classes = [
            new KPI_Dashboard_Auth_API(),
            new KPI_Dashboard_Users_API(),
            new KPI_Dashboard_Departments_API(),
            new KPI_Dashboard_Positions_API(),
            new KPI_Dashboard_KPIs_API(),
            new KPI_Dashboard_Data_API(),
            new KPI_Dashboard_Approvals_API(),
            new KPI_Dashboard_Reports_API(),
            new KPI_Dashboard_Analytics_API(),
            new KPI_Dashboard_Notifications_API(),
            new KPI_Dashboard_Settings_API(),
        ];

        foreach ($api_classes as $api) {
            $api->register_routes();
        }
    }

    /**
     * Add CORS headers for API requests
     */
    public function add_cors_headers() {
        // Allow same-origin requests
        header('Access-Control-Allow-Origin: ' . get_site_url());
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Credentials: true');
    }

    /**
     * Run the loader to execute all hooks
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number
     */
    public function get_version() {
        return $this->version;
    }
}
