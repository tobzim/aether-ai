jQuery(document).ready(function($) {
    const root  = aetherAiBlogGen.root;
    const nonce = aetherAiBlogGen.nonce;

    // DataTables initialisieren
    function initTable() {
        if ($.fn.DataTable) {
            $('#bloggenerator-table').DataTable({
                pageLength: 10,
                lengthMenu: [[10,20,30],[10,20,30]]
            });
        }
    }

    // Drafts laden mit SweetAlert2 Loading
    function loadDrafts() {
        Swal.fire({
            title: 'Lade Entwürfe...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(root + 'aether-ai/v1/blog/status', {
            headers: { 'X-WP-Nonce': nonce }
        })
        .then(res => res.json())
        .then(posts => {
            Swal.close();
            const $c = $('#bloggenerator-app').empty();
            const $table = $(
                '<table id="bloggenerator-table" class="wp-list-table widefat striped">' +
                '<thead><tr>' +
                '<th>ID</th><th>Titel</th><th>Kategorie</th><th>Status</th>' +
                '</tr></thead><tbody></tbody></table>'
            );
            posts.forEach(p => {
                // Link zum Editieren erstellen
                const editUrl = window.location.origin + '/wp-admin/post.php?post=' + p.id + '&action=edit';
                $table.find('tbody').append(
                    '<tr>' +
                    '<td>' + p.id + '</td>' +
                    '<td><a href="' + editUrl + '" target="_blank" rel="noopener noreferrer">' + p.title + '</a></td>' +
                    '<td>' + p.category + '</td>' +
                    '<td>' + p.status + '</td>' +
                    '</tr>'
                );
            });
            $c.append($table);
            initTable();
        })
        .catch(err => {
            Swal.close();
            console.error(err);
            Swal.fire('Fehler', 'Entwürfe konnten nicht geladen werden.', 'error');
        });
    }

    // Bulk-Generierung
    $('#bg-generate').off('click').on('click', function(e) {
        e.preventDefault();

        const topic    = $('#bg-topic').val().trim();
        const count    = parseInt($('#bg-count').val(), 10);
        const category = $('#bg-category').val();

        if (!topic) {
            return Swal.fire('Hinweis', 'Bitte ein Thema angeben.', 'warning');
        }

        Swal.fire({
            title: 'Generiere Beiträge…',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(root + 'aether-ai/v1/blog/posts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': nonce
            },
            body: JSON.stringify({ topic, count, category })
        })
        .then(res => res.json())
        .then(data => {
            Swal.close();
            Swal.fire('Fertig', 'Es wurden ' + data.length + ' Entwürfe erstellt.', 'success');
            loadDrafts();
        })
        .catch(err => {
            Swal.close();
            console.error(err);
            Swal.fire('Fehler', 'Beim Generieren ist ein Fehler aufgetreten.', 'error');
        });
    });

    // Initial
    loadDrafts();
});