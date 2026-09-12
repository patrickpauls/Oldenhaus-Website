<?php
/**
 * Lesefunktionen für das Theme.
 *
 * Alles, was ein Template über Kontaktdaten, Öffnungszeiten, Speisekarte oder FAQ
 * wissen muss, läuft über diese Funktionen. Dadurch kennt das Theme weder Optionsnamen
 * noch Meta-Schlüssel – ein Theme-Wechsel betrifft die Datenhaltung nicht.
 *
 * @package Restaurant_Basis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Kontakt
 * ---------------------------------------------------------------------- */

/**
 * Die Telefonnummer, so wie sie angezeigt werden soll.
 */
function restaurant_basis_telefon_anzeige(): string {
	return trim( restaurant_basis_wert( 'telefon' ) );
}

/**
 * Die Telefonnummer als anklickbarer tel:-Link.
 *
 * Aus der Anzeigeform („04864 1234 / 0171 9876543") lässt sich kein Link bilden, deshalb
 * wird hier auf Ziffern und ein führendes Plus reduziert. Ist keine Nummer hinterlegt,
 * kommt ein leerer String zurück – die Templates zeigen dann einen Platzhalter statt
 * eines toten Links.
 */
function restaurant_basis_telefon_link(): string {
	$roh = restaurant_basis_telefon_anzeige();

	if ( '' === $roh ) {
		return '';
	}

	$nummer = preg_replace( '/[^0-9+]/', '', $roh );
	// Ein Pluszeichen ergibt nur ganz am Anfang Sinn (Ländervorwahl).
	$nummer = preg_replace( '/(?<!^)\+/', '', (string) $nummer );

	return '' === $nummer ? '' : 'tel:' . $nummer;
}

/**
 * Ob eine nutzbare Telefonnummer hinterlegt ist.
 */
function restaurant_basis_hat_telefon(): bool {
	return '' !== restaurant_basis_telefon_link();
}

/**
 * Die E-Mail-Adresse.
 */
function restaurant_basis_email(): string {
	return trim( restaurant_basis_wert( 'email' ) );
}

/**
 * Die Instagram-Adresse.
 */
function restaurant_basis_instagram(): string {
	return trim( restaurant_basis_wert( 'instagram' ) );
}

/**
 * Die Anschrift, aufgeteilt und als fertige einzeilige Fassung.
 *
 * @return array{strasse:string, plz:string, ort:string, ort_zeile:string, einzeilig:string}
 */
function restaurant_basis_adresse(): array {
	$strasse = trim( restaurant_basis_wert( 'strasse' ) );
	$plz     = trim( restaurant_basis_wert( 'plz' ) );
	$ort     = trim( restaurant_basis_wert( 'ort' ) );

	$ort_zeile = trim( $plz . ' ' . $ort );

	$teile     = array_filter( array( $strasse, $ort_zeile ) );
	$einzeilig = implode( ', ', $teile );

	return array(
		'strasse'   => $strasse,
		'plz'       => $plz,
		'ort'       => $ort,
		'ort_zeile' => $ort_zeile,
		'einzeilig' => $einzeilig,
	);
}

/* -------------------------------------------------------------------------
 * Öffnungszeiten
 * ---------------------------------------------------------------------- */

/**
 * Die Öffnungszeiten der ganzen Woche.
 *
 * Montag steht an erster Stelle, passend zur deutschen Lesegewohnheit. Der Index
 * entspricht damit `(new Date().getDay() + 6) % 7` im Frontend-Skript.
 *
 * @return array<int, array{schluessel:string, name:string, ruhetag:bool, zeit:string}>
 */
function restaurant_basis_oeffnungszeiten(): array {
	$zeiten = array();

	foreach ( restaurant_basis_wochentage() as $schluessel => $name ) {
		$zeiten[] = array(
			'schluessel' => $schluessel,
			'name'       => $name,
			'ruhetag'    => '1' === restaurant_basis_wert( 'ruhetag_' . $schluessel ),
			'zeit'       => trim( restaurant_basis_wert( 'zeit_' . $schluessel ) ),
		);
	}

	return $zeiten;
}

/**
 * Die Sonderöffnungszeiten, sofern welche eingetragen sind.
 *
 * @return array{titel:string, zeilen:array<int,string>}|null
 */
function restaurant_basis_sonderzeiten(): ?array {
	$text = trim( restaurant_basis_wert( 'sonderzeiten_text' ) );

	// Ohne Text gibt es nichts anzuzeigen – der Abschnitt bleibt dann komplett weg.
	if ( '' === $text ) {
		return null;
	}

	$zeilen = array_values( array_filter( array_map( 'trim', preg_split( '/\R/', $text ) ) ) );

	return array(
		'titel'  => trim( restaurant_basis_wert( 'sonderzeiten_titel' ) ),
		'zeilen' => $zeilen,
	);
}

/* -------------------------------------------------------------------------
 * Speisekarte
 * ---------------------------------------------------------------------- */

/**
 * Alle Kategorien der Speisekarte in der vom Kunden gesetzten Reihenfolge.
 *
 * Sortiert wird nach dem Feld „Reihenfolge" an der Kategorie. Kategorien ohne Wert
 * landen hinten statt zu verschwinden – deshalb wird hier in PHP sortiert und nicht
 * über `meta_key` in der Abfrage, was solche Kategorien herausfiltern würde.
 *
 * @return WP_Term[]
 */
function restaurant_basis_kategorien(): array {
	$kategorien = get_terms(
		array(
			'taxonomy'   => 'restaurant_gericht_kategorie',
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $kategorien ) ) {
		return array();
	}

	usort(
		$kategorien,
		static function ( WP_Term $a, WP_Term $b ): int {
			$a_wert = get_term_meta( $a->term_id, 'reihenfolge', true );
			$b_wert = get_term_meta( $b->term_id, 'reihenfolge', true );

			// Ohne gepflegte Reihenfolge ans Ende, aber in stabiler Ordnung.
			$a_rang = ( '' === $a_wert ) ? PHP_INT_MAX : (int) $a_wert;
			$b_rang = ( '' === $b_wert ) ? PHP_INT_MAX : (int) $b_wert;

			if ( $a_rang === $b_rang ) {
				return strnatcasecmp( $a->name, $b->name );
			}

			return $a_rang <=> $b_rang;
		}
	);

	return $kategorien;
}

/**
 * Die sichtbaren Gerichte einer Kategorie.
 *
 * Gerichte, die der Kunde auf „nicht verfügbar" gesetzt hat, werden hier aussortiert.
 * Die Prüfung läuft bewusst in PHP und nicht als meta_query: Gerichte, die angelegt
 * wurden, bevor es das Feld gab, haben gar keinen Meta-Eintrag. Eine meta_query müsste
 * diesen Fall zusätzlich abfangen; so gilt schlicht „alles außer ausdrücklich nein".
 *
 * @return WP_Post[]
 */
function restaurant_basis_gerichte_der_kategorie( int $kategorie_id ): array {
	$gerichte = get_posts(
		array(
			'post_type'      => 'restaurant_gericht',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'tax_query'      => array(
				array(
					'taxonomy' => 'restaurant_gericht_kategorie',
					'field'    => 'term_id',
					'terms'    => $kategorie_id,
				),
			),
		)
	);

	return array_values(
		array_filter(
			$gerichte,
			static function ( WP_Post $gericht ): bool {
				return '0' !== (string) get_post_meta( $gericht->ID, 'verfuegbar', true );
			}
		)
	);
}

/**
 * Der Preis eines Gerichts, so wie er eingetragen wurde.
 *
 * Bewusst ein Textfeld: „9,50", „ab 9,50" und „12,50 / 15,00" sind alle gültig.
 */
function restaurant_basis_gericht_preis( int $gericht_id ): string {
	return trim( (string) get_post_meta( $gericht_id, 'preis', true ) );
}

/**
 * Die Beschreibung eines Gerichts.
 */
function restaurant_basis_gericht_beschreibung( int $gericht_id ): string {
	return trim( (string) get_post_meta( $gericht_id, 'beschreibung', true ) );
}

/**
 * Die Kennzeichnung eines Gerichts: „vegan", „vegetarisch" oder nichts.
 *
 * Vegan schließt vegetarisch ein. Sind beide Häkchen gesetzt, wird deshalb nur „vegan"
 * ausgegeben – zwei Auszeichnungen am selben Gericht wären redundant.
 */
function restaurant_basis_gericht_kennzeichnung( int $gericht_id ): string {
	if ( get_post_meta( $gericht_id, 'vegan', true ) ) {
		return 'vegan';
	}

	if ( get_post_meta( $gericht_id, 'vegetarisch', true ) ) {
		return 'vegetarisch';
	}

	return '';
}

/* -------------------------------------------------------------------------
 * Fragen & Antworten
 * ---------------------------------------------------------------------- */

/**
 * Die veröffentlichten Fragen samt Antwort.
 *
 * Entwürfe erscheinen hier bewusst nicht: Eine Frage, deren Antwort noch nicht bestätigt
 * ist, bleibt Entwurf und damit im Frontend unsichtbar.
 *
 * @return array<int, array{frage:string, antwort:string}>
 */
function restaurant_basis_faq_eintraege(): array {
	$fragen = get_posts(
		array(
			'post_type'      => 'restaurant_faq',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
		)
	);

	$eintraege = array();

	foreach ( $fragen as $frage ) {
		$antwort = trim( (string) get_post_meta( $frage->ID, 'antwort', true ) );

		// Eine Frage ohne Antwort hilft niemandem.
		if ( '' === $antwort ) {
			continue;
		}

		$eintraege[] = array(
			'frage'   => $frage->post_title,
			'antwort' => $antwort,
		);
	}

	return $eintraege;
}
