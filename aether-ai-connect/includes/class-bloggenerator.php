<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KI_BlogGenerator {

    public static function register_routes() {
        add_action( 'rest_api_init', function() {
            register_rest_route( 'aether-ai/v1', '/blog/posts', [
                'methods'  => 'POST',
                'callback' => [ __CLASS__, 'generate_posts' ],
                'permission_callback' => fn() => current_user_can( 'edit_posts' ),
            ] );
            register_rest_route( 'aether-ai/v1', '/blog/status', [
                'methods'  => 'GET',
                'callback' => [ __CLASS__, 'get_generated_posts' ],
                'permission_callback' => fn() => current_user_can( 'edit_posts' ),
            ] );
        } );
    }

    /**
     * 1–20 Beiträge generieren
     */
    public static function generate_posts( WP_REST_Request $req ) {
        $topic       = sanitize_text_field( $req->get_param( 'topic' ) );
        $count       = min( 20, max( 1, intval( $req->get_param( 'count' ) ) ) );
        $category_id = intval( $req->get_param( 'category' ) );
        $api         = new KI_API();
        $created     = [];
    
        // Liste verschiedener Stile für Variation
        $styles = [
            'formell und sachlich',
            'locker und konversationsnah',
            'emotional und erzählerisch',
            'analytisch und faktenbasiert',
            'anekdotisch mit kurzem Beispiel',
            'prägnant und auf den Punkt',
            'in Ich‑Perspektive',
            'mit rhetorischen Fragen',
            'als Checkliste/Tipps',
            'mit metaphorischen Bildern'
        ];
    
        for ( $i = 1; $i <= $count; $i++ ) {
            // Wähle einen Stil zyklisch aus
            $style = $styles[ ( $i - 1 ) % count( $styles ) ];
    
            //
            // 1) SEO‑Titel mit Stilangabe
            //
            $seo_title_prompt = sprintf(
                "Erstelle einen knackigen SEO‑Titel (max. 60 Zeichen) für einen Blogbeitrag über „%s“. " .
                "Verwende einen %s Stil, der sich klar von anderen unterscheidet:",
                $topic,
                $style
            );
            $raw_title = $api->call( $topic, $seo_title_prompt );
            // Entferne Anführungszeichen und Whitespace
            $seo_title = trim( $raw_title, "\"' \n" );
    
            //
            // 2) Body‑Prompt mit Style‑Anweisung
            //
            $body_prompt = sprintf(
                "Schreibe einen ausführlichen, SEO‑optimierten Blog‑Entwurf in reinem HTML zum Thema „%s“ (Entwurf %d von %d). " .
                "Jeder Entwurf soll sich deutlich unterscheiden und im %s Stil verfasst sein. " .
                "Verwende nur saubere HTML‑Tags (h1,h2,h3,p,ul,li,strong,em), keine Markdown- oder ```html```‑Fences.",
                $seo_title,
                $i,
                $count,
                $style
            );
            $raw_content = $api->call( $seo_title, $body_prompt );
    
            //
            // 3) Clean‑Up: Fences entfernen + nur erlaubte Tags
            //
            $raw_content   = preg_replace( '/```html.*?```/is', '', $raw_content );
            $allowed_tags  = [
                'h1'=>[], 'h2'=>[], 'h3'=>[],
                'p'=>[], 'ul'=>[], 'ol'=>[], 'li'=>[],
                'strong'=>[], 'em'=>[]
            ];
            $clean_content = wp_kses( $raw_content, $allowed_tags );
    
            //
            // 4) In WP als Entwurf speichern
            //
            $post_id = wp_insert_post( [
                'post_title'    => wp_strip_all_tags( $seo_title ),
                'post_content'  => $clean_content,
                'post_status'   => 'draft',
                'post_author'   => get_current_user_id(),
                'post_category' => $category_id ? [ $category_id ] : [],
            ] );
    
            $created[] = [
                'id'       => $post_id,
                'title'    => $seo_title,
                'category' => $category_id ? get_cat_name( $category_id ) : '',
                'status'   => 'draft',
            ];
        }
    
        return rest_ensure_response( $created );
    }
    
    /**
     * Gibt zuletzt angelegte Entwürfe zurück
     */
    public static function get_generated_posts( WP_REST_Request $req ) {
        $posts = get_posts( [
            'post_type'   => 'post',
            'post_status' => 'draft',
            'numberposts' => 50,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ] );
        $out = [];
        foreach ( $posts as $p ) {
            $out[] = [
                'id'       => $p->ID,
                'title'    => $p->post_title,
                'category' => implode( ', ', wp_get_post_categories( $p->ID, [ 'fields' => 'names' ] ) ),
                'status'   => $p->post_status,
            ];
        }
        return rest_ensure_response( $out );
    }
}
