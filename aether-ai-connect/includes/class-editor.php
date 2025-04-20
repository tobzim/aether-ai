<?php
class KI_Editor {

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_classic']);

        // Classic Editor Integration
        add_filter('mce_external_plugins', [$this, 'add_tinymce_plugin']);
        add_filter('mce_buttons', [$this, 'add_tinymce_button']);

        // Modal für Classic Editor einbauen
        add_action('admin_footer', [$this, 'render_classic_modal']);
        add_action('admin_notices', [$this, 'maybe_warn_gutenberg']);

    }

    public function maybe_warn_gutenberg() {
        // Nur im Adminbereich UND nur auf Beitrag/Seite
        $screen = get_current_screen();
        if (
            $screen && $screen->base === 'post' &&
            function_exists('use_block_editor_for_post') &&
            use_block_editor_for_post(get_post())
        ) {
            echo '<div class="notice notice-warning"><p><strong>Hinweis:</strong> Das Plugin <em>Aether AI Connect</em> funktioniert nur im <strong>Classic Editor</strong>. Bitte deaktiviere den Gutenberg-Editor oder verwende den Classic Editor Plugin-Modus.</p></div>';
        }
    }
    

    public function enqueue_classic($hook) {
        if (in_array($hook, ['post.php', 'post-new.php'])) {
            $plugin_file = dirname(__DIR__) . '/aether-ai-connect.php';
            $plugin_url  = plugins_url('', $plugin_file);
            $plugin_dir  = plugin_dir_path($plugin_file);

            wp_enqueue_script(
                'ki-editor-classic',
                $plugin_url . '/assets/js/editor.js',
                ['wp-util'],
                filemtime($plugin_dir . 'assets/js/editor.js'),
                true
            );

            wp_enqueue_script(
                'ki-modal-classic',
                $plugin_url . '/assets/js/modal.js',
                [],
                filemtime($plugin_dir . 'assets/js/modal.js'),
                true
            );

            wp_enqueue_style(
                'ki-editor-style-classic',
                $plugin_url . '/assets/css/editor.css',
                [],
                filemtime($plugin_dir . 'assets/css/editor.css')
            );

            wp_localize_script('ki-editor-classic', 'ki_vars', [
                'nonce'   => wp_create_nonce('ki_nonce'),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'custom_prompts'  => get_option('ki_custom_prompts', [])
            ]);
        }
    }

    public function add_tinymce_plugin($plugins) {
        $plugin_file = dirname(__DIR__) . '/aether-ai-connect.php';
        $plugin_url  = plugins_url('', $plugin_file);
        $plugins['ki_plugin'] = $plugin_url . '/assets/js/editor.js';
        return $plugins;
    }

    public function add_tinymce_button($buttons) {
        array_push($buttons, 'ki_button');
        return $buttons;
    }

    public function render_classic_modal() {
        if (!function_exists('get_current_screen')) return;
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->base, ['post', 'post-new'])) return;
        ?>
        <div id="ki-modal" class="ki-modal" style="display:none;">
            <div class="ki-modal-content">
                <h2>KI Vorschlag</h2>
                <div id="ki-loading" style="display:none; text-align:center; padding:20px;">
                    <span class="spinner is-active" style="float:none;"></span><br>Lade KI...
                </div>
                <textarea id="ki-result" style="width:100%; height:150px; display:none;"></textarea><br><br>
                <button id="ki-insert" class="button button-primary" style="display:none;">Übernehmen</button>
                <button id="ki-cancel" class="button">Abbrechen</button>
            </div>
        </div>
        <style>
            .ki-modal { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:10000; display:flex; align-items:center; justify-content:center; }
            .ki-modal-content { background:#fff; padding:20px; border-radius:4px; width:500px; max-width:90%; box-shadow:0 5px 15px rgba(0,0,0,0.3); position:relative; }
        </style>
        <?php
    }
    
}
