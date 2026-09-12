<?php
/**
 * ACF-Feldgruppen für Gerichte, Kategorien und Fragen.
 *
 * Registrierung im Code statt über die ACF-Oberfläche. Gründe:
 *   - Die Felddefinition liegt in Git und ist damit versioniert und nachvollziehbar.
 *   - Bei einem Umzug wandern die Felder mit dem Code mit, ohne Export/Import.
 *   - Im Backend lassen sie sich nicht versehentlich verändern oder löschen.
 *
 * Hier stehen ausschließlich Felder, die zu den Inhaltstypen dieses Plugins gehören.
 * Felder, die an einer bestimmten Seitenvorlage hängen (Hero, Galerie, Über uns),
 * gehören ins jeweilige Theme – sie ergeben ohne dessen Templates keinen Sinn.
 *
 * @package Restaurant_Basis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registriert alle Feldgruppen dieses Plugins.
 */
function restaurant_basis_feldgruppen_registrieren(): void {
	if ( ! restaurant_basis_acf_vorhanden() ) {
		return;
	}

	// ---------------------------------------------------------------------
	// Gericht: Beschreibung, Preis, Kennzeichnung, Verfügbarkeit
	// ---------------------------------------------------------------------
	acf_add_local_field_group(
		array(
			'key'                   => 'group_restaurant_gericht',
			'title'                 => 'Angaben zum Gericht',
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'restaurant_gericht',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'hide_on_screen'        => array( 'the_content', 'excerpt', 'custom_fields', 'discussion', 'comments' ),
			'active'                => true,
			'description'           => 'Der Name des Gerichts steht oben im Titelfeld.',
			'fields'                => array(
				array(
					'key'          => 'field_restaurant_gericht_beschreibung',
					'label'        => 'Beschreibung',
					'name'         => 'beschreibung',
					'type'         => 'textarea',
					'instructions' => 'Die Zutaten oder eine kurze Beschreibung, zum Beispiel: Tomatensoße, Mozzarella, frisches Basilikum',
					'rows'         => 3,
					'new_lines'    => '',
				),
				array(
					'key'          => 'field_restaurant_gericht_preis',
					'label'        => 'Preis',
					'name'         => 'preis',
					'type'         => 'text',
					'instructions' => 'Zum Beispiel: 9,50 € — auch „ab 9,50 €" oder „12,50 / 15,00 €" sind möglich.',
					'placeholder'  => '9,50 €',
					'wrapper'      => array( 'width' => '40' ),
				),
				array(
					'key'          => 'field_restaurant_gericht_vegetarisch',
					'label'        => 'Vegetarisch',
					'name'         => 'vegetarisch',
					'type'         => 'true_false',
					'instructions' => 'Häkchen setzen, wenn das Gericht vegetarisch ist.',
					'ui'           => 1,
					'default_value' => 0,
					'wrapper'      => array( 'width' => '30' ),
				),
				array(
					'key'          => 'field_restaurant_gericht_vegan',
					'label'        => 'Vegan',
					'name'         => 'vegan',
					'type'         => 'true_false',
					'instructions' => 'Häkchen setzen, wenn das Gericht vegan ist. Dann wird nur „vegan" angezeigt – ein zusätzliches Häkchen bei „vegetarisch" ist nicht nötig.',
					'ui'           => 1,
					'default_value' => 0,
					'wrapper'      => array( 'width' => '30' ),
				),
				array(
					'key'           => 'field_restaurant_gericht_verfuegbar',
					'label'         => 'Verfügbar',
					'name'          => 'verfuegbar',
					'type'          => 'true_false',
					'instructions'  => 'Nimm das Häkchen weg, um das Gericht vorübergehend von der Website zu nehmen – zum Beispiel außerhalb der Saison. Der Eintrag bleibt dabei erhalten und du kannst ihn jederzeit wieder einblenden.',
					'ui'            => 1,
					'ui_on_text'    => 'Auf der Karte',
					'ui_off_text'   => 'Ausgeblendet',
					'default_value' => 1,
				),
			),
		)
	);

	// ---------------------------------------------------------------------
	// Kategorie: Reihenfolge auf der Speisekarte
	// ---------------------------------------------------------------------
	acf_add_local_field_group(
		array(
			'key'      => 'group_restaurant_kategorie',
			'title'    => 'Anzeige auf der Speisekarte',
			'location' => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'restaurant_gericht_kategorie',
					),
				),
			),
			'active'   => true,
			'fields'   => array(
				array(
					'key'           => 'field_restaurant_kategorie_reihenfolge',
					'label'         => 'Reihenfolge',
					'name'          => 'reihenfolge',
					'type'          => 'number',
					'instructions'  => 'Bestimmt, an welcher Stelle die Kategorie auf der Speisekarte steht. Kleine Zahl = weiter oben. Für Getränke am Ende der Karte zum Beispiel 90.',
					'default_value' => 10,
					'min'           => 0,
					'step'          => 1,
				),
				array(
					'key'          => 'field_restaurant_kategorie_einleitung',
					'label'        => 'Einleitungssatz',
					'name'         => 'einleitung',
					'type'         => 'text',
					'instructions' => 'Optionaler Satz unter der Kategorie-Überschrift. Leer lassen, wenn keiner gewünscht ist.',
				),
			),
		)
	);

	// ---------------------------------------------------------------------
	// Frage: Antwort
	// ---------------------------------------------------------------------
	acf_add_local_field_group(
		array(
			'key'            => 'group_restaurant_faq',
			'title'          => 'Antwort',
			'location'       => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'restaurant_faq',
					),
				),
			),
			'hide_on_screen' => array( 'the_content', 'excerpt', 'custom_fields', 'discussion', 'comments' ),
			'active'         => true,
			'description'    => 'Die Frage selbst steht oben im Titelfeld.',
			'fields'         => array(
				array(
					'key'          => 'field_restaurant_faq_antwort',
					'label'        => 'Antwort',
					'name'         => 'antwort',
					'type'         => 'textarea',
					'instructions' => 'Solange hier nichts steht, erscheint die Frage nicht auf der Website.',
					'rows'         => 4,
					'new_lines'    => 'wpautop',
				),
			),
		)
	);
}
add_action( 'acf/init', 'restaurant_basis_feldgruppen_registrieren' );
