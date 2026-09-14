<?php
/**
 * Legt die Speisekarte des Kunden an: Kategorien und Gerichte.
 *
 * Ausfuehren mit WP-CLI:
 *     wp eval-file tools/speisekarte-anlegen.php
 *
 * Quelle ist die vom Kunden gelieferte Karte. Das Skript ersetzt die beim Aufbau
 * angelegten Beispielgerichte: Alles, was die Kennzeichnung _oldenhaus_beispiel
 * traegt, wird geloescht, bevor die echten Gerichte entstehen. Damit verschwindet
 * auch der Hinweis im Dashboard von selbst.
 *
 * Das Skript ist wiederholbar. Ein Gericht wird ueber seinen Namen wiedergefunden
 * und dann aktualisiert, nicht ein zweites Mal angelegt - eine Preisaenderung in
 * dieser Datei kommt also durch einen erneuten Lauf in der Datenbank an.
 *
 * Die Nummern der gedruckten Karte stehen in menu_order. Sie werden im Frontend
 * nicht ausgegeben, sortieren die Gerichte aber genau so, wie der Kunde sie kennt,
 * und lassen Luecken fuer spaetere Ergaenzungen.
 *
 * Bewusst NICHT uebernommen: die Allergenkennzeichnung. Dafuer fehlt bislang eine
 * Darstellung, die auf dem Handy lesbar bleibt - Buchstabenkuerzel ohne Legende in
 * Sichtweite helfen niemandem. Solange das nicht geklaert ist, steht sie nirgends
 * halb fertig herum.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Dieses Skript laeuft nur ueber WP-CLI.\n" );
}

/* -------------------------------------------------------------------------
 * Beispielgerichte entfernen
 * ---------------------------------------------------------------------- */

WP_CLI::log( 'Beispielgerichte entfernen' );

$beispiele = get_posts(
	array(
		'post_type'      => 'restaurant_gericht',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'meta_key'       => '_oldenhaus_beispiel',
		'meta_value'     => '1',
	)
);

if ( empty( $beispiele ) ) {
	WP_CLI::log( '  keine vorhanden' );
}

foreach ( $beispiele as $beispiel ) {
	// Endgueltig loeschen statt in den Papierkorb: Im Papierkorb traegt das
	// Gericht die Kennzeichnung weiter und der Dashboard-Hinweis bliebe stehen.
	wp_delete_post( $beispiel->ID, true );
	WP_CLI::log( "  geloescht: {$beispiel->post_title}" );
}

/* -------------------------------------------------------------------------
 * Kategorien
 * ---------------------------------------------------------------------- */

WP_CLI::log( 'Kategorien der Speisekarte' );

// Slug => Name, Reihenfolge, Einleitungssatz
$kategorien = array(
	'vorspeisen'  => array( 'Vorspeisen', 10, 'Zum Ankommen und Teilen.' ),
	'suppen'      => array( 'Suppen', 20, '' ),
	'salate'      => array( 'Salate', 30, '' ),
	'pizza'       => array( 'Pizza', 40, 'Aus unserem Ofen, auf Wunsch auch zum Mitnehmen.' ),
	'pasta'       => array( 'Pasta', 50, '' ),
	'fleisch'     => array( 'Fleisch', 60, '' ),
	'fisch'       => array( 'Fisch', 70, '' ),
	'kindermenue' => array( 'Kindermenü', 80, '' ),
	'dessert'     => array( 'Dessert', 90, 'Falls doch noch ein Eckchen frei ist.' ),
	// Die Getraenkekarte liegt noch nicht vor. Die Kategorie bleibt leer und
	// damit im Frontend unsichtbar (restaurant_basis_kategorien: hide_empty).
	'getraenke'   => array( 'Getränke', 100, '' ),
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

		// Ein leerer Einleitungssatz wuerde einen bereits gepflegten ueberschreiben.
		if ( '' !== $einleitung ) {
			update_field( 'field_restaurant_kategorie_einleitung', $einleitung, 'term_' . $term_id );
		}
	}
}

/* -------------------------------------------------------------------------
 * Gerichte
 * ---------------------------------------------------------------------- */

WP_CLI::log( 'Gerichte' );

// Nummer (= menu_order), Name, Kategorie, Preis, Beschreibung
$gerichte = array(
	array( 1, 'Klassische Bruschetta', 'vorspeisen', '7,50 €', 'Geröstetes Brot mit Tomaten, Schafskäse, Knoblauch und Olivenöl' ),
	array( 2, 'Caprese mit Büffelmozzarella', 'vorspeisen', '12,50 €', 'Tomaten mit Büffelmozzarella, Balsamico und Olivenöl' ),
	array( 3, 'Rindercarpaccio', 'vorspeisen', '14,50 €', 'Dünn geschnittenes Rinderfleisch mit Rucola und Parmesan' ),

	array( 5, 'Tomatensuppe', 'suppen', '6,50 €', 'Warme Suppe aus frischen Tomaten mit Basilikum' ),
	array( 6, 'Krabbensuppe', 'suppen', '9,50 €', 'Cremige Suppe mit Krabben' ),

	array( 10, 'Kleiner Salat', 'salate', '4,50 €', 'Gemischter Salat, Cherrytomaten und Gurke, Honig-Balsamico-Dressing' ),
	array( 11, 'Großer gemischter Salat', 'salate', '13,50 €', 'Gemischter Salat mit Büffelmozzarella, Cherrytomaten und Gurke, Honig-Balsamico-Dressing' ),
	array( 12, 'Caesar-Salat', 'salate', '13,90 €', 'Römersalat mit gegrillten Putenstreifen, Parmesan, Croutons, Cherrytomaten, Mais und Caesar-Dressing' ),
	array( 13, 'Bauernsalat', 'salate', '9,50 €', 'Tomate, Gurke, Zwiebel, Paprika, Oliven und Schafskäse' ),

	array( 20, 'Pizza Margherita', 'pizza', '8,50 €', 'Tomatensauce, Mozzarella, Basilikum' ),
	array( 21, 'Pizza Salami', 'pizza', '9,50 €', 'Tomatensauce, Mozzarella, Salami' ),
	array( 22, 'Pizza Salami-Prosciutto', 'pizza', '9,80 €', 'Tomatensauce, Mozzarella, Salami, Schinken' ),
	array( 23, 'Pizza Prosciutto-Funghi', 'pizza', '9,80 €', 'Tomatensauce, Mozzarella, Schinken, Champignons' ),
	array( 24, 'Pizza Hawaii', 'pizza', '9,80 €', 'Tomatensauce, Mozzarella, Schinken, Ananas' ),
	array( 25, 'Pizza Tonno', 'pizza', '12,00 €', 'Tomatensauce, Mozzarella, Thunfisch, Zwiebeln' ),
	array( 26, 'Pizza Quattro Formaggi', 'pizza', '12,50 €', 'Vier verschiedene Käsesorten' ),
	array( 27, 'Pizza Parma', 'pizza', '14,50 €', 'Tomatensauce, Mozzarella, Parmaschinken, Rucola' ),
	array( 28, 'Pizza Popeye', 'pizza', '12,50 €', 'Tomatensauce, Gorgonzola, Spinat, rote Zwiebeln, Knoblauch' ),
	array( 29, 'Pizza Vegetarisch', 'pizza', '12,50 €', 'Tomatensauce, Mozzarella, gegrilltes Gemüse' ),
	array( 30, 'Pizza Capricciosa', 'pizza', '12,50 €', 'Tomatensauce, Mozzarella, Salami, Schinken, Paprika, Oliven, Champignons' ),
	array( 31, 'Pizza Scampi', 'pizza', '16,50 €', 'Tomatensauce, Mozzarella, Garnelen, Rucola' ),

	array( 35, 'Spaghetti Bolognese', 'pasta', '11,50 €', 'Spaghetti mit Tomatensauce und Rinderhackfleisch' ),
	array( 36, 'Spaghetti Carbonara', 'pasta', '12,50 €', 'Spaghetti mit Ei, Speck und Parmesan' ),
	array( 37, 'Spaghetti mit Garnelen', 'pasta', '16,50 €', 'Spaghetti mit Garnelen, Knoblauch, Cherrytomaten, Peperoni und Olivenöl' ),
	array( 38, 'Tagliatelle mit Lachs', 'pasta', '16,50 €', 'Tagliatelle mit Lachs, Babyspinat und cremiger Sauce' ),
	array( 39, 'Penne Arrabbiata', 'pasta', '10,50 €', 'Penne mit scharfer Tomatensauce und Mozzarella' ),
	array( 40, 'Penne Thunfisch', 'pasta', '12,50 €', 'Penne mit Thunfisch, Zwiebeln und Sahnesauce, mit Käse überbacken' ),
	array( 41, 'Lasagne al Bolognese', 'pasta', '13,50 €', 'Überbackene Lasagne mit Rinderhackfleisch, Tomatensauce, Béchamelsauce und Parmesan' ),
	array( 42, 'Ravioli Ricotta-Spinat', 'pasta', '13,50 €', 'Ravioli, gefüllt mit Ricotta und Spinat, dazu Sahnesauce und Parmesan' ),

	array( 45, 'Rumpsteak', 'fleisch', '24,50 €', 'Ofenkartoffel, Chimichurri-Sauce, Sour Cream, Kräuterbrot, dazu Salat' ),
	array( 46, 'Putensteak', 'fleisch', '16,80 €', 'Bratkartoffeln, Jägersauce, Kräuterbrot, dazu Salat' ),
	array( 47, 'Schweinemedaillons', 'fleisch', '19,50 €', 'Rosmarinkartoffeln, Pfeffersauce, Kräuterbrot, dazu Salat' ),
	array( 48, 'Schnitzel', 'fleisch', '15,50 €', 'Pommes frites, Champignonrahmsauce, Mayo, Ketchup' ),
	array( 49, 'Grillteller', 'fleisch', '25,50 €', 'Drei verschiedene Fleischsorten, Beilage nach Wahl, Kräuterbrot, dazu Salat' ),

	array( 55, 'Schollenfilet', 'fisch', '18,50 €', 'Bratkartoffeln, Zwiebeln, Speck, dazu Salat' ),
	array( 56, 'Lachsfilet', 'fisch', '21,00 €', 'Salzkartoffeln, Brokkoli, cremige Dill-Sauce' ),
	array( 57, 'Fischteller', 'fisch', '23,50 €', 'Drei verschiedene Fischsorten mit Senfsauce, Beilage nach Wahl, dazu Salat' ),

	array( 60, 'Hasenpizza', 'kindermenue', '6,50 €', 'Tomatensauce, Mozzarella' ),
	array( 61, 'Penne in Tomatensauce', 'kindermenue', '5,50 €', 'Penne mit Tomatensauce' ),
	array( 62, 'Kinderschnitzel mit Pommes', 'kindermenue', '9,00 €', 'Schnitzel mit Pommes frites' ),

	array( 65, 'Tiramisu', 'dessert', '6,00 €', 'Hausgemachtes Tiramisu' ),
	array( 66, 'Crème brûlée', 'dessert', '6,50 €', 'Vanillecreme mit karamellisierter Zuckerkruste' ),
);

$angelegt     = 0;
$aktualisiert = 0;

foreach ( $gerichte as $gericht ) {
	list( $nummer, $name, $kategorie_slug, $preis, $beschreibung ) = $gericht;

	if ( ! isset( $kategorie_ids[ $kategorie_slug ] ) ) {
		WP_CLI::warning( "Gericht '{$name}': Kategorie '{$kategorie_slug}' fehlt." );
		continue;
	}

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
		$gericht_id = (int) $vorhandene[0];

		wp_update_post(
			array(
				'ID'         => $gericht_id,
				'menu_order' => $nummer,
			)
		);

		++$aktualisiert;
		WP_CLI::log( "  aktualisiert: {$name}" );
	} else {
		$gericht_id = wp_insert_post(
			array(
				'post_type'   => 'restaurant_gericht',
				'post_title'  => $name,
				'post_status' => 'publish',
				'menu_order'  => $nummer,
			),
			true
		);

		if ( is_wp_error( $gericht_id ) ) {
			WP_CLI::warning( "Gericht '{$name}': " . $gericht_id->get_error_message() );
			continue;
		}

		++$angelegt;
		WP_CLI::log( "  angelegt: {$name}" );
	}

	wp_set_object_terms( $gericht_id, array( $kategorie_ids[ $kategorie_slug ] ), 'restaurant_gericht_kategorie' );

	if ( function_exists( 'update_field' ) ) {
		update_field( 'field_restaurant_gericht_preis', $preis, $gericht_id );
		update_field( 'field_restaurant_gericht_beschreibung', $beschreibung, $gericht_id );
		update_field( 'field_restaurant_gericht_verfuegbar', 1, $gericht_id );

		// Vegetarisch und vegan bleiben ungesetzt: Die gelieferte Karte sagt dazu
		// nichts, und ob in der Bolognese-Kueche ein Gericht wirklich ohne
		// tierisches Lab auskommt, kann nur der Wirt beantworten. Die Haekchen
		// setzt er im Backend, dann erscheint die Auszeichnung von selbst.
		update_field( 'field_restaurant_gericht_vegetarisch', 0, $gericht_id );
		update_field( 'field_restaurant_gericht_vegan', 0, $gericht_id );
	}
}

WP_CLI::success( "Speisekarte steht: {$angelegt} angelegt, {$aktualisiert} aktualisiert." );
