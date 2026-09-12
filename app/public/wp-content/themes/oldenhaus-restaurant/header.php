<?php
/**
 * Kopfbereich und Seitenkopf.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$oldenhaus_ueber_hero = oldenhaus_hat_hero();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#inhalt">Zum Inhalt springen</a>

<header class="site-header <?php echo $oldenhaus_ueber_hero ? 'site-header--ueber-hero' : 'site-header--fest'; ?>">
	<div class="wrap">
		<?php oldenhaus_wortmarke(); ?>

		<nav id="hauptnavigation" class="hauptnav" aria-label="Hauptnavigation" hidden>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'hauptnavigation',
					'container'      => false,
					'depth'          => 1, // Keine Untermenüs – so verlangt es die Spec.
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>

		<?php
		oldenhaus_anruf_button(
			array(
				'beschriftung' => 'Anrufen',
				'klassen'      => 'btn header-call',
			)
		);
		?>

		<button class="menue-knopf" type="button" aria-expanded="false" aria-controls="hauptnavigation">
			<span class="menue-knopf__balken" aria-hidden="true"></span>
			<span class="nur-sr">Menü</span>
		</button>
	</div>

	<?php
	/*
	 * Ohne JavaScript lässt sich das Menü am Telefon nicht aufklappen. Statt es dann
	 * unerreichbar zu lassen, steht es einfach dauerhaft offen unter der Kopfzeile.
	 */
	?>
	<noscript>
		<style>
			/* Absolute Lage beibehalten, sonst rutscht die Navigation als Block
			   zwischen Wortmarke und Anruf-Button in die Kopfzeile hinein. */
			.hauptnav[hidden] { display: block; }
			.menue-knopf { display: none; }
		</style>
	</noscript>
</header>

<main id="inhalt">
