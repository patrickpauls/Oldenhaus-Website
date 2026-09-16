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
			<?php
			/*
			 * Bewusst nicht wp_get_document_title(): Der Seitentitel trägt seit
			 * inc/seo.php den Zusatz „| Oldenhaus" – als Überschrift auf der Seite
			 * gelesen ergäbe das „Suche nach … | Oldenhaus". Der Titel im Browsertab
			 * und die Überschrift im Text haben verschiedene Aufgaben.
			 */
			?>
			<h1>
				<?php
				if ( is_search() ) {
					printf( 'Suchergebnisse für „%s"', esc_html( get_search_query() ) );
				} else {
					echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
				}
				?>
			</h1>
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
			<?php
			/*
			 * Zwei verschiedene Sackgassen, die hier zusammenlaufen: eine Suche ohne
			 * Treffer und ein Archiv, aus dem alles verschwunden ist. Beide bekommen
			 * denselben Weg zurück, aber nicht denselben Satz – „diese Seite gibt es
			 * nicht" wäre bei einer Suche schlicht gelogen.
			 */
			?>
			<?php if ( is_search() ) : ?>
				<h1>Nichts gefunden</h1>
				<p>Zu „<?php echo esc_html( get_search_query() ); ?>" haben wir nichts. Vielleicht steht es auf der Speisekarte.</p>
			<?php else : ?>
				<h1>Hier ist nichts</h1>
				<p>Diese Seite gibt es nicht – vielleicht hat sich ein Tippfehler eingeschlichen.</p>
			<?php endif; ?>
		</header>

		<div class="abschnitt">
			<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Zurück zur Startseite</a></p>
		</div>

	<?php endif; ?>
</div>
<?php
get_footer();
