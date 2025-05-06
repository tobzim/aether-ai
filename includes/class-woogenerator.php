<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KI_WooGenerator {

    public static function register_routes() {
        add_action( 'rest_api_init', function() {
            register_rest_route(
                'wp-ai/v1',
                '/products',
                [
                    'methods'             => 'GET',
                    'callback'            => [ __CLASS__, 'get_products' ],
                    'permission_callback' => fn() => current_user_can( 'manage_woocommerce' ),
                ]
            );
            register_rest_route(
                'wp-ai/v1',
                '/products/generate',
                [
                    'methods'             => 'POST',
                    'callback'            => [ __CLASS__, 'generate_bulk' ],
                    'permission_callback' => fn() => current_user_can( 'manage_woocommerce' ),
                ]
            );
        } );
    }

    public static function get_products( WP_REST_Request $request ) {
        $products = wc_get_products([ 'status' => 'publish', 'limit' => -1 ]);
        $out = [];
        foreach ( $products as $p ) {
            $out[] = [
                'id'                => $p->get_id(),
                'title'             => $p->get_name(),
                'description'       => $p->get_description(),
                'short_description' => $p->get_short_description(),
                'price'             => $p->get_price(),
            ];
        }
        return rest_ensure_response( $out );
    }

    public static function generate_bulk( WP_REST_Request $request ) {
        $ids = $request->get_param( 'ids' );
        if ( ! is_array( $ids ) ) {
            return new WP_Error( 'invalid_ids', 'Ungültiges IDs-Format', [ 'status' => 400 ] );
        }

        $api = new KI_API();
        $count = 0;

        // Plain-HTML Prompt (nur Fragment, keine <html>/<body>)
        $prompt_html = <<<PROMPT
Erstelle nur das reine HTML-Fragment (ohne <html>, <head> oder <body>) einer ausführlichen Produktbeschreibung mit vielen Details für einen Artikel mit dem Titel „{{text}}“. Nutze Absätze (<p>), Listen (<ul>, <li>) und Überschriften (<h2>, <h3>) – aber liefere **nur** den Fragment-Code.
PROMPT;

        // Kurzbeschreibung max. 140 Zeichen
        $prompt_short = 'Erstelle eine präzise Kurzbeschreibung (maximal 140 Zeichen) für ein Produkt mit dem Titel: "{{text}}".';

        // Preisvorschlag
        $prompt_price = 'Schlage einen angemessenen Preis in Euro (nur Zahl oder Zahl mit €-Symbol) für ein Produkt mit dem Titel: "{{text}}" vor.';

        foreach ( $ids as $id ) {
            $p = wc_get_product( intval( $id ) );
            if ( ! $p ) {
                continue;
            }

            $title = $p->get_name();

            // 1) Ausführliche HTML-Beschreibung
            try {
                $html_desc = $api->call( $title, $prompt_html );
                $p->set_description( wp_kses_post( $html_desc ) );
            } catch ( Exception $e ) {
                error_log( 'WooGen HTML-Desc Error for ' . $title . ': ' . $e->getMessage() );
            }

            // 2) Kurzbeschreibung
            try {
                $short_desc = $api->call( $title, $prompt_short );
                if ( mb_strlen( $short_desc ) > 140 ) {
                    $short_desc = mb_substr( $short_desc, 0, 137 ) . '…';
                }
                $p->set_short_description( wp_kses_post( $short_desc ) );
            } catch ( Exception $e ) {
                error_log( 'WooGen Short-Desc Error for ' . $title . ': ' . $e->getMessage() );
            }

            // 3) Preisvorschlag
            try {
                $price_text = $api->call( $title, $prompt_price );
                if ( preg_match( '/([\d\.,]+)/', $price_text, $m ) ) {
                    $num = str_replace( [ ',', ' ' ], [ '.', '' ], $m[1] );
                    $price_val = floatval( $num );
                    if ( $price_val > 0 ) {
                        $p->set_price( $price_val );
                    }
                }
            } catch ( Exception $e ) {
                error_log( 'WooGen Price Error for ' . $title . ': ' . $e->getMessage() );
            }

            $p->save();
            $count++;
        }

        return rest_ensure_response( [ 'updated' => $count ] );
    }
}
