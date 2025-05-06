/**
 * ki-classic.js
 * – Legt Default ki_vars an, bevor es weitergeht
 * – Registriert TinyMCE-Plugin
 * – Stellt openKiModalClassic & openKiRequest bereit
 */
(function(){
    // 0) Default-Objekt anlegen, falls localize_script fehlt
    window.ki_vars = window.ki_vars || {};
  
    // 1) TinyMCE-Plugin registrieren
    if (window.tinymce && window.tinymce.PluginManager) {
      tinymce.PluginManager.add('ki_plugin', function(editor) {
        const prompts = window.ki_vars.custom_prompts || [];
        const menuItems = prompts.length
          ? prompts.map(item => ({
              text: item.name,
              onclick: () => openKiModalClassic(editor, item.prompt)
            }))
          : [{
              text: 'Keine Aktionen definiert',
              onclick: () => alert('Bitte definiere Aktionen im Adminbereich.')
            }];
  
        editor.addButton('ki_button', {
          type:    'menubutton',
          text:    'wp AI Optimize',
          icon:    false,
          menu:    menuItems
        });
      });
    } else {
      console.warn('TinyMCE nicht gefunden – Plugin ki_plugin nicht registriert.');
    }
  
    // 2) Funktion für den Button-Klick
    window.openKiModalClassic = function(editor, promptText) {
      const selected = editor.selection.getContent({ format: 'text' });
      if (!selected) {
        alert('Bitte markiere zuerst einen Text.');
        return;
      }
      openKiRequest(selected, promptText, html => {
        editor.execCommand('mceInsertContent', false, html);
      });
    };
  
    // 3) Modal-/AJAX-Logik
    window.openKiRequest = function(text, prompt, callback) {
      const modal      = document.getElementById('ki-modal');
      const loader     = document.getElementById('ki-loading');
      const spinner    = document.getElementById('ki-spinner');
      const resultBox  = document.getElementById('ki-result'); // jetzt <div>
      const insertBtn  = document.getElementById('ki-insert');
      const cancelBtn  = document.getElementById('ki-cancel');
      const closeBtn   = document.getElementById('ki-modal-close');
      const providerEl = document.getElementById('ki-provider-name');
      const modelEl    = document.getElementById('ki-selected-model');
  
      if (!modal) {
        console.error('KI-Modal nicht gefunden');
        return;
      }
  
      // Anbieter & Modell zeigen
      if (providerEl) providerEl.textContent = ki_vars.provider || 'Unbekannt';
      if (modelEl)    modelEl.textContent    = ki_vars.model    || 'Unbekannt';
  
      // Reset & öffnen
      modal.classList.add('open');
      loader.style.display      = 'flex';
      spinner.style.display     = 'block';
      resultBox.style.display   = 'none';
      insertBtn.style.display   = 'none';
  
      // AJAX-Call
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
                  // 1) Markdown-Fence ```html weg
                  .replace(/```html\s*/i, '')
                  // 2) abschließende ``` weg
                  .replace(/\s*```$/i, '')
                  // 3) kompletten Dokumenten-Wrapper (DOCTYPE…<body>) entfernen
                  .replace(/<!DOCTYPE[\s\S]*?<body[^>]*>/i, '')
                  // 4) alles ab </body> bis Ende weg
                  .replace(/<\/body>[\s\S]*$/i, '');
              }
              resultBox.innerHTML = html;
              resultBox.style.display = 'block';              
          insertBtn.style.display = 'inline-flex';
          insertBtn.onclick       = () => {
            callback(html);
            modal.classList.remove('open');
          };
        })
        .catch(err => {
          console.error('KI-Anfrage fehlgeschlagen', err);
          loader.style.display      = 'none';
          resultBox.value           = '❌ Fehler bei der KI-Anfrage.';
          resultBox.style.display   = 'block';
          insertBtn.style.display   = 'inline-flex';
        });
  
      // Schließen
      [cancelBtn, closeBtn].forEach(btn => {
        if (btn) btn.onclick = () => modal.classList.remove('open');
      });
    };
  })();
  