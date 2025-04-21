<?php
defined('ABSPATH') || exit;

/**
 * Einstellungen & Prompt-Management für Aether AI Connect
 */
class KI_Settings {

    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'handle_prompt_actions']);
        add_action('admin_notices', [$this, 'show_admin_notices']);
    }

    /**
     * Registriere alle Plugin-Optionen
     */
    public function register_settings() {
        register_setting('ki_settings_group', 'ki_api_provider');
        register_setting('ki_settings_group', 'ki_license_key');
        register_setting('ki_settings_group', 'ki_api_timeout');


        foreach (['openai', 'deepseek', 'mistral', 'gemini', 'claude'] as $provider) {
            register_setting('ki_settings_group', 'ki_api_key_' . $provider);
            register_setting('ki_settings_group', 'ki_api_model_' . $provider);
        }
    }

    /**
     * Handle Prompt hinzufügen / löschen
     */
    public function handle_prompt_actions() {
        if (!current_user_can('manage_options')) return;

        if (isset($_POST['ki_add_prompt']) && check_admin_referer('ki_manage_prompts')) {
            $prompts = get_option('ki_custom_prompts', []);
            $new_prompt = [
                'name'   => sanitize_text_field($_POST['ki_new_prompt_name']),
                'prompt' => sanitize_textarea_field($_POST['ki_new_prompt_text']),
            ];
            $prompts[] = $new_prompt;
            update_option('ki_custom_prompts', $prompts);

            wp_redirect(add_query_arg('ki_notice', 'prompt_added', admin_url('admin.php?page=aether-ai-connect')));
            exit;
        }

        if (isset($_POST['ki_delete_prompt']) && check_admin_referer('ki_manage_prompts')) {
            $prompts = get_option('ki_custom_prompts', []);
            $index = (int) $_POST['ki_delete_prompt'];
            if (isset($prompts[$index])) {
                unset($prompts[$index]);
                update_option('ki_custom_prompts', array_values($prompts));

                wp_redirect(add_query_arg('ki_notice', 'prompt_deleted', admin_url('admin.php?page=aether-ai-connect')));
                exit;
            }
        }
    }

    /**
     * Zeige Admin Notices
     */
    public function show_admin_notices() {
        if (!isset($_GET['ki_notice'])) return;

        $notices = [
            'prompt_added'   => __('✅ Neuer Prompt erfolgreich gespeichert.', 'aether-ai-connect'),
            'prompt_deleted' => __('🗑️ Prompt erfolgreich gelöscht.', 'aether-ai-connect'),
            'settings_saved' => __('✅ Einstellungen gespeichert.', 'aether-ai-connect')
        ];

        $notice_key = sanitize_text_field($_GET['ki_notice']);
        if (isset($notices[$notice_key])) {
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                esc_html($notices[$notice_key])
            );
        }
    }

    /**
     * API-Tab (Verbindung & Lizenz)
     */
    public function render_tab_api() {
        $active_provider = get_option('ki_api_provider', 'openai');
        ?>
        <form method="post" action="options.php" id="ki-settings-form">
            <?php settings_fields('ki_settings_group'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="ki_api_provider"><?php esc_html_e('Aktiver KI-Anbieter', 'aether-ai-connect'); ?></label></th>
                    <td>
                        <select name="ki_api_provider" id="ki_api_provider">
                            <option value="openai" <?php selected('openai', $active_provider); ?>>OpenAI (ChatGPT)</option>
                            <option value="deepseek" <?php selected('deepseek', $active_provider); ?>>DeepSeek</option>
                            <option value="mistral" <?php selected('mistral', $active_provider); ?>>Mistral</option>
                            <option value="gemini" <?php selected('gemini', $active_provider); ?>>Google Gemini</option>
                            <option value="claude" <?php selected('claude', $active_provider); ?>>Anthropic Claude</option>
                        </select>
                        <p class="description">Wähle deinen bevorzugten KI-Anbieter für Textoptimierungen.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="ki_api_key">API-Key</label></th>
                    <td>
                        <input type="text" id="ki_api_key" name="ki_api_key_<?php echo esc_attr($active_provider); ?>" value="<?php echo esc_attr(get_option('ki_api_key_' . $active_provider)); ?>" size="60" />
                        <p class="description">Trage hier deinen API-Key für den Anbieter ein.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="ki_model">Modell</label></th>
                    <td>
                        <select id="ki_model" name="ki_api_model_<?php echo esc_attr($active_provider); ?>">
                            <?php foreach ($this->get_models_for_provider($active_provider) as $model) : ?>
                                <option value="<?php echo esc_attr($model); ?>" <?php selected($model, get_option('ki_api_model_' . $active_provider)); ?>>
                                    <?php echo esc_html($model); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Optional: Wähle das Modell, z.B. GPT-4, Claude 3 oder Gemini Pro.</p>
                    </td>
                </tr>
            </table>

            <h2>🔐 Lizenz</h2>
            <table class="form-table">
                <tr>
                    <th><label for="ki_license_key">Lizenzschlüssel</label></th>
                    <td>
                        <input type="text" name="ki_license_key" id="ki_license_key" value="<?php echo esc_attr(get_option('ki_license_key')); ?>" size="50" />
                        <p class="description">Gib deinen Lizenzschlüssel ein, um Updates und Support zu erhalten.</p>
                    </td>
                </tr>
            </table>

            <?php submit_button('Änderungen speichern'); ?>
        </form>
        <?php
    }

    /**
     * Prompts-Tab (Verwaltung eigener Aktionen)
     */
    public function render_tab_prompts() {
        $prompts = get_option('ki_custom_prompts', []);
        $example_prompts = [
            [
                'name' => 'Optimiere',
                'prompt' => 'Optimiere diesen Text für bessere Lesbarkeit: {{text}}'
            ],
            [
                'name' => 'Kürze',
                'prompt' => 'Fasse diesen Text auf das Wesentliche zusammen: {{text}}'
            ],
            [
                'name' => 'Formuliere um',
                'prompt' => 'Formuliere den folgenden Text professioneller: {{text}}'
            ],
            [
                'name' => 'Erweitere',
                'prompt' => 'Füge diesem Text mehr Details hinzu: {{text}}'
            ]
        ];
        ?>
        <h2>⚙️ Eigene Aktionen / Prompts</h2>

        <form method="post" action="">
            <?php wp_nonce_field('ki_manage_prompts'); ?>

            <table class="form-table" style="max-width:800px;">
                <tr>
                    <th><label>Beispielaktion</label></th>
                    <td>
                        <select id="ki_prompt_examples" onchange="loadPromptExample(this)">
                            <option value="">– Wähle eine Beispielaktion –</option>
                            <?php foreach ($example_prompts as $ex) : ?>
                                <option value="<?php echo esc_attr($ex['name'] . '||' . $ex['prompt']); ?>">
                                    <?php echo esc_html($ex['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Wähle eine Beispielaktion zum schnellen Ausfüllen.</p>
                    </td>
                </tr>

                <tr>
                    <th><label for="ki_new_prompt_name">Name der Aktion</label></th>
                    <td><input type="text" id="ki_new_prompt_name" name="ki_new_prompt_name" class="regular-text" required></td>
                </tr>

                <tr>
                    <th><label for="ki_new_prompt_text">Prompt-Text</label></th>
                    <td>
                        <textarea id="ki_new_prompt_text" name="ki_new_prompt_text" rows="5" class="large-text code" required></textarea>
                        <p class="description">Verwende <code>{{text}}</code> als Platzhalter für den Inhalt.</p>
                    </td>
                </tr>
            </table>

            <p>
                <button type="submit" name="ki_add_prompt" class="button button-primary">➕ Aktion speichern</button>
            </p>
        </form>

        <hr>

        <h3>📄 Vorhandene Aktionen</h3>
        <table class="widefat fixed striped">
            <thead>
                <tr><th>Name</th><th>Prompt</th><th>Aktion</th></tr>
            </thead>
            <tbody>
                <?php if (empty($prompts)) : ?>
                    <tr><td colspan="3">Noch keine eigenen Aktionen.</td></tr>
                <?php else : ?>
                    <?php foreach ($prompts as $key => $data) : ?>
                        <tr>
                            <td><?php echo esc_html($data['name']); ?></td>
                            <td><code><?php echo esc_html($data['prompt']); ?></code></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <?php wp_nonce_field('ki_manage_prompts'); ?>
                                    <input type="hidden" name="ki_delete_prompt" value="<?php echo esc_attr($key); ?>">
                                    <button type="submit" class="button button-small">❌ Löschen</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <script>
            function loadPromptExample(select) {
                if (!select.value.includes('||')) return;
                const [name, prompt] = select.value.split('||');
                document.getElementById('ki_new_prompt_name').value = name;
                document.getElementById('ki_new_prompt_text').value = prompt;
            }
        </script>
        <?php
    }

    /**
     * Liefere Modelle je Provider
     */
    private function get_models_for_provider($provider) {
        switch ($provider) {
            case 'openai':
                return ['gpt-3.5-turbo', 'gpt-4', 'gpt-4-1106-preview', 'gpt-4o'];
            case 'mistral':
                return ['mistral-small', 'mistral-medium', 'mistral-large'];
            case 'claude':
                return ['claude-2', 'claude-3-sonnet-20240229', 'claude-3-opus-20240229'];
            case 'gemini':
                return ['gemini-pro', 'gemini-1.5-pro', 'gemini-1.0-pro'];
            case 'deepseek':
                return ['deepseek-chat', 'deepseek-coder'];
            default:
                return ['default-model'];
        }
    }
    
    /**
 * Liefert alle benutzerdefinierten Prompts als Array mit id, name und prompt.
 *
 * @return array [
 *   ['id' => '0', 'name' => 'Mein Prompt', 'prompt' => 'Text {{text}}'],
 *   …
 * ]
 */
public function get_all_prompts() {
    $custom = get_option( 'ki_custom_prompts', [] );
    $prompts = [];

    foreach ( $custom as $index => $item ) {
        $prompts[] = [
            'id'     => (string) $index,
            'name'   => $item['name'],
            'prompt' => $item['prompt'],
        ];
    }

    return $prompts;
}

}
