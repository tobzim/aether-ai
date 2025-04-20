<?php defined('ABSPATH') || exit(); ?>

<div class="aether-admin-wrapper full-dark">
    <div class="aether-header large">
        <img src="<?php echo plugins_url('../assets/img/aether-logo.svg', __FILE__); ?>" alt="Aether AI Connect Logo" class="aether-logo">
        <h1 class="aether-title">Aether AI Connect</h1>
        <p class="aether-subtitle">Dein smarter Helfer für perfekte Texte – KI-gestützt und benutzerfreundlich.</p>
    </div>

    <div class="aether-grid">
        
        <div class="aether-card">
            <h2>🚀 Schnellstart</h2>
            <p>🔹 API-Key deines bevorzugten KI-Anbieters hinterlegen.<br>
               🔹 Eigene Aktionen (Prompts) definieren oder Beispiele verwenden.<br>
               🔹 Text markieren und verbessern lassen – direkt im Classic Editor.</p>

            <div class="aether-actions">
                <a href="<?php echo admin_url('admin.php?page=aether-api-settings'); ?>" class="button-primary">🔌 API-Einstellungen</a>
                <a href="<?php echo admin_url('admin.php?page=aether-prompts'); ?>" class="button-secondary">⚙️ Aktionen verwalten</a>
            </div>
        </div>

        <div class="aether-card">
            <h2>🔑 Unterstützte KI-Anbieter</h2>
            <ul class="aether-list">
                <li><strong>OpenAI</strong> – GPT-4, GPT-3.5 Turbo</li>
                <li><strong>DeepSeek</strong> – DeepSeek Chat & Coder</li>
                <li><strong>Mistral</strong> – Small, Medium, Large Modelle</li>
                <li><strong>Anthropic Claude</strong> – Claude 3 Opus, Sonnet</li>
                <li><strong>Google Gemini</strong> – Gemini 1.5 Pro, Gemini Pro</li>
            </ul>
        </div>

        <div class="aether-card">
            <h2>📚 Nutzung von Aether</h2>
            <ol class="aether-steps">
                <li><strong>Text markieren</strong> im Classic Editor.</li>
                <li><strong>KI-Aktion wählen</strong> (z.B. "Optimiere", "Kürze").</li>
                <li><strong>Antwort prüfen</strong> und übernehmen oder verwerfen.</li>
                <li><strong>Fertig!</strong> Dein optimierter Text ist einsatzbereit.</li>
            </ol>
        </div>

        <div class="aether-card">
            <h2>🛠 Support & Hilfe</h2>
            <p>Fragen? Probleme?  
               Hier findest du Hilfestellungen zur API-Integration, Key-Erstellung und Prompt-Formulierung:</p>
            <p><a href="<?php echo admin_url('admin.php?page=aether-info'); ?>" class="button-secondary">➡️ Zur Info & Support-Seite</a></p>
        </div>

    </div>
</div>
