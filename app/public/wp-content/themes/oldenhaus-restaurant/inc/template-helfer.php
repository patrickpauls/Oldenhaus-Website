<?php
/**
 * Hilfsfunktionen für die Templates.
 *
 * Die Zugriffe auf das Plugin „Restaurant-Basis" laufen bewusst über dünne Wrapper.
 * Wäre das Plugin einmal nicht aktiv – etwa direkt nach einem Umzug, bevor es wieder
 * eingeschaltet wurde –, liefe das Theme sonst in einen Fatal Error. So zeigt die
 * Website stattdessen einfach den jeweiligen Abschnitt nicht an.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Zugriff auf die Inhalte aus dem Plugin
 * ---------------------------------------------------------------------- */

/**
 * Ob das Plugin „Restaurant-Basis" bereitsteht.
 */
function oldenhaus_inhalte_verfuegbar(): bool {
	return function_exists( 'restaurant_basis_telefon_link' );
}

/**
 * Die Telefonnummer in ihrer Anzeigeform.
 */
function oldenhaus_telefon_anzeige(): string {
	return oldenhaus_inhalte_verfuegbar() ? restaurant_basis_telefon_anzeige() : '';
}

/**
 * Die Telefonnummer als tel:-Link, oder leer, wenn keine hinterlegt ist.
 */
function oldenhaus_telefon_link(): string {
	return oldenhaus_inhalte_verfuegbar() ? restaurant_basis_telefon_link() : '';
}

/**
 * Ob eine nutzbare Telefonnummer vorliegt.
 */
function oldenhaus_hat_telefon(): bool {
	return oldenhaus_inhalte_verfuegbar() && restaurant_basis_hat_telefon();
}

/**
 * Die Anschrift.
 *
 * @return array{strasse:string, plz:string, ort:string, ort_zeile:string, einzeilig:string}
 */
function oldenhaus_adresse(): array {
	if ( oldenhaus_inhalte_verfuegbar() ) {
		return restaurant_basis_adresse();
	}

	return array(
		'strasse'   => '',
		'plz'       => '',
		'ort'       => '',
		'ort_zeile' => '',
		'einzeilig' => '',
	);
}

/**
 * Die Öffnungszeiten der Woche, Montag zuerst.
 */
function oldenhaus_oeffnungszeiten(): array {
	return oldenhaus_inhalte_verfuegbar() ? restaurant_basis_oeffnungszeiten() : array();
}

/**
 * Die Sonderöffnungszeiten, oder null, wenn keine eingetragen sind.
 */
function oldenhaus_sonderzeiten(): ?array {
	return oldenhaus_inhalte_verfuegbar() ? restaurant_basis_sonderzeiten() : null;
}

/**
 * Die Instagram-Adresse.
 */
function oldenhaus_instagram(): string {
	return oldenhaus_inhalte_verfuegbar() ? restaurant_basis_instagram() : '';
}

/**
 * Liest ein ACF-Feld, ohne auf ACF angewiesen zu sein.
 *
 * Fehlt ACF, greift der Rückfallwert aus den normalen Metadaten. So bleiben bereits
 * gepflegte Inhalte sichtbar, statt spurlos zu verschwinden.
 */
function oldenhaus_feld( string $name, $beitrag_id = null, string $standard = '' ): string {
	$beitrag_id = $beitrag_id ?? get_the_ID();

	if ( ! $beitrag_id ) {
		return $standard;
	}

	$wert = function_exists( 'get_field' )
		? get_field( $name, $beitrag_id )
		: get_post_meta( $beitrag_id, $name, true );

	if ( is_array( $wert ) || is_object( $wert ) ) {
		return $standard;
	}

	$wert = trim( (string) $wert );

	return '' === $wert ? $standard : $wert;
}

/**
 * Liest ein ACF-Bildfeld und gibt die Anhang-ID zurück.
 *
 * Die Feldgruppen sind auf „ID" als Rückgabeformat eingestellt; der zusätzliche
 * Umgang mit Array und Objekt fängt ab, falls jemand das später umstellt.
 */
function oldenhaus_bildfeld( string $name, $beitrag_id = null ): int {
	$beitrag_id = $beitrag_id ?? get_the_ID();

	if ( ! $beitrag_id ) {
		return 0;
	}

	$wert = function_exists( 'get_field' )
		? get_field( $name, $beitrag_id )
		: get_post_meta( $beitrag_id, $name, true );

	if ( is_array( $wert ) ) {
		$wert = $wert['ID'] ?? 0;
	} elseif ( is_object( $wert ) ) {
		$wert = $wert->ID ?? 0;
	}

	return (int) $wert;
}

/* -------------------------------------------------------------------------
 * Wiederkehrende Bausteine
 * ---------------------------------------------------------------------- */

/**
 * Das Telefonhörer-Symbol.
 *
 * Inline statt als Bilddatei: ein einzelnes Symbol als eigene Datei wäre eine
 * zusätzliche Anfrage, und so übernimmt es automatisch die Textfarbe.
 */
function oldenhaus_telefon_symbol(): string {
	return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1L6.6 10.8z"/></svg>';
}

/**
 * Das Instagram-Symbol.
 *
 * Gebaut wie oldenhaus_telefon_symbol(): inline im HTML, kein Icon-Font, keine
 * Bilddatei und vor allem nichts von einem fremden Server. Ein nachgeladenes
 * Symbol-Set wäre genau die Anfrage, wegen der die Website sonst ein
 * Einwilligungsbanner bräuchte – für ein einziges Zeichen.
 *
 * Anders als der Telefonhörer ist dies eine Kontur und keine Fläche: Das
 * Instagram-Zeichen besteht aus Rahmen, Kreis und Punkt und bliebe als Fläche
 * bei 17 Pixeln ein unlesbarer Klecks. `currentColor` an Strich und Füllung
 * sorgt dafür, dass es die Farbe des Links übernimmt – auch im Hover.
 *
 * aria-hidden, weil direkt daneben „Instagram" steht: Eine Vorlesehilfe soll
 * das Ziel einmal nennen und nicht zweimal.
 */
function oldenhaus_instagram_symbol(): string {
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
		. '<rect x="3" y="3" width="18" height="18" rx="5.2"/>'
		. '<circle cx="12" cy="12" r="4.1"/>'
		. '<circle cx="17.3" cy="6.7" r="1.15" fill="currentColor" stroke="none"/>'
		. '</svg>';
}

/**
 * Gibt den Anruf-Button aus.
 *
 * Ist noch keine Telefonnummer hinterlegt, erscheint bewusst kein Link, sondern ein
 * sichtbarer Hinweis. Ein Button, der ins Leere führt, wäre für Gäste schlechter als
 * ein ehrlicher Platzhalter – und im Backend fällt die Lücke so sofort auf.
 *
 * @param array{beschriftung?:string, klassen?:string, symbol?:bool} $argumente Optionen.
 */
function oldenhaus_anruf_button( array $argumente = array() ): void {
	$argumente = array_merge(
		array(
			'beschriftung' => 'Tisch reservieren',
			'klassen'      => 'btn',
			'symbol'       => true,
		),
		$argumente
	);

	$symbol = $argumente['symbol'] ? oldenhaus_telefon_symbol() : '';

	if ( ! oldenhaus_hat_telefon() ) {
		printf(
			'<span class="%s btn--ohne-nummer">%s Telefonnummer folgt</span>',
			esc_attr( $argumente['klassen'] ),
			$symbol // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- feste Zeichenkette aus oldenhaus_telefon_symbol().
		);

		return;
	}

	printf(
		'<a class="%s" href="%s">%s <span>%s</span></a>',
		esc_attr( $argumente['klassen'] ),
		esc_url( oldenhaus_telefon_link() ),
		$symbol, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- feste Zeichenkette aus oldenhaus_telefon_symbol().
		esc_html( $argumente['beschriftung'] )
	);
}

/**
 * Gibt die Wortmarke aus – oder das Logo, sobald eines hochgeladen wurde.
 *
 * Die Spec verlangt, dass das spätere Logo ohne Code-Änderung eingesetzt werden kann.
 * Genau das passiert hier: Lädt der Kunde im Customizer ein Logo hoch, ersetzt es
 * automatisch den Schriftzug.
 */
function oldenhaus_wortmarke(): void {
	$startseite = home_url( '/' );

	if ( has_custom_logo() ) {
		$logo_id = (int) get_theme_mod( 'custom_logo' );

		printf(
			'<a class="wortmarke wortmarke--logo" href="%s" rel="home">%s</a>',
			esc_url( $startseite ),
			wp_get_attachment_image( $logo_id, 'full', false, array( 'alt' => esc_attr( get_bloginfo( 'name' ) ) ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() escaped selbst.
		);

		return;
	}

	printf(
		'<a class="wortmarke" href="%s" rel="home">Oldenhaus<small>Restaurant · Pizzeria</small></a>',
		esc_url( $startseite )
	);
}

/**
 * Gibt ein Bild aus – oder eine Platzhalterfläche, wenn keines gesetzt ist.
 *
 * Die Platzhalterfläche stammt aus dem Moodboard: warmer Holzton mit einer Beschriftung,
 * was an dieser Stelle hingehört. Das ist bewusst so gebaut, damit das Layout nicht
 * zusammenfällt, wenn der Kunde ein Bild entfernt, und damit sofort sichtbar ist,
 * wo noch ein Foto fehlt.
 *
 * @param int    $bild_id      Anhang-ID, 0 für keinen Eintrag.
 * @param string $groesse      Bildgröße.
 * @param string $hinweis      Was an dieser Stelle stehen soll, falls das Bild fehlt.
 * @param array  $klassen      Zusätzliche CSS-Klassen für den umgebenden Rahmen.
 */
function oldenhaus_foto( int $bild_id, string $groesse, string $hinweis, array $klassen = array() ): void {
	$klassen_text = implode( ' ', array_map( 'sanitize_html_class', $klassen ) );

	if ( $bild_id > 0 && wp_attachment_is_image( $bild_id ) ) {
		printf(
			'<figure class="foto %s">%s</figure>',
			esc_attr( $klassen_text ),
			wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() escaped selbst.
				$bild_id,
				$groesse,
				false,
				array(
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			)
		);

		return;
	}

	printf(
		'<div class="foto ph %s"><span>%s</span></div>',
		esc_attr( $klassen_text ),
		esc_html( $hinweis )
	);
}

/**
 * Gibt einen Text aus – oder einen sichtbaren Hinweis, dass er noch fehlt.
 *
 * Bewusst kein Blindtext: Fehlender Inhalt soll als solcher erkennbar sein, damit er
 * nicht versehentlich mit veröffentlicht wird.
 */
function oldenhaus_text_oder_hinweis( string $text, string $hinweis ): void {
	if ( '' !== trim( $text ) ) {
		echo wp_kses_post( wpautop( $text ) );

		return;
	}

	printf( '<p class="fehlt">%s</p>', esc_html( $hinweis ) );
}

/**
 * Ob gerade die Startseite mit dem Hero angezeigt wird.
 *
 * Steuert, ob der Header durchsichtig startet oder sofort im festen Zustand steht.
 */
function oldenhaus_hat_hero(): bool {
	return is_front_page();
}

/* -------------------------------------------------------------------------
 * Galerie
 * ---------------------------------------------------------------------- */

/**
 * Lässt Galeriebilder auf die Bilddatei verweisen statt auf eine Anhang-Seite.
 *
 * WordPress verlinkt Galeriebilder ab Werk auf eine eigene Anhang-Seite. Für diese
 * Website wäre das doppelt ungünstig: Es gibt keine Vorlage dafür, und es entstünden
 * dutzende dünne Unterseiten. Mit dem Verweis direkt auf die Datei greift stattdessen
 * die Lightbox – und ohne JavaScript öffnet sich schlicht das Bild.
 */
function oldenhaus_galerie_auf_datei_verlinken( array $werte ): array {
	$werte['link'] = 'file';

	/*
	 * Bildgroesse ebenfalls festlegen. Ohne diese Zeile nimmt WordPress die
	 * Vorschaugroesse: 150 x 150 Pixel, hart quadratisch beschnitten. Das Stylesheet
	 * zieht sie anschliessend auf Spaltenbreite auf - sichtbar unscharf, und die
	 * Mauerwerk-Optik waere unmoeglich, weil alle Kacheln gleich hoch waeren.
	 *
	 * "large" behaelt das Seitenverhaeltnis bei, liefert genug Pixel fuer hohe
	 * Bildschirmaufloesungen und bringt srcset mit. Bewusst hier erzwungen und nicht
	 * dem Kunden ueberlassen: Im Galerie-Dialog ist "Vorschaubild" die Voreinstellung,
	 * und niemand soll sich die Galerie mit einem Klick zerschiessen koennen.
	 */
	$werte['size'] = 'large';

	return $werte;
}
add_filter( 'shortcode_atts_gallery', 'oldenhaus_galerie_auf_datei_verlinken' );

/**
 * Leitet Anhang-Seiten auf die Startseite um.
 *
 * Ergänzt die Regel oben für Bilder, die auf anderem Weg verlinkt wurden. Anhang-Seiten
 * haben keinen eigenen Inhalt und sollen weder aufrufbar noch auffindbar sein.
 */
function oldenhaus_anhangseiten_umleiten(): void {
	if ( is_attachment() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'oldenhaus_anhangseiten_umleiten' );

/**
 * Erlaubte HTML-Auszeichnung für Inhalte, die Bilder enthalten.
 *
 * wp_kses_post() streicht `srcset` und `sizes` aus img-Elementen. Bei der Galerie
 * bedeutete das: WordPress erzeugt die responsiven Bildquellen korrekt, und das
 * Escaping wirft sie anschließend weg – jedes Gerät lädt dann dieselbe große Datei.
 *
 * Statt auf das Escaping zu verzichten, wird die Liste hier um genau die Attribute
 * erweitert, die WordPress selbst ausgibt. Alles andere bleibt gefiltert.
 */
function oldenhaus_erlaubtes_html_mit_bildern(): array {
	$erlaubt = wp_kses_allowed_html( 'post' );

	$erlaubt['img'] = array_merge(
		$erlaubt['img'] ?? array(),
		array(
			'srcset'        => true,
			'sizes'         => true,
			'loading'       => true,
			'decoding'      => true,
			'fetchpriority' => true,
		)
	);

	return $erlaubt;
}
