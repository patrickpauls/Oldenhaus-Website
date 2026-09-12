<?php
/**
 * Speisekarte: Post Type „Gericht" und die zugehörige Kategorie-Taxonomie.
 *
 * @package Restaurant_Basis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registriert den Post Type für ein einzelnes Gericht.
 *
 * Bewusst nicht öffentlich (`public => false`): Gerichte erscheinen ausschließlich
 * innerhalb der Speisekarten-Seite, nie als eigene Unterseite. Wären sie öffentlich,
 * entstünden unter /gericht/margherita/ Seiten, die niemand verlinkt, für die es kein
 * Template gibt und die in der Suche als dünne Treffer auftauchen würden.
 *
 * Der Name des Gerichts ist das normale WordPress-Titelfeld. Ein zusätzliches ACF-Feld
 * dafür würde zwei konkurrierende Quellen erzeugen, und der Name fehlte dann in der
 * Übersichtsliste und in der Suche des Backends.
 */
function restaurant_basis_post_type_gericht_registrieren(): void {
	register_post_type(
		'restaurant_gericht',
		array(
			'labels'              => array(
				'name'                  => 'Speisekarte',
				'singular_name'         => 'Gericht',
				'menu_name'             => 'Speisekarte',
				'add_new'               => 'Gericht hinzufügen',
				'add_new_item'          => 'Neues Gericht',
				'edit_item'             => 'Gericht bearbeiten',
				'new_item'              => 'Neues Gericht',
				'view_item'             => 'Gericht ansehen',
				'search_items'          => 'Gerichte durchsuchen',
				'not_found'             => 'Noch keine Gerichte angelegt.',
				'not_found_in_trash'    => 'Keine Gerichte im Papierkorb.',
				'all_items'             => 'Alle Gerichte',
				'item_updated'          => 'Gericht gespeichert.',
				'item_published'        => 'Gericht veröffentlicht.',
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			/*
			 * Keine REST-Schnittstelle. Der Editor braucht sie nicht, weil 'supports'
			 * kein 'editor' enthält – es gibt also keinen Blockeditor zu bedienen.
			 * Mit true wären dagegen auch ausgeblendete Einträge weiterhin öffentlich
			 * über /wp-json/ abrufbar gewesen, obwohl sie im Frontend verschwinden.
			 */
			'show_in_rest'        => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-food',
			// „page-attributes" liefert das Feld „Reihenfolge", über das der Kunde die
			// Abfolge innerhalb einer Kategorie selbst bestimmt.
			'supports'            => array( 'title', 'page-attributes' ),
			'capability_type'     => 'post',
		)
	);
}
add_action( 'init', 'restaurant_basis_post_type_gericht_registrieren' );

/**
 * Registriert die Kategorien der Speisekarte (Pizza, Pasta, Getränke …).
 *
 * Hierarchisch angelegt, damit der Kunde im Backend Auswahlkästchen bekommt statt eines
 * Freitextfelds – so entstehen keine Dubletten durch Tippfehler.
 */
function restaurant_basis_taxonomie_kategorie_registrieren(): void {
	register_taxonomy(
		'restaurant_gericht_kategorie',
		array( 'restaurant_gericht' ),
		array(
			'labels'            => array(
				'name'          => 'Kategorien',
				'singular_name' => 'Kategorie',
				'menu_name'     => 'Kategorien',
				'all_items'     => 'Alle Kategorien',
				'edit_item'     => 'Kategorie bearbeiten',
				'add_new_item'  => 'Neue Kategorie',
				'search_items'  => 'Kategorien durchsuchen',
				'not_found'     => 'Noch keine Kategorien angelegt.',
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
		)
	);
}
add_action( 'init', 'restaurant_basis_taxonomie_kategorie_registrieren' );

/**
 * Beschriftet das Titelfeld im Gericht-Editor um.
 *
 * Ohne das stünde dort „Titel hinzufügen", was beim Anlegen eines Gerichts nicht
 * selbsterklärend ist.
 */
function restaurant_basis_titelfeld_beschriften( string $platzhalter, WP_Post $beitrag ): string {
	if ( 'restaurant_gericht' === $beitrag->post_type ) {
		return 'Name des Gerichts';
	}

	if ( 'restaurant_faq' === $beitrag->post_type ) {
		return 'Frage';
	}

	return $platzhalter;
}
add_filter( 'enter_title_here', 'restaurant_basis_titelfeld_beschriften', 10, 2 );

/**
 * Ergänzt die Gericht-Übersicht im Backend um Preis und Verfügbarkeit.
 *
 * Der Kunde soll auf einen Blick sehen, was gerade auf der Karte steht, ohne jedes
 * Gericht einzeln öffnen zu müssen.
 */
function restaurant_basis_gericht_spalten( array $spalten ): array {
	$neu = array();

	foreach ( $spalten as $schluessel => $beschriftung ) {
		$neu[ $schluessel ] = $beschriftung;

		// Die eigenen Spalten direkt hinter den Namen einsortieren.
		if ( 'title' === $schluessel ) {
			$neu['restaurant_preis']       = 'Preis';
			$neu['restaurant_kennzeichen'] = 'Kennzeichnung';
			$neu['restaurant_verfuegbar']  = 'Verfügbar';
		}
	}

	return $neu;
}
add_filter( 'manage_restaurant_gericht_posts_columns', 'restaurant_basis_gericht_spalten' );

/**
 * Füllt die eigenen Spalten der Gericht-Übersicht.
 */
function restaurant_basis_gericht_spalten_inhalt( string $spalte, int $beitrag_id ): void {
	switch ( $spalte ) {
		case 'restaurant_preis':
			$preis = get_post_meta( $beitrag_id, 'preis', true );
			echo $preis ? esc_html( $preis ) : '<span style="color:#a7aaad">—</span>';
			break;

		case 'restaurant_kennzeichen':
			// Vegan schließt vegetarisch ein, deshalb gewinnt es in der Anzeige.
			if ( get_post_meta( $beitrag_id, 'vegan', true ) ) {
				echo 'vegan';
			} elseif ( get_post_meta( $beitrag_id, 'vegetarisch', true ) ) {
				echo 'vegetarisch';
			} else {
				echo '<span style="color:#a7aaad">—</span>';
			}
			break;

		case 'restaurant_verfuegbar':
			// Bei Gerichten, die vor Einführung des Feldes angelegt wurden, ist der
			// Wert leer. Die sind verfügbar, nicht ausgeblendet.
			$verfuegbar = get_post_meta( $beitrag_id, 'verfuegbar', true );

			if ( '0' === $verfuegbar ) {
				echo '<span style="color:#b32d2e">ausgeblendet</span>';
			} else {
				echo 'ja';
			}
			break;
	}
}
add_action( 'manage_restaurant_gericht_posts_custom_column', 'restaurant_basis_gericht_spalten_inhalt', 10, 2 );

/**
 * Sortiert die Backend-Übersicht nach der vom Kunden gesetzten Reihenfolge.
 *
 * Standardmäßig sortiert WordPress nach Datum. Damit stünde die Liste im Backend in
 * einer anderen Ordnung als auf der Website, was beim Sortieren verwirrt.
 */
function restaurant_basis_gericht_sortierung( WP_Query $abfrage ): void {
	if ( ! is_admin() || ! $abfrage->is_main_query() ) {
		return;
	}

	if ( 'restaurant_gericht' !== $abfrage->get( 'post_type' ) ) {
		return;
	}

	// Eine ausdrücklich angeklickte Spaltensortierung nicht überschreiben.
	if ( $abfrage->get( 'orderby' ) ) {
		return;
	}

	$abfrage->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
}
add_action( 'pre_get_posts', 'restaurant_basis_gericht_sortierung' );
