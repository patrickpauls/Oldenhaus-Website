<?php
/**
 * Legt Seiten, Menues, Einstellungen und Beispielinhalte an.
 *
 * Ausfuehren mit WP-CLI:
 *     wp eval-file tools/inhalte-anlegen.php
 *
 * Das Skript ist wiederholbar: Es sucht vorhandene Eintraege anhand ihres
 * Adresszusatzes und legt nur an, was fehlt. Bereits gepflegte Inhalte werden
 * nicht ueberschrieben.
 *
 * Warum ueberhaupt ein Skript: So ist nachvollziehbar, wie die Seitenstruktur
 * entstanden ist, und sie laesst sich auf einer leeren Installation in einem
 * Schritt wiederherstellen.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Dieses Skript laeuft nur ueber WP-CLI.\n" );
}

/* -------------------------------------------------------------------------
 * Hilfsfunktionen
 * ---------------------------------------------------------------------- */

/**
 * Legt eine Seite an, falls sie noch nicht existiert.
 */
function oldenhaus_seite_sicherstellen( string $slug, string $titel, string $vorlage = '', string $inhalt = '' ): int {
	$vorhanden = get_page_by_path( $slug );

	if ( $vorhanden instanceof WP_Post ) {
		WP_CLI::log( "  Seite vorhanden: {$titel} (ID {$vorhanden->ID})" );
		$id = $vorhanden->ID;
	} else {
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $titel,
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_content' => $inhalt,
			),
			true
		);

		if ( is_wp_error( $id ) ) {
			WP_CLI::error( "Seite '{$titel}' konnte nicht angelegt werden: " . $id->get_error_message() );
		}

		WP_CLI::log( "  Seite angelegt: {$titel} (ID {$id})" );
	}

	if ( '' !== $vorlage ) {
		update_post_meta( $id, '_wp_page_template', $vorlage );
	}

	return (int) $id;
}

/**
 * Setzt ein ACF-Feld nur, wenn es noch leer ist.
 *
 * So ueberschreibt ein erneuter Lauf keine Texte, die der Kunde inzwischen
 * angepasst hat.
 */
function oldenhaus_feld_sicherstellen( string $feldschluessel, $wert, int $beitrag_id ): void {
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}

	$aktuell = get_field( $feldschluessel, $beitrag_id );

	if ( is_string( $aktuell ) && '' !== trim( $aktuell ) ) {
		return;
	}

	if ( ! is_string( $aktuell ) && ! empty( $aktuell ) ) {
		return;
	}

	update_field( $feldschluessel, $wert, $beitrag_id );
}

/* -------------------------------------------------------------------------
 * 1 · WordPress-Standardinhalte entfernen
 * ---------------------------------------------------------------------- */

WP_CLI::log( 'WordPress-Standardinhalte' );

foreach ( array( 'hello-world', 'sample-page' ) as $slug ) {
	$beitrag = get_page_by_path( $slug, OBJECT, array( 'post', 'page' ) );

	if ( $beitrag instanceof WP_Post ) {
		wp_delete_post( $beitrag->ID, true );
		WP_CLI::log( "  entfernt: {$beitrag->post_title}" );
	}
}

/* -------------------------------------------------------------------------
 * 2 · Seiten
 * ---------------------------------------------------------------------- */

WP_CLI::log( 'Seiten' );

$rechtstext_hinweis = "<p>Inhalt folgt.</p>\n";

$startseite = oldenhaus_seite_sicherstellen( 'startseite', 'Startseite' );
$speisekarte = oldenhaus_seite_sicherstellen( 'speisekarte', 'Speisekarte', 'template-speisekarte.php' );
$galerie = oldenhaus_seite_sicherstellen( 'galerie', 'Galerie', 'template-galerie.php' );
$ueber_uns = oldenhaus_seite_sicherstellen( 'ueber-uns', 'Über uns', 'template-ueber-uns.php' );

$impressum = oldenhaus_seite_sicherstellen(
	'impressum',
	'Impressum',
	'',
	$rechtstext_hinweis . "<p>Für das Impressum werden noch benötigt: vollständiger Firmenname und Rechtsform, vertretungsberechtigte Person, Anschrift, Telefonnummer, E-Mail-Adresse, gegebenenfalls Registergericht und Registernummer sowie Umsatzsteuer-Identifikationsnummer oder Steuernummer.</p>\n"
);

$datenschutz = oldenhaus_seite_sicherstellen(
	'datenschutzerklaerung',
	'Datenschutzerklärung',
	'',
	$rechtstext_hinweis . "<p>Die Website bindet bewusst keine Dienste von Drittanbietern ein: keine Karte, keinen Social-Media-Feed, keine externen Schriften, kein Tracking. Die Datenschutzerklärung muss daher im Wesentlichen den Hoster, die Server-Protokolldateien und die Kontaktaufnahme per Telefon abdecken.</p>\n"
);

/* -------------------------------------------------------------------------
 * 3 · Startseite als Startseite setzen
 * ---------------------------------------------------------------------- */

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $startseite );
// Es gibt bewusst keine Blog-Seite - die Website hat keinen Blog.
update_option( 'page_for_posts', 0 );
update_option( 'wp_page_for_privacy_policy', $datenschutz );
WP_CLI::log( '  Startseite und Datenschutzseite in den Einstellungen hinterlegt' );

/* -------------------------------------------------------------------------
 * 4 · Menues
 * ---------------------------------------------------------------------- */

WP_CLI::log( 'Menues' );

/**
 * Legt ein Menue mit festen Eintraegen an.
 */
function oldenhaus_menue_sicherstellen( string $name, string $position, array $seiten_ids ): void {
	$menue = wp_get_nav_menu_object( $name );

	if ( ! $menue ) {
		$menue_id = wp_create_nav_menu( $name );

		if ( is_wp_error( $menue_id ) ) {
			WP_CLI::warning( "Menue '{$name}' konnte nicht angelegt werden." );
			return;
		}

		WP_CLI::log( "  Menue angelegt: {$name}" );
	} else {
		$menue_id = (int) $menue->term_id;
		WP_CLI::log( "  Menue vorhanden: {$name}" );
	}

	// Vorhandene Eintraege einsammeln, damit nichts doppelt entsteht.
	$vorhandene = array();

	foreach ( wp_get_nav_menu_items( $menue_id ) ?: array() as $eintrag ) {
		$vorhandene[] = (int) $eintrag->object_id;
	}

	foreach ( $seiten_ids as $position_nr => $seiten_id ) {
		if ( in_array( (int) $seiten_id, $vorhandene, true ) ) {
			continue;
		}

		wp_update_nav_menu_item(
			$menue_id,
			0,
			array(
				'menu-item-object-id' => $seiten_id,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position_nr + 1,
			)
		);
	}

	$positionen              = get_theme_mod( 'nav_menu_locations', array() );
	$positionen[ $position ] = $menue_id;
	set_theme_mod( 'nav_menu_locations', $positionen );
}

// Reihenfolge wie abgestimmt: Speisekarte, Galerie, Ueber uns.
// Die Startseite ist ueber die Wortmarke erreichbar und steht deshalb nicht im Menue.
oldenhaus_menue_sicherstellen( 'Hauptnavigation', 'hauptnavigation', array( $speisekarte, $galerie, $ueber_uns ) );
oldenhaus_menue_sicherstellen( 'Rechtliches', 'rechtliches', array( $impressum, $datenschutz ) );

/* -------------------------------------------------------------------------
 * 5 · Kontaktdaten und Oeffnungszeiten
 * ---------------------------------------------------------------------- */

WP_CLI::log( 'Kontakt und Oeffnungszeiten' );

$einstellungen = get_option( 'restaurant_basis_einstellungen', array() );

if ( ! is_array( $einstellungen ) ) {
	$einstellungen = array();
}

// Telefon und E-Mail bleiben bewusst leer - sie liegen noch nicht vor.
// Die Website zeigt an diesen Stellen einen sichtbaren Hinweis statt eines toten Links.
$vorgaben = array(
	'strasse'   => 'Dorfstraße 19',
	'plz'       => '25870',
	'ort'       => 'Oldenswort',
	'instagram' => 'https://www.instagram.com/oldenhaus/',

	'zeit_montag'        => '17–22 Uhr',
	'ruhetag_dienstag'   => '1',
	'zeit_dienstag'      => '',
	'zeit_mittwoch'      => '17–22 Uhr',
	'zeit_donnerstag'    => '17–22 Uhr',
	'zeit_freitag'       => '12–22 Uhr',
	'zeit_samstag'       => '12–22 Uhr',
	'zeit_sonntag'       => '12–22 Uhr',
);

foreach ( $vorgaben as $schluessel => $wert ) {
	if ( ! isset( $einstellungen[ $schluessel ] ) || '' === $einstellungen[ $schluessel ] ) {
		$einstellungen[ $schluessel ] = $wert;
	}
}

update_option( 'restaurant_basis_einstellungen', $einstellungen );
WP_CLI::log( '  Anschrift, Instagram und Oeffnungszeiten eingetragen' );
WP_CLI::log( '  Telefon und E-Mail bleiben leer - liegen noch nicht vor' );

/* -------------------------------------------------------------------------
 * 6 · Texte der Startseite
 * ---------------------------------------------------------------------- */

WP_CLI::log( 'Texte' );

$startseiten_texte = array(
	'field_oldenhaus_leitspruch'      => 'Komm hungrig. Geh als Stammgast.',
	'field_oldenhaus_unterzeile'      => 'Italienische Küche und Pizza aus dem Ofen – mitten in Oldenswort.',

	'field_oldenhaus_zeiten_titel'    => 'Wann wir für dich kochen',
	'field_oldenhaus_zeiten_text'     => 'Dienstags gönnt sich der Ofen eine Pause. An Feiertagen und im Urlaub steht hier, was sich ändert.',
	'field_oldenhaus_parken_text'     => 'Parken kostet bei uns nichts. Direkt vor der Tür ist Platz, du musst also nicht erst eine Runde drehen.',

	'field_oldenhaus_abholung_titel'  => 'Lieber aufs Sofa? Alles gibt’s auch zum Mitnehmen.',
	'field_oldenhaus_abholung_text'   => 'Jedes Gericht der Karte packen wir dir ein. Kurz anrufen, bestellen, abholen – warm ist sie dann noch.',
	'field_oldenhaus_abholung_button' => 'Zum Abholen bestellen',

	'field_oldenhaus_events_titel'    => 'Wenn’s mehr als ein Tisch sein darf',
	'field_oldenhaus_events_text'     => "Firmenfeier, Weihnachtsessen oder Hochzeit: Bei uns haben rund 70 Gäste Platz.\n\nSag uns, was ihr vorhabt, und wir überlegen gemeinsam, wie wir es hinbekommen. Am schnellsten geht das am Telefon – dann klären wir Termin, Anzahl und Essen in einem Rutsch.",

	'field_oldenhaus_gutscheine_titel' => 'Gutscheine gibt’s bei uns',
	'field_oldenhaus_gutscheine_text'  => 'Verschenk einen Abend bei uns. Gutscheine bekommst du direkt vor Ort – komm einfach vorbei, wir stellen dir einen aus.',

	'field_oldenhaus_faq_titel'       => 'Gut zu wissen',
	'field_oldenhaus_faq_einleitung'  => 'Was Gäste uns am häufigsten fragen.',
);

foreach ( $startseiten_texte as $feld => $wert ) {
	oldenhaus_feld_sicherstellen( $feld, $wert, $startseite );
}
WP_CLI::log( '  Startseite' );

oldenhaus_feld_sicherstellen(
	'field_oldenhaus_speisekarte_einleitung',
	'Alles auf einer Seite, einmal von oben nach unten. Kein Anklicken, kein Suchen.',
	$speisekarte
);
oldenhaus_feld_sicherstellen(
	'field_oldenhaus_speisekarte_legende',
	'kennzeichnen fleischlose Gerichte.',
	$speisekarte
);
WP_CLI::log( '  Speisekarte' );

oldenhaus_feld_sicherstellen(
	'field_oldenhaus_galerie_einleitung',
	'Ein paar Eindrücke vom Teller und aus dem Gastraum.',
	$galerie
);
WP_CLI::log( '  Galerie' );

$ueber_uns_texte = array(
	'field_oldenhaus_ueberuns_einleitung' => 'Ein Team, ein Ofen, italienische Küche. Viel mehr braucht es eigentlich nicht.',

	'field_oldenhaus_ueberuns_titel_1' => 'Bei uns kennt man sich',
	'field_oldenhaus_ueberuns_text_1'  => 'Wer zum dritten Mal kommt, muss seine Bestellung meistens nicht mehr aufsagen. Das ist uns wichtiger als jede Hochglanzkarte: dass du dich gesehen fühlst und nicht wie eine Tischnummer behandelt wirst.',

	'field_oldenhaus_ueberuns_titel_2' => 'Pizza, wie sie sein soll',
	'field_oldenhaus_ueberuns_text_2'  => 'Der Teig ruht, bis er so weit ist. Die Tomatensoße köchelt, statt aus der Dose zu kommen. Dazu Pasta und was die italienische Küche sonst hergibt – ohne Chichi, aber mit Zeit gemacht.',

	'field_oldenhaus_ueberuns_titel_3' => 'Gutes Essen muss nicht teuer sein',
	'field_oldenhaus_ueberuns_text_3'  => 'Wir rechnen so, dass du auch unter der Woche spontan vorbeikommen kannst. Familien mit Kindern, Paare am Abend, die Runde aus der Nachbarschaft: Bei uns sitzt das alles im selben Raum.',
);

foreach ( $ueber_uns_texte as $feld => $wert ) {
	oldenhaus_feld_sicherstellen( $feld, $wert, $ueber_uns );
}
WP_CLI::log( '  Über uns' );

WP_CLI::success( 'Seiten, Menues und Texte stehen.' );
