<?php
defined( 'ABSPATH' ) || exit;

/**
 * Admin-Handler für Aether AI Connect
 */
class KI_Admin {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_head', [ $this, 'admin_icon_size' ] );
        $this->register_prompt_actions();
    }

    /**
     * Menü und Unterseiten registrieren
     */
    public function register_menu() {
        // Haupt-Menü
        add_menu_page(
            esc_html__( 'Aether AI Connect', 'aether-ai-connect' ),
            esc_html__( 'Aether AI Connect', 'aether-ai-connect' ),
            'manage_options',
            'aether-dashboard',
            [ $this, 'render_dashboard_page' ],
            plugins_url( '/assets/icons/aether.svg', __FILE__ ),
            60
        );

        // API-Einstellungen
        add_submenu_page(
            'aether-dashboard',
            esc_html__( 'API‑Einstellungen', 'aether-ai-connect' ),
            esc_html__( 'API‑Einstellungen', 'aether-ai-connect' ),
            'manage_options',
            'aether-api-settings',
            [ $this, 'render_api_settings_page' ]
        );

        // Aktionen & Prompts
        add_submenu_page(
            'aether-dashboard',
            esc_html__( 'Aktionen', 'aether-ai-connect' ),
            esc_html__( 'Aktionen', 'aether-ai-connect' ),
            'manage_options',
            'aether-prompts',
            [ $this, 'render_prompts_page' ]
        );

        // WooGenerator
        add_submenu_page(
            'aether-dashboard',
            esc_html__( 'WooGenerator', 'aether-ai-connect' ),
            esc_html__( 'WooGenerator', 'aether-ai-connect' ),
            'manage_woocommerce',
            'woogenerator',
            [ $this, 'render_woogenerator_page' ]
        );

        // Info & Support
        add_submenu_page(
            'aether-dashboard',
            esc_html__( 'Info & Support', 'aether-ai-connect' ),
            esc_html__( 'Info & Support', 'aether-ai-connect' ),
            'manage_options',
            'aether-info',
            [ $this, 'render_info_page' ]
        );
    }

    /**
     * Dashboard-Seite rendern
     */
    public function render_dashboard_page() {
        include plugin_dir_path( __FILE__ ) . 'views/dashboard-page.php';
    }

    /**
     * API Settings rendern
     */
    public function render_api_settings_page() {
        include plugin_dir_path( __FILE__ ) . 'views/api-settings-page.php';
    }

    /**
     * Prompts Manager rendern
     */
    public function render_prompts_page() {
        include plugin_dir_path( __FILE__ ) . 'views/prompts-page.php';
    }

    /**
     * WooGenerator-Page rendern
     */
    public function render_woogenerator_page() {
        $view  = plugin_dir_path( __FILE__ ) . 'views/woogenerator-page.php';
        if ( file_exists( $view ) ) {
            include $view;
        } else {
            echo '<div class="wrap"><h1>' . esc_html__( 'WooGenerator', 'aether-ai-connect' ) . '</h1>';
            echo '<p>' . esc_html__( 'View-Datei fehlt!', 'aether-ai-connect' ) . '</p></div>';
        }
    }

    /**
     * Info & Support rendern
     */
    public function render_info_page() {
        include plugin_dir_path( __FILE__ ) . 'views/info-page.php';
    }

    /**
     * Prompts speichern/löschen Actions registrieren
     */
    protected function register_prompt_actions() {
        // Prompt speichern
        add_action( 'admin_post_save_ki_prompts', function() {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'Keine Berechtigung.', 'aether-ai-connect' ) );
            }
            check_admin_referer( 'ki_save_prompts', 'ki_prompts_nonce' );

            $prompts = get_option( 'ki_custom_prompts', [] );
            $prompts[] = [
                'name'   => sanitize_text_field( $_POST['new_prompt_name'] ?? '' ),
                'prompt' => sanitize_textarea_field( $_POST['new_prompt_text'] ?? '' ),
            ];
            update_option( 'ki_custom_prompts', $prompts );

            wp_safe_redirect( admin_url( 'admin.php?page=aether-prompts&saved=1' ) );
            exit;
        } );

        // Prompt löschen
        add_action( 'admin_post_delete_ki_prompt', function() {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'Keine Berechtigung.', 'aether-ai-connect' ) );
            }
            $index = intval( $_POST['prompt_index'] ?? -1 );
            if ( $index >= 0 ) {
                check_admin_referer( 'ki_delete_prompt_' . $index );
                $prompts = get_option( 'ki_custom_prompts', [] );
                if ( isset( $prompts[ $index ] ) ) {
                    unset( $prompts[ $index ] );
                    update_option( 'ki_custom_prompts', array_values( $prompts ) );
                }
            }
            wp_safe_redirect( admin_url( 'admin.php?page=aether-prompts&deleted=1' ) );
            exit;
        } );
    }

    /**
     * CSS/JS im Admin laden
     */
    public function enqueue_assets( $hook ) {
        // Alle Aether‑Seiten
        if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'aether' ) === 0 ) {
            $plugin_file = dirname( dirname( __FILE__ ) ) . '/aether-ai-connect.php';
            $plugin_url  = plugins_url( '', $plugin_file ) . '/';
            $plugin_dir  = plugin_dir_path( $plugin_file );

            // Gemeinsame Admin-Skripte
            wp_enqueue_script(
                'ki-admin-script',
                $plugin_url . 'admin/assets/js/admin.js',
                [ 'jquery' ],
                file_exists( $plugin_dir . 'admin/assets/js/admin.js' ) ? filemtime( $plugin_dir . 'admin/assets/js/admin.js' ) : '1.0.0',
                true
            );
            wp_localize_script( 'ki-admin-script', 'ki_vars', [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'ki_nonce' ),
            ] );
            wp_enqueue_style(
                'ki-admin-style',
                $plugin_url . 'admin/assets/css/admin-aether.css',
                [],
                file_exists( $plugin_dir . 'admin/assets/css/admin-aether.css' ) ? filemtime( $plugin_dir . 'admin/assets/css/admin-aether.css' ) : '1.0.0'
            );
        }

        // WooGenerator-spezifisch
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'woogenerator' ) {
            $plugin_file = dirname( dirname( __FILE__ ) ) . '/aether-ai-connect.php';
            $plugin_url  = plugins_url( '', $plugin_file ) . '/';
            $plugin_dir  = plugin_dir_path( $plugin_file );

            wp_enqueue_script(
                'aether-ai-woogenerator',
                $plugin_url . 'assets/js/woogenerator.js',
                [ 'jquery' ],
                file_exists( $plugin_dir . 'assets/js/woogenerator.js' ) ? filemtime( $plugin_dir . 'assets/js/woogenerator.js' ) : '1.0.0',
                true
            );
            wp_enqueue_style(
                'aether-ai-woogenerator',
                $plugin_url . 'admin/assets/css/admin-aether.css',
                [],
                file_exists( $plugin_dir . 'admin/assets/css/admin-aether.css' ) ? filemtime( $plugin_dir . 'admin/assets/css/admin-aether.css' ) : '1.0.0'
            );
            wp_localize_script(
                'aether-ai-woogenerator',
                'aetherAiWoogenerator',
                [
                    // nur Basis‑REST‑URL
                    'root'  => esc_url_raw( rest_url() ),
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                ]
            );
            
        }

        // WooGenerator-spezifisch
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'woogenerator' ) {
            // … dein bestehender wp_enqueue_script + wp_enqueue_style …

            // SweetAlert2 laden (via CDN)
            wp_enqueue_script(
                'sweetalert2',
                'https://cdn.jsdelivr.net/npm/sweetalert2@11',
                [],
                '11.0.0',
                true
            );
            wp_enqueue_style(
                'sweetalert2',
                'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
                [],
                '11.0.0'
            );
            
        // DataTables CSS & JS
        wp_enqueue_style(
            'datatables-css',
            'https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css',
            [],
            '1.13.4'
        );
        wp_enqueue_script(
            'datatables-js',
            'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js',
            [ 'jquery' ],
            '1.13.4',
            true
        );

        }


    }

    /**
     * Admin-Menü-Icon-Zeile skalieren
     */
    public function admin_icon_size() {
        echo '<style>
            #adminmenu .toplevel_page_aether-dashboard .wp-menu-image img {
                width:16px; height:16px;
            }
        </style>';
    }
}
