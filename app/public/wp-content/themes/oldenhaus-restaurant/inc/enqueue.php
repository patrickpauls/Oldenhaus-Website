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

/**
 * Entfernt das Block-Stylesheet auf Seiten, die gar keine Blöcke enthalten.
 *
 * WordPress lädt rund 20 KB Block-CSS auf jeder Seite, auch wenn keiner der Blöcke
 * vorkommt. Auf dieser Website sind alle Inhalte über eigene Vorlagen und ACF-Felder
 * aufgebaut; der Blockeditor kommt allenfalls auf Impressum und Datenschutz zum Einsatz.
 *
 * Geprüft wird am Inhalt selbst, nicht an einer Liste von Vorlagen: Sobald der Kunde
 * irgendwo doch einen Block einfügt, lädt das Stylesheet von allein wieder mit. So kann
 * sich niemand versehentlich das Layout zerschießen.
 */
function oldenhaus_ungenutztes_block_css_entfernen(): void {
	$beitrag = get_queried_object();

	// Im Zweifel nichts entfernen.
	if ( ! $beitrag instanceof WP_Post ) {
		return;
	}

	if ( has_blocks( $beitrag->post_content ) ) {
		return;
	}

	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'classic-theme-styles' );

	/*
	 * Die globalen Blockstile hängen in WordPress 7.1 an zwei Stellen:
	 * an 'wp_enqueue_scripts' und zusätzlich an 'wp_footer' mit Priorität 1
	 * (siehe wp-includes/default-filters.php). Ein blosses wp_dequeue_style()
	 * greift deshalb nur gegen den ersten Aufruf - der zweite meldet sie wieder an.
	 */
	wp_dequeue_style( 'global-styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
}
add_action( 'wp_enqueue_scripts', 'oldenhaus_ungenutztes_block_css_entfernen', 100 );
