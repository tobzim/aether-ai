<?php

class KI_API {

    /**
     * Optimiert den gegebenen Text anhand eines gespeicherten oder Standard-Prompts
     * Entfernt dabei ```html```-Fences und filtert nur erlaubte HTML-Tags heraus.
     *
     * @param string $text       Der Eingabetext.
     * @param string $prompt_id  Optional: ID eines gespeicherten Prompts.
     * @return string            Gefilterter HTML-Text.
     * @throws Exception         Wenn keine Antwort von der KI kommt oder Prompt-ID ungültig ist.
     */
    public function optimize_text( string $text, string $prompt_id = '' ): string {
        // 1) Prompt-Text bestimmen
        if ( $prompt_id ) {
            $settings = new KI_Settings();
            $all      = $settings->get_all_prompts();
            $found    = array_filter( $all, fn( $p ) => $p['id'] === $prompt_id );
            if ( ! $found ) {
                throw new Exception( "Unbekannte Prompt-ID: {$prompt_id}" );
            }
            $promptData = array_shift( $found );
            $promptText = $promptData['prompt'];
        } else {
            // Fallback‑Prompt: gibt einfach den reinen Text zurück
            $promptText = get_option( 'ki_api_default_prompt', '{{text}}' );
        }
    
        // 2) KI-Call
        $result = $this->call( $text, $promptText );
        if ( '' === trim( $result ) ) {
            throw new Exception( 'Keine Antwort von der KI erhalten.' );
        }
    
        // 3) Clean‑Up
        // a) Alle ```html```‑Fences entfernen
        $result = preg_replace( '/```html[\\s\\S]*?```/i', '', $result );
    
        // b) Nur gewünschte HTML‑Tags erlauben
        $allowed_tags = [
            'h1'     => [],
            'h2'     => [],
            'h3'     => [],
            'p'      => [],
            'ul'     => [],
            'ol'     => [],
            'li'     => [],
            'strong' => [],
            'em'     => [],
        ];
        $clean = wp_kses( $result, $allowed_tags );
    
        // Rückgabe: reines, erlaubtes HTML
        return trim( $clean );
    }
    

    /**
     * Führt den API-Call aus und cached das Ergebnis kurzzeitig.
     *
     * @param string $text         Eingabetext zum Prompt.
     * @param string $promptText   Promptvorlage mit {{text}} Platzhalter.
     * @return string              Rückgabe der rohen KI-Antwort.
     */
    public function call( $text, $promptText ) {
        $cache_key = 'ki_cache_' . md5( $text . $promptText );
        $cached    = get_transient( $cache_key );
        if ( $cached ) {
            return $cached;
        }

        $provider = get_option( 'ki_api_provider', 'openai' );
        $api_key  = get_option( 'ki_api_key_' . $provider );
        $model    = get_option( 'ki_api_model_' . $provider );

        if ( ! $api_key || ! $promptText || ! $text ) {
            return '⚠️ Kein Text oder Prompt angegeben.';
        }

        // Platzhalter ersetzen
        $fullPrompt = str_replace( [ '{{text}}', '{text}' ], $text, $promptText );
        $response   = '';

        switch ( $provider ) {
            case 'openai':
                $response = $this->call_openai( $api_key, $model, $fullPrompt );
                break;
            case 'deepseek':
                $response = $this->call_openai( $api_key, $model ?: 'deepseek-chat', $fullPrompt, 'deepseek' );
                break;
            case 'mistral':
                $response = $this->call_openai( $api_key, $model ?: 'mistral-small', $fullPrompt, 'mistral' );
                break;
            case 'claude':
                $response = $this->call_claude( $api_key, $model ?: 'claude-3-opus-20240229', $fullPrompt );
                break;
            case 'gemini':
                $response = $this->call_gemini( $api_key, $model ?: 'gemini-pro', $fullPrompt );
                break;
            default:
                return '⚠️ Kein Anbieter konfiguriert.';
        }

        // Cache für 5 Minuten
        set_transient( $cache_key, $response, 5 * MINUTE_IN_SECONDS );
        return $response ?: '⚠️ Keine Antwort von der KI.';
    }

    /**
     * OpenAI / Chat-Completions API-Call
     */
    private function call_openai( $api_key, $model, $prompt, $provider = 'openai' ) {
        $url = 'https://api.openai.com/v1/chat/completions';
        if ( 'deepseek' === $provider ) {
            $url = 'https://api.deepseek.com/v1/chat/completions';
        } elseif ( 'mistral' === $provider ) {
            $url = 'https://api.mistral.ai/v1/chat/completions';
        }

        $messages = [
            [ 'role' => 'system', 'content' => 'Du bist ein professioneller KI-Textexperte.' ],
            [ 'role' => 'user', 'content' => $prompt ]
        ];

        error_log( '🧠 Prompt an Modell: ' . $prompt );
        $response = wp_remote_post( $url, [
            'timeout' => (int) get_option( 'ki_api_timeout', 15 ),
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json'
            ],
            'body' => wp_json_encode( [
                'model'       => $model,
                'messages'    => $messages,
                'temperature' => 0.7,
                'max_tokens'  => 1024
            ] )
        ] );

        if ( is_wp_error( $response ) ) {
            return '';
        }
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        error_log( '📦 KI-Rohantwort: ' . print_r( $body, true ) );
        return $body['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Claude API-Call
     */
    private function call_claude( $api_key, $model, $prompt ) {
        $endpoint = 'https://api.anthropic.com/v1/messages';
        $response = wp_remote_post( $endpoint, [
            'timeout' => (int) get_option( 'ki_api_timeout', 15 ),
            'headers' => [
                'x-api-key'         => $api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type'      => 'application/json'
            ],
            'body' => wp_json_encode( [
                'model'    => $model,
                'max_tokens'=> 1024,
                'messages' => [ [ 'role' => 'user', 'content' => $prompt ] ]
            ] )
        ] );

        if ( is_wp_error( $response ) ) {
            return '';
        }
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        return $body['content'][0]['text'] ?? '';
    }

    /**
     * Google Gemini API-Call
     */
    private function call_gemini( $api_key, $model, $prompt ) {
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
        $response = wp_remote_post( $endpoint, [
            'timeout' => (int) get_option( 'ki_api_timeout', 15 ),
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => wp_json_encode( [ 'contents'=> [ [ 'parts'=> [ [ 'text'=> $prompt ] ] ] ] ] )
        ] );

        if ( is_wp_error( $response ) ) {
            return '';
        }
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        return $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

}
