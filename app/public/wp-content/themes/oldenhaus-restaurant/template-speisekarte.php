<?php
/**
 * Template Name: Speisekarte
 *
 * Alle Kategorien untereinander auf einer durchscrollbaren Seite. Bewusst ohne
 * Auswahlmenü, ohne Reiter und ohne Filter – der Gast soll die Karte lesen können
 * wie eine gedruckte, ohne vorher etwas anklicken zu müssen.
 *
 * Die Reihenfolge der Kategorien bestimmt der Kunde am jeweiligen Kategorie-Eintrag;
 * Getränke bekommen dort einfach eine hohe Zahl und landen dadurch am Ende.
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
$oldenhaus_legende    = oldenhaus_feld( 'legende', null, 'kennzeichnen fleischlose Gerichte.' );
$oldenhaus_kategorien = function_exists( 'restaurant_basis_kategorien' )
	? restaurant_basis_kategorien()
	: array();
?>
<div class="wrap">
	<div class="karte">

		<header class="seitenkopf">
			<h1><?php the_title(); ?></h1>
			<?php if ( '' !== $oldenhaus_einleitung ) : ?>
				<p class="karte__einleitung"><?php echo esc_html( $oldenhaus_einleitung ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( empty( $oldenhaus_kategorien ) ) : ?>

			<p class="fehlt" class="mt-gross">
				Die Speisekarte wird gerade eingepflegt.
			</p>

		<?php else : ?>

			<?php foreach ( $oldenhaus_kategorien as $oldenhaus_kategorie ) : ?>
				<?php
				$oldenhaus_gerichte = restaurant_basis_gerichte_der_kategorie( $oldenhaus_kategorie->term_id );

				// Eine Kategorie, in der alle Gerichte ausgeblendet sind, wird übersprungen.
				if ( empty( $oldenhaus_gerichte ) ) {
					continue;
				}

				$oldenhaus_kat_einleitung = (string) get_term_meta( $oldenhaus_kategorie->term_id, 'einleitung', true );
				?>
				<section class="karte__kategorie">
					<h2><?php echo esc_html( $oldenhaus_kategorie->name ); ?></h2>

					<?php if ( '' !== trim( $oldenhaus_kat_einleitung ) ) : ?>
						<p class="karte__kategorie-einleitung"><?php echo esc_html( $oldenhaus_kat_einleitung ); ?></p>
					<?php endif; ?>

					<ul class="gerichte">
						<?php foreach ( $oldenhaus_gerichte as $oldenhaus_gericht ) : ?>
							<?php
							$oldenhaus_preis         = restaurant_basis_gericht_preis( $oldenhaus_gericht->ID );
							$oldenhaus_beschreibung  = restaurant_basis_gericht_beschreibung( $oldenhaus_gericht->ID );
							$oldenhaus_kennzeichnung = restaurant_basis_gericht_kennzeichnung( $oldenhaus_gericht->ID );
							?>
							<li class="gericht">
								<div class="gericht__kopf">
									<span class="gericht__name">
										<?php echo esc_html( $oldenhaus_gericht->post_title ); ?>
										<?php if ( '' !== $oldenhaus_kennzeichnung ) : ?>
											<span class="kennzeichen"><?php echo esc_html( $oldenhaus_kennzeichnung ); ?></span>
										<?php endif; ?>
									</span>

									<span class="gericht__linie" aria-hidden="true"></span>

									<?php if ( '' !== $oldenhaus_preis ) : ?>
										<span class="gericht__preis"><?php echo esc_html( $oldenhaus_preis ); ?></span>
									<?php endif; ?>
								</div>

								<?php if ( '' !== $oldenhaus_beschreibung ) : ?>
									<p class="gericht__beschreibung"><?php echo esc_html( $oldenhaus_beschreibung ); ?></p>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endforeach; ?>

			<p class="karte__legende">
				<span class="kennzeichen">vegetarisch</span>
				<span class="kennzeichen">vegan</span>
				<?php echo esc_html( $oldenhaus_legende ); ?>
			</p>

		<?php endif; ?>

	</div>
</div>
<?php
get_footer();
