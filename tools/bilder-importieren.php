<?php
/**
 * Importiert die Uebergangsbilder in die Mediathek und ordnet sie den Feldern zu.
 *
 * Ausfuehren mit WP-CLI:
 *     wp eval-file tools/bilder-importieren.php /pfad/zum/bilderordner
 *
 * Die Bilder stammen aus der WordPress-Fotodatenbank (wordpress.org/photos) und stehen
 * unter CC0. Herkunft und Lizenz jedes Bildes sind in reference/bildquellen.md und
 * reference/bildquellen.json festgehalten.
 *
 * Es sind Uebergangsbilder bis zum Fototermin. Der Austausch laeuft spaeter ueber die
 * Mediathek, ohne Aenderung am Code.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Dieses Skript laeuft nur ueber WP-CLI.\n" );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$bilderordner = $args[0] ?? '';

if ( '' === $bilderordner || ! is_dir( $bilderordner ) ) {
	WP_CLI::error( 'Bitte den Ordner mit den Bildern angeben: wp eval-file tools/bilder-importieren.php /pfad/zum/ordner' );
}

$manifest_pfad = dirname( ABSPATH, 2 ) . '/reference/bildquellen.json';

if ( ! file_exists( $manifest_pfad ) ) {
	WP_CLI::error( "Manifest nicht gefunden: {$manifest_pfad}" );
}

$manifest = json_decode( (string) file_get_contents( $manifest_pfad ), true );

if ( ! is_array( $manifest ) ) {
	WP_CLI::error( 'Manifest konnte nicht gelesen werden.' );
}

/**
 * Legt ein Bild in der Mediathek an, falls es noch nicht da ist.
 */
function oldenhaus_bild_importieren( array $eintrag, string $ordner ): int {
	$datei = trailingslashit( $ordner ) . $eintrag['name'] . '.jpg';

	if ( ! file_exists( $datei ) ) {
		WP_CLI::warning( "Datei fehlt: {$datei}" );
		return 0;
	}

	// Schon importiert? Dann nicht doppelt anlegen.
	$vorhandene = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_oldenhaus_bildname',
			'meta_value'     => $eintrag['name'],
		)
	);

	if ( ! empty( $vorhandene ) ) {
		WP_CLI::log( "  vorhanden: {$eintrag['name']}" );
		return (int) $vorhandene[0];
	}

	$hochgeladen = wp_upload_bits( basename( $datei ), null, (string) file_get_contents( $datei ) );

	if ( ! empty( $hochgeladen['error'] ) ) {
		WP_CLI::warning( "Upload fehlgeschlagen ({$eintrag['name']}): {$hochgeladen['error']}" );
		return 0;
	}

	/*
	 * Herkunft und Lizenz kommen in die Beschreibung, nicht in die Bildunterschrift.
	 * CC0 verlangt keine Namensnennung, und unter jedem Galeriebild eine Quellenangabe
	 * waere fuer Gaeste nur Rauschen. In der Mediathek ist sie trotzdem jederzeit
	 * nachlesbar.
	 */
	$beschreibung = sprintf(
		"Übergangsbild bis zum Fototermin.\nQuelle: %s\nUrheber: %s\nLizenz: %s (%s)\nOriginal: %s",
		$eintrag['anbieter'],
		$eintrag['urheber'],
		$eintrag['lizenz'],
		$eintrag['lizenz_url'],
		$eintrag['quelle']
	);

	$anhang_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $eintrag['alt'],
			'post_content'   => $beschreibung,
			'post_status'    => 'inherit',
		),
		$hochgeladen['file']
	);

	if ( is_wp_error( $anhang_id ) || 0 === $anhang_id ) {
		WP_CLI::warning( "Anhang fehlgeschlagen: {$eintrag['name']}" );
		return 0;
	}

	wp_update_attachment_metadata(
		$anhang_id,
		wp_generate_attachment_metadata( $anhang_id, $hochgeladen['file'] )
	);

	// Alt-Text fuer Screenreader und fuer den Fall, dass ein Bild nicht laedt.
	update_post_meta( $anhang_id, '_wp_attachment_image_alt', $eintrag['alt'] );
	update_post_meta( $anhang_id, '_oldenhaus_bildname', $eintrag['name'] );

	WP_CLI::log( "  importiert: {$eintrag['name']} (ID {$anhang_id})" );

	return (int) $anhang_id;
}

WP_CLI::log( 'Bilder importieren' );

$ids = array();

foreach ( $manifest as $eintrag ) {
	$id = oldenhaus_bild_importieren( $eintrag, $bilderordner );

	if ( $id > 0 ) {
		$ids[ $eintrag['name'] ] = $id;
	}
}

/* -------------------------------------------------------------------------
 * Zuordnung zu den Feldern
 * ---------------------------------------------------------------------- */

WP_CLI::log( 'Bilder zuordnen' );

$startseite = (int) get_option( 'page_on_front' );
$ueber_uns  = get_page_by_path( 'ueber-uns' );
$galerie    = get_page_by_path( 'galerie' );

/**
 * Setzt ein Bildfeld, sofern es noch leer ist.
 */
function oldenhaus_bildfeld_setzen( string $feldschluessel, string $bildname, array $ids, int $beitrag_id ): void {
	if ( ! isset( $ids[ $bildname ] ) || ! $beitrag_id || ! function_exists( 'update_field' ) ) {
		return;
	}

	if ( ! empty( get_field( $feldschluessel, $beitrag_id ) ) ) {
		return;
	}

	update_field( $feldschluessel, $ids[ $bildname ], $beitrag_id );
}

oldenhaus_bildfeld_setzen( 'field_oldenhaus_hero_bild_1', 'hero-1-pizza-ofen', $ids, $startseite );
oldenhaus_bildfeld_setzen( 'field_oldenhaus_hero_bild_2', 'hero-2-gastraum', $ids, $startseite );
oldenhaus_bildfeld_setzen( 'field_oldenhaus_hero_bild_3', 'hero-3-pasta', $ids, $startseite );
oldenhaus_bildfeld_setzen( 'field_oldenhaus_abholung_bild', 'abholung-pizza', $ids, $startseite );
oldenhaus_bildfeld_setzen( 'field_oldenhaus_events_bild', 'feier-tafel', $ids, $startseite );
WP_CLI::log( '  Startseite' );

if ( $ueber_uns instanceof WP_Post ) {
	oldenhaus_bildfeld_setzen( 'field_oldenhaus_ueberuns_bild_1', 'ueberuns-1-raum', $ids, $ueber_uns->ID );
	oldenhaus_bildfeld_setzen( 'field_oldenhaus_ueberuns_bild_2', 'ueberuns-2-tische', $ids, $ueber_uns->ID );
	oldenhaus_bildfeld_setzen( 'field_oldenhaus_ueberuns_bild_3', 'ueberuns-3-gnocchi', $ids, $ueber_uns->ID );
	WP_CLI::log( '  Über uns' );
}

/*
 * Die Galerie wird als normaler WordPress-Galerie-Shortcode hinterlegt. Genau den
 * erzeugt auch der Dialog "Galerie erstellen" - der Kunde kann sie also anschliessend
 * ganz normal bearbeiten, sortieren und erweitern.
 */
if ( $galerie instanceof WP_Post && function_exists( 'update_field' ) ) {
	$vorhandene_galerie = (string) get_field( 'field_oldenhaus_galerie_bilder', $galerie->ID );

	if ( ! str_contains( $vorhandene_galerie, '[gallery' ) ) {
		$galerie_ids = array();

		foreach ( $ids as $name => $id ) {
			if ( str_starts_with( $name, 'galerie-' ) ) {
				$galerie_ids[] = $id;
			}
		}

		if ( ! empty( $galerie_ids ) ) {
			update_field(
				'field_oldenhaus_galerie_bilder',
				'[gallery ids="' . implode( ',', $galerie_ids ) . '" columns="3" link="file"]',
				$galerie->ID
			);
			WP_CLI::log( '  Galerie (' . count( $galerie_ids ) . ' Bilder)' );
		}
	}
}

WP_CLI::success( 'Bilder stehen.' );
