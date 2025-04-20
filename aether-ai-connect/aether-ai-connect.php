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

