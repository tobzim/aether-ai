<?php
defined('ABSPATH') || exit;

// Hole vorhandene Prompts
$custom_prompts = get_option('ki_custom_prompts', []);
$example_prompts = [
    ['name' => 'Optimiere Stil', 'prompt' => 'Optimiere den folgenden Text: {{text}}'],
    ['name' => 'Kürze Text', 'prompt' => 'Fasse den Text zusammen: {{text}}'],
    ['name' => 'Formuliere freundlicher', 'prompt' => 'Formuliere den Text freundlicher: {{text}}'],
    ['name' => 'Füge Details hinzu', 'prompt' => 'Erweitere den Text um relevante Details: {{text}}'],
];
?>

<div class="wp-admin-wrapper full-dark fade-in">

    <div class="wp-header large">
        <img src="<?php echo plugins_url('../assets/img/wp-logo.svg', __FILE__); ?>" alt="WordPress AI Connect Logo" class="wp-logo">
        <h1 class="wp-title">Aktionen & Prompts verwalten</h1>
        <p class="wp-subtitle">Erstelle, optimiere und verwalte deine individuellen KI-Aktionen effizient.</p>
    </div>

    <div class="wp-card wp-create-form">
        <h2>➕ Neuen Prompt erstellen</h2>

        <div class="wp-info">
            <strong>Hinweis:</strong> Verwende <code>{{text}}</code> als Platzhalter für den Benutzertext.<br>
            <small>Beispiel: "Optimiere diesen Text: {{text}}"</small>
        </div>

        <form id="add-prompt-form">
            <div class="wp-field">
                <label class="wp-label">Name der Aktion</label>
                <input type="text" id="new_prompt_name" class="wp-input" placeholder="z.B. Zusammenfassen" required>
            </div>

            <div class="wp-field">
                <label class="wp-label">Prompt Vorlage</label>
                <textarea id="new_prompt_text" rows="4" class="wp-input" placeholder="z.B. Kürze folgenden Text: {{text}}" required></textarea>
                <button type="button" class="button-secondary" onclick="insertPlaceholder()">🔗 {{text}} einfügen</button>
            </div>

            <div class="wp-footer">
                <button type="submit" class="button-primary" id="save-prompt-btn">💾 Prompt speichern</button>
            </div>
        </form>

        <h3 class="wp-section-title" style="margin-top:30px;">🌟 Beispielaktionen</h3>
        <ul style="list-style-type: disc; margin-left: 20px;">
            <?php foreach ($example_prompts as $example): ?>
                <li><strong><?php echo esc_html($example['name']); ?>:</strong> <code><?php echo esc_html($example['prompt']); ?></code></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="wp-grid" style="margin-top: 40px;">
        <?php if (!empty($custom_prompts)): ?>
            <?php foreach ($custom_prompts as $index => $prompt): ?>
                <div class="wp-card wp-prompt-card">
                    <h3><?php echo esc_html($prompt['name']); ?></h3>

                    <textarea id="prompt-text-<?php echo esc_attr($index); ?>" rows="5" class="wp-input" style="margin-top:10px;"><?php echo esc_textarea($prompt['prompt']); ?></textarea>

                    <div class="wp-actions" style="margin-top: 15px;">
                        <button type="button" class="button-primary" onclick="savePrompt(<?php echo esc_attr($index); ?>)">💾 Speichern</button>
                        <button type="button" class="button-secondary" onclick="optimizePrompt(<?php echo esc_attr($index); ?>)">✨ Optimieren</button>
                        <button type="button" class="button-secondary danger" onclick="deletePrompt(<?php echo esc_attr($index); ?>)">🗑 Löschen</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="wp-card">
                <p style="text-align:center;">⚡️ Noch keine Aktionen erstellt.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function insertPlaceholder() {
    const textarea = document.getElementById('new_prompt_text');
    if (!textarea) return;
    const start = textarea.selectionStart;
    textarea.value = textarea.value.substring(0, start) + '{{text}}' + textarea.value.substring(start);
    textarea.focus();
}

// Prompt speichern (Neu)
document.getElementById('add-prompt-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const name = document.getElementById('new_prompt_name').value.trim();
    const text = document.getElementById('new_prompt_text').value.trim();
    const button = document.getElementById('save-prompt-btn');
    showLoading(button);

    if (!name || !text) return;

    showLoading(button);

    fetch(ki_vars.ajaxurl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action: 'ki_save_prompt',
            name: name,
            prompt: text,
            nonce: ki_vars.nonce
        })
    })
    .then(res => res.json())
    .then(res => {
        hideLoading(button);
        if (res.success) {
            Swal.fire('Gespeichert', 'Dein neuer Prompt wurde gespeichert.', 'success').then(() => {
                location.reload();
            });
        }
    });
});

// Bestehenden Prompt speichern
function savePrompt(index) {
    const nameInput = document.getElementById('prompt-name-' + index);
    const promptTextarea = document.getElementById('prompt-text-' + index);

    if (!nameInput || !promptTextarea) {
        Swal.fire('Fehler', 'Eingabefelder konnten nicht gefunden werden.', 'error');
        return;
    }

    const name = nameInput.value.trim();
    const text = promptTextarea.value.trim();

    if (!name || !text) {
        Swal.fire('Fehler', 'Bitte alle Felder ausfüllen.', 'warning');
        return;
    }

    fetch(ki_vars.ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'ki_update_prompt',
            index: index,
            name: name,
            prompt: text,
            nonce: ki_vars.nonce
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            Swal.fire('Gespeichert', 'Dein Prompt wurde aktualisiert.', 'success');
        } else {
            Swal.fire('Fehler', 'Aktualisierung fehlgeschlagen.', 'error');
        }
    })
    .catch(() => {
        Swal.fire('Fehler', 'Verbindungsproblem.', 'error');
    });
}


// Bestehenden Prompt optimieren
function optimizePrompt(index) {
    const text = document.getElementById('prompt-text-' + index).value.trim();
    if (!text) return;

    fetch(ki_vars.ajaxurl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action: 'ki_optimize_prompt',
            prompt: text,
            nonce: ki_vars.nonce
        })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success && res.data.optimized) {
            document.getElementById('prompt-text-' + index).value = res.data.optimized;
            Swal.fire('✨ Optimiert', 'Dein Prompt wurde von der KI verbessert.', 'success');
        } else {
            Swal.fire('Fehler', 'Optimierung fehlgeschlagen.', 'error');
        }
    });
}

// Prompt löschen
function deletePrompt(index) {
    Swal.fire({
        title: 'Bist du sicher?',
        text: "Du kannst das später nicht rückgängig machen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#00ffd5',
        cancelButtonColor: '#ff4d4d',
        confirmButtonText: 'Ja, löschen'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(ki_vars.ajaxurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    action: 'ki_delete_prompt',
                    index: index,
                    nonce: ki_vars.nonce
                })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    Swal.fire('Gelöscht!', 'Prompt wurde entfernt.', 'success').then(() => {
                        location.reload();
                    });
                }
            });
        }
    });
}

// Ladeanimation
function showLoading(button) {
    button.disabled = true;
    const spinner = document.createElement('span');
    spinner.classList.add('spinner', 'is-active');
    button.appendChild(spinner);
}

function hideLoading(button) {
    button.disabled = false;
    const spinner = button.querySelector('.spinner');
    if (spinner) spinner.remove();
}
</script>
