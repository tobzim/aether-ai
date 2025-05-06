<?php defined('ABSPATH') || exit(); ?>

<div class="wp-admin-wrapper full-dark fade-in">
    <div class="wp-header large">
    <img src="<?php echo plugins_url('../assets/img/wp-logo.svg', __FILE__); ?>" alt="WordPress AI Connect Logo" class="wp-logo">

        <h1 class="wp-title">Info & Support</h1>
        <p class="wp-subtitle">Alles, was du über WordPress AI Connect, die API-Anbieter und die Nutzung wissen musst.</p>
    </div>

    <div class="wp-grid">
        
        <div class="wp-card">
            <h2>🧠 Was ist WordPress AI Connect?</h2>
            <p>
                WordPress AI Connect ist dein smarter Begleiter für bessere Texte. 
                Nutze moderne KI-Modelle wie ChatGPT, Claude oder Gemini, um Texte automatisch zu optimieren, umzuformulieren oder zusammenzufassen – alles direkt im Classic Editor!
            </p>
            <p><strong>Features:</strong></p>
            <ul>
                <li>🔹 Integration direkt im Classic Editor</li>
                <li>🔹 Eigene Aktionen (Prompts) erstellen</li>
                <li>🔹 Unterstützung für verschiedene KI-Anbieter</li>
                <li>🔹 Lizenzsystem für Sicherheit & Updates</li>
            </ul>
        </div>

        <div class="wp-card">
            <h2>🌐 Unterstützte KI-Anbieter</h2>
            <p>Du kannst folgende KI-Dienste nutzen:</p>
            <ul>
                <li>💬 <strong>OpenAI</strong> – gpt-3.5, gpt-4, gpt-4o</li>
                <li>⚡ <strong>DeepSeek</strong> – DeepSeek Chat und Coder</li>
                <li>🧩 <strong>Mistral</strong> – Small, Medium, Large Modelle</li>
                <li>🤖 <strong>Anthropic Claude</strong> – Claude 2, Claude 3</li>
                <li>🔮 <strong>Google Gemini</strong> – Gemini 1.5 Pro & Co.</li>
            </ul>
            <p class="wp-description">
                Du kannst deinen Anbieter jederzeit in den API-Einstellungen ändern.
            </p>
        </div>

        <div class="wp-card">
            <h2>🛠 API-Key einrichten</h2>
            <p>So erhältst du deine API-Schlüssel:</p>
            <ul>
                <li>🔹 <strong>OpenAI:</strong> <a href="https://platform.openai.com/account/api-keys" target="_blank">Hier registrieren</a></li>
                <li>🔹 <strong>DeepSeek:</strong> Auf der <a href="https://platform.deepseek.com/" target="_blank">DeepSeek Plattform</a></li>
                <li>🔹 <strong>Mistral:</strong> <a href="https://console.mistral.ai/" target="_blank">Mistral Console</a></li>
                <li>🔹 <strong>Claude:</strong> <a href="https://console.anthropic.com/" target="_blank">Anthropic Console</a></li>
                <li>🔹 <strong>Gemini:</strong> <a href="https://makersuite.google.com/" target="_blank">Google MakerSuite</a></li>
            </ul>
            <p class="wp-description">Wichtig: Bewahre deine API-Keys sicher auf und gib sie nur in vertrauenswürdige Anwendungen ein.</p>
        </div>

        <div class="wp-card">
            <h2>⚙️ Prompts erstellen</h2>
            <p>Im Reiter <strong>Aktionen</strong> kannst du eigene KI-Befehle definieren, z.B.:</p>
            <ul>
                <li>✏️ "Formuliere diesen Text professioneller: {{text}}"</li>
                <li>🔍 "Fasse den folgenden Text in 3 Sätzen zusammen: {{text}}"</li>
                <li>🔧 "Optimiere die Lesbarkeit und Grammatik: {{text}}"</li>
            </ul>
            <p class="wp-description">
                Nutze <code>{{text}}</code> als Platzhalter für den vom Benutzer markierten Text.
            </p>
        </div>

          <!-- Hier die neue Karte für WooGenerator Bulk -->
          <div class="wp-card">
            <h2>🛒 WooGenerator Bulk‑Generator</h2>
            <p>
                Mit dem neuen <strong>WooGenerator Bulk‑Generator</strong> kannst du mehrere WooCommerce‑Produkte auf einmal optimieren:
            </p>
            <ul>
                <li>🔹 Wähle in einer übersichtlichen Tabelle deine Produkte aus</li>
                <li>🔹 Erhalte automatisch ausführliche HTML‑Beschreibungen und prägnante Kurztexte</li>
                <li>🔹 Führe eine Volltext‑Suche durch und paginiere durch tausende Produkte</li>
                <li>🔹 Alles per AJAX, ohne die Seite neu zu laden</li>
            </ul>
            <p>
                <?php
                /* Link zur WooGenerator‑Seite */
                $woo_url = admin_url( 'admin.php?page=woogenerator' );
                ?>
                <a href="<?php echo esc_url( $woo_url ); ?>" class="button-primary">
                    <?php esc_html_e( 'Zur WooGenerator Bulk‑Seite', 'wp-ai-connect' ); ?>
                </a>
            </p>
        </div>

        <div class="wp-card">
            <h2>Support & Hilfe</h2>
            <p>Bei Fragen, Problemen oder Feature-Wünschen erreichst du uns hier:</p>
            <ul>
                <li>✉️ Support E-Mail: <a href="mailto:support@wptext.ai">support@wptext.ai</a></li>
                <li>🌐 Dokumentation: <a href="https://wptext.ai/docs" target="_blank">WordPress AI Connect Docs</a></li>
                <li>🛡 Lizenzfragen: <a href="https://wptext.ai/license" target="_blank">Lizenzbereich</a></li>
            </ul>
            <p class="wp-description">Wir antworten in der Regel innerhalb von 24 Stunden. 🚀</p>
        </div>

    </div>
</div>
