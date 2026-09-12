<?php
/**
 * Startseite.
 *
 * Reihenfolge der Abschnitte nach Vorgabe aus der Spec:
 * Hero, Öffnungszeiten, Abholung, Feiern & Events, Gutscheine, Fragen & Antworten.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Die Startseite ist eine echte WordPress-Seite; ihre Felder hängen an dieser ID.
if ( have_posts() ) {
	the_post();
}

get_template_part( 'template-parts/startseite/hero' );
get_template_part( 'template-parts/startseite/zeiten' );
get_template_part( 'template-parts/startseite/abholung' );
get_template_part( 'template-parts/startseite/events' );
get_template_part( 'template-parts/startseite/gutscheine' );
get_template_part( 'template-parts/startseite/faq' );

get_footer();
