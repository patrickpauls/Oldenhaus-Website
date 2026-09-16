<?php
/**
 * Basis-Suchmaschinenoptimierung – schlank im Theme statt als Plugin.
 *
 * Bewusst kein SEO-Plugin. Die gängigen Vertreter bringen Werbeflächen, eine eigene
 * Menüstruktur und ein Vielfaches an Code mit, um am Ende dieselben fünf Meta-Angaben
 * auszugeben, die hier stehen. Für eine Website mit sechs Seiten wäre das ein
 * schlechtes Geschäft – und jedes Plugin ist eine weitere Stelle, die aktualisiert
 * werden muss.
 *
 * Was hier passiert:
 *
 *   1. Seitentitel        – über die Kernfilter, nicht hartcodiert im header.php
 *   2. Beschreibung       – je Seite pflegbar, mit seitenbezogenem Rückfalltext
 *   3. Robots             – über den Kernfilter wp_robots, nicht als zweites Meta-Tag
 *   4. Open Graph         – für die Vorschau beim Teilen, ohne fremde Verifizierungs-Tags
 *   5. Strukturierte Daten – Schema.org „Restaurant" auf der Startseite
 *   6. Sitemap            – Statuskorrektur und Aufräumen der Einträge
 *   7. Sprachkennung      – lang="de-DE" statt nur "de"
 *   8. Die Backend-Felder – „Suchmaschinen (SEO)" an jeder Seite
 *
 * Nichts davon lädt etwas von einem fremden Server. „schema.org" im JSON-LD ist ein
 * Namensraum, der als Zeichenkette dasteht und nie abgerufen wird – genauso wie
 * „api.w.org" im Kopfbereich. Die Website bleibt damit ohne Einwilligungsbanner.
 *
 * Der Anmeldename der Redaktion taucht hier nirgends auf: kein „author"-Meta, kein
 * „article:author", kein Autorenfeld im JSON-LD. Das ist Absicht und ergänzt die
 * Maßnahmen in inc/sicherheit.php.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================================
 * 0 · Grundlagen
 * ====================================================================== */

/**
 * Die Marke, wie sie hinter jedem Seitentitel steht.
 *
 * Bewusst nicht get_bloginfo( 'name' ): Der Website-Name lautet „Oldenhaus Restaurant".
 * Als Titelzusatz gelesen ergäbe das „Speisekarte | Oldenhaus Restaurant" – das Wort
 * „Restaurant" steht dann auf jeder Unterseite ein zweites Mal im Titel und kostet
 * zehn der rund sechzig Zeichen, die Google anzeigt. Die Startseite trägt den vollen
 * Namen samt Ort, die Unterseiten nur die kurze Marke.
 */
function oldenhaus_seo_marke(): string {
	return 'Oldenhaus';
}

/**
 * Die ID des Beitrags, um den es auf dieser Seite geht – oder 0.
 *
 * Auf Archiven, der Suche und dem 404 gibt es keinen solchen Beitrag; alle Funktionen
 * hier unten müssen damit umgehen können.
 */
function oldenhaus_seo_seiten_id(): int {
	return is_singular() ? (int) get_queried_object_id() : 0;
}

/**
 * Liest eines der beiden SEO-Textfelder der aktuellen Seite.
 */
function oldenhaus_seo_feld( string $name ): string {
	$seiten_id = oldenhaus_seo_seiten_id();

	return $seiten_id > 0 ? oldenhaus_feld( $name, $seiten_id ) : '';
}

/**
 * Sucht die Seite, die eine bestimmte Seitenvorlage benutzt.
 *
 * Die Konvention des Projekts ist „Template Name" statt slug-basierter Vorlagen, damit
 * ein umbenannter Seitentitel nichts bricht. Dieselbe Überlegung gilt hier: Für den
 * Verweis auf die Speisekarte im JSON-LD wird nicht nach dem Adresszusatz
 * „speisekarte" gesucht, sondern nach der Vorlage – die kann der Kunde im Backend
 * nicht versehentlich verändern.
 */
function oldenhaus_seo_seite_mit_vorlage( string $vorlage ): int {
	static $gefunden = array();

	if ( isset( $gefunden[ $vorlage ] ) ) {
		return $gefunden[ $vorlage ];
	}

	$treffer = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- eine Seite, einmal pro Aufruf.
			'meta_value'     => $vorlage,            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'no_found_rows'  => true,
		)
	);

	$gefunden[ $vorlage ] = empty( $treffer ) ? 0 : (int) $treffer[0];

	return $gefunden[ $vorlage ];
}

/* =========================================================================
 * 1 · Seitentitel
 * ====================================================================== */

/**
 * Der Titel der Startseite.
 *
 * Aufbau: Marke zuerst, dann was es ist, dann wo. Wer „Pizzeria Oldenswort" sucht,
 * findet beide Begriffe im Titel; wer „Oldenhaus" sucht, erkennt die Marke sofort.
 * Der Website-Name allein („Oldenhaus Restaurant") sagte weder, dass es Pizza gibt,
 * noch wo das Lokal steht.
 */
function oldenhaus_seo_startseitentitel(): string {
	return 'Oldenhaus – Restaurant & Pizzeria in Oldenswort';
}

/**
 * Setzt den Seitentitel zusammen.
 *
 * Ein eigener SEO-Titel ersetzt den Titel vollständig – ohne angehängte Marke. Das
 * Feld ist mit „50–60 Zeichen" beschriftet; würde hinter einem so ausgereizten Titel
 * noch „| Oldenhaus" erscheinen, wäre die Vorgabe im selben Moment gebrochen.
 *
 * @param array $teile Die Bestandteile des Titels.
 */
function oldenhaus_seo_titel_bauen( array $teile ): array {
	$eigener = oldenhaus_seo_feld( 'seo_titel' );

	if ( '' !== $eigener ) {
		return array( 'title' => $eigener );
	}

	if ( is_front_page() ) {
		return array( 'title' => oldenhaus_seo_startseitentitel() );
	}

	/*
	 * Der Untertitel der Website ist leer und soll es bleiben – stünde dort etwas,
	 * hinge es sonst zusätzlich in jedem Titel der Startseite.
	 */
	unset( $teile['tagline'] );

	$teile['site'] = oldenhaus_seo_marke();

	return $teile;
}
add_filter( 'document_title_parts', 'oldenhaus_seo_titel_bauen' );

/**
 * Das Trennzeichen zwischen Seitenname und Marke.
 *
 * WordPress nimmt ab Werk den Halbgeviertstrich „–". Der senkrechte Strich ist in
 * Suchergebnissen schmaler und damit sparsamer mit dem knappen Platz.
 */
add_filter( 'document_title_separator', fn(): string => '|' );

/* =========================================================================
 * 2 · Beschreibung
 * ====================================================================== */

/**
 * Der seitenbezogene Rückfalltext für die Beschreibung.
 *
 * Wichtig: Diese Funktion arbeitet allein mit einer Beitrags-ID und nicht mit den
 * Bedingungs-Funktionen (is_page() und Verwandte). Nur so lässt sie sich auch außerhalb
 * einer Seitenanfrage aufrufen – genau das tut tools/seo-texte-anlegen.php, um die
 * Entwürfe ins Backend zu schreiben. Die Texte stehen damit an genau einer Stelle und
 * können nicht auseinanderlaufen.
 *
 * Bewusst stehen in keinem dieser Texte Öffnungszeiten. Sie ändern sich, und eine
 * Beschreibung ist ein von Hand gepflegtes Feld – eine falsche Zeit im Suchergebnis
 * wäre schlechter als gar keine. Google liest die Zeiten ohnehin aus den
 * strukturierten Daten weiter unten, und die kommen live aus „Kontakt & Zeiten".
 */
function oldenhaus_seo_standard_beschreibung( int $seiten_id ): string {
	if ( $seiten_id <= 0 ) {
		return '';
	}

	if ( $seiten_id === (int) get_option( 'page_on_front' ) ) {
		return 'Italienische Küche und Pizza aus dem Ofen in Oldenswort, Dorfstraße 19. Im Gastraum essen oder alles zum Mitnehmen bestellen – reserviert wird per Telefon.';
	}

	switch ( (string) get_page_template_slug( $seiten_id ) ) {
		case 'template-speisekarte.php':
			return 'Vorspeisen, Pizza, Pasta, Fleisch, Fisch und Dessert: die ganze Karte des Oldenhaus in Oldenswort auf einer Seite. Alle Gerichte gibt es auch zum Mitnehmen.';

		case 'template-galerie.php':
			return 'Fotos aus dem Oldenhaus in Oldenswort: Eindrücke aus dem Gastraum und von dem, was bei uns auf den Teller kommt – Pizza, Pasta und italienische Vorspeisen.';

		case 'template-ueber-uns.php':
			return 'Wer im Oldenhaus in Oldenswort kocht und worauf es uns ankommt: italienische Küche, Pizza aus dem Ofen und ein Gastraum, in dem man sich noch kennt.';
	}

	if ( $seiten_id === (int) get_option( 'wp_page_for_privacy_policy' ) ) {
		return 'Wie das Oldenhaus mit personenbezogenen Daten umgeht: Diese Website lädt nichts von fremden Servern nach, misst kein Verhalten und kommt ohne Banner aus.';
	}

	if ( oldenhaus_seo_ist_impressum( $seiten_id ) ) {
		return 'Impressum des Oldenhaus – Restaurant und Pizzeria, Dorfstraße 19 in 25870 Oldenswort. Anbieterkennzeichnung, Kontakt und Verantwortung für diese Website.';
	}

	/*
	 * Für Seiten, die der Kunde später selbst anlegt, gibt es keinen vorbereiteten
	 * Text. Statt überall denselben Satz auszugeben – was Google als doppelte
	 * Beschreibung wertet – wird der Anfang des Seiteninhalts genommen. Ist der zu
	 * kurz, um etwas auszusagen, greift ein Satz, der wenigstens Lokal und Ort nennt.
	 */
	$seite = get_post( $seiten_id );

	if ( $seite instanceof WP_Post ) {
		$inhalt = trim( wp_strip_all_tags( strip_shortcodes( (string) $seite->post_content ) ) );

		if ( mb_strlen( $inhalt ) >= 80 ) {
			return wp_trim_words( $inhalt, 26, '…' );
		}
	}

	return sprintf(
		'%s – Oldenhaus, Restaurant und Pizzeria in Oldenswort, Dorfstraße 19.',
		get_the_title( $seiten_id )
	);
}

/**
 * Die Beschreibung der gerade angezeigten Seite.
 *
 * Reihenfolge: gepflegtes Feld, dann der seitenbezogene Rückfalltext, dann die beiden
 * Sonderfälle ohne Beitrag.
 */
function oldenhaus_seo_beschreibung(): string {
	$eigene = oldenhaus_seo_feld( 'seo_beschreibung' );

	if ( '' !== $eigene ) {
		return $eigene;
	}

	if ( is_search() ) {
		return 'Suchergebnisse auf der Website des Oldenhaus – Restaurant und Pizzeria in Oldenswort.';
	}

	if ( is_404() ) {
		return 'Diese Seite gibt es nicht. Zurück zur Startseite des Oldenhaus – Restaurant und Pizzeria in Oldenswort, Dorfstraße 19.';
	}

	return oldenhaus_seo_standard_beschreibung( oldenhaus_seo_seiten_id() );
}

/* =========================================================================
 * 3 · Robots
 * ====================================================================== */

/**
 * Ob eine Seite das Impressum ist.
 *
 * Über die Seiten-ID zu gehen wäre der bequeme Weg und der falsche: IDs stehen dann
 * fest im Code, und sobald der Kunde die Seite einmal löscht und neu anlegt, zeigt die
 * Regel ins Leere – ohne dass es jemandem auffiele, weil eine fehlende Robots-Angabe
 * nichts sichtbar kaputt macht.
 *
 * Deshalb zwei Wege: Der verlässliche ist das Häkchen „Von Suchmaschinen ausschließen"
 * an der Seite selbst (siehe Abschnitt 8) – das hängt am Beitrag und übersteht jedes
 * Umbenennen. Diese Funktion hier ist nur das Sicherheitsnetz für den Fall, dass eine
 * Rechtsseite neu angelegt und das Häkchen vergessen wurde.
 *
 * Für die Datenschutzerklärung braucht es das Netz nicht: Die führt WordPress selbst
 * in der Einstellung wp_page_for_privacy_policy.
 */
function oldenhaus_seo_ist_impressum( int $seiten_id ): bool {
	if ( $seiten_id <= 0 ) {
		return false;
	}

	$slug  = (string) get_post_field( 'post_name', $seiten_id );
	$titel = (string) get_the_title( $seiten_id );

	foreach ( array( 'impressum', 'anbieterkennzeichnung' ) as $begriff ) {
		if ( str_contains( $slug, $begriff ) || false !== mb_stripos( $titel, $begriff ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Ob die aktuelle Seite aus dem Suchindex herausgehalten werden soll.
 */
function oldenhaus_seo_nicht_indexieren(): bool {
	if ( is_404() ) {
		return true;
	}

	/*
	 * Ein Archiv ohne Einträge – etwa eine Kategorie, aus der das letzte Gericht
	 * entfernt wurde. WordPress liefert dafür eine 200er-Seite ohne Inhalt aus;
	 * indexiert gehört sie trotzdem nicht.
	 */
	if ( is_archive() && 0 === (int) $GLOBALS['wp_query']->found_posts ) {
		return true;
	}

	$seiten_id = oldenhaus_seo_seiten_id();

	if ( $seiten_id <= 0 ) {
		return false;
	}

	// Das Häkchen an der Seite.
	if ( '1' === oldenhaus_feld( 'seo_noindex', $seiten_id ) ) {
		return true;
	}

	if ( $seiten_id === (int) get_option( 'wp_page_for_privacy_policy' ) ) {
		return true;
	}

	return oldenhaus_seo_ist_impressum( $seiten_id );
}

/**
 * Ergänzt die Robots-Angaben des Kerns.
 *
 * Bewusst über den Kernfilter und nicht als eigenes Meta-Tag im wp_head: Zwei
 * Robots-Angaben auf derselben Seite wertet Google als die jeweils strengste – ein
 * versehentliches „noindex" neben einem „index" nähme die Seite aus dem Index, ohne
 * dass im Quelltext ein Fehler zu sehen wäre.
 *
 * Der Kern setzt vor uns bereits „noindex" für die Suche, für eingebettete Ansichten
 * und für eine im Backend gesperrte Indexierung (Einstellungen → Lesen). Das wird hier
 * nicht noch einmal nachgebaut, sondern erkannt und in Ruhe gelassen.
 *
 * @param array $robots Die bisherigen Angaben.
 */
function oldenhaus_seo_robots( array $robots ): array {
	if ( ! empty( $robots['noindex'] ) ) {
		return $robots;
	}

	if ( oldenhaus_seo_nicht_indexieren() ) {
		// „follow": Die Seite selbst soll nicht in den Index, ihre Links dürfen aber
		// verfolgt werden – sonst verlöre die Fußzeile ihren Wert für die Rechtsseiten.
		return array_merge( array( 'noindex' => true, 'follow' => true ), $robots );
	}

	return array_merge( array( 'index' => true, 'follow' => true ), $robots );
}
add_filter( 'wp_robots', 'oldenhaus_seo_robots', 20 );

/* =========================================================================
 * 4 · Open Graph
 * ====================================================================== */

/**
 * Das Bild für die Vorschau beim Teilen.
 *
 * Erst das Beitragsbild der Seite, sonst das erste Hero-Bild der Startseite. Ein
 * Rückfall auf ein festes Theme-Bild wäre hier falsch: Die Fotos sind Übergangsbilder
 * und werden nach dem Fototermin in der Mediathek getauscht – ein im Code hinterlegtes
 * Bild bliebe dabei stehen.
 */
function oldenhaus_seo_vorschaubild_id(): int {
	$seiten_id = oldenhaus_seo_seiten_id();

	if ( $seiten_id > 0 && has_post_thumbnail( $seiten_id ) ) {
		return (int) get_post_thumbnail_id( $seiten_id );
	}

	$startseite = (int) get_option( 'page_on_front' );

	return $startseite > 0 ? oldenhaus_bildfeld( 'hero_bild_1', $startseite ) : 0;
}

/**
 * Das Vorschaubild mit Adresse, Maßen und Alternativtext – oder null.
 *
 * Zur Bildgröße: Facebook, WhatsApp und Co. zeigen die große Kachel erst ab etwa
 * 1200 Pixeln Breite, darunter nur ein kleines Quadrat am Rand. „large" sind hier
 * 1024 Pixel und damit knapp zu wenig; „full" wären 2400 Pixel und mehrere hundert
 * Kilobyte, die jedes Mal mitgeladen werden. Dazwischen liegt die Größe, die
 * WordPress seit 5.3 automatisch anlegt: 1536 Pixel.
 *
 * Fehlt sie – etwa weil das Original kleiner war –, gibt WordPress stillschweigend
 * das Original zurück. Genau dafür ist die Prüfung auf die Breite da: Kommt etwas
 * unerwartet Großes heraus, wird doch die mittlere Größe genommen.
 *
 * @return array{url:string, breite:int, hoehe:int, alt:string}|null
 */
function oldenhaus_seo_vorschaubild(): ?array {
	$bild_id = oldenhaus_seo_vorschaubild_id();

	if ( $bild_id <= 0 ) {
		return null;
	}

	$bild = wp_get_attachment_image_src( $bild_id, '1536x1536' );

	if ( ! is_array( $bild ) || (int) $bild[1] > 2048 ) {
		$bild = wp_get_attachment_image_src( $bild_id, 'large' );
	}

	if ( ! is_array( $bild ) || empty( $bild[0] ) ) {
		return null;
	}

	return array(
		'url'    => (string) $bild[0],
		'breite' => (int) $bild[1],
		'hoehe'  => (int) $bild[2],
		'alt'    => trim( (string) get_post_meta( $bild_id, '_wp_attachment_image_alt', true ) ),
	);
}

/**
 * Gibt eine Meta-Angabe aus, sofern sie einen Wert hat.
 *
 * @param string $name      Der Name bzw. die Eigenschaft.
 * @param string $wert      Der Inhalt.
 * @param bool   $property  true für Open Graph („property"), false für „name".
 */
function oldenhaus_seo_meta( string $name, string $wert, bool $property = true ): void {
	if ( '' === trim( $wert ) ) {
		return;
	}

	printf(
		'<meta %s="%s" content="%s">' . "\n",
		$property ? 'property' : 'name',
		esc_attr( $name ),
		esc_attr( $wert )
	);
}

/* =========================================================================
 * 5 · Strukturierte Daten (Schema.org „Restaurant")
 * ====================================================================== */

/**
 * Zerlegt eine Zeitangabe wie „17–22 Uhr" in Anfang und Ende.
 *
 * Die Zeiten sind ein Freitextfeld – der Kunde soll dort „12–22 Uhr" oder
 * „17:30 - 23 Uhr" schreiben können, ohne ein Format zu lernen. Für
 * openingHoursSpecification braucht es daraus „HH:MM".
 *
 * Erkannt werden Halbgeviertstrich, Geviertstrich, Bindestrich und „bis" als Trenner
 * sowie Minuten mit Punkt oder Doppelpunkt. Was sich nicht sicher lesen lässt, fällt
 * weg. Das ist Absicht: Eine geratene Öffnungszeit stünde als harte Aussage in Google –
 * ein fehlender Tag fällt niemandem auf den Fuß.
 *
 * Mehrere Zeiträume an einem Tag werden einzeln zurückgegeben. „11.30–14 und 17–22 Uhr"
 * ergibt also zwei Einträge und nicht nur den Mittag. Das ist kein hypothetischer Fall:
 * Sobald der Business-Lunch geklärt ist (offener Punkt im Projekt), stehen genau solche
 * geteilten Zeiten im Feld.
 *
 * @return array<int, array{opens:string, closes:string}>
 */
function oldenhaus_seo_zeitspannen( string $text ): array {
	$treffer = array();

	if ( ! preg_match_all( '/(\d{1,2})(?:[.:](\d{2}))?\s*(?:–|—|-|bis)\s*(\d{1,2})(?:[.:](\d{2}))?/u', $text, $treffer, PREG_SET_ORDER ) ) {
		return array();
	}

	$spannen = array();

	foreach ( $treffer as $satz ) {
		$von_stunde = (int) $satz[1];
		$von_minute = isset( $satz[2] ) && '' !== $satz[2] ? (int) $satz[2] : 0;
		$bis_stunde = (int) $satz[3];
		$bis_minute = isset( $satz[4] ) && '' !== $satz[4] ? (int) $satz[4] : 0;

		if ( $von_stunde > 23 || $bis_stunde > 24 || $von_minute > 59 || $bis_minute > 59 ) {
			continue;
		}

		// Mitternacht als Ende: 24:00 ist als Uhrzeit gültig, wird von Google aber als
		// „bis 23:59 desselben Tages" gelesen. Genau so wird es hier auch geschrieben.
		if ( 24 === $bis_stunde ) {
			$bis_stunde = 23;
			$bis_minute = 59;
		}

		$von = sprintf( '%02d:%02d', $von_stunde, $von_minute );
		$bis = sprintf( '%02d:%02d', $bis_stunde, $bis_minute );

		/*
		 * Reicht die Angabe über Mitternacht („18–02 Uhr"), lässt sie sich in einer
		 * einzelnen openingHoursSpecification nicht richtig abbilden – dort stünde dann
		 * „öffnet 18:00, schließt 02:00 am selben Tag". Statt etwas Falsches zu
		 * behaupten, fällt der Zeitraum heraus.
		 */
		if ( $bis <= $von ) {
			continue;
		}

		$spannen[] = array(
			'opens'  => $von,
			'closes' => $bis,
		);
	}

	return $spannen;
}

/**
 * Die Öffnungszeiten in der Form, die Schema.org erwartet.
 *
 * Tage mit gleicher Zeit werden zusammengefasst – das ist kürzer und entspricht dem,
 * was die Suchmaschinen ohnehin daraus machen.
 */
function oldenhaus_seo_oeffnungszeiten_schema(): array {
	$englisch = array(
		'montag'     => 'Monday',
		'dienstag'   => 'Tuesday',
		'mittwoch'   => 'Wednesday',
		'donnerstag' => 'Thursday',
		'freitag'    => 'Friday',
		'samstag'    => 'Saturday',
		'sonntag'    => 'Sunday',
	);

	$gruppen = array();

	foreach ( oldenhaus_oeffnungszeiten() as $tag ) {
		// Ruhetage werden weggelassen, nicht mit einer Null-Zeit eingetragen: „nicht
		// aufgeführt" heißt in Schema.org bereits „geschlossen".
		if ( ! empty( $tag['ruhetag'] ) || ! isset( $englisch[ $tag['schluessel'] ] ) ) {
			continue;
		}

		foreach ( oldenhaus_seo_zeitspannen( (string) $tag['zeit'] ) as $spanne ) {
			$schluessel = $spanne['opens'] . '-' . $spanne['closes'];

			if ( ! isset( $gruppen[ $schluessel ] ) ) {
				$gruppen[ $schluessel ] = array(
					'@type'     => 'OpeningHoursSpecification',
					'dayOfWeek' => array(),
					'opens'     => $spanne['opens'],
					'closes'    => $spanne['closes'],
				);
			}

			$gruppen[ $schluessel ]['dayOfWeek'][] = $englisch[ $tag['schluessel'] ];
		}
	}

	return array_values( $gruppen );
}

/**
 * Baut die strukturierten Daten des Lokals zusammen.
 *
 * Grundsatz wie im ganzen Projekt: Was nicht vorliegt, wird weggelassen und nicht leer
 * ausgegeben. Ein `"telephone": ""` wäre schlechter als kein Telefonfeld – es behauptet
 * eine Angabe, die es nicht gibt, und kann eine Rich-Snippet-Prüfung durchfallen lassen.
 */
function oldenhaus_seo_restaurant_daten(): array {
	$adresse = oldenhaus_adresse();

	$daten = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Restaurant',
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
	);

	if ( '' !== $adresse['einzeilig'] ) {
		$anschrift = array( '@type' => 'PostalAddress' );

		if ( '' !== $adresse['strasse'] ) {
			$anschrift['streetAddress'] = $adresse['strasse'];
		}
		if ( '' !== $adresse['plz'] ) {
			$anschrift['postalCode'] = $adresse['plz'];
		}
		if ( '' !== $adresse['ort'] ) {
			$anschrift['addressLocality'] = $adresse['ort'];
		}

		$anschrift['addressCountry'] = 'DE';

		$daten['address'] = $anschrift;
	}

	if ( oldenhaus_hat_telefon() ) {
		$daten['telephone'] = oldenhaus_telefon_anzeige();
	}

	$zeiten = oldenhaus_seo_oeffnungszeiten_schema();

	if ( ! empty( $zeiten ) ) {
		$daten['openingHoursSpecification'] = $zeiten;
	}

	// Beides steht so auf der Startseite: „Italienische Küche und Pizza aus dem Ofen".
	$daten['servesCuisine'] = array( 'Italienisch', 'Pizza' );

	$speisekarte = oldenhaus_seo_seite_mit_vorlage( 'template-speisekarte.php' );

	if ( $speisekarte > 0 ) {
		$daten['hasMenu'] = (string) get_permalink( $speisekarte );
	}

	$instagram = oldenhaus_instagram();

	if ( '' !== $instagram ) {
		$daten['sameAs'] = array( $instagram );
	}

	$bild = oldenhaus_seo_vorschaubild();

	if ( null !== $bild ) {
		$daten['image'] = $bild['url'];
	}

	return $daten;
}

/* =========================================================================
 * 6 · Ausgabe im Kopfbereich
 * ====================================================================== */

/**
 * Gibt Beschreibung, Open Graph und strukturierte Daten aus.
 */
function oldenhaus_seo_kopfangaben(): void {
	$beschreibung = oldenhaus_seo_beschreibung();

	oldenhaus_seo_meta( 'description', $beschreibung, false );

	/*
	 * Open Graph nur dort, wo es etwas zu teilen gibt. Auf einer 404-Seite oder einer
	 * Suchergebnisliste zeigte og:url auf eine Adresse, hinter der nichts steht – wer
	 * den Link weiterschickte, verschickte eine Fehlerseite mit hübscher Vorschau.
	 */
	if ( ! is_singular() && ! is_front_page() ) {
		return;
	}

	// Dieselbe Adresse wie im Canonical: Die Startseite ist unter „/" zu erreichen und
	// nicht unter dem Adresszusatz der Seite, die als Startseite eingestellt ist.
	$seiten_id = oldenhaus_seo_seiten_id();
	$adresse   = ( is_front_page() || $seiten_id <= 0 )
		? home_url( '/' )
		: (string) get_permalink( $seiten_id );

	oldenhaus_seo_meta( 'og:type', 'website' );
	oldenhaus_seo_meta( 'og:locale', 'de_DE' );
	oldenhaus_seo_meta( 'og:site_name', get_bloginfo( 'name' ) );
	oldenhaus_seo_meta( 'og:title', wp_get_document_title() );
	oldenhaus_seo_meta( 'og:description', $beschreibung );
	oldenhaus_seo_meta( 'og:url', $adresse );

	$bild = oldenhaus_seo_vorschaubild();

	if ( null !== $bild ) {
		oldenhaus_seo_meta( 'og:image', $bild['url'] );
		oldenhaus_seo_meta( 'og:image:width', (string) $bild['breite'] );
		oldenhaus_seo_meta( 'og:image:height', (string) $bild['hoehe'] );
		oldenhaus_seo_meta( 'og:image:alt', $bild['alt'] );
	}

	if ( ! is_front_page() ) {
		return;
	}

	/*
	 * Zur Kodierung: JSON_HEX_TAG schreibt spitze Klammern als Unicode-Escapes statt
	 * als Zeichen. Damit kann kein Inhalt aus dem Backend – etwa ein Lokalname, in dem
	 * jemand ein schließendes script-Element unterbringt – das Element hier vorzeitig
	 * beenden und eigenen Code einschleusen. Das ist der Grund, warum die Ausgabe
	 * unten ohne esc_html() auskommt: Das Escaping steckt in der Kodierung.
	 *
	 * JSON_UNESCAPED_UNICODE hält Umlaute lesbar, JSON_UNESCAPED_SLASHES die Adressen.
	 */
	$json = wp_json_encode(
		oldenhaus_seo_restaurant_daten(),
		JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
	);

	if ( false === $json ) {
		return;
	}

	echo '<script type="application/ld+json">' . $json . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode() mit JSON_HEX_TAG, siehe Kommentar.
}
add_action( 'wp_head', 'oldenhaus_seo_kopfangaben', 5 );

/* =========================================================================
 * 7 · Sitemap und Sprachkennung
 * ====================================================================== */

/**
 * Liefert die Sitemap mit Status 200 statt 404 aus.
 *
 * Ein Fund, der ohne Prüfung per curl nicht aufgefallen wäre: /wp-sitemap.xml gab den
 * vollständigen, korrekten XML-Inhalt zurück – aber mit dem Status 404. Die Google
 * Search Console lehnt eine so ausgelieferte Sitemap ab, ohne sie überhaupt zu lesen.
 *
 * Die Ursache liegt in WP::handle_404(): Der Status wird gesetzt, bevor die Sitemap
 * überhaupt gerendert wird, und zwar allein danach, ob die Abfrage Beiträge gefunden
 * hat. Auf einer gewöhnlichen Website mit Blog liefert die Abfrage die letzten
 * Beiträge und alles ist gut. Diese Website hat bewusst keinen einzigen Beitrag –
 * damit ist die Liste leer und WordPress entscheidet auf 404. Die Sitemap-Klasse setzt
 * danach zwar $wp_query->is_404 zurück, den bereits gesendeten Statuscode holt sie
 * damit aber nicht mehr ein.
 *
 * pre_handle_404 ist der dafür vorgesehene Filter: Ein Rückgabewert ungleich false
 * überspringt die Statusermittlung vollständig, es bleibt beim 200.
 *
 * Wichtig ist dabei, nicht pauschal jede Sitemap-Adresse durchzuwinken. Beim ersten
 * Versuch tat dieser Filter genau das – und prompt beantwortete
 * /wp-sitemap-users-1.xml (der Anbieter darunter ist abgemeldet, siehe unten) die
 * Anfrage mit einem Status 200 und einer ganz normalen, indexierbaren HTML-Seite.
 * Aus einem 404 wäre so eine Fundstelle für Suchmaschinen geworden.
 *
 * Deshalb wird hier nachgesehen, ob es die angefragte Sitemap überhaupt gibt. Nur dann
 * bleibt es beim 200. Ist der Anbieter zwar vorhanden, seine Liste aber leer, setzt
 * WP_Sitemaps::render_sitemaps() später weiterhin selbst einen 404 – dieser Weg bleibt
 * unangetastet, weil er erst bei 'template_redirect' greift.
 *
 * @param bool     $vorentscheidung Rückgabewert vorheriger Filter.
 * @param WP_Query $abfrage         Die Hauptabfrage.
 * @return bool
 */
function oldenhaus_seo_sitemap_status( $vorentscheidung, WP_Query $abfrage ) {
	// Hat sich schon jemand anders entschieden, bleibt es dabei.
	if ( false !== $vorentscheidung || ! $abfrage->is_sitemap ) {
		return $vorentscheidung;
	}

	// Die beiden Stilvorlagen der Sitemap-Ansicht.
	if ( '' !== (string) $abfrage->get( 'sitemap-stylesheet' ) ) {
		return true;
	}

	$name = (string) $abfrage->get( 'sitemap' );

	if ( 'index' === $name ) {
		return true;
	}

	$server = function_exists( 'wp_sitemaps_get_server' ) ? wp_sitemaps_get_server() : null;

	if ( $server instanceof WP_Sitemaps && $server->registry->get_provider( $name ) ) {
		return true;
	}

	return $vorentscheidung;
}
add_filter( 'pre_handle_404', 'oldenhaus_seo_sitemap_status', 10, 2 );

/**
 * Nimmt die Benutzer aus der Sitemap.
 *
 * Aktuell liefert dieser Anbieter ohnehin nichts, weil es keine Beiträge gibt, an denen
 * ein Autor hängen könnte – die Sitemap enthält nur die sechs Seiten. Verlassen sollte
 * man sich darauf nicht: Sobald jemand einen Blogbeitrag anlegt, stünde der Anmeldename
 * der Redaktion in einer öffentlich abrufbaren XML-Datei. Genau das verhindern
 * ?author=1-Sperre, die geschlossene REST-Benutzerliste und die abgemeldete
 * oEmbed-Route in inc/sicherheit.php an anderer Stelle bereits.
 *
 * Anhänge müssen hier nicht ausgeschlossen werden: Die machen weder Gerichte noch
 * Fragen mit (beide sind mit 'public' => false registriert), und die Anhang-Seiten
 * selbst entfernt WordPress in WP_Sitemaps_Posts::get_object_subtypes() von sich aus.
 *
 * @param WP_Sitemaps_Provider|null $anbieter Der Anbieter.
 * @param string                    $name     Sein Name.
 */
function oldenhaus_seo_sitemap_ohne_benutzer( $anbieter, string $name ) {
	return 'users' === $name ? false : $anbieter;
}
add_filter( 'wp_sitemaps_add_provider', 'oldenhaus_seo_sitemap_ohne_benutzer', 10, 2 );

/**
 * Macht aus der Sprachkennung „de" die vollständige Form „de-DE".
 *
 * get_bloginfo( 'language' ) liefert hier „de", weil die deutsche Übersetzung den
 * Platzhalter html_lang_attribute bewusst auf das bloße Sprachkürzel setzt – dieselbe
 * Übersetzung wird schließlich in Deutschland, Österreich und der Schweiz benutzt.
 *
 * Diese Website richtet sich an genau eine Region. Die vollständige Kennung ist damit
 * die genauere Angabe und passt zum og:locale de_DE weiter oben. Beide Formen sind
 * gültig; wer lieber beim schlichten „de" bliebe, entfernt diesen einen Filter.
 *
 * Gebildet wird sie aus der eingestellten Sprache und nicht fest verdrahtet: Stellt
 * jemand die Website auf eine andere Sprache um, wandert die Kennung mit.
 *
 * Angesetzt wird am Filter 'language_attributes' und nicht an 'bloginfo'. Der
 * naheliegendere Weg über 'bloginfo' läuft ins Leere: get_bloginfo() wendet diesen
 * Filter nur an, wenn es mit $filter = 'display' aufgerufen wird –
 * get_language_attributes() fragt aber unformatiert ab.
 *
 * Der reguläre Ausdruck trifft lang="…" und xml:lang="…" gleichermaßen, weil vor dem
 * Doppelpunkt eine Wortgrenze liegt. Ein eventuelles dir="rtl" bleibt unberührt.
 *
 * @param string $ausgabe Die fertigen Attribute, etwa 'lang="de"'.
 */
function oldenhaus_seo_sprachkennung( $ausgabe ) {
	if ( ! is_string( $ausgabe ) ) {
		return $ausgabe;
	}

	$aus_locale = str_replace( '_', '-', get_locale() );

	// Nur die schlichte Form „xx-YY" übernehmen. Sprachen wie de_DE_formal ergäben
	// sonst „de-DE-formal" – das ist keine gültige Sprachkennung mehr.
	if ( ! preg_match( '/^([a-z]{2})-[A-Z]{2}$/', $aus_locale, $teile ) ) {
		return $ausgabe;
	}

	// Und nur dort ergänzen, wo wirklich bloß das Sprachkürzel steht.
	return (string) preg_replace(
		'/\blang="' . $teile[1] . '"/',
		'lang="' . $aus_locale . '"',
		$ausgabe
	);
}
add_filter( 'language_attributes', 'oldenhaus_seo_sprachkennung' );

/* =========================================================================
 * 8 · Die Felder im Backend
 * ====================================================================== */

/**
 * Registriert die Feldgruppe „Suchmaschinen (SEO)".
 *
 * Sie liegt bewusst hier und nicht in inc/acf-felder.php: Dort stehen Felder, die zu
 * genau einer Seitenvorlage gehören und ohne sie sinnlos wären. Diese drei Felder
 * gelten dagegen für jede Seite, unabhängig von der Vorlage, und werden ausschließlich
 * von dem Code gelesen, der zwanzig Zeilen weiter oben steht. Definition und Auswertung
 * in einer Datei heißt: eine Datei lesen, um zu verstehen, was das Häkchen bewirkt –
 * und eine Datei löschen, falls diese Website eines Tages doch ein SEO-Plugin bekommt.
 */
function oldenhaus_seo_felder_registrieren(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'        => 'group_oldenhaus_seo',
			'title'      => 'Suchmaschinen (SEO)',
			'location'   => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
				),
			),
			/*
			 * Kein 'hide_on_screen' – anders als bei den Vorlagen-Feldgruppen. Diese
			 * Gruppe erscheint auch auf Impressum und Datenschutzerklärung, und dort
			 * ist der normale Inhaltseditor genau das Feld, das gebraucht wird.
			 */
			'menu_order' => 20,
			'position'   => 'normal',
			'active'     => true,
			'fields'     => array(
				array(
					'key'      => 'field_oldenhaus_seo_hinweis',
					'label'    => '',
					'type'     => 'message',
					'message'  => 'Diese Angaben stehen nicht auf der Seite selbst, sondern im Suchergebnis bei Google – und in der Vorschau, wenn jemand den Link bei WhatsApp oder Instagram verschickt.<br><br>Beide Felder dürfen leer bleiben: Dann bildet die Website den Eintrag selbst aus Seitenname und Inhalt.',
					'esc_html' => 0,
				),
				array(
					'key'          => 'field_oldenhaus_seo_titel',
					'label'        => 'SEO-Titel',
					'name'         => 'seo_titel',
					'type'         => 'text',
					'instructions' => '50–60 Zeichen, wichtigstes Stichwort vorne. Leer lassen, dann heißt der Eintrag „Seitenname | Oldenhaus".',
				),
				array(
					'key'          => 'field_oldenhaus_seo_beschreibung',
					'label'        => 'SEO-Beschreibung',
					'name'         => 'seo_beschreibung',
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => '150–160 Zeichen, beschreibt die Seite und lädt zum Klicken ein. Keine Öffnungszeiten hineinschreiben – die holt sich Google direkt aus „Kontakt &amp; Zeiten".',
				),
				array(
					'key'          => 'field_oldenhaus_seo_noindex',
					'label'        => 'Von Suchmaschinen ausschließen',
					'name'         => 'seo_noindex',
					'type'         => 'true_false',
					'ui'           => 1,
					'ui_on_text'   => 'Ausgeschlossen',
					'ui_off_text'  => 'Wird gefunden',
					'instructions' => 'Die Seite bleibt für Gäste ganz normal erreichbar, taucht aber nicht mehr in Suchergebnissen auf. Sinnvoll für Impressum und Datenschutzerklärung – nicht für Seiten, über die Gäste euch finden sollen.',
				),
			),
		)
	);
}
add_action( 'acf/init', 'oldenhaus_seo_felder_registrieren' );
