<?php
/**
 * Gutscheine.
 *
 * Kurzer Hinweis. Gutscheine gibt es ausschließlich vor Ort, es gibt bewusst
 * keinen Online-Verkauf.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$oldenhaus_titel = oldenhaus_feld( 'gutscheine_titel' );
$oldenhaus_text  = oldenhaus_feld( 'gutscheine_text' );

if ( '' === $oldenhaus_titel && '' === $oldenhaus_text ) {
	return;
}
?>
<section class="abschnitt" id="gutscheine">
	<div class="wrap">
		<h2><?php echo esc_html( '' !== $oldenhaus_titel ? $oldenhaus_titel : 'Gutscheine' ); ?></h2>
		<div class="mt-klein">
			<?php oldenhaus_text_oder_hinweis( $oldenhaus_text, 'Text folgt' ); ?>
		</div>
	</div>
</section>
