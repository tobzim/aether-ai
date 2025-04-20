<?php
defined('ABSPATH') || exit;

/**
 * Admin-Handler für Aether AI Connect
 */
class KI_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_post_save_ki_prompts', function() {
            if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
            check_admin_referer('ki_save_prompts', 'ki_prompts_nonce');
        
            $prompts = get_option('ki_custom_prompts', []);
            $prompts[] = [
                'name'   => sanitize_text_field($_POST['new_prompt_name']),
                'prompt' => sanitize_textarea_field($_POST['new_prompt_text']),
            ];
            update_option('ki_custom_prompts', $prompts);
        
            // SAUBER redirecten
            wp_safe_redirect(admin_url('admin.php?page=aether-prompts&saved=1'));
            exit;
        });
        
        // Prompt löschen
        add_action('admin_post_delete_ki_prompt', function() {
            if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        
            $index = intval($_POST['prompt_index'] ?? -1);
            if ($index >= 0) {
                check_admin_referer('ki_delete_prompt_' . $index);
                $prompts = get_option('ki_custom_prompts', []);
                if (isset($prompts[$index])) {
                    unset($prompts[$index]);
                    update_option('ki_custom_prompts', array_values($prompts));
                }
            }
        
            // SAUBER redirecten
            wp_safe_redirect(admin_url('admin.php?page=aether-prompts&deleted=1'));
            exit;
        });
        
    }

    

    /**
     * Menü und Unterseiten registrieren
     */
    public function register_menu() {
        add_menu_page(
            esc_html__('Aether AI Connect', 'aether-ai-connect'),
            esc_html__('Aether AI Connect', 'aether-ai-connect'),
            'manage_options',
            'aether-dashboard',
            [$this, 'render_dashboard_page'],
            'dashicons-cloud', // Icon
            60
        );

        add_submenu_page(
            'aether-dashboard',
            esc_html__('API-Einstellungen', 'aether-ai-connect'),
            esc_html__('API-Einstellungen', 'aether-ai-connect'),
            'manage_options',
            'aether-api-settings',
            [$this, 'render_api_settings_page']
        );

        add_submenu_page(
            'aether-dashboard',
            esc_html__('Aktionen', 'aether-ai-connect'),
            esc_html__('Aktionen', 'aether-ai-connect'),
            'manage_options',
            'aether-prompts',
            [$this, 'render_prompts_page']
        );

        add_submenu_page(
            'aether-dashboard',
            esc_html__('Info & Support', 'aether-ai-connect'),
            esc_html__('Info & Support', 'aether-ai-connect'),
            'manage_options',
            'aether-info',
            [$this, 'render_info_page']
        );
    }

    /**
     * Haupt-Dashboard rendern
     */
    public function render_dashboard_page() {
        require_once plugin_dir_path(__FILE__) . '/views/dashboard-page.php';
    }

    /**
     * API Settings Seite rendern
     */
    public function render_api_settings_page() {
        require_once plugin_dir_path(__FILE__) . '/views/api-settings-page.php';
    }

    /**
     * Prompts Manager Seite rendern
     */
    public function render_prompts_page() {
        require_once plugin_dir_path(__FILE__) . '/views/prompts-page.php';
    }

    /**
     * Info & Support Seite rendern
     */
    public function render_info_page() {
        require_once plugin_dir_path(__FILE__) . '/views/info-page.php';
    }

    /**
     * Admin CSS/JS laden
     */
    public function enqueue_assets($hook) {
        if (strpos($hook, 'aether') !== false) {
            $plugin_file = dirname(__DIR__) . '/aether-ai-connect.php'; // FIXED
            $plugin_url  = plugins_url('', $plugin_file) . '/';
            $plugin_dir  = plugin_dir_path($plugin_file);
    
            wp_enqueue_script(
                'ki-admin-script',
                $plugin_url . 'admin/assets/js/admin.js',
                ['jquery'],
                filemtime($plugin_dir . 'admin/assets/js/admin.js'),
                true
            );
            
            wp_localize_script('ki-admin-script', 'ki_vars', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ki_nonce')
            ]);
    
            wp_enqueue_style(
                'ki-admin-style',
                $plugin_url . 'admin/assets/css/admin-aether.css',
                [],
                filemtime($plugin_dir . 'admin/assets/css/admin-aether.css')
            );
        }
    }
    
}

?>


