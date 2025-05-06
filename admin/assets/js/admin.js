document.addEventListener('DOMContentLoaded', function () {

    // Ladeanimation für Button starten
function showLoading(button) {
    button.disabled = true;
    const spinner = document.createElement('span');
    spinner.className = 'wp-spinner';
    spinner.setAttribute('id', 'spinner-' + button.id);
    button.appendChild(spinner);
}

// Ladeanimation stoppen
function hideLoading(button) {
    button.disabled = false;
    const spinner = button.querySelector('.wp-spinner');
    if (spinner) spinner.remove();
}


    /**
     * Insert {{text}} placeholder in Textarea
     */
    const insertBtn = document.getElementById('insert-placeholder');
    if (insertBtn) {
        insertBtn.addEventListener('click', function () {
            const textarea = document.getElementById('new_prompt_textarea');
            if (!textarea) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const value = textarea.value;

            textarea.value = value.substring(0, start) + '{{text}}' + value.substring(end);
            textarea.focus();
            textarea.selectionEnd = start + 8;
        });
    }

    /**
     * Speichern eines neuen Prompts via Ajax
     */
    const promptForm = document.getElementById('ki-prompt-form');
    if (promptForm) {
        promptForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const name = promptForm.querySelector('[name="new_prompt_name"]').value.trim();
            const prompt = promptForm.querySelector('[name="new_prompt_text"]').value.trim();

            if (!name || !prompt) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Fehler',
                    text: 'Bitte fülle alle Felder aus!',
                    confirmButtonColor: '#00ffd5',
                    background: '#1c1c1c',
                    color: '#ccc'
                });
                return;
            }

            fetch(ki_vars.ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ki_save_prompt',
                    nonce: ki_vars.nonce,
                    name: name,
                    prompt: prompt
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Gespeichert!',
                        text: 'Dein neuer Prompt wurde erfolgreich gespeichert.',
                        confirmButtonColor: '#00ffd5',
                        background: '#1c1c1c',
                        color: '#ccc'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Fehler',
                        text: 'Speichern fehlgeschlagen. Versuche es erneut.',
                        confirmButtonColor: '#00ffd5',
                        background: '#1c1c1c',
                        color: '#ccc'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Fehler',
                    text: 'Es ist ein Verbindungsfehler aufgetreten.',
                    confirmButtonColor: '#00ffd5',
                    background: '#1c1c1c',
                    color: '#ccc'
                });
            });
        });
    }

    /**
     * Optimieren eines bestehenden Prompts via Ajax
     */
    document.querySelectorAll('.optimize-prompt-btn').forEach(button => {
        button.addEventListener('click', function () {
            const index = this.getAttribute('data-index');
            const promptTextArea = document.getElementById('prompt-text-' + index);
            const originalPrompt = promptTextArea.value.trim();

            if (!originalPrompt) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Hinweis',
                    text: 'Bitte gib zuerst einen Prompttext ein.',
                    confirmButtonColor: '#00ffd5',
                    background: '#1c1c1c',
                    color: '#ccc'
                });
                return;
            }

            // Anfrage an die KI
            fetch(ki_vars.ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ki_optimize',
                    nonce: ki_vars.nonce,
                    text: originalPrompt,
                    prompt: 'Formuliere diesen Text klarer und freundlicher um: {{text}}'
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success && result.data.optimized) {
                    promptTextArea.value = result.data.optimized;
                    Swal.fire({
                        icon: 'success',
                        title: 'Optimiert!',
                        text: 'Der Prompt wurde erfolgreich verbessert.',
                        confirmButtonColor: '#00ffd5',
                        background: '#1c1c1c',
                        color: '#ccc'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Fehler',
                        text: 'Die Optimierung ist fehlgeschlagen.',
                        confirmButtonColor: '#00ffd5',
                        background: '#1c1c1c',
                        color: '#ccc'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Fehler',
                    text: 'Es ist ein Verbindungsfehler aufgetreten.',
                    confirmButtonColor: '#00ffd5',
                    background: '#1c1c1c',
                    color: '#ccc'
                });
            });
        });
    });

});
