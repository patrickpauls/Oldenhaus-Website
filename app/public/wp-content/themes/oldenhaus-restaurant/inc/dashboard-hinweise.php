<?php
/**
 * Übersicht der noch offenen Inhalte im WordPress-Dashboard.
 *
 * Das ist der einzige projektspezifische Teil des Themes. Beim Bau der Website war eine
 * Reihe von Angaben noch nicht verfügbar – Telefonnummer, Impressum, die Speisekarte.
 * Statt diese Lücken mit Blindtext zu überdecken, führt dieses Widget sie auf und
 * verlinkt jeweils direkt dorthin, wo sie zu füllen sind.
 *
 * Die Punkte verschwinden von selbst, sobald der Inhalt da ist – die Liste wird bei
 * jedem Aufruf neu aus dem tatsächlichen Stand gebildet.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sucht eine Seite anhand ihres Adresszusatzes.
 */
function oldenhaus_seite_finden( string $slug ): ?WP_Post {
	$seite = get_page_by_path( $slug );

	return $seite instanceof WP_Post ? $seite : null;
}

/**
 * Ob der Inhalt einer Seite noch die Platzhalter-Markierung trägt.
 */
function oldenhaus_seite_noch_leer( ?WP_Post $seite ): bool {
	if ( ! $seite instanceof WP_Post ) {
		return true;
	}

	$inhalt = trim( wp_strip_all_tags( $seite->post_content ) );

	return '' === $inhalt || str_contains( $inhalt, 'Inhalt folgt' );
}

/**
 * Stellt die Liste der offenen Punkte zusammen.
 *
 * @return array<int, array{text:string, link:string, link_text:string}>
 */
function oldenhaus_offene_punkte(): array {
	$punkte = array();

	$einstellungen_link = admin_url( 'admin.php?page=restaurant-basis-einstellungen' );

	if ( ! oldenhaus_hat_telefon() ) {
		$punkte[] = array(
			'text'      => '<strong>Telefonnummer fehlt.</strong> Solange sie fehlt, zeigen alle Anruf-Buttons nur einen Hinweis statt einer Nummer – und Gäste können weder reservieren noch zum Abholen bestellen.',
			'link'      => $einstellungen_link,
			'link_text' => 'Nummer eintragen',
		);
	}

	if ( '' === trim( oldenhaus_adresse()['einzeilig'] ) ) {
		$punkte[] = array(
			'text'      => '<strong>Anschrift fehlt.</strong> Sie steht in der Fußzeile und auf der Startseite.',
			'link'      => $einstellungen_link,
			'link_text' => 'Anschrift eintragen',
		);
	}

	if ( function_exists( 'restaurant_basis_email' ) && '' === restaurant_basis_email() ) {
		$punkte[] = array(
			'text'      => '<strong>E-Mail-Adresse fehlt.</strong> Fürs Impressum ist sie gesetzlich vorgeschrieben.',
			'link'      => $einstellungen_link,
			'link_text' => 'E-Mail eintragen',
		);
	}

	// Speisekarte: Die beim Aufbau angelegten Beispielgerichte tragen eine
	// Kennzeichnung. Der Hinweis verschwindet, sobald sie durch die echte Karte
	// ersetzt wurden - es muss also niemand daran denken, ihn abzuschalten.
	$beispielgerichte = get_posts(
		array(
			'post_type'      => 'restaurant_gericht',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_oldenhaus_beispiel',
			'meta_value'     => '1',
		)
	);

	if ( ! empty( $beispielgerichte ) ) {
		$punkte[] = array(
			'text'      => '<strong>Auf der Speisekarte stehen noch ' . count( $beispielgerichte ) . ' Beispielgerichte.</strong> Sie zeigen, wie Einträge aussehen, und sollten durch die echte Karte ersetzt werden.',
			'link'      => admin_url( 'edit.php?post_type=restaurant_gericht' ),
			'link_text' => 'Zur Speisekarte',
		);
	}

	// Fragen, deren Antwort noch nicht bestätigt ist, liegen als Entwurf vor.
	$faq_entwuerfe = (int) wp_count_posts( 'restaurant_faq' )->draft;

	if ( $faq_entwuerfe > 0 ) {
		$punkte[] = array(
			'text'      => '<strong>' . $faq_entwuerfe . ' Fragen warten auf eine Antwort.</strong> Sie liegen als Entwurf bereit und sind auf der Website nicht sichtbar. Der Antworttext ist ein Vorschlag – bitte prüfen, ob er inhaltlich stimmt, dann veröffentlichen.',
			'link'      => admin_url( 'edit.php?post_status=draft&post_type=restaurant_faq' ),
			'link_text' => 'Fragen ansehen',
		);
	}

	// Impressum
	$impressum = oldenhaus_seite_finden( 'impressum' );

	if ( oldenhaus_seite_noch_leer( $impressum ) ) {
		$punkte[] = array(
			'text'      => '<strong>Das Impressum ist noch leer.</strong> Ohne vollständiges Impressum darf die Website nicht online gehen.',
			'link'      => $impressum ? get_edit_post_link( $impressum->ID, 'raw' ) : admin_url( 'edit.php?post_type=page' ),
			'link_text' => 'Impressum bearbeiten',
		);
	}

	// Datenschutzerklärung – WordPress führt sie in einer eigenen Einstellung.
	$datenschutz_id = (int) get_option( 'wp_page_for_privacy_policy' );
	$datenschutz    = $datenschutz_id ? get_post( $datenschutz_id ) : oldenhaus_seite_finden( 'datenschutzerklaerung' );

	if ( oldenhaus_seite_noch_leer( $datenschutz instanceof WP_Post ? $datenschutz : null ) ) {
		$punkte[] = array(
			'text'      => '<strong>Die Datenschutzerklärung ist noch leer.</strong> Auch sie ist vor dem Livegang Pflicht.',
			'link'      => $datenschutz instanceof WP_Post ? get_edit_post_link( $datenschutz->ID, 'raw' ) : admin_url( 'edit.php?post_type=page' ),
			'link_text' => 'Datenschutz bearbeiten',
		);
	}

	if ( ! has_custom_logo() ) {
		$punkte[] = array(
			'text'      => '<strong>Kein Logo hinterlegt.</strong> Bis dahin steht oben der Schriftzug „Oldenhaus". Sobald ein Logo vorliegt, kannst du es selbst hochladen – es ersetzt den Schriftzug automatisch.',
			'link'      => admin_url( 'customize.php' ),
			'link_text' => 'Logo hochladen',
		);
	}

	$punkte[] = array(
		'text'      => '<strong>Die Fotos sind Übergangsbilder.</strong> Sie stammen aus einer freien Bilddatenbank und sollten nach dem Fototermin gegen eigene Aufnahmen getauscht werden. Das geht überall direkt in der Mediathek, ohne Änderungen am Code.',
		'link'      => admin_url( 'upload.php' ),
		'link_text' => 'Zur Mediathek',
	);

	return $punkte;
}

/**
 * Gibt das Dashboard-Widget aus.
 */
function oldenhaus_dashboard_widget_ausgeben(): void {
	$punkte = oldenhaus_offene_punkte();

	if ( empty( $punkte ) ) {
		echo '<p>Alles erledigt – es fehlt nichts mehr.</p>';

		return;
	}

	echo '<p>Diese Angaben fehlen noch. Jeder Punkt verschwindet von selbst, sobald er erledigt ist.</p>';

	/*
	 * Die beiden style-Attribute hier bleiben bewusst inline. Sie betreffen
	 * ausschliesslich dieses eine Widget im Backend; eine eigene Admin-Stylesheet-Datei
	 * samt Einbindung waere fuer zwei Regeln unverhaeltnismaessig. Im Frontend steht
	 * dafuer kein einziges style-Attribut - dort gehoert alles ins Stylesheet.
	 */
	echo '<ul style="margin:0;padding:0;list-style:none">';

	foreach ( $punkte as $punkt ) {
		printf(
			'<li style="padding:10px 0;border-top:1px solid #f0f0f1">%s<br><a href="%s">%s →</a></li>',
			wp_kses( $punkt['text'], array( 'strong' => array() ) ),
			esc_url( (string) $punkt['link'] ),
			esc_html( $punkt['link_text'] )
		);
	}

	echo '</ul>';
}

/**
 * Meldet das Widget im Dashboard an.
 */
function oldenhaus_dashboard_widget_anmelden(): void {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	wp_add_dashboard_widget(
		'oldenhaus_offene_punkte',
		'Noch zu erledigen',
		'oldenhaus_dashboard_widget_ausgeben'
	);
}
add_action( 'wp_dashboard_setup', 'oldenhaus_dashboard_widget_anmelden' );
