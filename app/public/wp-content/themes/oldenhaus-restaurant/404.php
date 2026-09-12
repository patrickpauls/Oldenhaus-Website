<?php
/**
 * Seite nicht gefunden.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="wrap">
	<header class="seitenkopf">
		<h1>Da ist wohl was angebrannt</h1>
		<p>Diese Seite gibt es nicht. Vielleicht hat sich ein Tippfehler eingeschlichen – oder wir haben sie umbenannt.</p>
	</header>

	<div class="abschnitt">
		<p>Hier geht es weiter:</p>
		<ul style="margin-top:12px;padding-left:1.2em">
			<li style="margin-top:6px"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Zur Startseite</a></li>
			<?php
			$oldenhaus_speisekarte = get_page_by_path( 'speisekarte' );

			if ( $oldenhaus_speisekarte instanceof WP_Post ) :
				?>
				<li style="margin-top:6px">
					<a href="<?php echo esc_url( (string) get_permalink( $oldenhaus_speisekarte ) ); ?>">Zur Speisekarte</a>
				</li>
			<?php endif; ?>
		</ul>

		<?php if ( oldenhaus_hat_telefon() ) : ?>
			<p style="margin-top:26px">Oder ruf uns einfach an:</p>
			<p style="margin-top:12px"><?php oldenhaus_anruf_button(); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
