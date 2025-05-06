/**
 * Öffnet das Modal & führt die KI-Anfrage aus
 */
function openKiModalClassic(editor, promptText) {
    const selected = editor.selection.getContent({ format: 'text' });
    if (!selected) {
      alert('Bitte markiere zuerst einen Text.');
      return;
    }
    openKiRequest(selected, promptText, optimizedHtml => {
      editor.execCommand('mceInsertContent', false, optimizedHtml);
    });
  }
  
  function openKiRequest(text, prompt, callback) {
    const modal      = document.getElementById('ki-modal');
    const loader     = document.getElementById('ki-loading');
    const spinner    = document.getElementById('ki-spinner');
    const resultBox  = document.getElementById('ki-result');
    const insertBtn  = document.getElementById('ki-insert');
    const cancelBtn  = document.getElementById('ki-cancel');
    const closeBtn   = document.getElementById('ki-modal-close');
    const providerEl = document.getElementById('ki-provider-name');
    const modelEl    = document.getElementById('ki-selected-model');
  
    if (!modal) {
      console.error('KI-Modal nicht gefunden');
      return;
    }
  
    // Anbieter & Modell setzen
    if (providerEl) providerEl.textContent = ki_vars.provider || 'Unbekannt';
    if (modelEl)    modelEl.textContent    = ki_vars.model    || 'Unbekannt';
  
    // Reset & Anzeigen
    modal.classList.add('open');
    loader.style.display      = 'flex';
    spinner.style.display     = 'block';
    resultBox.style.display   = 'none';
    insertBtn.style.display   = 'none';
  
    fetch(ki_vars.ajaxurl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'ki_optimize',
        nonce:  ki_vars.nonce,
        text:   text,
        prompt: prompt
      })
    })
      .then(res => res.json())
      .then(result => {
        loader.style.display = 'none';
  
        let html = result.success
          ? (result.data.optimized || result.data.optimized_text || '')
          : `❌ Fehler: ${result.data || 'Unbekannt'}`;
  
        if (result.success) {
          html = html
            .replace(/```html[\s\S]*?```/gi, '')
            .replace(/<(\/?)(?!\/?(?:h1|h2|h3|p|ul|ol|li|strong|em)\b)[^>]*>/gi, '');
        }
  
        resultBox.value         = html;
        resultBox.style.display = 'block';
        insertBtn.style.display = 'inline-flex';
        insertBtn.onclick       = () => { callback(html); modal.classList.remove('open'); };
      })
      .catch(err => {
        console.error('KI-Anfrage fehlgeschlagen', err);
        loader.style.display      = 'none';
        resultBox.value           = '❌ Fehler bei der KI-Anfrage.';
        resultBox.style.display   = 'block';
        insertBtn.style.display   = 'inline-flex';
      });
  
    // Schließen-Handler
    [cancelBtn, closeBtn].forEach(btn => {
      if (btn) btn.onclick = () => modal.classList.remove('open');
    });
  }
  