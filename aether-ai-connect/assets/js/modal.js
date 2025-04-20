function openKiModalClassic(editor, promptText) {
    const selectedText = editor.selection.getContent({ format: 'text' });
    if (!selectedText) {
        alert('Bitte markiere zuerst einen Text.');
        return;
    }

    const modal      = document.getElementById('ki-modal');
    const spinner    = document.getElementById('ki-loading');
    const resultArea = document.getElementById('ki-result');
    const insertBtn  = document.getElementById('ki-insert');
    const cancelBtn  = document.getElementById('ki-cancel');

    if (!modal || !spinner || !resultArea || !insertBtn || !cancelBtn) {
        alert('⚠️ KI-Modal konnte nicht geladen werden.');
        return;
    }

    // Reset Modal Ansicht
    spinner.style.display = 'block';
    resultArea.style.display = 'none';
    insertBtn.style.display = 'none';
    cancelBtn.style.display = 'inline-block';
    resultArea.value = '';
    modal.style.display = 'flex';

    // Prompt Platzhalter ersetzen
    const prompt = promptText.replace('{{text}}', selectedText);

    // API-Aufruf
    fetch(ki_vars.ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'ki_optimize',
            nonce: ki_vars.nonce,
            text: selectedText,
            prompt: prompt
        })
    })
    .then(response => response.json())
    .then(result => {
        spinner.style.display = 'none';
        resultArea.style.display = 'block';
        insertBtn.style.display = 'inline-block';

        if (!result.success || !result.data?.optimized) {
            resultArea.value = '⚠️ Keine Antwort erhalten.';
            return;
        }

        resultArea.value = result.data.optimized;
    })
    .catch(() => {
        spinner.style.display = 'none';
        resultArea.style.display = 'block';
        resultArea.value = '❌ Fehler bei der Anfrage.';
    });

    // Übernehmen-Button
    insertBtn.onclick = () => {
        editor.selection.setContent(resultArea.value);
        modal.style.display = 'none';
    };

    // Abbrechen
    cancelBtn.onclick = () => {
        modal.style.display = 'none';
    };
}
