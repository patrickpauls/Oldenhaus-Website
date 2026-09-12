<?php
/**
 * Fragen & Antworten: Post Type für den FAQ-Abschnitt.
 *
 * @package Restaurant_Basis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registriert den Post Type für eine Frage samt Antwort.
 *
 * Die Frage ist das Titelfeld, die Antwort ein ACF-Feld. Wie bei den Gerichten bewusst
 * nicht öffentlich – eine einzelne Frage bekommt keine eigene Unterseite, sie erscheint
 * nur im FAQ-Abschnitt.
 *
 * Der Entwurfsstatus von WordPress wird hier bewusst genutzt: Fragen, deren Antwort noch
 * nicht bestätigt ist, bleiben Entwurf und damit im Frontend unsichtbar. So steht nie
 * eine ungeprüfte Aussage über das Lokal auf der Website.
 */
function restaurant_basis_post_type_faq_registrieren(): void {
	register_post_type(
		'restaurant_faq',
		array(
			'labels'              => array(
				'name'               => 'Fragen & Antworten',
				'singular_name'      => 'Frage',
				'menu_name'          => 'Fragen & Antworten',
				'add_new'            => 'Frage hinzufügen',
				'add_new_item'       => 'Neue Frage',
				'edit_item'          => 'Frage bearbeiten',
				'new_item'           => 'Neue Frage',
				'search_items'       => 'Fragen durchsuchen',
				'not_found'          => 'Noch keine Fragen angelegt.',
				'not_found_in_trash' => 'Keine Fragen im Papierkorb.',
				'all_items'          => 'Alle Fragen',
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
			'menu_position'       => 21,
			'menu_icon'           => 'dashicons-editor-help',
			'supports'            => array( 'title', 'page-attributes' ),
			'capability_type'     => 'post',
		)
	);
}
add_action( 'init', 'restaurant_basis_post_type_faq_registrieren' );

/**
 * Zeigt in der FAQ-Übersicht den Anfang der Antwort.
 *
 * Ohne das steht in der Liste nur die Frage, und der Kunde muss jeden Eintrag öffnen,
 * um zu sehen, ob die Antwort schon gepflegt ist.
 */
function restaurant_basis_faq_spalten( array $spalten ): array {
	$neu = array();

	foreach ( $spalten as $schluessel => $beschriftung ) {
		$neu[ $schluessel ] = $beschriftung;

		if ( 'title' === $schluessel ) {
			$neu['restaurant_antwort'] = 'Antwort';
		}
	}

	return $neu;
}
add_filter( 'manage_restaurant_faq_posts_columns', 'restaurant_basis_faq_spalten' );

/**
 * Füllt die Antwort-Spalte der FAQ-Übersicht.
 */
function restaurant_basis_faq_spalten_inhalt( string $spalte, int $beitrag_id ): void {
	if ( 'restaurant_antwort' !== $spalte ) {
		return;
	}

	$antwort = (string) get_post_meta( $beitrag_id, 'antwort', true );

	if ( '' === trim( $antwort ) ) {
		echo '<span style="color:#b32d2e">fehlt noch</span>';
		return;
	}

	echo esc_html( wp_trim_words( wp_strip_all_tags( $antwort ), 14 ) );
}
add_action( 'manage_restaurant_faq_posts_custom_column', 'restaurant_basis_faq_spalten_inhalt', 10, 2 );

/**
 * Sortiert die FAQ-Übersicht nach der vom Kunden gesetzten Reihenfolge.
 */
function restaurant_basis_faq_sortierung( WP_Query $abfrage ): void {
	if ( ! is_admin() || ! $abfrage->is_main_query() ) {
		return;
	}

	if ( 'restaurant_faq' !== $abfrage->get( 'post_type' ) || $abfrage->get( 'orderby' ) ) {
		return;
	}

	$abfrage->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'ASC' ) );
}
add_action( 'pre_get_posts', 'restaurant_basis_faq_sortierung' );
