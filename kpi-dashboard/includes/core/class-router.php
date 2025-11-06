<?php
/**
 * Custom Router for KPI Dashboard
 * Handles /kpi and /kpi/* routes
 */
class KPI_Dashboard_Router {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Add custom rewrite rules for /kpi endpoint
     */
    public function add_rewrite_rules() {
        // Match /kpi and /kpi/* routes
        add_rewrite_rule('^kpi/?$', 'index.php?kpi_dashboard=1', 'top');
        add_rewrite_rule('^kpi/(.+)/?$', 'index.php?kpi_dashboard=1&kpi_route=$matches[1]', 'top');
    }

    /**
     * Add custom query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'kpi_dashboard';
        $vars[] = 'kpi_route';
        return $vars;
    }

    /**
     * Handle KPI dashboard routes
     */
    public function handle_kpi_route() {
        if (!get_query_var('kpi_dashboard')) {
            return;
        }

        // Prevent WordPress from loading theme
        remove_action('wp_head', '_admin_bar_bump_cb');

        // Serve the React app
        $this->serve_react_app();
        exit;
    }

    /**
     * Serve the React application
     */
    private function serve_react_app() {
        $build_dir = KPI_DASHBOARD_PLUGIN_DIR . 'assets/dist';
        $build_url = KPI_DASHBOARD_PLUGIN_URL . 'assets/dist';

        // Check if build exists (development vs production)
        $is_dev = !file_exists($build_dir . '/index.html');

        if ($is_dev) {
            // Development mode - point to Vite dev server
            $this->serve_dev_app();
        } else {
            // Production mode - serve built files
            $this->serve_prod_app($build_url);
        }
    }

    /**
     * Serve development app (Vite dev server)
     */
    private function serve_dev_app() {
        $vite_url = 'http://localhost:5173';
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>KPI Dashboard - MBD Corp</title>
            <script>
                window.KPI_DASHBOARD_CONFIG = {
                    apiUrl: '<?php echo esc_url(rest_url('kpi/v1')); ?>',
                    siteUrl: '<?php echo esc_url(get_site_url()); ?>',
                    baseUrl: '<?php echo esc_url(get_site_url() . '/kpi'); ?>',
                    nonce: '<?php echo wp_create_nonce('wp_rest'); ?>',
                    version: '<?php echo KPI_DASHBOARD_VERSION; ?>',
                    companyName: '<?php echo esc_js(get_option('blogname', 'MBD Corp')); ?>',
                    companyLogo: 'https://www.mbdcorp.id/wp-content/uploads/2022/08/logo-favicon-mbd-corp-150x150-1.webp',
                };
            </script>
        </head>
        <body>
            <div id="root"></div>
            <script type="module" src="<?php echo $vite_url; ?>/@vite/client"></script>
            <script type="module" src="<?php echo $vite_url; ?>/src/main.tsx"></script>
        </body>
        </html>
        <?php
    }

    /**
     * Serve production app (built files)
     */
    private function serve_prod_app($build_url) {
        $manifest_path = KPI_DASHBOARD_PLUGIN_DIR . 'assets/dist/.vite/manifest.json';

        // Read Vite manifest
        $manifest = [];
        if (file_exists($manifest_path)) {
            $manifest = json_decode(file_get_contents($manifest_path), true);
        }

        $main_js = isset($manifest['assets/src/main.tsx']['file'])
            ? $build_url . '/' . $manifest['assets/src/main.tsx']['file']
            : $build_url . '/assets/main.js';

        $main_css = isset($manifest['assets/src/main.tsx']['css'])
            ? $build_url . '/' . $manifest['assets/src/main.tsx']['css'][0]
            : $build_url . '/assets/main.css';
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="theme-color" content="#1565C0">
            <title>KPI Dashboard - MBD Corp</title>
            <link rel="icon" href="https://www.mbdcorp.id/wp-content/uploads/2022/08/logo-favicon-mbd-corp-150x150-1.webp">
            <?php if (file_exists($manifest_path) && isset($manifest['src/main.tsx']['css'])): ?>
            <link rel="stylesheet" href="<?php echo esc_url($main_css); ?>">
            <?php endif; ?>
            <script>
                window.KPI_DASHBOARD_CONFIG = {
                    apiUrl: '<?php echo esc_url(rest_url('kpi/v1')); ?>',
                    siteUrl: '<?php echo esc_url(get_site_url()); ?>',
                    baseUrl: '<?php echo esc_url(get_site_url() . '/kpi'); ?>',
                    nonce: '<?php echo wp_create_nonce('wp_rest'); ?>',
                    version: '<?php echo KPI_DASHBOARD_VERSION; ?>',
                    companyName: '<?php echo esc_js(get_option('blogname', 'MBD Corp')); ?>',
                    companyLogo: 'https://www.mbdcorp.id/wp-content/uploads/2022/08/logo-favicon-mbd-corp-150x150-1.webp',
                };
            </script>
        </head>
        <body>
            <div id="root"></div>
            <script type="module" src="<?php echo esc_url($main_js); ?>"></script>
        </body>
        </html>
        <?php
    }

    /**
     * Enqueue frontend assets (not used in standalone mode, but here for compatibility)
     */
    public function enqueue_frontend_assets() {
        if (!get_query_var('kpi_dashboard')) {
            return;
        }

        // Assets are handled directly in serve_react_app()
        // This function is here for the WordPress hooks compatibility
    }
}
