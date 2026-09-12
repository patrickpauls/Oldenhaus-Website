<?php
/**
 * Oldenhaus Restaurant – Theme-Einstieg.
 *
 * Diese Datei bindet nur ein. Die eigentliche Logik liegt nach Zuständigkeit getrennt
 * in inc/, damit sie auffindbar bleibt und functions.php nicht über die Jahre zur
 * Sammelstelle wird.
 *
 * Inhalte (Speisekarte, Fragen & Antworten, Kontaktdaten, Öffnungszeiten) liegen
 * bewusst nicht hier, sondern im Plugin „Restaurant-Basis" – siehe dessen Kopfkommentar.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OLDENHAUS_VERSION', '1.0.0' );
define( 'OLDENHAUS_PFAD', get_template_directory() );
define( 'OLDENHAUS_URL', get_template_directory_uri() );

require_once OLDENHAUS_PFAD . '/inc/theme-setup.php';
require_once OLDENHAUS_PFAD . '/inc/enqueue.php';
require_once OLDENHAUS_PFAD . '/inc/sicherheit.php';
require_once OLDENHAUS_PFAD . '/inc/template-helfer.php';
require_once OLDENHAUS_PFAD . '/inc/acf-felder.php';
require_once OLDENHAUS_PFAD . '/inc/dashboard-hinweise.php';
