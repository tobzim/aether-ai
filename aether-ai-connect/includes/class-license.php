<?php
/**
 * Lizenzprüfung für Aether AI Connect
 */

defined('ABSPATH') || exit;

class KI_License {

    public function __construct() {
        // Prüfe Lizenz bei jedem Laden des Adminbereichs
        add_action('admin_init', [$this, 'check_license']);
    }

    /**
     * Prüft, ob eine gültige Lizenz vorliegt – zeigt ggf. Warnung im Admin
     */
    public function check_license() {
        $license = trim(get_option('ki_license_key'));

        // Einfache lokale Prüfung – später per externem API-Call erweiterbar
        if (!$this->is_valid($license)) {
            // Admin-Warnung anzeigen
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p><strong>⚠️ Aether AI Connect:</strong> Bitte trage einen gültigen Lizenzschlüssel unter <em>Einstellungen → Aether AI Connect</em> ein, um das Plugin zu nutzen.</p></div>';
            });

            // Optional: weitere Plugin-Funktionalitäten hier deaktivieren
        }
    }

    /**
     * Validiert den Lizenzschlüssel lokal (min. 8 Zeichen)
     * Später: Optional Remote-Call zu Lizenzserver
     */
    public function is_valid($license = null): bool {
        if (!$license) {
            $license = get_option('ki_license_key');
        }

        return !empty($license) && strlen($license) >= 8;
    }

    /**
     * Für spätere Erweiterung: Remote-Check gegen externen Lizenzserver
     */
    public function verify_remotely(): bool {
        $key = trim(get_option('ki_license_key'));

        if (!$key) return false;

        $response = wp_remote_get('https://deinserver.de/api/check_license?key=' . urlencode($key));

        if (is_wp_error($response)) return false;

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return isset($data['valid']) && $data['valid'] === true;
    }
}
