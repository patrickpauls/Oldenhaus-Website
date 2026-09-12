<?php
/**
 * Legt Beispielgerichte und die Fragen des FAQ-Abschnitts an.
 *
 * Ausfuehren mit WP-CLI:
 *     wp eval-file tools/beispielinhalte-anlegen.php
 *
 * Die Gerichte sind als Beispiele gekennzeichnet (Meta _oldenhaus_beispiel). Das
 * Dashboard weist darauf hin, solange welche vorhanden sind - der Hinweis verschwindet
 * von selbst, sobald sie durch die echte Karte ersetzt wurden.
 *
 * Die Fragen 1 bis 4 werden bewusst als ENTWURF angelegt: Ob Hunde erlaubt sind oder
 * ob man mit Karte zahlen kann, weiss ausser dem Wirt niemand. Als Entwurf sind sie
 * im Frontend unsichtbar, bis die Aussage bestaetigt und veroeffentlicht wurde.
 * Frage 5 ist beantwortet und deshalb veroeffentlicht.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Dieses Skript laeuft nur ueber WP-CLI.\n" );
}

/* -------------------------------------------------------------------------
 * Kategorien
 * ---------------------------------------------------------------------- */

WP_CLI::log( 'Kategorien der Speisekarte' );

$kategorien = array(
	'vorspeisen' => array( 'Vorspeisen', 10, 'Zum Ankommen und Teilen.' ),
	'pizza'      => array( 'Pizza', 20, 'Aus unserem Ofen, auf Wunsch auch zum Mitnehmen.' ),
	'pasta'      => array( 'Pasta', 30, '' ),
	'dessert'    => array( 'Dessert', 40, 'Falls doch noch ein Eckchen frei ist.' ),
	'getraenke'  => array( 'Getränke', 90, '' ),
);

$kategorie_ids = array();

foreach ( $kategorien as $slug => $angaben ) {
	list( $name, $reihenfolge, $einleitung ) = $angaben;

	$vorhanden = get_term_by( 'slug', $slug, 'restaurant_gericht_kategorie' );

	if ( $vorhanden instanceof WP_Term ) {
		$term_id = (int) $vorhanden->term_id;
		WP_CLI::log( "  vorhanden: {$name}" );
	} else {
		$ergebnis = wp_insert_term( $name, 'restaurant_gericht_kategorie', array( 'slug' => $slug ) );

		if ( is_wp_error( $ergebnis ) ) {
			WP_CLI::warning( "Kategorie '{$name}': " . $ergebnis->get_error_message() );
			continue;
		}

		$term_id = (int) $ergebnis['term_id'];
		WP_CLI::log( "  angelegt: {$name}" );
	}

	$kategorie_ids[ $slug ] = $term_id;

	if ( function_exists( 'update_field' ) ) {
		update_field( 'field_restaurant_kategorie_reihenfolge', $reihenfolge, 'term_' . $term_id );

		if ( '' !== $einleitung ) {
			update_field( 'field_restaurant_kategorie_einleitung', $einleitung, 'term_' . $term_id );
		}
	}
}

/* -------------------------------------------------------------------------
 * Gerichte
 * ---------------------------------------------------------------------- */

WP_CLI::log( 'Beispielgerichte' );

// Name, Kategorie, Preis, Beschreibung, vegetarisch, vegan
$gerichte = array(
	array( 'Bruschetta', 'vorspeisen', '5,50 €', 'Geröstetes Brot, Tomaten, Knoblauch, frisches Basilikum', true, false ),
	array( 'Insalata mista', 'vorspeisen', '6,50 €', 'Gemischter Blattsalat, Tomate, Gurke, Hausdressing', true, true ),

	array( 'Margherita', 'pizza', '9,50 €', 'Tomatensoße, Mozzarella, frisches Basilikum', true, false ),
	array( 'Salami', 'pizza', '11,00 €', 'Tomatensoße, Mozzarella, italienische Salami', false, false ),
	array( 'Prosciutto e Funghi', 'pizza', '12,00 €', 'Tomatensoße, Mozzarella, Kochschinken, Champignons', false, false ),
	array( 'Verdure', 'pizza', '12,50 €', 'Tomatensoße, gegrilltes Gemüse, Rucola, ohne Käse', true, true ),

	array( 'Spaghetti Aglio e Olio', 'pasta', '9,50 €', 'Knoblauch, Olivenöl, Peperoncino, Petersilie', true, true ),
	array( 'Tagliatelle al Ragù', 'pasta', '12,50 €', 'Rinderragout, das lange geschmort hat, dazu Parmesan', false, false ),

	array( 'Tiramisu', 'dessert', '5,50 €', 'Hausgemacht, mit Espresso und Mascarpone', true, false ),

	array( 'Apfelschorle 0,3 l', 'getraenke', '3,20 €', '', false, false ),
	array( 'Hauswein weiß 0,2 l', 'getraenke', '5,50 €', '', false, false ),
	array( 'Espresso', 'getraenke', '2,40 €', '', false, false ),
);

$reihenfolge_je_kategorie = array();

foreach ( $gerichte as $gericht ) {
	list( $name, $kategorie_slug, $preis, $beschreibung, $vegetarisch, $vegan ) = $gericht;

	if ( ! isset( $kategorie_ids[ $kategorie_slug ] ) ) {
		continue;
	}

	// Nicht doppelt anlegen, falls das Skript erneut laeuft.
	$vorhandene = get_posts(
		array(
			'post_type'      => 'restaurant_gericht',
			'post_status'    => 'any',
			'title'          => $name,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $vorhandene ) ) {
		WP_CLI::log( "  vorhanden: {$name}" );
		continue;
	}

	$reihenfolge_je_kategorie[ $kategorie_slug ] = ( $reihenfolge_je_kategorie[ $kategorie_slug ] ?? 0 ) + 10;

	$gericht_id = wp_insert_post(
		array(
			'post_type'   => 'restaurant_gericht',
			'post_title'  => $name,
			'post_status' => 'publish',
			'menu_order'  => $reihenfolge_je_kategorie[ $kategorie_slug ],
		),
		true
	);

	if ( is_wp_error( $gericht_id ) ) {
		WP_CLI::warning( "Gericht '{$name}': " . $gericht_id->get_error_message() );
		continue;
	}

	wp_set_object_terms( $gericht_id, array( $kategorie_ids[ $kategorie_slug ] ), 'restaurant_gericht_kategorie' );

	if ( function_exists( 'update_field' ) ) {
		update_field( 'field_restaurant_gericht_preis', $preis, $gericht_id );
		update_field( 'field_restaurant_gericht_beschreibung', $beschreibung, $gericht_id );
		update_field( 'field_restaurant_gericht_vegetarisch', $vegetarisch ? 1 : 0, $gericht_id );
		update_field( 'field_restaurant_gericht_vegan', $vegan ? 1 : 0, $gericht_id );
		update_field( 'field_restaurant_gericht_verfuegbar', 1, $gericht_id );
	}

	// Kennzeichnung als Beispiel - das Dashboard weist darauf hin, solange
	// noch Beispiele vorhanden sind.
	update_post_meta( $gericht_id, '_oldenhaus_beispiel', '1' );

	WP_CLI::log( "  angelegt: {$name}" );
}

/* -------------------------------------------------------------------------
 * Fragen und Antworten
 * ---------------------------------------------------------------------- */

WP_CLI::log( 'Fragen und Antworten' );

$entwurfs_hinweis = "ENTWURF – bitte prüfen, ob das so stimmt, danach diese erste Zeile löschen und die Frage veröffentlichen.\n\n";

// Frage, Antwort, veroeffentlichen?
$fragen = array(
	array(
		'Sind Hunde willkommen?',
		$entwurfs_hinweis . 'Klar, dein Hund darf mit rein. An der Leine und mit einem Napf Wasser neben dem Tisch fühlt er sich bei uns meistens ganz wohl.',
		false,
	),
	array(
		'Gibt es auch was für den kleinen Hunger?',
		$entwurfs_hinweis . 'Ja. Wenn es kein ganzes Essen sein soll, findest du bei den Vorspeisen und Salaten etwas Kleineres. Sag einfach Bescheid, wir beraten dich gern.',
		false,
	),
	array(
		'Kann man mit Karte zahlen?',
		$entwurfs_hinweis . 'Ja, mit EC- und Kreditkarte. Bar geht natürlich auch.',
		false,
	),
	array(
		'Darf man auch nur was trinken?',
		$entwurfs_hinweis . 'Aber sicher. Auf ein Glas Wein oder einen Espresso bist du bei uns genauso willkommen wie zum Essen – auch ohne vorher zu reservieren.',
		false,
	),
	array(
		'Kann man bei euch parken?',
		'Ja, und zwar kostenlos. Direkt bei uns ist Platz, du musst also nicht erst eine Runde ums Dorf drehen.',
		true,
	),
);

foreach ( $fragen as $position => $frage ) {
	list( $titel, $antwort, $veroeffentlichen ) = $frage;

	$vorhandene = get_posts(
		array(
			'post_type'      => 'restaurant_faq',
			'post_status'    => 'any',
			'title'          => $titel,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $vorhandene ) ) {
		WP_CLI::log( "  vorhanden: {$titel}" );
		continue;
	}

	$frage_id = wp_insert_post(
		array(
			'post_type'   => 'restaurant_faq',
			'post_title'  => $titel,
			'post_status' => $veroeffentlichen ? 'publish' : 'draft',
			'menu_order'  => ( $position + 1 ) * 10,
		),
		true
	);

	if ( is_wp_error( $frage_id ) ) {
		WP_CLI::warning( "Frage '{$titel}': " . $frage_id->get_error_message() );
		continue;
	}

	if ( function_exists( 'update_field' ) ) {
		update_field( 'field_restaurant_faq_antwort', $antwort, $frage_id );
	}

	WP_CLI::log( '  angelegt: ' . $titel . ( $veroeffentlichen ? ' (veröffentlicht)' : ' (Entwurf)' ) );
}

WP_CLI::success( 'Beispielinhalte stehen.' );
