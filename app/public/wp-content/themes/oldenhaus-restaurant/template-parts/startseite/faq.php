<?php
/**
 * Fragen und Antworten.
 *
 * Letzter Abschnitt der Startseite und bewusst nicht in der Navigation.
 * Aufgeklappt wird über natives details/summary – kein JavaScript, keine Animation.
 *
 * Fragen ohne Antwort und Entwürfe erscheinen hier nicht: Eine Aussage über das Lokal,
 * die noch niemand bestätigt hat, soll nicht öffentlich stehen.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'restaurant_basis_faq_eintraege' ) ) {
	return;
}

$oldenhaus_fragen = restaurant_basis_faq_eintraege();

if ( empty( $oldenhaus_fragen ) ) {
	return;
}

$oldenhaus_titel     = oldenhaus_feld( 'faq_titel', null, 'Gut zu wissen' );
$oldenhaus_einleitung = oldenhaus_feld( 'faq_einleitung' );
?>
<section class="abschnitt abschnitt--holz" id="fragen">
	<div class="wrap">
		<div class="abschnitt__kopf">
			<h2><?php echo esc_html( $oldenhaus_titel ); ?></h2>
			<?php if ( '' !== $oldenhaus_einleitung ) : ?>
				<p><?php echo esc_html( $oldenhaus_einleitung ); ?></p>
			<?php endif; ?>
		</div>

		<div class="faq">
			<?php foreach ( $oldenhaus_fragen as $oldenhaus_frage ) : ?>
				<details>
					<summary><?php echo esc_html( $oldenhaus_frage['frage'] ); ?></summary>
					<div class="faq__antwort">
						<?php echo wp_kses_post( wpautop( $oldenhaus_frage['antwort'] ) ); ?>
					</div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
