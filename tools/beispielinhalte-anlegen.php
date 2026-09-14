<?php
/**
 * Legt die Fragen des FAQ-Abschnitts an.
 *
 * Ausfuehren mit WP-CLI:
 *     wp eval-file tools/beispielinhalte-anlegen.php
 *
 * Die Speisekarte stand hier frueher auch drin, als Beispielgerichte. Seit die echte
 * Karte des Kunden vorliegt, entsteht sie in tools/speisekarte-anlegen.php - sonst
 * holte ein erneuter Lauf dieses Skripts die Platzhalter zurueck.
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
