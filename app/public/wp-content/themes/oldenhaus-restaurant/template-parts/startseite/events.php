<?php
/**
 * Feiern und Events.
 *
 * Hinweisblock, keine eigene Unterseite und kein Anfrageformular – Anfragen laufen
 * telefonisch. Der Bild-Text-Block ist gedreht, damit die Bilder der Startseite nicht
 * alle in derselben Spalte stehen.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$oldenhaus_titel = oldenhaus_feld( 'events_titel' );
$oldenhaus_text  = oldenhaus_feld( 'events_text' );
$oldenhaus_bild  = oldenhaus_bildfeld( 'events_bild' );

if ( '' === $oldenhaus_titel && '' === $oldenhaus_text ) {
	return;
}
?>
<section class="abschnitt abschnitt--holz" id="feiern">
	<div class="wrap duo duo--gedreht">

		<div class="duo__bild">
			<?php oldenhaus_foto( $oldenhaus_bild, 'oldenhaus-quer', 'Foto folgt: gedeckte Tafel für eine Feier' ); ?>
		</div>

		<div>
			<h2><?php echo esc_html( '' !== $oldenhaus_titel ? $oldenhaus_titel : 'Feiern bei uns' ); ?></h2>
			<?php oldenhaus_text_oder_hinweis( $oldenhaus_text, 'Text folgt' ); ?>

			<p class="mt-mittel">
				<?php oldenhaus_anruf_button( array( 'beschriftung' => 'Feier anfragen' ) ); ?>
			</p>
		</div>

	</div>
</section>
