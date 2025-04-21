
document.addEventListener('DOMContentLoaded', function () {

    

    /** CLASSIC EDITOR INTEGRATION **/
    if (typeof tinymce !== 'undefined' && tinymce.PluginManager) {
        tinymce.PluginManager.add('ki_plugin', function (editor) {

            const prompts = ki_vars.custom_prompts || [];

            const menuItems = prompts.length
                ? prompts.map(item => ({
                    text: item.name,
                    onclick: () => openKiModalClassic(editor, item.prompt)
                }))
                : [{
                    text: 'Keine Aktionen definiert',
                    onclick: () => alert('Bitte füge Aktionen im Adminbereich hinzu.')
                }];

            editor.addButton('ki_button', {
                type: 'menubutton',
                text: 'Aether AI Optimize',
                icon: false,
                menu: menuItems
            });
        });
    }

});

/** SHARED FUNCTION **/
function openKiModalClassic(editor, promptText) {
    const selectedText = editor.selection.getContent({ format: 'text' });
    if (!selectedText) {
        alert('Bitte markiere zuerst einen Text.');
        return;
    }

    openKiRequest(selectedText, promptText, (optimizedText) => {
        editor.selection.setContent(optimizedText);
    });
}

function openKiRequest(text, prompt, callback) {
    const modal     = document.getElementById('ki-modal');
    const resultBox = document.getElementById('ki-result');
    const insertBtn = document.getElementById('ki-insert');
    const cancelBtn = document.getElementById('ki-cancel');
    const spinner   = document.getElementById('ki-spinner');

    if (!modal || !resultBox || !insertBtn || !cancelBtn) {
        alert('Modal konnte nicht geladen werden.');
        return;
    }

    modal.classList.add('open');
    resultBox.value = 'Lade KI-Ergebnis...';
    if (spinner) spinner.style.visibility = 'visible';

    fetch(ki_vars.ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'ki_optimize',
            nonce: ki_vars.nonce,
            text: text,
            prompt: prompt
        })
    })
    .then(response => response.json())
    .then(result => {
        if (!result.success) {
            resultBox.value = 'Fehler: ' + (result.data || 'Unbekannter Fehler');
            return;
        }

        resultBox.value = result.data.optimized || 'Keine Antwort erhalten';
        insertBtn.onclick = () => {
            callback(result.data.optimized);
            modal.classList.remove('open');
        };
    })
    .catch(() => {
        resultBox.value = '❌ Fehler bei der KI-Anfrage.';
    })
    .finally(() => {
        if (spinner) spinner.style.visibility = 'hidden';
    });

    cancelBtn.onclick = () => {
        modal.classList.remove('open');
    };
}
