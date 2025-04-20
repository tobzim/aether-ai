<?php

class KI_API {

    public function call($text, $promptText) {
        $cache_key = 'ki_cache_' . md5($text . $promptText);
        $cached = get_transient($cache_key);
        if ($cached) return $cached;

        $provider  = get_option('ki_api_provider', 'openai');
        $api_key   = get_option('ki_api_key_' . $provider);
        $model     = get_option('ki_api_model_' . $provider);

        if (!$api_key || !$promptText || !$text) return '⚠️ Kein Text oder Prompt angegeben.';

        $fullPrompt = str_replace(['{{text}}', '{text}'], $text, $promptText);
        $response = '';

        switch ($provider) {
            case 'openai':
                $response = $this->call_openai($api_key, $model, $fullPrompt);
                break;

            case 'deepseek':
                $response = $this->call_openai($api_key, $model ?: 'deepseek-chat', $fullPrompt, 'deepseek');
                break;

            case 'mistral':
                $response = $this->call_openai($api_key, $model ?: 'mistral-small', $fullPrompt, 'mistral');
                break;

            case 'claude':
                $response = $this->call_claude($api_key, $model ?: 'claude-3-opus-20240229', $fullPrompt);
                break;

            case 'gemini':
                $response = $this->call_gemini($api_key, $model ?: 'gemini-pro', $fullPrompt);
                break;

            default:
                return '⚠️ Kein Anbieter konfiguriert.';
        }

        set_transient($cache_key, $response, 5 * MINUTE_IN_SECONDS);
        return $response ?: '⚠️ Keine Antwort von der KI.';
    }

    private function call_openai($api_key, $model, $prompt, $provider = 'openai') {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        if ($provider === 'deepseek') $endpoint = 'https://api.deepseek.com/v1/chat/completions';
        if ($provider === 'mistral')  $endpoint = 'https://api.mistral.ai/v1/chat/completions';
    
        $messages = [
            ['role' => 'system', 'content' => 'Du bist ein professioneller KI-Textexperte.'],
            ['role' => 'user', 'content' => $prompt]
        ];
    
        error_log('🧠 Prompt an Modell: ' . $prompt);
    
        $response = wp_remote_post($endpoint, [
            'timeout' => (int) get_option('ki_api_timeout', 15), // Hole gespeicherten Timeout
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json'
            ],
            'body' => json_encode([
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens'  => 1024
            ])
        ]);
    
        if (is_wp_error($response)) return '';
    
        $body = json_decode(wp_remote_retrieve_body($response), true);
        error_log('📦 KI-Rohantwort: ' . print_r($body, true));
    
        return $body['choices'][0]['message']['content'] ?? '';
    }
    

    private function call_claude($api_key, $model, $prompt) {
        $endpoint = 'https://api.anthropic.com/v1/messages';

        $response = wp_remote_post($endpoint, [
            'timeout' => (int) get_option('ki_api_timeout', 15), // Hole gespeicherten Timeout
            'headers' => [
                'x-api-key'         => $api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type'      => 'application/json'
            ],
            'body' => json_encode([
                'model'    => $model,
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ])
        ]);

        if (is_wp_error($response)) return '';
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['content'][0]['text'] ?? '';
    }

    private function call_gemini($api_key, $model, $prompt) {
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $api_key;

        $response = wp_remote_post($endpoint, [
            'timeout' => (int) get_option('ki_api_timeout', 15), // Hole gespeicherten Timeout
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body' => json_encode([
                'contents' => [[ 'parts' => [[ 'text' => $prompt ]] ]]
            ])
        ]);

        if (is_wp_error($response)) return '';
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
}
