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
			// stur untereinander in derselben Spalte stehen.
			$oldenhaus_gedreht = ( 0 === $oldenhaus_sichtbare % 2 ) ? ' duo--gedreht' : '';
			?>
			<section class="block duo<?php echo esc_attr( $oldenhaus_gedreht ); ?>">
				<div class="duo__bild">
					<?php
					oldenhaus_foto(
						$oldenhaus_bild,
						'oldenhaus-quer',
						'Foto folgt: Gastraum',
						1 === $oldenhaus_sichtbare % 2 ? array() : array( 'ph--hochkant' )
					);
					?>
				</div>

				<div>
					<?php if ( '' !== $oldenhaus_titel ) : ?>
						<h2><?php echo esc_html( $oldenhaus_titel ); ?></h2>
					<?php endif; ?>
					<div style="margin-top:12px">
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
