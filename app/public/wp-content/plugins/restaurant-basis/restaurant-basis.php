<?php
/**
 * Plugin Name: Restaurant-Basis
 * Description: Speisekarte, Fragen & Antworten sowie zentrale Kontakt- und Öffnungszeitendaten für Restaurant-Websites. Bewusst als Plugin und nicht im Theme, damit die Inhalte einen Theme-Wechsel überleben.
 * Version:     1.0.0
 * Author:      Patrick Pauls
 * License:     GPL-2.0-or-later
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package Restaurant_Basis
 *
 * Warum dieses Plugin themeunabhängig ist
 * ---------------------------------------
 * Wirft ein Theme einen PHP-Fatal-Error – etwa nach einem PHP-Versionssprung beim
 * Hoster – schaltet WordPress seit Version 5.2 selbsttätig auf ein Standard-Theme um.
 * Wäre die Speisekarte im Theme registriert, wären sämtliche Gerichte in diesem Moment
 * auch aus dem Backend verschwunden: Die Einträge lägen weiter in der Datenbank, aber
 * ohne registrierten Post Type sind sie unsichtbar und ohne Entwickler nicht erreichbar.
 *
 * Sicherheitstechnisch macht die Ablage keinen Unterschied – Plugins und Themes laufen
 * im selben PHP-Prozess mit identischen Rechten. Es geht ausschließlich um Ausfallsicherheit.
 *
 * Bewusst ohne Bezug zu einem konkreten Kunden gehalten, damit das Plugin auf weiteren
 * Restaurant-Websites unverändert eingesetzt werden kann. Auch die Öffnungszeiten sind
 * Inhalt der jeweiligen Installation, keine Standardwerte im Code.
 *
 * Sprache: Die Oberfläche ist durchgehend deutsch ausgeschrieben statt über
 * Übersetzungsfunktionen geführt. Das Plugin richtet sich an deutschsprachige
 * Gastronomie-Websites; ein Text-Domain ohne Übersetzungsdateien wäre nur Beiwerk.
 */

// Direktaufruf der Datei verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RESTAURANT_BASIS_VERSION', '1.0.0' );
define( 'RESTAURANT_BASIS_PFAD', plugin_dir_path( __FILE__ ) );
define( 'RESTAURANT_BASIS_DATEI', __FILE__ );

require_once RESTAURANT_BASIS_PFAD . 'includes/post-type-gericht.php';
require_once RESTAURANT_BASIS_PFAD . 'includes/post-type-faq.php';
require_once RESTAURANT_BASIS_PFAD . 'includes/einstellungen-seite.php';
require_once RESTAURANT_BASIS_PFAD . 'includes/acf-feldgruppen.php';
require_once RESTAURANT_BASIS_PFAD . 'includes/template-funktionen.php';

/**
 * Gibt zurück, ob Advanced Custom Fields aktiv ist.
 *
 * Die Feldgruppen werden über acf_add_local_field_group() registriert. Fehlt ACF,
 * soll das Plugin nicht in einen Fatal Error laufen, sondern verständlich hinweisen.
 */
function restaurant_basis_acf_vorhanden(): bool {
	return function_exists( 'acf_add_local_field_group' );
}

/**
 * Weist im Backend auf ein fehlendes ACF hin.
 *
 * Ohne ACF funktionieren Speisekarte und FAQ zwar grundsätzlich, es fehlen aber
 * sämtliche Detailfelder (Beschreibung, Preis, Kennzeichnungen).
 */
function restaurant_basis_hinweis_acf_fehlt(): void {
	if ( restaurant_basis_acf_vorhanden() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$installationslink = wp_nonce_url(
		self_admin_url( 'update.php?action=install-plugin&plugin=advanced-custom-fields' ),
		'install-plugin_advanced-custom-fields'
	);

	printf(
		'<div class="notice notice-error"><p><strong>Restaurant-Basis:</strong> Das Plugin „Advanced Custom Fields" fehlt. Ohne ACF lassen sich Beschreibung, Preis und Kennzeichnungen der Gerichte nicht pflegen. <a href="%s">Jetzt installieren</a></p></div>',
		esc_url( $installationslink )
	);
}
add_action( 'admin_notices', 'restaurant_basis_hinweis_acf_fehlt' );

/**
 * Entfernt den „Deaktivieren"-Link für dieses Plugin.
 *
 * Die Speisekarte ist der zentrale Inhalt der Website. Ein Fehlklick in der
 * Plugin-Übersicht würde sie komplett aus dem Backend entfernen. Das Plugin bleibt
 * sichtbar und nachvollziehbar – nur eben nicht versehentlich abschaltbar.
 */
function restaurant_basis_deaktivieren_verhindern( array $links ): array {
	unset( $links['deactivate'] );

	$links['hinweis'] = '<span style="color:#646970">Dauerhaft aktiv</span>';

	return $links;
}
add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	'restaurant_basis_deaktivieren_verhindern'
);

/**
 * Beim Aktivieren die Rewrite-Regeln neu schreiben.
 *
 * Die Post Types registrieren zwar keine öffentlichen Einzelseiten, ein einmaliges
 * Leeren hält die Regeln aber sauber, falls sich das später ändert.
 */
function restaurant_basis_aktivierung(): void {
	restaurant_basis_post_type_gericht_registrieren();
	restaurant_basis_taxonomie_kategorie_registrieren();
	restaurant_basis_post_type_faq_registrieren();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'restaurant_basis_aktivierung' );
