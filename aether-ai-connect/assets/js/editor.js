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

    openKiRequest(selectedText, promptText, (optimizedHtml) => {
        // Füge sauberes HTML in den Editor ein
        if (window.tinymce && tinymce.activeEditor) {
            tinymce.activeEditor.execCommand('mceInsertContent', false, optimizedHtml);
        } else {
            // Fallback: Textarea-Modus
            const textarea = document.getElementById('content');
            textarea.value += optimizedHtml;
        }
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
    resultBox.style.display = 'none';
    spinner.style.display = 'block';

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
        spinner.style.display = 'none';
        if (!result.success) {
            resultBox.value = 'Fehler: ' + (result.data || 'Unbekannter Fehler');
            resultBox.style.display = 'block';
            return;
        }

        // Hole das optimierte HTML
        let html = result.data.optimized || result.data.optimized_text || '';
        // 1) ```html```-Fences entfernen
        html = html.replace(/```html[\s\S]*?```/gi, '');
        // 2) Nur erlaubte Tags lassen (h1-h3,p,ul,ol,li,strong,em)
        html = html.replace(/<(\/?)(?!\/?(?:h1|h2|h3|p|ul|ol|li|strong|em)\b)[^>]*>/gi, '');

        resultBox.value = html;
        resultBox.style.display = 'block';
        insertBtn.style.display = 'inline-block';

        insertBtn.onclick = () => {
            callback(html);
            resultBox.style.display = 'none';
            insertBtn.style.display = 'none';
            modal.classList.remove('open');
        };
    })
    .catch(() => {
        spinner.style.display = 'none';
        resultBox.value = '❌ Fehler bei der KI-Anfrage.';
        resultBox.style.display = 'block';
    });

    cancelBtn.onclick = () => {
        spinner.style.display = 'none';
        resultBox.style.display = 'none';
        insertBtn.style.display = 'none';
        modal.classList.remove('open');
    };
}
