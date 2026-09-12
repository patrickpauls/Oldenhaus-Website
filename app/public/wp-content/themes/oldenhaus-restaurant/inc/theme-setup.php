<?php
/**
 * Grundeinstellungen des Themes und Menüpositionen.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meldet die Funktionen an, die das Theme unterstützt.
 */
function oldenhaus_theme_setup(): void {
	// Der Seitentitel kommt von WordPress, nicht hartcodiert aus header.php.
	add_theme_support( 'title-tag' );

	// Beitragsbilder werden für die Text-Bild-Blöcke und die Galerie gebraucht.
	add_theme_support( 'post-thumbnails' );

	/*
	 * Logo-Unterstützung, obwohl aktuell noch kein Logo vorliegt.
	 *
	 * Die Spec verlangt, dass das spätere Logo ohne Code-Änderung eingesetzt werden
	 * kann. Über den Customizer lädt der Kunde es hoch, und der Header zeigt es
	 * automatisch statt der Wortmarke an – siehe oldenhaus_wortmarke().
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support(
		'html5',
		array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	// Sorgt dafür, dass eingebettete Inhalte nicht aus ihrem Container laufen.
	add_theme_support( 'responsive-embeds' );

	// Bildformate für die Text-Bild-Blöcke und die Galerie.
	add_image_size( 'oldenhaus-hochkant', 720, 900, true );
	add_image_size( 'oldenhaus-quer', 960, 640, true );

	register_nav_menus(
		array(
			'hauptnavigation' => 'Hauptnavigation (Kopfzeile)',
			'rechtliches'     => 'Rechtliches (Fußzeile)',
		)
	);
}
add_action( 'after_setup_theme', 'oldenhaus_theme_setup' );

/**
 * Begrenzt die Breite eingebetteter Inhalte auf die Containerbreite.
 */
function oldenhaus_content_width(): void {
	$GLOBALS['content_width'] = 1120;
}
add_action( 'after_setup_theme', 'oldenhaus_content_width', 0 );
