<?php defined('ABSPATH') || exit(); ?>

<div class="aether-admin-wrapper full-dark fade-in">
    <div class="aether-header large">
    <img src="<?php echo plugins_url('../assets/img/aether-logo.svg', __FILE__); ?>" alt="Aether AI Connect Logo" class="aether-logo">
        <h1 class="aether-title">API & Lizenzverwaltung</h1>
        <p class="aether-subtitle">Stelle deine Verbindung zu KI-Anbietern her und verwalte deinen Lizenzschlüssel. Sicher. Schnell. Einfach.</p>
    </div>

    <div class="aether-card">
        <form method="post" action="options.php">
            <?php
            settings_fields('ki_settings_group');
            $active_provider = get_option('ki_api_provider', 'openai');
            ?>

            <div class="aether-section">
                <h2 class="aether-section-title">⚡ KI-Anbieter auswählen</h2>
                <p class="aether-description">Wähle deinen bevorzugten KI-Anbieter für die Textoptimierung.</p>
                <select name="ki_api_provider" id="ki_api_provider" class="aether-select">
                    <option value="openai" <?= selected('openai', $active_provider); ?>>OpenAI (ChatGPT)</option>
                    <option value="deepseek" <?= selected('deepseek', $active_provider); ?>>DeepSeek</option>
                    <option value="mistral" <?= selected('mistral', $active_provider); ?>>Mistral AI</option>
                    <option value="gemini" <?= selected('gemini', $active_provider); ?>>Google Gemini</option>
                    <option value="claude" <?= selected('claude', $active_provider); ?>>Anthropic Claude</option>
                </select>
            </div>

            <div class="aether-section">
                <h2 class="aether-section-title">🔑 API-Schlüssel eingeben</h2>
                <p class="aether-description">Dein API-Key wird sicher gespeichert und für die Kommunikation mit dem KI-Anbieter genutzt.</p>
                <input type="password"
                       name="ki_api_key_<?php echo esc_attr($active_provider); ?>"
                       value="<?php echo esc_attr(get_option('ki_api_key_' . $active_provider)); ?>"
                       class="aether-input"
                       placeholder="API-Key eingeben" />
            </div>

            <div class="aether-section">
                <h2 class="aether-section-title">⏳ Timeout-Einstellung</h2>
                <p class="aether-description">Lege fest, wie lange das Plugin auf eine Antwort des KI-Servers warten soll, bevor ein Fehler ausgegeben wird.</p>

                <select name="ki_api_timeout" class="aether-select">
                    <?php
                    $current_timeout = get_option('ki_api_timeout', 15); // Standard 15 Sekunden
                    $options = [5, 10, 15, 20, 30, 60, 90, 120];
                    foreach ($options as $sec) {
                        echo '<option value="' . esc_attr($sec) . '" ' . selected($current_timeout, $sec, false) . '>' . esc_html($sec . ' Sekunden') . '</option>';
                    }
                    ?>
                </select>
            </div>


            <div class="aether-section">
                <h2 class="aether-section-title">🧠 Modell wählen</h2>
                <p class="aether-description">Wähle dein Modell – je nach Anbieter stehen verschiedene Optionen zur Verfügung.</p>
                <select name="ki_api_model_<?php echo esc_attr($active_provider); ?>" id="ki_api_model" class="aether-select">
                    <?php
                    $models = [
                        'openai' => ['gpt-3.5-turbo', 'gpt-4', 'gpt-4-1106-preview', 'gpt-4o'],
                        'deepseek' => ['deepseek-chat', 'deepseek-coder'],
                        'mistral' => ['mistral-small', 'mistral-medium', 'mistral-large'],
                        'gemini' => ['gemini-pro', 'gemini-1.5-pro', 'gemini-1.0-pro'],
                        'claude' => ['claude-2', 'claude-3-sonnet-20240229', 'claude-3-opus-20240229']
                    ];
                    $current_model = get_option('ki_api_model_' . $active_provider);

                    foreach ($models[$active_provider] as $model) {
                        echo '<option value="' . esc_attr($model) . '" ' . selected($current_model, $model, false) . '>' . esc_html($model) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="aether-section">
                <h2 class="aether-section-title">🛡 Lizenzschlüssel</h2>
                <p class="aether-description">Dein Lizenzschlüssel aktiviert dein Plugin vollständig. Ohne Lizenz ist der Funktionsumfang eingeschränkt.</p>
                <input type="password"
                       name="ki_license_key"
                       id="ki_license_key"
                       value="<?php echo esc_attr(get_option('ki_license_key')); ?>"
                       class="aether-input"
                       placeholder="Lizenzschlüssel eingeben" />
            </div>

            <div class="aether-footer">
                <?php submit_button('💾 Änderungen speichern', 'primary', 'submit', false, ['class' => 'button-primary']); ?>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const providerSelect = document.getElementById('ki_api_provider');
    const modelSelect = document.getElementById('ki_api_model');

    const models = {
        openai: ['gpt-3.5-turbo', 'gpt-4', 'gpt-4-1106-preview', 'gpt-4o'],
        deepseek: ['deepseek-chat', 'deepseek-coder'],
        mistral: ['mistral-small', 'mistral-medium', 'mistral-large'],
        gemini: ['gemini-pro', 'gemini-1.5-pro', 'gemini-1.0-pro'],
        claude: ['claude-2', 'claude-3-sonnet-20240229', 'claude-3-opus-20240229']
    };

    providerSelect.addEventListener('change', function () {
        const selected = this.value;
        const availableModels = models[selected] || [];

        modelSelect.innerHTML = '';
        availableModels.forEach(function (model) {
            const option = document.createElement('option');
            option.value = model;
            option.textContent = model;
            modelSelect.appendChild(option);
        });

        // Feldnamen richtig setzen
        document.getElementById('ki_api_key').name = 'ki_api_key_' + selected;
        modelSelect.name = 'ki_api_model_' + selected;
    });
});
</script>
