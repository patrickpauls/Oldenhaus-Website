<?php
/**
 * Template Name: Über uns
 *
 * Drei Text-Bild-Blöcke, die sich im Layout abwechseln – Bild mal links, mal rechts.
 * Leere Blöcke werden übersprungen, der Kunde muss also nicht alle drei füllen.
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
?>
<div class="wrap">

	<header class="seitenkopf">
		<h1><?php the_title(); ?></h1>
		<?php if ( '' !== $oldenhaus_einleitung ) : ?>
			<p><?php echo esc_html( $oldenhaus_einleitung ); ?></p>
		<?php endif; ?>
	</header>

	<div class="abschnitt">
		<?php
		$oldenhaus_sichtbare = 0;

		for ( $oldenhaus_i = 1; $oldenhaus_i <= 3; $oldenhaus_i++ ) :
			$oldenhaus_titel = oldenhaus_feld( "block_{$oldenhaus_i}_titel" );
			$oldenhaus_text  = oldenhaus_feld( "block_{$oldenhaus_i}_text" );
			$oldenhaus_bild  = oldenhaus_bildfeld( "block_{$oldenhaus_i}_bild" );

			if ( '' === $oldenhaus_titel && '' === $oldenhaus_text && 0 === $oldenhaus_bild ) {
				continue;
			}

			++$oldenhaus_sichtbare;

			// Jeder zweite sichtbare Block wird gedreht, damit die Bilder nicht
			// stur untereinander in derselben Spalte stehen. Der gedrehte Block
			// bekommt zusätzlich ein hochkantes Bild – das bringt Abwechslung ins
			// Seitenbild, wie in den Layout-Grundsätzen gefordert.
			$oldenhaus_ist_hochkant = ( 0 === $oldenhaus_sichtbare % 2 );
			$oldenhaus_gedreht      = $oldenhaus_ist_hochkant ? ' duo--gedreht' : '';
			?>
			<section class="block duo<?php echo esc_attr( $oldenhaus_gedreht ); ?>">
				<div class="duo__bild">
					<?php
					/*
					 * Die Bildgröße muss zur Ausrichtung passen. Zuvor wurde immer die
					 * quere Größe geholt und anschließend per CSS ins Hochformat
					 * beschnitten – das Bild wurde dadurch aus zu wenig Pixeln
					 * hochskaliert.
					 */
					oldenhaus_foto(
						$oldenhaus_bild,
						$oldenhaus_ist_hochkant ? 'oldenhaus-hochkant' : 'oldenhaus-quer',
						'Foto folgt: Gastraum',
						$oldenhaus_ist_hochkant ? array( 'foto--hochkant' ) : array()
					);
					?>
				</div>

				<div>
					<?php if ( '' !== $oldenhaus_titel ) : ?>
						<h2><?php echo esc_html( $oldenhaus_titel ); ?></h2>
					<?php endif; ?>
					<div class="mt-klein">
						<?php oldenhaus_text_oder_hinweis( $oldenhaus_text, 'Text folgt' ); ?>
					</div>
				</div>
			</section>
			<?php
		endfor;

		if ( 0 === $oldenhaus_sichtbare ) {
			echo '<p class="fehlt">Die Texte für diese Seite folgen.</p>';
		}
		?>
	</div>

</div>
<?php
get_footer();
