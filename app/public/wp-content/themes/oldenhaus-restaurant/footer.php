<?php
/**
 * Fußbereich.
 *
 * Adresse, Telefon und Öffnungszeiten stammen aus derselben Quelle wie die Startseite:
 * „Kontakt & Zeiten" im Backend. Eine Änderung dort wirkt sich hier automatisch aus.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$oldenhaus_adresse   = oldenhaus_adresse();
$oldenhaus_zeiten    = oldenhaus_oeffnungszeiten();
$oldenhaus_instagram = oldenhaus_instagram();
?>
</main>

<footer class="site-footer">
	<div class="wrap">
		<div class="site-footer__spalten">

			<div>
				<h2>Oldenhaus</h2>
				<?php if ( '' !== $oldenhaus_adresse['strasse'] || '' !== $oldenhaus_adresse['ort_zeile'] ) : ?>
					<p>
						<?php if ( '' !== $oldenhaus_adresse['strasse'] ) : ?>
							<?php echo esc_html( $oldenhaus_adresse['strasse'] ); ?><br>
						<?php endif; ?>
						<?php echo esc_html( $oldenhaus_adresse['ort_zeile'] ); ?>
					</p>
				<?php else : ?>
					<p class="fehlt">Anschrift folgt</p>
				<?php endif; ?>

				<p class="mt-klein">
					<?php if ( oldenhaus_hat_telefon() ) : ?>
						<a href="<?php echo esc_url( oldenhaus_telefon_link() ); ?>">
							<?php echo esc_html( oldenhaus_telefon_anzeige() ); ?>
						</a>
					<?php else : ?>
						<span class="fehlt">Telefonnummer folgt</span>
					<?php endif; ?>
				</p>

				<?php if ( '' !== $oldenhaus_instagram ) : ?>
					<p class="mt-klein">
						<?php
						/*
						 * Bewusst ein einfacher Link statt eines eingebetteten Feeds.
						 * Ein Feed würde Inhalte von Instagram nachladen und damit eine
						 * Einwilligung nach TTDSG §25 erfordern.
						 *
						 * Das Symbol ist aria-hidden und liegt vor dem Wort. Vorgelesen
						 * und angesprungen wird der Link über „Instagram" – das Symbol
						 * ist Schmuck, nicht Information.
						 */
						?>
						<a class="instagram-link" href="<?php echo esc_url( $oldenhaus_instagram ); ?>" rel="noopener noreferrer nofollow" target="_blank">
							<?php
							echo oldenhaus_instagram_symbol(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- feste Zeichenkette aus oldenhaus_instagram_symbol().
							?>
							<span>Instagram</span>
						</a>
					</p>
				<?php endif; ?>
			</div>

			<div>
				<h2>Öffnungszeiten</h2>
				<?php if ( ! empty( $oldenhaus_zeiten ) ) : ?>
					<ul class="zeiten">
						<?php foreach ( $oldenhaus_zeiten as $oldenhaus_tag ) : ?>
							<li>
								<span class="tag"><?php echo esc_html( $oldenhaus_tag['name'] ); ?></span>
								<?php if ( $oldenhaus_tag['ruhetag'] ) : ?>
									<span class="zeit ruhetag">Ruhetag</span>
								<?php elseif ( '' !== $oldenhaus_tag['zeit'] ) : ?>
									<span class="zeit"><?php echo esc_html( $oldenhaus_tag['zeit'] ); ?></span>
								<?php else : ?>
									<span class="zeit">—</span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<div>
				<h2>Tisch reservieren</h2>
				<p>Ruf einfach an – wir nehmen deine Reservierung gern persönlich entgegen.</p>
				<p class="mt-mittel">
					<?php oldenhaus_anruf_button(); ?>
				</p>
			</div>

		</div>

		<div class="site-footer__unten">
			<span>&copy; <?php echo esc_html( (string) gmdate( 'Y' ) ); ?> Oldenhaus – Restaurant &amp; Pizzeria</span>

			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'rechtliches',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => false,
				)
			);
			?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
