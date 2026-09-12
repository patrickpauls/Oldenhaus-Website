<?php
/**
 * Rückfallvorlage.
 *
 * WordPress verlangt diese Datei. Die Website hat bewusst keinen Blog und keine
 * Archivseiten – hier landet also nur, wer eine Adresse aufruft, die es nicht als
 * eigene Seite gibt. Statt einer leeren Liste bekommt er einen Weg zurück.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="wrap">
	<?php if ( have_posts() ) : ?>

		<header class="seitenkopf">
			<h1><?php echo esc_html( wp_get_document_title() ); ?></h1>
		</header>

		<div class="abschnitt textseite">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article class="block">
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php the_excerpt(); ?>
				</article>
				<?php
			endwhile;
			?>
		</div>

	<?php else : ?>

		<header class="seitenkopf">
			<h1>Hier ist nichts</h1>
			<p>Diese Seite gibt es nicht – vielleicht hat sich ein Tippfehler eingeschlichen.</p>
		</header>

		<div class="abschnitt">
			<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Zurück zur Startseite</a></p>
		</div>

	<?php endif; ?>
</div>
<?php
get_footer();
