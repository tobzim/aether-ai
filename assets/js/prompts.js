/**
 * assets/js/prompts.js
 */
(function(){

    // Lade-Spinner in Buttons
    function showLoading(btn) {
      btn.disabled = true;
      const sp = document.createElement('span');
      sp.className = 'wp-spinner';
      btn.appendChild(sp);
    }
    function hideLoading(btn) {
      btn.disabled = false;
      const sp = btn.querySelector('.wp-spinner');
      if (sp) sp.remove();
    }
  
    // Platzhalter in New-Prompt-Form einfügen
    window.insertPlaceholder = function() {
      const ta = document.getElementById('new-prompt-text');
      if (!ta) return;
      const pos = ta.selectionStart;
      ta.value = ta.value.slice(0,pos) + '{{text}}' + ta.value.slice(pos);
      ta.focus();
      ta.selectionStart = ta.selectionEnd = pos + 8;
    };
  
    // Neuen Prompt speichern (Form mit id="add-prompt-form")
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('add-prompt-form');
      const btn  = document.getElementById('save-prompt-btn');
      if (form && btn) {
        form.addEventListener('submit', e => {
          e.preventDefault();
          const name   = document.getElementById('new-prompt-name').value.trim();
          const prompt = document.getElementById('new-prompt-text').value.trim();
          if (!name || !prompt) {
            Swal.fire('Fehler','Bitte fülle alle Felder aus!','warning');
            return;
          }
          showLoading(btn);
          fetch(ki_vars.ajaxurl, {
            method: 'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
              action: 'ki_save_prompt',
              nonce:  ki_vars.nonce,
              name, prompt
            })
          })
          .then(r => r.json())
          .then(res => {
            hideLoading(btn);
            if (res.success) {
              Swal.fire('Gespeichert','Prompt erstellt.','success')
                .then(() => location.reload());
            } else {
              Swal.fire('Fehler','Speichern fehlgeschlagen.','error');
            }
          })
          .catch(() => {
            hideLoading(btn);
            Swal.fire('Fehler','Netzwerkfehler.','error');
          });
        });
      }
    });
  
    // Speichern eines existierenden Prompts
    window.savePrompt = function(index) {
      const btn   = event.currentTarget;
      const name  = document.getElementById(`prompt-name-${index}`).value.trim();
      const text  = document.getElementById(`prompt-text-${index}`).value.trim();
      if (!name || !text) {
        Swal.fire('Fehler','Bitte alle Felder ausfüllen.','warning');
        return;
      }
      showLoading(btn);
      fetch(ki_vars.ajaxurl, {
        method: 'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
          action: 'ki_update_prompt',
          nonce:  ki_vars.nonce,
          index, name, prompt: text
        })
      })
      .then(r => r.json())
      .then(res => {
        hideLoading(btn);
        if (res.success) {
          Swal.fire('Gespeichert','Prompt aktualisiert.','success');
        } else {
          Swal.fire('Fehler','Aktualisierung fehlgeschlagen.','error');
        }
      })
      .catch(() => {
        hideLoading(btn);
        Swal.fire('Fehler','Netzwerkfehler.','error');
      });
    };
  
    // Prompt optimieren
    window.optimizePrompt = function(index) {
      const btn = event.currentTarget;
      const ta  = document.getElementById(`prompt-text-${index}`);
      const txt = ta.value.trim();
      if (!txt) {
        Swal.fire('Hinweis','Prompt-Text fehlt.','warning');
        return;
      }
      showLoading(btn);
      fetch(ki_vars.ajaxurl, {
        method: 'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
          action: 'ki_optimize_prompt',
          nonce:  ki_vars.nonce,
          prompt: txt
        })
      })
      .then(r => r.json())
      .then(res => {
        hideLoading(btn);
        if (res.success && res.data.optimized) {
          ta.value = res.data.optimized;
          Swal.fire('✨ Optimiert','Dein Prompt wurde verbessert.','success');
        } else {
          Swal.fire('Fehler','Optimierung fehlgeschlagen.','error');
        }
      })
      .catch(() => {
        hideLoading(btn);
        Swal.fire('Fehler','Netzwerkfehler.','error');
      });
    };
  
    // Prompt löschen
    window.deletePrompt = function(index) {
      const btn = event.currentTarget;
      Swal.fire({
        title: 'Löschen bestätigen?',
        text: 'Kann nicht rückgängig gemacht werden.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#00ffd5',
        cancelButtonColor: '#ff4d4d',
        confirmButtonText: 'Ja, löschen'
      }).then(result => {
        if (!result.isConfirmed) return;
        showLoading(btn);
        fetch(ki_vars.ajaxurl, {
          method: 'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body: new URLSearchParams({
            action: 'ki_delete_prompt',
            nonce:  ki_vars.nonce,
            index
          })
        })
        .then(r => r.json())
        .then(res => {
          hideLoading(btn);
          if (res.success) {
            Swal.fire('Gelöscht!','Prompt entfernt.','success')
              .then(() => location.reload());
          } else {
            Swal.fire('Fehler','Löschen fehlgeschlagen.','error');
          }
        })
        .catch(() => {
          hideLoading(btn);
          Swal.fire('Fehler','Netzwerkfehler.','error');
        });
      });
    };
  
  })();
  