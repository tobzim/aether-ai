<?php
class KI_Editor {

  public function __construct() {
    add_action('admin_enqueue_scripts',   [$this, 'enqueue_classic']);
    add_filter('mce_external_plugins',    [$this, 'add_tinymce_plugin']);
    add_filter('mce_buttons',             [$this, 'add_tinymce_button']);
    add_action('admin_footer',            [$this, 'render_classic_modal']);
    add_action('admin_notices',           [$this, 'maybe_warn_gutenberg']);
  }

  public function maybe_warn_gutenberg() {
    $screen = get_current_screen();
    if (
      $screen && $screen->base === 'post' &&
      function_exists('use_block_editor_for_post') &&
      use_block_editor_for_post(get_post())
    ) {
      echo '<div class="notice notice-warning"><p><strong>Hinweis:</strong> Das Plugin <em>WordPress AI Connect</em> funktioniert nur im <strong>Classic Editor</strong>. Bitte deaktiviere Gutenberg oder nutze den Classic Editor Modus.</p></div>';
    }
  }

  public function enqueue_classic( $hook ) {
    if ( ! in_array( $hook, ['post.php', 'post-new.php'], true ) ) {
        return;
    }
    $plugin_file = dirname(__DIR__) . '/wp-ai-connect.php';
    $plugin_url  = plugins_url( '', $plugin_file );
    $plugin_dir  = plugin_dir_path( $plugin_file );

    // EIN einziges JS-File, enthält Plugin + Modal + AJAX
    wp_enqueue_script(
        'ki-classic',
        $plugin_url . '/assets/js/ki-classic.js',
        [], // läuft sofort, keine Dependencies
        filemtime( $plugin_dir . 'assets/js/ki-classic.js' ),
        true // im Footer
    );
    // Styles wie gehabt
    wp_enqueue_style(
        'ki-editor-style-classic',
        $plugin_url . '/assets/css/classic-editor.css',
        [],
        filemtime( $plugin_dir . 'assets/css/classic-editor.css' )
    );
    // Daten für ki-classic.js
    // Aktiver Anbieter
    $provider = get_option('ki_api_provider', 'openai');
    // Aktuelles Modell für diesen Anbieter
    $model_option_key = 'ki_api_model_' . $provider;
    $model = get_option($model_option_key, '');

    // Übergabe an JS
    wp_localize_script('ki-classic', 'ki_vars', [
    'nonce'          => wp_create_nonce('ki_nonce'),
    'ajaxurl'        => admin_url('admin-ajax.php'),
    'custom_prompts' => get_option('ki_custom_prompts', []),
    'provider'       => $provider,
    'model'          => $model,
    ]);

}


  public function add_tinymce_plugin($plugins) {
    $plugin_file = dirname(__DIR__) . '/wp-ai-connect.php';
    $plugin_url  = plugins_url('', $plugin_file);
    $plugins['ki_plugin'] = $plugin_url . '/assets/js/ki-classic.js';
    return $plugins;
  }

  public function add_tinymce_button($buttons) {
    $buttons[] = 'ki_button';
    return $buttons;
  }

  public function render_classic_modal() {
    if (! function_exists('get_current_screen')) return;
    $screen = get_current_screen();
    if (! $screen || ! in_array($screen->base, ['post','post-new'], true)) return;
    ?>
    <div id="ki-modal" class="ki-modal">
      <div class="ki-modal-content">
        <button type="button" id="ki-modal-close" class="ki-modal-close" aria-label="Schließen">&times;</button>
        <div class="ki-modal-header">
          <h2>WordPress AI Connect</h2>
          <div class="ki-model-info">
            Anbieter: <strong id="ki-provider-name">– Lädt…–</strong><br>
            Modell:  <strong id="ki-selected-model">– Lädt…–</strong>
            </div>
        </div>
        <div id="ki-loading" class="ki-loading">
          <span id="ki-spinner" class="spinner is-active"></span>
          <em>Lade KI…</em>
        </div>
        <div id="ki-result" class="ki-html-output"></div>
        <div class="ki-modal-actions">
          <button type="button" id="ki-insert" class="button-primary">Übernehmen</button>
          <button type="button" id="ki-cancel" class="button-secondary">Abbrechen</button>
        </div>
      </div>
    </div>
    <?php
  }
}
