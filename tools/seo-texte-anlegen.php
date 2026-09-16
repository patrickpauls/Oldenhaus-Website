<?php
/**
 * Schreibt die SEO-Entwuerfe in die Felder der Seiten.
 *
 * Ausfuehren mit WP-CLI:
 *     wp eval-file tools/seo-texte-anlegen.php
 *
 * Oder ohne WP-CLI ueber ein geladenes WordPress:
 *     php -r "define('WP_USE_THEMES',false); require 'wp-load.php'; require 'tools/seo-texte-anlegen.php';"
 *
 * Das Skript ist wiederholbar: Ein bereits gepflegtes Feld wird nicht ueberschrieben.
 * Wer eine Beschreibung von Hand umformuliert, verliert sie also nicht beim naechsten
 * Lauf. Mit dem Schalter --ueberschreiben werden die Entwuerfe erzwungen.
 *
 * Woher die Texte kommen: aus oldenhaus_seo_standard_beschreibung() im Theme
 * (inc/seo.php). Bewusst nicht noch einmal hier abgetippt - sonst gaebe es zwei
 * Fassungen derselben Saetze, und eine davon waere irgendwann die veraltete. Das
 * Skript traegt also genau das ins Backend ein, was die Website sonst von sich aus
 * ausgeben wuerde. Der Unterschied: Im Feld sieht der Kunde den Text, kann ihn lesen
 * und aendern - im Code waere er unsichtbar.
 *
 * Zusaetzlich setzt es das Haekchen "Von Suchmaschinen ausschliessen" bei Impressum
 * und Datenschutzerklaerung. Das Theme erkennt beide Seiten zwar auch ohne das
 * Haekchen, aber nur ueber Namensmuster und die WordPress-Datenschutzeinstellung. Das
 * Haekchen haengt am Beitrag selbst und ueberlebt jedes Umbenennen.
 *
 * @package Oldenhaus
 */

/* -------------------------------------------------------------------------
 * Wachposten und Ausgabe
 * ---------------------------------------------------------------------- */

$oldenhaus_mit_cli = defined( 'WP_CLI' ) && WP_CLI;

// Niemals ueber den Browser: Das Skript schreibt Inhalte und hat im Web nichts zu
// suchen. WP-CLI oder ein php-Aufruf auf der Kommandozeile, sonst nichts.
if ( ! $oldenhaus_mit_cli && 'cli' !== PHP_SAPI ) {
	exit( "Dieses Skript laeuft nur auf der Kommandozeile.\n" );
}

/**
 * Gibt eine Zeile aus - ueber WP-CLI oder schlicht auf die Konsole.
 */
function oldenhaus_seo_log( string $zeile ): void {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::log( $zeile );

		return;
	}

	echo $zeile, "\n";
}

if ( ! function_exists( 'update_field' ) ) {
	oldenhaus_seo_log( 'ACF ist nicht aktiv - ohne ACF gibt es die Felder nicht. Abbruch.' );

	return;
}

if ( ! function_exists( 'oldenhaus_seo_standard_beschreibung' ) ) {
	oldenhaus_seo_log( 'Das Theme Oldenhaus ist nicht geladen (inc/seo.php fehlt). Abbruch.' );

	return;
}

$oldenhaus_ueberschreiben = in_array( '--ueberschreiben', (array) ( $argv ?? array() ), true );

/* -------------------------------------------------------------------------
 * Beschreibungen
 * ---------------------------------------------------------------------- */

oldenhaus_seo_log( 'SEO-Beschreibungen eintragen' );

$oldenhaus_seiten = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'posts_per_page' => -1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
	)
);

foreach ( $oldenhaus_seiten as $oldenhaus_seite ) {
	$oldenhaus_vorhanden = trim( (string) get_field( 'seo_beschreibung', $oldenhaus_seite->ID ) );

	if ( '' !== $oldenhaus_vorhanden && ! $oldenhaus_ueberschreiben ) {
		oldenhaus_seo_log( sprintf( '  uebersprungen (schon gepflegt): %s', $oldenhaus_seite->post_title ) );

		continue;
	}

	$oldenhaus_text = oldenhaus_seo_standard_beschreibung( $oldenhaus_seite->ID );

	if ( '' === $oldenhaus_text ) {
		oldenhaus_seo_log( sprintf( '  kein Entwurf vorhanden: %s', $oldenhaus_seite->post_title ) );

		continue;
	}

	update_field( 'seo_beschreibung', $oldenhaus_text, $oldenhaus_seite->ID );

	oldenhaus_seo_log(
		sprintf(
			'  %-24s %3d Zeichen  %s',
			$oldenhaus_seite->post_title,
			mb_strlen( $oldenhaus_text ),
			$oldenhaus_text
		)
	);
}

/* -------------------------------------------------------------------------
 * Rechtsseiten aus dem Suchindex nehmen
 * ---------------------------------------------------------------------- */

oldenhaus_seo_log( '' );
oldenhaus_seo_log( 'Rechtsseiten von Suchmaschinen ausschliessen' );

$oldenhaus_rechtsseiten = array();

$oldenhaus_datenschutz = (int) get_option( 'wp_page_for_privacy_policy' );

if ( $oldenhaus_datenschutz > 0 ) {
	$oldenhaus_rechtsseiten[] = $oldenhaus_datenschutz;
}

foreach ( $oldenhaus_seiten as $oldenhaus_seite ) {
	if ( oldenhaus_seo_ist_impressum( $oldenhaus_seite->ID ) ) {
		$oldenhaus_rechtsseiten[] = $oldenhaus_seite->ID;
	}
}

foreach ( array_unique( $oldenhaus_rechtsseiten ) as $oldenhaus_id ) {
	update_field( 'seo_noindex', true, $oldenhaus_id );

	oldenhaus_seo_log( sprintf( '  Haekchen gesetzt: %s (ID %d)', get_the_title( $oldenhaus_id ), $oldenhaus_id ) );
}

oldenhaus_seo_log( '' );
oldenhaus_seo_log( 'Fertig.' );
