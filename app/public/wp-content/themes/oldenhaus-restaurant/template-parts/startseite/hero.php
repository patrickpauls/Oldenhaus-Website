<?php
/**
 * Hero mit ruhiger Bild-Slideshow.
 *
 * Bis zu fünf Bilder, langsamer Crossfade. Das Wechseln übernimmt assets/js/oldenhaus.js
 * und unterbleibt, wenn im Betriebssystem weniger Bewegung eingestellt ist.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Nur die tatsächlich gefüllten Bildfelder einsammeln – Lücken werden übersprungen.
$oldenhaus_bilder = array();

for ( $oldenhaus_i = 1; $oldenhaus_i <= 5; $oldenhaus_i++ ) {
	$oldenhaus_bild_id = oldenhaus_bildfeld( 'hero_bild_' . $oldenhaus_i );

	if ( $oldenhaus_bild_id > 0 ) {
		$oldenhaus_bilder[] = $oldenhaus_bild_id;
	}
}

$oldenhaus_leitspruch = oldenhaus_feld( 'leitspruch' );
$oldenhaus_unterzeile = oldenhaus_feld( 'unterzeile' );

/*
 * Die Öffnungszeiten reisen als JSON mit, damit der Hinweis „Heute geöffnet" im
 * Browser gebildet werden kann. Das bleibt auch dann richtig, wenn die Seite später
 * aus einem Cache kommt – anders als ein auf dem Server gerenderter Wochentag.
 */
$oldenhaus_zeiten_json = array();

foreach ( oldenhaus_oeffnungszeiten() as $oldenhaus_tag ) {
	$oldenhaus_zeiten_json[] = array(
		'ruhetag' => $oldenhaus_tag['ruhetag'],
		'zeit'    => $oldenhaus_tag['zeit'],
	);
}
?>
<section class="hero">
	<div class="hero__bilder">
		<?php if ( ! empty( $oldenhaus_bilder ) ) : ?>
			<?php foreach ( $oldenhaus_bilder as $oldenhaus_index => $oldenhaus_bild_id ) : ?>
				<div class="hero__bild<?php echo 0 === $oldenhaus_index ? ' is-active' : ''; ?>">
					<?php
					/*
					 * Das erste Bild ist das grösste sichtbare Element der Seite und wird
					 * deshalb bevorzugt geladen. Die übrigen erst bei Bedarf – am Telefon
					 * der spürbarste Unterschied beim Laden.
					 */
					echo wp_get_attachment_image(
						$oldenhaus_bild_id,
						'full',
						false,
						array(
							'loading'       => 0 === $oldenhaus_index ? 'eager' : 'lazy',
							'fetchpriority' => 0 === $oldenhaus_index ? 'high' : 'auto',
							'decoding'      => 'async',
							'sizes'         => '100vw',
							'alt'           => '',
						)
					);
					?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<div class="hero__inhalt">
		<div class="wrap">
			<h1>
				<?php
				echo '' !== $oldenhaus_leitspruch
					? esc_html( $oldenhaus_leitspruch )
					: 'Leitspruch folgt';
				?>
			</h1>

			<?php if ( '' !== $oldenhaus_unterzeile ) : ?>
				<p class="hero__unterzeile"><?php echo esc_html( $oldenhaus_unterzeile ); ?></p>
			<?php endif; ?>

			<div class="hero__aktionen">
				<?php oldenhaus_anruf_button(); ?>

				<?php if ( ! empty( $oldenhaus_zeiten_json ) ) : ?>
					<span class="heute" data-zeiten="<?php echo esc_attr( (string) wp_json_encode( $oldenhaus_zeiten_json ) ); ?>" hidden></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
