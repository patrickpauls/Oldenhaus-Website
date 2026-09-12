<?php
/**
 * Einfache Inhaltsseite.
 *
 * Greift für alles, was keine eigene Vorlage hat – vor allem Impressum und
 * Datenschutzerklärung. Beide sind ausschließlich über die Fußzeile erreichbar.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="wrap">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article class="textseite">
			<header class="seitenkopf">
				<h1><?php the_title(); ?></h1>
			</header>

			<div class="abschnitt">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</div>
<?php
get_footer();
