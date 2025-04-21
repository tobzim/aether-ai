<?php
/*
Plugin Name: Aether AI Connect
Description: Optimiert Texte im Gutenberg und Classic Editor mit OpenAI oder DeepSeek.
Version: 1.0.0
Author: Tobias Zimmer
Text Domain: aether-ai-connect
*/

defined('ABSPATH') || exit;

// Autoloader
foreach (glob(plugin_dir_path(__FILE__) . 'includes/class-*.php') as $file) {
    require_once $file;
}
require_once plugin_dir_path(__FILE__) . 'admin/class-admin.php';

// Init
add_action('plugins_loaded', function () {
    load_plugin_textdomain('aether-ai-connect', false, dirname(plugin_basename(__FILE__)) . '/languages/');
});

// Backend + Admin
if (is_admin()) {
    new KI_Admin();
}

// Editor
new KI_Editor();

// AJAX
new KI_AJAX();

new KI_Settings();

// Lizenzprüfung
require_once plugin_dir_path(__FILE__) . 'includes/class-license.php';
new KI_License();

/**
 * Einfachen Gutenberg-Block registrieren (ohne npm/Build-Toolchain)
 */
function aether_ai_register_block_simple() {
    $dir = plugin_dir_path( __FILE__ );
    $url = plugins_url( '', __FILE__ );

    $js_file = $dir . 'assets/js/block.js';
    $css_file = $dir . 'assets/css/editor.css'; // kannst hier deine bestehende CSS nehmen

    // Version via Timestamp oder Fallback
    $ver_js  = file_exists( $js_file )  ? filemtime( $js_file )  : '1.0.0';
    $ver_css = file_exists( $css_file ) ? filemtime( $css_file ) : '1.0.0';

    // JS laden (abhängig von WP-Globals)
    wp_register_script(
        'aether-ai-block',
        $url . '/assets/js/block.js',
        [ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch' ],
        $ver_js,
        true
    );
    // Editor-CSS laden (optional)
    wp_register_style(
        'aether-ai-block-editor',
        $url . '/assets/css/editor.css',
        [ 'wp-edit-blocks' ],
        $ver_css
    );
    // Blocktyp registrieren (Name muss lowercase)
    register_block_type(
        'aether-ai/connect-block',
        [
            'editor_script' => 'aether-ai-block',
            'editor_style'  => 'aether-ai-block-editor',
        ]
    );
}
add_action( 'init', 'aether_ai_register_block_simple' );

/**
 * REST-API-Route für Block-Optimierung
 */
/**
 * REST-API-Route: Text optimieren
 */
function aether_ai_register_rest_routes() {
    register_rest_route(
        'aether-ai/v1',
        '/optimize',
        [
            'methods'             => 'POST',
            'callback'            => 'aether_ai_rest_optimize',
            'permission_callback' => function() {
                return current_user_can( 'edit_posts' );
            },
        ]
    );
}
add_action( 'rest_api_init', 'aether_ai_register_rest_routes' );

function aether_ai_rest_optimize( WP_REST_Request $request ) {
    try {
        $text = $request->get_param( 'text' );
        if ( empty( $text ) ) {
            return new WP_Error( 'no_text', 'Kein Text übergeben.', [ 'status' => 400 ] );
        }
        // optional: prompt_id verarbeiten
        $prompt_id = $request->get_param( 'prompt_id' ) ?? '';

        // KI_API aufrufen
        $api = new KI_API();
        $optimized = $api->optimize_text( $text, $prompt_id );

        return rest_ensure_response( [ 'optimized_text' => $optimized ] );
    } catch ( Exception $e ) {
        return new WP_Error( 'api_error', $e->getMessage(), [ 'status' => 500 ] );
    }
}

/**
 * REST-API-Route: Liste aller Prompts
 */
function aether_ai_register_prompt_route() {
    register_rest_route(
        'aether-ai/v1',
        '/prompts',
        [
            'methods'             => 'GET',
            'callback'            => 'aether_ai_get_prompts',
            'permission_callback' => function() {
                return current_user_can( 'edit_posts' );
            },
        ]
    );
}
add_action( 'rest_api_init', 'aether_ai_register_prompt_route' );

function aether_ai_get_prompts() {
    $settings = new KI_Settings();
    $all     = $settings->get_all_prompts(); 
    // erwartet ein Array like [ ['id'=>'uuid1','name'=>'Mein Prompt 1'], ... ]
    return rest_ensure_response( $all );
}
