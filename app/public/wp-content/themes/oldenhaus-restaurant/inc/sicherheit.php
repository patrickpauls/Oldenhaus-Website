<?php
/**
 * Härtung und Drittanbieter-Freiheit.
 *
 * Zwei Ziele, die hier zusammenlaufen:
 *
 * 1. Die Website lädt nachweislich nichts von fremden Servern. Nur deshalb kommt sie
 *    ohne Einwilligungsbanner aus. WordPress bringt ab Werk zwei solche Anfragen mit,
 *    die hier abgeschaltet werden.
 * 2. Die Angriffsfläche wird verkleinert, ohne ein zusätzliches Plugin.
 *
 * Ausgangslage hilft: Die Website hat keine Formulare, nimmt keine Uploads von Besuchern
 * entgegen und bindet keinen Fremdcode ein. Reservierung und Bestellung laufen über
 * tel:-Links. Damit fällt die häufigste Einfallsklasse komplett weg.
 *
 * @package Oldenhaus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================================
 * 1 · Keine Anfragen an fremde Server
 * ====================================================================== */

/**
 * Schaltet die Emoji-Ersatzgrafiken von WordPress ab.
 *
 * Das ist der wichtigste Punkt dieser Datei. WordPress lädt fehlende Emoji als Bilder
 * von s.w.org nach, einem Server von Automattic (siehe wp-includes/formatting.php).
 * Dabei wird die IP-Adresse jedes Besuchers an einen Dritten übertragen – ohne Zutun
 * des Themes und ohne Einwilligung. Genau der Fall, der nach TTDSG §25 und DSGVO ein
 * Einwilligungsbanner nötig machen würde.
 *
 * Moderne Browser stellen Emoji ohnehin selbst dar; das Skript ist Altlast für sehr
 * alte Systeme.
 */
function oldenhaus_emoji_abschalten(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'embed_head', 'print_emoji_detection_script' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );

	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'enqueue_embed_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );

	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'oldenhaus_emoji_abschalten' );

/**
 * Entfernt das Emoji-Modul aus dem Editor.
 *
 * Ohne das würde der Editor weiterhin versuchen, das Modul von s.w.org nachzuladen.
 */
function oldenhaus_emoji_aus_editor( $plugins ) {
	return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
}
add_filter( 'tiny_mce_plugins', 'oldenhaus_emoji_aus_editor' );

/**
 * Verhindert den DNS-Prefetch auf s.w.org.
 *
 * WordPress kündigt den Emoji-Server im Kopfbereich per dns-prefetch an. Das allein
 * löst bereits eine DNS-Abfrage beim Besucher aus.
 */
function oldenhaus_emoji_prefetch_entfernen( $hinweise, string $beziehung ): array {
	if ( 'dns-prefetch' !== $beziehung ) {
		return (array) $hinweise;
	}

	return array_values(
		array_filter(
			(array) $hinweise,
			static function ( $hinweis ): bool {
				$url = is_array( $hinweis ) ? ( $hinweis['href'] ?? '' ) : $hinweis;

				return ! str_contains( (string) $url, 's.w.org' );
			}
		)
	);
}
add_filter( 'wp_resource_hints', 'oldenhaus_emoji_prefetch_entfernen', 10, 2 );

/**
 * Schaltet Gravatar ab.
 *
 * Profilbilder werden von secure.gravatar.com geladen – ebenfalls ein fremder Server.
 * Die Website zeigt ohnehin keine Kommentare, das Abschalten schließt nur die Lücke.
 */
add_filter( 'pre_option_show_avatars', '__return_zero' );

/**
 * Entfernt die oEmbed-Erkennung aus dem Kopfbereich.
 *
 * Betrifft nicht das Einbetten selbst, sondern die Verweise, über die fremde Seiten
 * Inhalte dieser Website einbetten könnten. Für ein Restaurant ohne Blog ohne Nutzen.
 */
function oldenhaus_oembed_aufraeumen(): void {
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 4 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );

	/*
	 * Auch die oEmbed-Schnittstelle selbst schliessen.
	 *
	 * Gefunden beim Nachpruefen: /wp-json/oembed/1.0/embed?url=... lieferte
	 * 'author_name' und 'author_url' aus - also genau den Anmeldenamen, den
	 * ?author=1 und /wp-json/wp/v2/users weiter unten muehsam verbergen. Die
	 * Umleitung des Autorenarchivs half nicht: Der Name stand im JSON, bevor
	 * ueberhaupt eine Adresse aufgerufen werden musste.
	 *
	 * Die Route wird deshalb gar nicht erst angemeldet. Moeglich ist das nur,
	 * weil diese Website nirgends eingebettet werden soll - die
	 * Erkennungsverweise oben sind aus demselben Grund schon entfernt.
	 */
	remove_action( 'rest_api_init', 'wp_oembed_register_route' );

	/*
	 * Auch das automatische Einbetten abschalten.
	 *
	 * Fügt jemand später eine YouTube- oder Maps-Adresse in einen Text ein, baut
	 * WordPress daraus von selbst ein iframe – das die Inhaltsrichtlinie weiter unten
	 * wortlos blockiert. Rechtlich ist das Blockieren richtig, praktisch sähe man nur
	 * eine leere Stelle ohne Erklärung. Ohne automatisches Einbetten bleibt die
	 * Adresse als Text stehen: sichtbar, nachvollziehbar, und ohne Anfrage an Dritte.
	 *
	 * Soll später bewusst etwas eingebettet werden, gehört ohnehin eine
	 * Einwilligungslösung dazu.
	 */
	if ( isset( $GLOBALS['wp_embed'] ) && $GLOBALS['wp_embed'] instanceof WP_Embed ) {
		remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
	}
}
add_action( 'init', 'oldenhaus_oembed_aufraeumen' );

/**
 * Streicht den Autorennamen aus einer oEmbed-Antwort.
 *
 * Zweites Netz zur Route oben: Meldet ein spaeteres Plugin die Schnittstelle
 * wieder an, steht der Anmeldename trotzdem nicht mehr darin.
 *
 * @param array<string, mixed> $daten Die Antwort.
 * @return array<string, mixed>
 */
function oldenhaus_oembed_autor_entfernen( $daten ) {
	if ( ! is_array( $daten ) ) {
		return $daten;
	}

	unset( $daten['author_name'], $daten['author_url'] );

	return $daten;
}
add_filter( 'oembed_response_data', 'oldenhaus_oembed_autor_entfernen', 99 );

/* =========================================================================
 * 2 · Weniger Angriffsfläche
 * ====================================================================== */

/**
 * Räumt den Kopfbereich auf.
 *
 * wp_generator verrät die WordPress-Version und damit, welche bekannten Lücken in
 * Frage kommen. RSD und WLW-Manifest gehören zu längst eingestellten Blog-Editoren.
 */
function oldenhaus_kopfbereich_aufraeumen(): void {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
}
add_action( 'init', 'oldenhaus_kopfbereich_aufraeumen' );

/**
 * Entfernt die Version auch aus Feeds und aus den Datei-Adressen.
 *
 * Das Cache-Busting übernimmt stattdessen der Zeitstempel der Datei,
 * siehe oldenhaus_dateiversion() in inc/enqueue.php.
 */
add_filter( 'the_generator', '__return_empty_string' );

/**
 * Streicht den ?ver=-Parameter mit der WordPress-Version aus Skript- und Stil-Adressen.
 */
function oldenhaus_version_aus_dateiadresse( string $adresse ): string {
	if ( ! str_contains( $adresse, 'ver=' ) ) {
		return $adresse;
	}

	$wp_version = get_bloginfo( 'version' );

	if ( '' !== $wp_version && str_contains( $adresse, 'ver=' . $wp_version ) ) {
		$adresse = remove_query_arg( 'ver', $adresse );
	}

	return $adresse;
}
add_filter( 'style_loader_src', 'oldenhaus_version_aus_dateiadresse', 20 );
add_filter( 'script_loader_src', 'oldenhaus_version_aus_dateiadresse', 20 );

/**
 * Schaltet XML-RPC ab.
 *
 * Die Schnittstelle ist das häufigste Ziel automatisierter Angriffe: Über
 * system.multicall lassen sich hunderte Passwörter in einer einzigen Anfrage
 * durchprobieren, und über Pingbacks lässt sich die Website als Verstärker für
 * Angriffe auf Dritte missbrauchen. Genutzt wird sie hier von nichts.
 *
 * Wichtig: Die beiden Filter unten reichen dafür nicht aus, auch wenn sie in vielen
 * Anleitungen als Lösung stehen. 'xmlrpc_enabled' schaltet nur die Methoden ab, die
 * eine Anmeldung erfordern. Und 'xmlrpc_methods' kann system.multicall gar nicht
 * entfernen, weil IXR_Server::setCallbacks() die system.*-Methoden erst *nach* dem
 * Filter hinzufügt (siehe wp-includes/IXR/class-IXR-server.php). Geprüft: Ohne die
 * Sperre unten beantwortet die Schnittstelle system.listMethods weiterhin bereitwillig.
 *
 * Deshalb wird die Anfrage abgewiesen, bevor überhaupt eine Methode zum Zug kommt.
 * xmlrpc.php setzt die Konstante XMLRPC_REQUEST, bevor WordPress geladen wird – daran
 * lässt sich der Zugriff sicher erkennen.
 */
function oldenhaus_xmlrpc_abweisen(): void {
	if ( ! defined( 'XMLRPC_REQUEST' ) || ! XMLRPC_REQUEST ) {
		return;
	}

	status_header( 403 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	exit( 'XML-RPC ist auf dieser Website abgeschaltet.' );
}
add_action( 'init', 'oldenhaus_xmlrpc_abweisen', 0 );

// Zusätzlich, für den Fall, dass die Sperre oben einmal umgangen wird.
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );

/**
 * Blockiert das Ausspähen von Benutzernamen über ?author=1.
 *
 * WordPress leitet diese Adresse normalerweise auf die Autorenseite um und verrät
 * dabei den Anmeldenamen – die halbe Arbeit für einen Brute-Force-Angriff. Da die
 * Website keine Autorenseiten hat, geht es zurück zur Startseite.
 */
function oldenhaus_autoren_ausspaehen_blockieren(): void {
	if ( is_admin() ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reiner Lesezugriff auf die Adresse.
	if ( is_author() || isset( $_GET['author'] ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'oldenhaus_autoren_ausspaehen_blockieren' );

/**
 * Schließt die Benutzerliste der REST-Schnittstelle für Nichtangemeldete.
 *
 * Dieselbe Information wie oben, nur über einen anderen Weg: /wp-json/wp/v2/users
 * liefert ab Werk alle Benutzernamen aus.
 */
function oldenhaus_rest_benutzerliste_schliessen( array $endpunkte ): array {
	if ( is_user_logged_in() ) {
		return $endpunkte;
	}

	unset( $endpunkte['/wp/v2/users'] );
	unset( $endpunkte['/wp/v2/users/(?P<id>[\d]+)'] );

	return $endpunkte;
}
add_filter( 'rest_endpoints', 'oldenhaus_rest_benutzerliste_schliessen' );

/**
 * Sperrt den Datei-Editor im Backend.
 *
 * Ohne das lässt sich über einen übernommenen Administratorzugang direkt PHP-Code in
 * Theme-Dateien schreiben – aus einem gestohlenen Passwort wird damit sofort
 * Codeausführung auf dem Server.
 *
 * Auf dem Livesystem gehört dieselbe Zeile zusätzlich in die wp-config.php, damit sie
 * auch dann greift, wenn das Theme einmal nicht geladen wird.
 */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

/**
 * Schaltet Anwendungspasswörter ab.
 *
 * Sie sind für externe Anwendungen gedacht, die sich an der Website anmelden. Hier
 * nutzt das nichts – es bleibt nur ein zusätzlicher Anmeldeweg offen.
 */
add_filter( 'wp_is_application_passwords_available', '__return_false' );

/**
 * Schaltet Kommentare, Pingbacks und Trackbacks vollständig ab.
 *
 * Ein Restaurant braucht sie nicht, und sie sind ein klassisches Ziel für Spam und
 * eingeschleuste Links.
 */
function oldenhaus_kommentare_abschalten(): void {
	// Kommentarunterstützung aus allen Inhaltstypen entfernen.
	foreach ( get_post_types() as $inhaltstyp ) {
		if ( post_type_supports( $inhaltstyp, 'comments' ) ) {
			remove_post_type_support( $inhaltstyp, 'comments' );
			remove_post_type_support( $inhaltstyp, 'trackbacks' );
		}
	}
}
add_action( 'init', 'oldenhaus_kommentare_abschalten', 100 );

add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 20 );

/**
 * Entfernt den Kommentar-Menüpunkt aus dem Backend.
 */
function oldenhaus_kommentarmenue_entfernen(): void {
	remove_menu_page( 'edit-comments.php' );
}
add_action( 'admin_menu', 'oldenhaus_kommentarmenue_entfernen' );

/* =========================================================================
 * 3 · Anmeldung
 * ====================================================================== */

/**
 * Wie oft darf eine IP sich vertun, bevor gesperrt wird.
 */
const OLDENHAUS_LOGIN_VERSUCHE_FREI = 5;

/**
 * Merkt sich innerhalb einer Anfrage, dass die Anmeldung wegen einer Sperre
 * abgewiesen wurde.
 *
 * Nötig, weil die Vereinheitlichung der Fehlermeldung unten sonst auch den
 * Sperrhinweis überschreiben würde – wer ausgesperrt ist, bekäme dann nur „Name oder
 * Passwort stimmen nicht" zu sehen und würde endlos weiterprobieren, ohne zu erfahren,
 * dass er schlicht warten muss.
 *
 * @param string|null $setzen Meldung setzen, oder null zum Auslesen.
 */
function oldenhaus_login_sperrmeldung( ?string $setzen = null ): string {
	static $meldung = '';

	if ( null !== $setzen ) {
		$meldung = $setzen;
	}

	return $meldung;
}

/**
 * Vereinheitlicht die Fehlermeldung bei der Anmeldung.
 *
 * WordPress unterscheidet ab Werk zwischen „Benutzername unbekannt" und „Passwort
 * falsch". Damit lässt sich Schritt für Schritt herausfinden, welche Benutzernamen
 * existieren – und der Angriff auf das Passwort beschränken.
 *
 * Der Sperrhinweis bleibt als einzige Ausnahme erhalten: Er verrät nichts über
 * vorhandene Konten, ist für rechtmäßige Nutzer aber die einzige Erklärung dafür,
 * warum gerade gar nichts mehr geht.
 */
function oldenhaus_anmeldefehler_vereinheitlichen(): string {
	$sperrhinweis = oldenhaus_login_sperrmeldung();

	if ( '' !== $sperrhinweis ) {
		return esc_html( $sperrhinweis );
	}

	return 'Anmeldename oder Passwort stimmen nicht.';
}
add_filter( 'login_errors', 'oldenhaus_anmeldefehler_vereinheitlichen' );

/**
 * Die IP-Adresse des Anfragenden.
 *
 * Bewusst ausschließlich REMOTE_ADDR. Header wie X-Forwarded-For kann ein Angreifer
 * frei setzen – wer darauf sperrt, sperrt in Wahrheit niemanden.
 *
 * Hinweis für den Livebetrieb: Steht die Website hinter einem Reverse Proxy oder CDN,
 * ist REMOTE_ADDR die Adresse des Proxys. Dann müsste die Auswertung dort erfolgen,
 * wo die echte Adresse bekannt ist.
 */
function oldenhaus_anfrage_ip(): string {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';
	$ip = filter_var( $ip, FILTER_VALIDATE_IP );

	return is_string( $ip ) ? $ip : '';
}

/**
 * Der Name des Zwischenspeichers, in dem die Fehlversuche einer IP stehen.
 */
function oldenhaus_login_schluessel( string $ip ): string {
	return 'oldenhaus_login_' . md5( $ip );
}

/**
 * Wie lange nach der wievielten Fehleingabe gesperrt wird.
 *
 * Die Wartezeit steigt an, statt sofort hart zu sperren: Wer sein Passwort wirklich
 * vergessen hat, wartet fünf Minuten. Ein automatisierter Angriff läuft dagegen schnell
 * in Stunden, was ihn unwirtschaftlich macht.
 */
function oldenhaus_sperrdauer( int $versuche ): int {
	if ( $versuche >= 15 ) {
		return 2 * HOUR_IN_SECONDS;
	}

	if ( $versuche >= 10 ) {
		return 30 * MINUTE_IN_SECONDS;
	}

	return 5 * MINUTE_IN_SECONDS;
}

/**
 * Zählt einen Fehlversuch und sperrt, wenn das Maß voll ist.
 */
function oldenhaus_fehlversuch_zaehlen(): void {
	$ip = oldenhaus_anfrage_ip();

	if ( '' === $ip ) {
		return;
	}

	$schluessel = oldenhaus_login_schluessel( $ip );
	$daten      = get_transient( $schluessel );

	$versuche = is_array( $daten ) ? (int) ( $daten['versuche'] ?? 0 ) : 0;
	++$versuche;

	$gesperrt_bis = ( $versuche >= OLDENHAUS_LOGIN_VERSUCHE_FREI )
		? time() + oldenhaus_sperrdauer( $versuche )
		: 0;

	set_transient(
		$schluessel,
		array(
			'versuche'     => $versuche,
			'gesperrt_bis' => $gesperrt_bis,
		),
		// Der Zähler selbst verfällt nach einem Tag, damit alte Fehlversuche nicht
		// ewig nachwirken.
		DAY_IN_SECONDS
	);
}
add_action( 'wp_login_failed', 'oldenhaus_fehlversuch_zaehlen' );

/**
 * Weist die Anmeldung ab, solange die IP gesperrt ist.
 *
 * Läuft nach der eigentlichen Passwortprüfung (Priorität 30), damit auch ein zufällig
 * richtiges Passwort während der Sperre nicht durchkommt.
 *
 * @param WP_User|WP_Error|null $benutzer Ergebnis der bisherigen Prüfung.
 * @return WP_User|WP_Error|null
 */
function oldenhaus_gesperrte_anmeldung_abweisen( $benutzer, string $anmeldename ) {
	if ( '' === $anmeldename ) {
		return $benutzer;
	}

	$ip = oldenhaus_anfrage_ip();

	if ( '' === $ip ) {
		return $benutzer;
	}

	$daten = get_transient( oldenhaus_login_schluessel( $ip ) );

	if ( ! is_array( $daten ) ) {
		return $benutzer;
	}

	$gesperrt_bis = (int) ( $daten['gesperrt_bis'] ?? 0 );

	if ( $gesperrt_bis <= time() ) {
		return $benutzer;
	}

	$restminuten = max( 1, (int) ceil( ( $gesperrt_bis - time() ) / MINUTE_IN_SECONDS ) );

	$hinweis = sprintf(
		'Zu viele fehlgeschlagene Anmeldeversuche. Bitte versuche es in %d Minuten erneut.',
		$restminuten
	);

	// Damit die Vereinheitlichung der Fehlermeldung diesen Hinweis stehen lässt.
	oldenhaus_login_sperrmeldung( $hinweis );

	return new WP_Error( 'oldenhaus_zu_viele_versuche', $hinweis );
}
add_filter( 'authenticate', 'oldenhaus_gesperrte_anmeldung_abweisen', 30, 2 );

/**
 * Löscht den Zähler nach erfolgreicher Anmeldung.
 */
function oldenhaus_fehlversuche_zuruecksetzen(): void {
	$ip = oldenhaus_anfrage_ip();

	if ( '' !== $ip ) {
		delete_transient( oldenhaus_login_schluessel( $ip ) );
	}
}
add_action( 'wp_login', 'oldenhaus_fehlversuche_zuruecksetzen' );

/* =========================================================================
 * 4 · Sicherheits-Header
 * ====================================================================== */

/**
 * Setzt Sicherheits-Header.
 *
 * Zur Content-Security-Policy: Sie ist hier so streng, wie sie es nur sein kann, weil
 * die Website nachweislich nichts von fremden Servern lädt. Sie erzwingt damit
 * technisch, was sonst nur eine Absichtserklärung wäre – selbst wenn später versehentlich
 * ein fremdes Skript eingebunden würde, lädt der Browser es nicht.
 *
 * Ehrlich dazugesagt: 'unsafe-inline' bleibt für Stile und Skripte nötig, weil
 * WordPress an mehreren Stellen eingebetteten Code ausgibt. Die Richtlinie schützt
 * hier also vor *fremden Quellen*, nicht vor eingeschleustem Inline-Code.
 *
 * Soll später bewusst etwas Externes eingebunden werden – eine Karte, ein Video –,
 * lässt sich die Richtlinie über den Filter 'oldenhaus_csp' anpassen. Dann wäre
 * allerdings ohnehin ein Einwilligungsbanner fällig.
 */
function oldenhaus_sicherheits_header(): void {
	if ( is_admin() || headers_sent() ) {
		return;
	}

	header( 'X-Content-Type-Options: nosniff' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=(), interest-cohort=()' );

	$richtlinie = implode(
		'; ',
		array(
			"default-src 'self'",
			"base-uri 'self'",
			"object-src 'none'",
			"frame-ancestors 'self'",
			"frame-src 'none'",
			"form-action 'self'",
			"img-src 'self' data:",
			"font-src 'self'",
			"style-src 'self' 'unsafe-inline'",
			"script-src 'self' 'unsafe-inline'",
			"connect-src 'self'",
		)
	);

	/**
	 * Erlaubt das Anpassen der Content-Security-Policy.
	 *
	 * Ein leerer Rückgabewert schaltet den Header ab.
	 */
	$richtlinie = (string) apply_filters( 'oldenhaus_csp', $richtlinie );

	if ( '' !== $richtlinie ) {
		header( 'Content-Security-Policy: ' . $richtlinie );
	}
}
add_action( 'send_headers', 'oldenhaus_sicherheits_header' );

/*
 * Die Anmeldeseite zusaetzlich versorgen.
 *
 * 'send_headers' feuert in WP::send_headers() und damit nur im normalen
 * Seitenaufbau. wp-login.php ruft wp() nie auf - die Header fehlten dort also,
 * ausgerechnet auf der einzigen Seite der Installation, die ueberhaupt Eingaben
 * entgegennimmt und die am haeufigsten angegriffen wird.
 */
add_action( 'login_init', 'oldenhaus_sicherheits_header' );

/* =========================================================================
 * 5 · Fehlersuchmodus
 * ====================================================================== */

/**
 * Wohin WordPress das Fehlerprotokoll schreibt - oder '', wenn es aus ist.
 *
 * WP_DEBUG_LOG kennt drei Zustaende: aus (false), ein mit Standardpfad (true,
 * dann wp-content/debug.log) oder ein mit eigenem Pfad (ein String). Nur der
 * mittlere Fall ist der gefaehrliche - er legt die Datei mitten in den
 * oeffentlichen Webordner.
 */
function oldenhaus_fehlerprotokoll_pfad(): string {
	if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
		return '';
	}

	if ( is_string( WP_DEBUG_LOG ) ) {
		return wp_normalize_path( WP_DEBUG_LOG );
	}

	return wp_normalize_path( WP_CONTENT_DIR . '/debug.log' );
}

/**
 * Ob ein Pfad innerhalb des oeffentlichen Webordners liegt.
 *
 * Alles unterhalb von ABSPATH ist ueber eine Adresse abrufbar, sofern der
 * Webserver es nicht ausdruecklich sperrt. Genau das ist die Schwachstelle:
 * Ein Fehlerprotokoll nennt vollstaendige Serverpfade, Dateinamen und
 * Zeilennummern - eine Landkarte der Installation.
 */
function oldenhaus_pfad_im_webordner( string $pfad ): bool {
	if ( '' === $pfad ) {
		return false;
	}

	return str_starts_with( $pfad, wp_normalize_path( ABSPATH ) );
}

/**
 * Sammelt die Beanstandungen rund um den Fehlersuchmodus.
 *
 * @return array<int, string>
 */
function oldenhaus_fehlersuche_beanstandungen(): array {
	$beanstandungen = array();
	$umgebung       = wp_get_environment_type();
	$livebetrieb    = ! in_array( $umgebung, array( 'local', 'development' ), true );

	$protokoll = oldenhaus_fehlerprotokoll_pfad();

	if ( oldenhaus_pfad_im_webordner( $protokoll ) ) {
		$beanstandungen[] = sprintf(
			'Das Fehlerprotokoll liegt im oeffentlichen Webordner (<code>%s</code>) und ist damit ueber die Adresse abrufbar. Es nennt vollstaendige Serverpfade. In der <code>wp-config.php</code> gehoert <code>WP_DEBUG_LOG</code> auf einen Pfad ausserhalb des Webordners.',
			esc_html( str_replace( wp_normalize_path( ABSPATH ), '', $protokoll ) )
		);
	}

	if ( $livebetrieb && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$beanstandungen[] = 'Der Fehlersuchmodus (<code>WP_DEBUG</code>) ist im Livebetrieb eingeschaltet.';
	}

	if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY && $livebetrieb ) {
		$beanstandungen[] = 'Fehlermeldungen werden im Livebetrieb auf der Seite ausgegeben (<code>WP_DEBUG_DISPLAY</code>). Besucher saehen Serverpfade.';
	}

	// Unabhaengig von den Einstellungen: Liegt die Datei da, ist sie abrufbar.
	// Ein Plugin oder eine wiederhergestellte Sicherung kann sie erneut anlegen.
	$altlast = wp_normalize_path( WP_CONTENT_DIR . '/debug.log' );

	if ( '' === $protokoll && file_exists( $altlast ) ) {
		$beanstandungen[] = 'In <code>wp-content/</code> liegt noch eine <code>debug.log</code> aus einer frueheren Sitzung. Sie ist abrufbar und sollte geloescht werden.';
	}

	return $beanstandungen;
}

/**
 * Weist im Backend auf einen offenen Fehlersuchmodus hin.
 *
 * Bewusst ein Hinweis und keine stille Korrektur: WP_DEBUG_LOG ist eine
 * Konstante und laesst sich zur Laufzeit nicht mehr aendern - eine Umleitung
 * des Protokolls waere Flickwerk und wuerde den eigentlichen Fehler in der
 * wp-config.php verdecken. Der Hinweis ist nur fuer Administratoren sichtbar
 * und nennt genau die Datei, in der die Einstellung steht.
 */
function oldenhaus_fehlersuche_hinweis(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$beanstandungen = oldenhaus_fehlersuche_beanstandungen();

	if ( array() === $beanstandungen ) {
		return;
	}

	echo '<div class="notice notice-error"><p><strong>Sicherheit: Der Fehlersuchmodus gibt Interna preis.</strong></p><ul style="list-style:disc;margin-left:1.5em;">';

	foreach ( $beanstandungen as $beanstandung ) {
		// wp_kses_post() laesst <code> stehen; die Texte stammen ausschliesslich
		// von oben, Variablen sind dort bereits escaped.
		echo '<li>' . wp_kses_post( $beanstandung ) . '</li>';
	}

	echo '</ul></div>';
}
add_action( 'admin_notices', 'oldenhaus_fehlersuche_hinweis' );
