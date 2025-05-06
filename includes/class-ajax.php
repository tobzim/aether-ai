<?php

class KI_AJAX {

    public function __construct() {
        // Bestehende AJAX Aktionen
        add_action('wp_ajax_ki_optimize', [$this, 'process']);
        add_action('wp_ajax_ki_test_api', [$this, 'test_api']);

        // ➕ Neue AJAX Aktionen für Prompts
        add_action('wp_ajax_ki_save_prompt', [$this, 'save_prompt']);
        add_action('wp_ajax_ki_update_prompt', [$this, 'update_prompt']);
        add_action('wp_ajax_ki_delete_prompt', [$this, 'delete_prompt']);
        add_action('wp_ajax_ki_optimize_prompt', [$this, 'optimize_prompt']);
    }

    public function process() {
        check_ajax_referer('ki_nonce', 'nonce');

        // 🔐 Lizenz prüfen
        $license = new KI_License();
        if (!$license->is_valid()) {
            wp_send_json_success(['optimized' => '❌ Kein gültiger Lizenzschlüssel vorhanden.']);
            return;
        }

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Keine Berechtigung');
        }

        $text   = sanitize_textarea_field($_POST['text'] ?? '');
        $prompt = sanitize_text_field($_POST['prompt'] ?? '');

        if (empty($text) || empty($prompt)) {
            wp_send_json_success(['optimized' => '⚠️ Kein Text oder Prompt angegeben.']);
            return;
        }

        $api = new KI_API();
        $result = $api->call($text, $prompt);

        if (empty($result)) {
            wp_send_json_success(['optimized' => '⚠️ Keine Antwort erhalten.']);
        } else {
            wp_send_json_success(['optimized' => $result]);
        }
    }

    public function test_api() {
        check_ajax_referer('ki_nonce', 'nonce');

        $license = new KI_License();
        if (!$license->is_valid()) {
            wp_send_json_error('❌ Ungültiger Lizenzschlüssel');
            return;
        }

        wp_send_json_success('✅ Verbindung erfolgreich');
    }

    // ➕ Prompt hinzufügen
    public function save_prompt() {
        check_ajax_referer('ki_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Keine Berechtigung');
        }

        $name = sanitize_text_field($_POST['name'] ?? '');
        $prompt = sanitize_textarea_field($_POST['prompt'] ?? '');

        if (empty($name) || empty($prompt)) {
            wp_send_json_error('Name oder Prompt fehlt.');
        }

        $prompts = get_option('ki_custom_prompts', []);
        $prompts[] = [
            'name' => $name,
            'prompt' => $prompt
        ];
        update_option('ki_custom_prompts', $prompts);

        wp_send_json_success('Prompt gespeichert');
    }

    // ✏️ Prompt aktualisieren
    public function update_prompt() {
        check_ajax_referer('ki_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Keine Berechtigung');
        }

        $index = (int)($_POST['index'] ?? -1);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $prompt = sanitize_textarea_field($_POST['prompt'] ?? '');

        if ($index < 0 || empty($name) || empty($prompt)) {
            wp_send_json_error('Ungültige Daten');
        }

        $prompts = get_option('ki_custom_prompts', []);
        if (!isset($prompts[$index])) {
            wp_send_json_error('Prompt nicht gefunden.');
        }

        $prompts[$index]['name'] = $name;
        $prompts[$index]['prompt'] = $prompt;

        update_option('ki_custom_prompts', array_values($prompts));

        wp_send_json_success('Prompt aktualisiert');
    }

    // 🗑 Prompt löschen
    public function delete_prompt() {
        check_ajax_referer('ki_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Keine Berechtigung');
        }

        $index = (int)($_POST['index'] ?? -1);
        if ($index < 0) {
            wp_send_json_error('Ungültiger Index');
        }

        $prompts = get_option('ki_custom_prompts', []);
        if (!isset($prompts[$index])) {
            wp_send_json_error('Prompt nicht gefunden.');
        }

        unset($prompts[$index]);
        update_option('ki_custom_prompts', array_values($prompts));

        wp_send_json_success('Prompt gelöscht');
    }

    // ✨ Prompt KI-optimieren lassen
    public function optimize_prompt() {
        check_ajax_referer('ki_nonce', 'nonce');

        $text = sanitize_textarea_field($_POST['prompt'] ?? '');

        if (empty($text)) {
            wp_send_json_error('Kein Text zum Optimieren erhalten.');
        }

        $api = new KI_API();
        $optimized = $api->call($text, 'Formuliere diesen Text besser um: {{text}}');

        if (empty($optimized)) {
            wp_send_json_error('Keine Antwort erhalten');
        }

        wp_send_json_success(['optimized' => $optimized]);
    }
}
