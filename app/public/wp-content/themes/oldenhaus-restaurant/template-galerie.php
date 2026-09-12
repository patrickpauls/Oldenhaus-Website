<?php
/**
 * Template Name: Galerie
 *
 * Die Bilder pflegt der Kunde über den normalen Galerie-Dialog von WordPress, der im
 * ACF-Feld über „Dateien hinzufügen" erreichbar ist. Sortieren per Ziehen und
 * Bildunterschriften funktionieren damit automatisch – ohne Plugin und ohne das
 * kostenpflichtige ACF-Galeriefeld.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) {
	the_post();
}

$oldenhaus_einleitung = oldenhaus_feld( 'einleitung' );

// Der Inhalt des WYSIWYG-Feldes enthält den Galerie-Shortcode von WordPress.
$oldenhaus_galerie = function_exists( 'get_field' )
	? (string) get_field( 'galerie' )
	: (string) get_post_meta( get_the_ID(), 'galerie', true );
?>
<div class="wrap">

	<header class="seitenkopf">
		<h1><?php the_title(); ?></h1>
		<?php if ( '' !== $oldenhaus_einleitung ) : ?>
			<p><?php echo esc_html( $oldenhaus_einleitung ); ?></p>
		<?php endif; ?>
	</header>

	<div class="galerie abschnitt">
		<?php
		/*
		 * Nicht auf Textinhalt pruefen: Eine Galerie besteht nur aus Bildern, ihr
		 * Textanteil ist leer. Und der Shortcode taucht hier nicht mehr auf, weil ACF
		 * bei WYSIWYG-Feldern die Inhaltsfilter bereits angewendet hat - do_shortcode()
		 * unten laeuft nur noch fuer den Fall, dass doch einmal ein roher Shortcode
		 * im Feld steht.
		 */
		if ( '' !== trim( $oldenhaus_galerie ) ) {
			echo wp_kses_post( do_shortcode( $oldenhaus_galerie ) );
		} else {
			echo '<p class="fehlt">Die Bilder folgen nach dem Fototermin.</p>';
		}
		?>
	</div>

</div>
<?php
get_footer();
