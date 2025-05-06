<?php defined('ABSPATH') || exit(); ?>

<div class="wp-admin-wrapper full-dark">
    <div class="wp-header large">
        <img src="<?php echo plugins_url('../assets/img/wp-logo.svg', __FILE__); ?>" alt="WordPress AI Connect Logo" class="wp-logo">
        <h1 class="wp-title">WordPress AI Connect</h1>
        <p class="wp-subtitle">Dein smarter Helfer für perfekte Texte – KI-gestützt und benutzerfreundlich.</p>
    </div>

    <div class="wp-grid">
        
        <div class="wp-card">
            <h2>🚀 Schnellstart</h2>
            <p>🔹 API-Key deines bevorzugten KI-Anbieters hinterlegen.<br>
               🔹 Eigene Aktionen (Prompts) definieren oder Beispiele verwenden.<br>
               🔹 Text markieren und verbessern lassen – direkt im Classic Editor.</p>

            <div class="wp-actions">
                <a href="<?php echo admin_url('admin.php?page=wp-api-settings'); ?>" class="button-primary">🔌 API-Einstellungen</a>
                <a href="<?php echo admin_url('admin.php?page=wp-prompts'); ?>" class="button-secondary">⚙️ Aktionen verwalten</a>
            </div>
        </div>

        <div class="wp-card">
            <h2>🔑 Unterstützte KI-Anbieter</h2>
            <ul class="wp-list">
                <li><strong>OpenAI</strong> – GPT-4, GPT-3.5 Turbo</li>
                <li><strong>DeepSeek</strong> – DeepSeek Chat & Coder</li>
                <li><strong>Mistral</strong> – Small, Medium, Large Modelle</li>
                <li><strong>Anthropic Claude</strong> – Claude 3 Opus, Sonnet</li>
                <li><strong>Google Gemini</strong> – Gemini 1.5 Pro, Gemini Pro</li>
            </ul>
        </div>

        <div class="wp-card">
            <h2>📚 Nutzung von wp</h2>
            <ol class="wp-steps">
                <li><strong>Text markieren</strong> im Classic Editor.</li>
                <li><strong>KI-Aktion wählen</strong> (z.B. "Optimiere", "Kürze").</li>
                <li><strong>Antwort prüfen</strong> und übernehmen oder verwerfen.</li>
                <li><strong>Fertig!</strong> Dein optimierter Text ist einsatzbereit.</li>
            </ol>
        </div>

        <div class="wp-card">
            <h2>🛠 Support & Hilfe</h2>
            <p>Fragen? Probleme?  
               Hier findest du Hilfestellungen zur API-Integration, Key-Erstellung und Prompt-Formulierung:</p>
            <p><a href="<?php echo admin_url('admin.php?page=wp-info'); ?>" class="button-secondary">➡️ Zur Info & Support-Seite</a></p>
        </div>

    </div>
</div>
