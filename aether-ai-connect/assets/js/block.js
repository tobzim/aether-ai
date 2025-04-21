( function( blocks, i18n, element, blockEditor, components, apiFetch ) {
    var el           = element.createElement;
    var RichText     = blockEditor.RichText;
    var SelectControl= components.SelectControl;
    var Button       = components.Button;
    var Spinner      = components.Spinner;
    var useState     = element.useState;
    var useEffect    = element.useEffect;

    blocks.registerBlockType( 'aether-ai/connect-block', {
        title: i18n.__( 'Aether AI Optimize', 'aether-ai' ),
        icon: 'edit',
        category: 'widgets',
        attributes: {
            content:  { type: 'string', source: 'html', selector: 'p' },
            promptId: { type: 'string', default: '' },
        },
        edit: function( props ) {
            var content    = props.attributes.content;
            var promptId   = props.attributes.promptId;
            var setAttrs   = props.setAttributes;

            var [ prompts, setPrompts ]   = useState( [] );
            var [ isLoading, setLoading ] = useState( false );

            // beim Mount die Prompts holen
            useEffect( function() {
                apiFetch( { path: '/aether-ai/v1/prompts' } )
                .then( setPrompts )
                .catch( console.error );
            }, [] );

            function optimize() {
                setLoading( true );
                apiFetch( {
                    path:   '/aether-ai/v1/optimize',
                    method: 'POST',
                    data:   { text: content, prompt_id: promptId }
                } ).then( function( res ) {
                    setAttrs( { content: res.optimized_text } );
                } ).catch( function( err ) {
                    console.error( err );
                } ).finally( function() {
                    setLoading( false );
                } );
            }

            // Options für SelectControl
            var options = [ { label: i18n.__( 'Default', 'aether-ai' ), value: '' } ]
                .concat( prompts.map( function( p ) {
                    return { label: p.name, value: p.id };
                } ) );

            return el(
                'div',
                { className: 'aether-ai-block' },
                el( SelectControl, {
                    label:       i18n.__( 'Prompt auswählen', 'aether-ai' ),
                    value:       promptId,
                    options:     options,
                    onChange:    function( v ) { setAttrs( { promptId: v } ); },
                    help:        i18n.__( 'Wähle einen vordefinierten Prompt.', 'aether-ai' )
                } ),
                el( RichText, {
                    tagName:    'p',
                    value:      content,
                    onChange:   function( v ) { setAttrs( { content: v } ); },
                    placeholder: i18n.__( 'Text eingeben…', 'aether-ai' )
                } ),
                isLoading
                    ? el( Spinner )
                    : el(
                        Button,
                        { isPrimary: true, onClick: optimize, disabled: ! content },
                        i18n.__( 'Optimize', 'aether-ai' )
                      )
            );
        },
        save: function( props ) {
            return el( RichText.Content, {
                tagName: 'p',
                value:   props.attributes.content
            } );
        }
    } );
} )(
    window.wp.blocks,
    window.wp.i18n,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.apiFetch
);
