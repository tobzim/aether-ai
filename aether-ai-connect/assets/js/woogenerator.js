jQuery( document ).ready( function( $ ) {
    const root      = aetherAiWoogenerator.root;
    const nonce     = aetherAiWoogenerator.nonce;
    const adminBase = window.location.origin + '/wp-admin/';

    function truncateText( text, max = 200 ) {
        if ( typeof text !== 'string' ) return '';
        return text.length > max
            ? text.substring( 0, max ) + '…'
            : text;
    }

    function loadProducts() {
        Swal.fire({
            title: 'Produkte laden…',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch( root + 'aether-ai/v1/products', { headers: { 'X-WP-Nonce': nonce } } )
        .then( res => res.json() )
        .then( products => {
            Swal.close();
            renderTable( products );
            initDataTable();
        } )
        .catch( err => {
            console.error( err );
            Swal.fire({
                icon: 'error',
                title: 'Fehler',
                text: 'Produkte konnten nicht geladen werden.'
            });
        } );
    }

    function renderTable( products ) {
        const $c = $( '#woogenerator-app' ).empty();
        // Tabelle mit ID versehen
        const $table = $( '<table id="woogenerator-table" class="wp-list-table widefat fixed striped"></table>' );
        const $thead = $( `
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>ID</th>
                    <th>Titel</th>
                    <th>Beschreibung</th>
                    <th>Kurzbeschreibung</th>
                </tr>
            </thead>` );
        const $tbody = $( '<tbody></tbody>' );

        products.forEach( p => {
            const editUrl   = adminBase + 'post.php?post=' + p.id + '&action=edit';
            const desc      = truncateText( p.description, 200 );
            const shortDesc = truncateText( p.short_description, 140 );

            const $row = $( `
                <tr>
                    <td><input type="checkbox" class="woo-select" value="${ p.id }"></td>
                    <td>${ p.id }</td>
                    <td>${ p.title }</td>
                    <td>${ desc }<br><a href="${ editUrl }" target="_blank">Ansehen</a></td>
                    <td>${ shortDesc }<br><a href="${ editUrl }" target="_blank">Ansehen</a></td>
                </tr>
            ` );
            $tbody.append( $row );
        } );

        $table.append( $thead, $tbody );
        $c.append( $table );
        $c.append( '<button id="woogenerate-button" class="button button-primary">Generate</button>' );

        // Select all
        $( '#select-all' ).on( 'change', function() {
            $( '.woo-select' ).prop( 'checked', this.checked );
        } );
        // Bulk-Generate
        initGenerate();
    }

    function initDataTable() {
        // Wenn schon initialisiert, zerstören
        if ( $.fn.DataTable.isDataTable( '#woogenerator-table' ) ) {
            $( '#woogenerator-table' ).DataTable().destroy();
        }
        // Neu initialisieren
        $( '#woogenerator-table' ).DataTable({
            lengthMenu: [ [10, 20, 30], [10, 20, 30] ],
            pageLength: 10,
            searching:  true,
            autoWidth:  false,
            columnDefs: [
                { orderable: false, targets: 0 } // kein Sortieren auf Checkbox-Spalte
            ],
            language: {
                search:    "Suche:",
                lengthMenu:"Zeige _MENU_ Einträge",
                paginate: {
                    first:    "Erste",
                    last:     "Letzte",
                    next:     "›",
                    previous: "‹"
                },
                info:       "Zeige _START_ bis _END_ von _TOTAL_ Produkten",
                infoFiltered: "(gefiltert von _MAX_ Gesamt)",
                zeroRecords:   "Keine Produkte gefunden"
            }
        });
    }

    function initGenerate() {
        $( '#woogenerate-button' ).off('click').on( 'click', function() {
            const ids = $( '.woo-select:checked' ).map(function() { return this.value; }).get();
            if ( ! ids.length ) {
                Swal.fire({ icon:'warning', title:'Keine Auswahl', text:'Bitte mindestens ein Produkt wählen.' });
                return;
            }
            Swal.fire({ title:'Generiere…', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });

            fetch( root + 'aether-ai/v1/products/generate', {
                method: 'POST',
                headers:{ 'Content-Type':'application/json','X-WP-Nonce':nonce },
                body: JSON.stringify({ ids: ids })
            } )
            .then(res=>res.json())
            .then(data=>{
                Swal.fire({ icon:'success', title:'Fertig', text:'Updated '+data.updated+' Produkte.', confirmButtonText:'OK' })
                    .then(()=> loadProducts() );
            })
            .catch(err=>{
                console.error(err);
                Swal.fire({ icon:'error', title:'Fehler', text:'Beim Generieren ist ein Fehler aufgetreten.' });
            });
        });
    }

    // initial
    loadProducts();
} );
