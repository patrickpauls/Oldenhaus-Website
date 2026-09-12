<?php
/**
 * Abholung.
 *
 * Nur ein Hinweis mit Anruf-Button – kein Bestellsystem, kein Lieferportal.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$oldenhaus_titel  = oldenhaus_feld( 'abholung_titel' );
$oldenhaus_text   = oldenhaus_feld( 'abholung_text' );
$oldenhaus_knopf  = oldenhaus_feld( 'abholung_button', null, 'Zum Abholen bestellen' );
$oldenhaus_bild   = oldenhaus_bildfeld( 'abholung_bild' );

// Ohne Überschrift und Text wäre der Abschnitt leer – dann lieber ganz weglassen.
if ( '' === $oldenhaus_titel && '' === $oldenhaus_text ) {
	return;
}
?>
<section class="abschnitt" id="abholung">
	<div class="wrap duo duo--schmales-bild">

		<div class="duo__bild">
			<?php oldenhaus_foto( $oldenhaus_bild, 'oldenhaus-hochkant', 'Foto folgt: Pizzakarton auf dem Tresen', array( 'foto--hochkant' ) ); ?>
		</div>

		<div>
			<h2><?php echo esc_html( '' !== $oldenhaus_titel ? $oldenhaus_titel : 'Zum Mitnehmen' ); ?></h2>
			<?php oldenhaus_text_oder_hinweis( $oldenhaus_text, 'Text folgt' ); ?>

			<p class="mt-mittel">
				<?php oldenhaus_anruf_button( array( 'beschriftung' => $oldenhaus_knopf ) ); ?>
			</p>
		</div>

	</div>
</section>
