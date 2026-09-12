<?php
/**
 * ACF-Feldgruppen der Seitenvorlagen.
 *
 * Hier stehen nur Felder, die an eine Vorlage dieses Themes gebunden sind – Hero,
 * Galerie, Über uns. Sie ergeben ohne die zugehörigen Templates keinen Sinn und
 * gehören deshalb ins Theme und nicht ins Plugin.
 *
 * Die Felder der Inhaltstypen (Gericht, Kategorie, Frage) liegen im Plugin
 * „Restaurant-Basis", damit sie einen Theme-Wechsel überleben.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Baut ein Bildfeld mit einheitlichen Einstellungen.
 *
 * Rückgabeformat ist immer die Anhang-ID: Damit kann wp_get_attachment_image()
 * responsive Bildgrößen erzeugen, was bei einer festen URL nicht ginge.
 */
function oldenhaus_bildfeld_definition( string $schluessel, string $name, string $beschriftung, string $hinweis = '', string $breite = '' ): array {
	$feld = array(
		'key'           => $schluessel,
		'label'         => $beschriftung,
		'name'          => $name,
		'type'          => 'image',
		'return_format' => 'id',
		'preview_size'  => 'medium',
		'library'       => 'all',
		'mime_types'    => 'jpg,jpeg,png,webp',
		'instructions'  => $hinweis,
	);

	if ( '' !== $breite ) {
		$feld['wrapper'] = array( 'width' => $breite );
	}

	return $feld;
}

/**
 * Registriert die Feldgruppen der Seitenvorlagen.
 */
function oldenhaus_seitenfelder_registrieren(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// ---------------------------------------------------------------------
	// Startseite
	// ---------------------------------------------------------------------
	acf_add_local_field_group(
		array(
			'key'            => 'group_oldenhaus_startseite',
			'title'          => 'Inhalte der Startseite',
			'location'       => array(
				array(
					array(
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'front_page',
					),
				),
			),
			'hide_on_screen' => array( 'the_content', 'excerpt', 'custom_fields', 'discussion', 'comments' ),
			'active'         => true,
			'fields'         => array(

				// --- Hero -------------------------------------------------
				array(
					'key'       => 'field_oldenhaus_tab_hero',
					'label'     => 'Oben auf der Seite',
					'type'      => 'tab',
					'placement' => 'top',
				),
				array(
					'key'          => 'field_oldenhaus_leitspruch',
					'label'        => 'Leitspruch',
					'name'         => 'leitspruch',
					'type'         => 'text',
					'instructions' => 'Die große Überschrift über den Fotos. Kurz halten – etwa sechs bis acht Wörter.',
				),
				array(
					'key'          => 'field_oldenhaus_unterzeile',
					'label'        => 'Unterzeile',
					'name'         => 'unterzeile',
					'type'         => 'text',
					'instructions' => 'Ein Satz darunter, der sagt, was es gibt und wo ihr seid.',
				),
				array(
					'key'          => 'field_oldenhaus_hero_hinweis',
					'label'        => '',
					'type'         => 'message',
					'message'      => '<strong>Die Fotos der Slideshow.</strong> Mindestens eines, höchstens fünf. Sie wechseln langsam ineinander. Leere Felder werden einfach übersprungen – du musst nicht alle fünf füllen.',
					'new_lines'    => '',
					'esc_html'     => 0,
				),
				oldenhaus_bildfeld_definition( 'field_oldenhaus_hero_bild_1', 'hero_bild_1', 'Hero-Bild 1', 'Das erste Bild. Es wird zuerst angezeigt.', '20' ),
				oldenhaus_bildfeld_definition( 'field_oldenhaus_hero_bild_2', 'hero_bild_2', 'Hero-Bild 2', '', '20' ),
				oldenhaus_bildfeld_definition( 'field_oldenhaus_hero_bild_3', 'hero_bild_3', 'Hero-Bild 3', '', '20' ),
				oldenhaus_bildfeld_definition( 'field_oldenhaus_hero_bild_4', 'hero_bild_4', 'Hero-Bild 4', '', '20' ),
				oldenhaus_bildfeld_definition( 'field_oldenhaus_hero_bild_5', 'hero_bild_5', 'Hero-Bild 5', '', '20' ),

				// --- Öffnungszeiten ---------------------------------------
				array(
					'key'   => 'field_oldenhaus_tab_zeiten',
					'label' => 'Öffnungszeiten',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_oldenhaus_zeiten_hinweis',
					'label'        => '',
					'type'         => 'message',
					'message'      => 'Die Zeiten selbst pflegst du unter <strong>Kontakt &amp; Zeiten</strong> im linken Menü – von dort speisen sich auch Fußzeile und der Hinweis „Heute geöffnet". Hier stehen nur Überschrift und Begleittext.',
					'esc_html'     => 0,
				),
				array(
					'key'   => 'field_oldenhaus_zeiten_titel',
					'label' => 'Überschrift',
					'name'  => 'zeiten_titel',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_oldenhaus_zeiten_text',
					'label'        => 'Begleittext',
					'name'         => 'zeiten_text',
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => 'Ein oder zwei Sätze neben den Zeiten.',
				),
				array(
					'key'          => 'field_oldenhaus_parken_text',
					'label'        => 'Hinweis zu Anfahrt und Parken',
					'name'         => 'parken_text',
					'type'         => 'textarea',
					'rows'         => 2,
					'instructions' => 'Erscheint zusammen mit der Anschrift. Die Anschrift selbst kommt aus „Kontakt & Zeiten".',
				),

				// --- Abholung ---------------------------------------------
				array(
					'key'   => 'field_oldenhaus_tab_abholung',
					'label' => 'Abholung',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_oldenhaus_abholung_titel',
					'label' => 'Überschrift',
					'name'  => 'abholung_titel',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_oldenhaus_abholung_text',
					'label' => 'Text',
					'name'  => 'abholung_text',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'          => 'field_oldenhaus_abholung_button',
					'label'        => 'Beschriftung des Buttons',
					'name'         => 'abholung_button',
					'type'         => 'text',
					'instructions' => 'Zum Beispiel: Zum Abholen bestellen',
					'placeholder'  => 'Zum Abholen bestellen',
				),
				oldenhaus_bildfeld_definition( 'field_oldenhaus_abholung_bild', 'abholung_bild', 'Bild', 'Hochkant sieht hier am besten aus.' ),

				// --- Feiern & Events --------------------------------------
				array(
					'key'   => 'field_oldenhaus_tab_events',
					'label' => 'Feiern & Events',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_oldenhaus_events_titel',
					'label' => 'Überschrift',
					'name'  => 'events_titel',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_oldenhaus_events_text',
					'label'        => 'Text',
					'name'         => 'events_text',
					'type'         => 'textarea',
					'rows'         => 4,
					'instructions' => 'Was ihr ausrichtet und für wie viele Gäste. Angefragt wird telefonisch.',
				),
				oldenhaus_bildfeld_definition( 'field_oldenhaus_events_bild', 'events_bild', 'Bild', '' ),

				// --- Gutscheine -------------------------------------------
				array(
					'key'   => 'field_oldenhaus_tab_gutscheine',
					'label' => 'Gutscheine',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_oldenhaus_gutscheine_titel',
					'label' => 'Überschrift',
					'name'  => 'gutscheine_titel',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_oldenhaus_gutscheine_text',
					'label'        => 'Text',
					'name'         => 'gutscheine_text',
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => 'Kurzer Hinweis. Gutscheine gibt es nur vor Ort, nicht online.',
				),

				// --- Fragen & Antworten -----------------------------------
				array(
					'key'   => 'field_oldenhaus_tab_faq',
					'label' => 'Fragen & Antworten',
					'type'  => 'tab',
				),
				array(
					'key'      => 'field_oldenhaus_faq_hinweis',
					'label'    => '',
					'type'     => 'message',
					'message'  => 'Die Fragen selbst pflegst du unter <strong>Fragen &amp; Antworten</strong> im linken Menü. Fragen ohne Antwort und Entwürfe erscheinen nicht auf der Website.',
					'esc_html' => 0,
				),
				array(
					'key'   => 'field_oldenhaus_faq_titel',
					'label' => 'Überschrift',
					'name'  => 'faq_titel',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_oldenhaus_faq_einleitung',
					'label' => 'Einleitung',
					'name'  => 'faq_einleitung',
					'type'  => 'textarea',
					'rows'  => 2,
				),
			),
		)
	);

	// ---------------------------------------------------------------------
	// Speisekarte
	// ---------------------------------------------------------------------
	acf_add_local_field_group(
		array(
			'key'            => 'group_oldenhaus_speisekarte',
			'title'          => 'Speisekarten-Seite',
			'location'       => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-speisekarte.php',
					),
				),
			),
			'hide_on_screen' => array( 'the_content', 'excerpt', 'custom_fields', 'discussion', 'comments' ),
			'active'         => true,
			'fields'         => array(
				array(
					'key'      => 'field_oldenhaus_speisekarte_hinweis',
					'label'    => '',
					'type'     => 'message',
					'message'  => 'Die Gerichte selbst pflegst du unter <strong>Speisekarte</strong> im linken Menü. Die Reihenfolge der Kategorien stellst du bei der jeweiligen Kategorie ein.',
					'esc_html' => 0,
				),
				array(
					'key'          => 'field_oldenhaus_speisekarte_einleitung',
					'label'        => 'Einleitungssatz',
					'name'         => 'einleitung',
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => 'Steht unter der Überschrift, bevor die erste Kategorie kommt.',
				),
				array(
					'key'          => 'field_oldenhaus_speisekarte_legende',
					'label'        => 'Hinweis zur Kennzeichnung',
					'name'         => 'legende',
					'type'         => 'text',
					'instructions' => 'Erscheint am Ende der Karte, neben den Kennzeichnungen.',
				),
			),
		)
	);

	// ---------------------------------------------------------------------
	// Galerie
	// ---------------------------------------------------------------------
	acf_add_local_field_group(
		array(
			'key'            => 'group_oldenhaus_galerie',
			'title'          => 'Galerie-Seite',
			'location'       => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-galerie.php',
					),
				),
			),
			'hide_on_screen' => array( 'the_content', 'excerpt', 'custom_fields', 'discussion', 'comments' ),
			'active'         => true,
			'fields'         => array(
				array(
					'key'   => 'field_oldenhaus_galerie_einleitung',
					'label' => 'Einleitungssatz',
					'name'  => 'einleitung',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'          => 'field_oldenhaus_galerie_bilder',
					'label'        => 'Bilder',
					'name'         => 'galerie',
					'type'         => 'wysiwyg',
					'instructions' => 'Klicke auf <strong>Dateien hinzufügen</strong> und wähle links <strong>Galerie erstellen</strong>. Dort kannst du Bilder hochladen, sie per Ziehen sortieren und Bildunterschriften vergeben. Zum Ändern später einfach die Galerie anklicken und auf das Stift-Symbol gehen.',
					// Medien-Buttons an: Damit bekommt der Kunde genau den Galerie-Dialog
					// von WordPress. Ein ACF-Galeriefeld waere kostenpflichtig (Pro).
					'media_upload' => 1,
					'tabs'         => 'visual',
					'toolbar'      => 'basic',
				),
			),
		)
	);

	// ---------------------------------------------------------------------
	// Über uns
	// ---------------------------------------------------------------------
	$ueber_uns_felder = array(
		array(
			'key'          => 'field_oldenhaus_ueberuns_einleitung',
			'label'        => 'Einleitung',
			'name'         => 'einleitung',
			'type'         => 'textarea',
			'rows'         => 3,
			'instructions' => 'Steht direkt unter der Überschrift.',
		),
	);

	// Drei Text-Bild-Blöcke, die sich im Layout abwechselnd anordnen.
	// Bewusst feste Blöcke statt eines Wiederholungsfeldes: Das gibt es in ACF nur
	// in der kostenpflichtigen Fassung.
	foreach ( array( 1, 2, 3 ) as $nummer ) {
		$ueber_uns_felder[] = array(
			'key'   => "field_oldenhaus_ueberuns_tab_{$nummer}",
			'label' => "Abschnitt {$nummer}",
			'type'  => 'tab',
		);
		$ueber_uns_felder[] = array(
			'key'   => "field_oldenhaus_ueberuns_titel_{$nummer}",
			'label' => 'Überschrift',
			'name'  => "block_{$nummer}_titel",
			'type'  => 'text',
		);
		$ueber_uns_felder[] = array(
			'key'          => "field_oldenhaus_ueberuns_text_{$nummer}",
			'label'        => 'Text',
			'name'         => "block_{$nummer}_text",
			'type'         => 'textarea',
			'rows'         => 5,
			'instructions' => 1 === $nummer ? 'Bleibt dieser Abschnitt leer, wird er auf der Website übersprungen.' : '',
		);
		$ueber_uns_felder[] = oldenhaus_bildfeld_definition(
			"field_oldenhaus_ueberuns_bild_{$nummer}",
			"block_{$nummer}_bild",
			'Bild',
			'Text und Bild stehen abwechselnd links und rechts.'
		);
	}

	acf_add_local_field_group(
		array(
			'key'            => 'group_oldenhaus_ueberuns',
			'title'          => 'Über-uns-Seite',
			'location'       => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-ueber-uns.php',
					),
				),
			),
			'hide_on_screen' => array( 'the_content', 'excerpt', 'custom_fields', 'discussion', 'comments' ),
			'active'         => true,
			'fields'         => $ueber_uns_felder,
		)
	);
}
add_action( 'acf/init', 'oldenhaus_seitenfelder_registrieren' );
