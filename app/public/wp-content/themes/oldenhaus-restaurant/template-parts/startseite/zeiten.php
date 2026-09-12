<?php
/**
 * Öffnungszeiten mit Anschrift.
 *
 * Da es keine eigene Seite „Öffnungszeiten & Anfahrt" gibt, stehen Anschrift und
 * Parkhinweis hier mit – und zusätzlich in der Fußzeile.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$oldenhaus_zeiten       = oldenhaus_oeffnungszeiten();
$oldenhaus_sonderzeiten = oldenhaus_sonderzeiten();
$oldenhaus_adresse      = oldenhaus_adresse();
$oldenhaus_titel        = oldenhaus_feld( 'zeiten_titel', null, 'Wann wir für dich kochen' );
$oldenhaus_text         = oldenhaus_feld( 'zeiten_text' );
$oldenhaus_parken       = oldenhaus_feld( 'parken_text' );

if ( empty( $oldenhaus_zeiten ) ) {
	return;
}
?>
<section class="abschnitt abschnitt--dunkel" id="oeffnungszeiten">
	<div class="wrap duo duo--breiter-text">

		<div>
			<h2><?php echo esc_html( $oldenhaus_titel ); ?></h2>

			<?php if ( '' !== $oldenhaus_text ) : ?>
				<p class="mt-klein"><?php echo esc_html( $oldenhaus_text ); ?></p>
			<?php endif; ?>

			<div class="anschrift">
				<h3>So findest du uns</h3>
				<?php if ( '' !== $oldenhaus_adresse['einzeilig'] ) : ?>
					<p>
						<?php echo esc_html( $oldenhaus_adresse['strasse'] ); ?><br>
						<?php echo esc_html( $oldenhaus_adresse['ort_zeile'] ); ?>
					</p>
				<?php else : ?>
					<p class="fehlt">Anschrift folgt</p>
				<?php endif; ?>

				<?php if ( '' !== $oldenhaus_parken ) : ?>
					<p><?php echo esc_html( $oldenhaus_parken ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div>
			<ul class="zeiten">
				<?php foreach ( $oldenhaus_zeiten as $oldenhaus_tag ) : ?>
					<li>
						<span class="tag"><?php echo esc_html( $oldenhaus_tag['name'] ); ?></span>
						<?php if ( $oldenhaus_tag['ruhetag'] ) : ?>
							<span class="zeit ruhetag">Ruhetag</span>
						<?php elseif ( '' !== $oldenhaus_tag['zeit'] ) : ?>
							<span class="zeit"><?php echo esc_html( $oldenhaus_tag['zeit'] ); ?></span>
						<?php else : ?>
							<span class="zeit fehlt">Zeit folgt</span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php
			// Der Kasten erscheint nur, wenn tatsächlich Sonderzeiten eingetragen sind.
			if ( null !== $oldenhaus_sonderzeiten ) :
				?>
				<div class="sonderzeiten">
					<?php if ( '' !== $oldenhaus_sonderzeiten['titel'] ) : ?>
						<h3><?php echo esc_html( $oldenhaus_sonderzeiten['titel'] ); ?></h3>
					<?php endif; ?>
					<ul>
						<?php foreach ( $oldenhaus_sonderzeiten['zeilen'] as $oldenhaus_zeile ) : ?>
							<li><?php echo esc_html( $oldenhaus_zeile ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>

	</div>
</section>
