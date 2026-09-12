<?php
/**
 * Stylesheet, Skript und Schriften einbinden.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bildet eine Versionsnummer aus dem Änderungsdatum einer Datei.
 *
 * Die WordPress-Version wird aus dem Quelltext entfernt (siehe inc/sicherheit.php),
 * damit sie Angreifern nicht verrät, welche Lücken in Frage kommen. Als Ersatz fürs
 * Cache-Busting dient hier der Zeitstempel der Datei: Nach jeder Änderung laden
 * Browser die Datei neu, ohne dass eine Versionsnummer gepflegt werden muss.
 */
function oldenhaus_dateiversion( string $relativer_pfad ): string {
	$vollpfad = OLDENHAUS_PFAD . $relativer_pfad;

	if ( ! file_exists( $vollpfad ) ) {
		return OLDENHAUS_VERSION;
	}

	return (string) filemtime( $vollpfad );
}

/**
 * Bindet Stylesheet und Skript ein.
 */
function oldenhaus_assets_einbinden(): void {
	wp_enqueue_style(
		'oldenhaus',
		get_stylesheet_uri(),
		array(),
		oldenhaus_dateiversion( '/style.css' )
	);

	wp_enqueue_script(
		'oldenhaus',
		OLDENHAUS_URL . '/assets/js/oldenhaus.js',
		array(),
		oldenhaus_dateiversion( '/assets/js/oldenhaus.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'oldenhaus_assets_einbinden' );

/**
 * Lädt die beiden wichtigsten Schriftschnitte vorab.
 *
 * Die Schriften liegen lokal im Theme – es geht keine Anfrage an einen externen
 * Font-Server. Ohne Preload würde der Browser sie erst entdecken, nachdem er das
 * Stylesheet geparst hat; das erzeugt ein sichtbares Nachspringen der Schrift.
 *
 * Vorab geladen werden nur die zwei Schnitte, die auf jeder Seite sofort sichtbar sind:
 * Alegreya Sans 800 für die Überschriften, Source Sans 3 400 für den Fließtext.
 */
function oldenhaus_schriften_vorladen(): void {
	$schriften = array(
		'/assets/fonts/alegreya-sans-800.woff2',
		'/assets/fonts/source-sans-3-400.woff2',
	);

	foreach ( $schriften as $schrift ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( OLDENHAUS_URL . $schrift )
		);
	}
}
add_action( 'wp_head', 'oldenhaus_schriften_vorladen', 1 );
