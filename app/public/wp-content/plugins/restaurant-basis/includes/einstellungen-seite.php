<?php
/**
 * Zentrale Kontakt- und Öffnungszeitendaten.
 *
 * Diese Angaben speisen mehrere Stellen der Website gleichzeitig – Hero, den Abschnitt
 * unter dem Hero und den Footer. Sie liegen deshalb an genau einer Stelle: Ändert der
 * Kunde die Telefonnummer hier, ändert sie sich überall.
 *
 * Warum die Settings API und nicht ACF: ACF-Options-Pages sind kostenpflichtig (Pro).
 * Die Daten stattdessen auf eine normale Seite zu legen wäre fragil – der Kunde kann
 * Seiten umbenennen, in den Papierkorb legen oder löschen, und damit wären Telefonnummer
 * und Öffnungszeiten der gesamten Website weg. Die Settings API gehört zum WordPress-Kern,
 * ist seit Jahren stabil und bringt Nonce- und Rechteprüfung mit.
 *
 * @package Restaurant_Basis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const RESTAURANT_BASIS_OPTION  = 'restaurant_basis_einstellungen';
const RESTAURANT_BASIS_GRUPPE  = 'restaurant_basis_einstellungen_gruppe';
const RESTAURANT_BASIS_SEITE   = 'restaurant-basis-einstellungen';

/**
 * Die Wochentage in der Reihenfolge, in der sie angezeigt werden.
 *
 * Schlüssel werden als Feldnamen gespeichert und dürfen sich nicht mehr ändern,
 * sonst gehen gepflegte Zeiten verloren.
 */
function restaurant_basis_wochentage(): array {
	return array(
		'montag'     => 'Montag',
		'dienstag'   => 'Dienstag',
		'mittwoch'   => 'Mittwoch',
		'donnerstag' => 'Donnerstag',
		'freitag'    => 'Freitag',
		'samstag'    => 'Samstag',
		'sonntag'    => 'Sonntag',
	);
}

/**
 * Hängt die Einstellungsseite als eigenen Menüpunkt ins Backend.
 *
 * Bewusst auf oberster Ebene statt unter „Einstellungen": Der Kunde soll die
 * Öffnungszeiten finden, ohne zu wissen, dass WordPress so etwas „Einstellungen" nennt.
 */
function restaurant_basis_menue_anlegen(): void {
	add_menu_page(
		'Kontakt und Öffnungszeiten',
		'Kontakt & Zeiten',
		'manage_options',
		RESTAURANT_BASIS_SEITE,
		'restaurant_basis_seite_ausgeben',
		'dashicons-phone',
		22
	);
}
add_action( 'admin_menu', 'restaurant_basis_menue_anlegen' );

/**
 * Registriert Option, Abschnitte und Felder.
 */
function restaurant_basis_einstellungen_registrieren(): void {
	register_setting(
		RESTAURANT_BASIS_GRUPPE,
		RESTAURANT_BASIS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'restaurant_basis_einstellungen_pruefen',
			'default'           => array(),
		)
	);

	add_settings_section(
		'restaurant_basis_kontakt',
		'Kontakt',
		static function (): void {
			echo '<p>Diese Angaben erscheinen im Footer und steuern alle Anruf-Buttons der Website.</p>';
		},
		RESTAURANT_BASIS_SEITE
	);

	$kontaktfelder = array(
		'telefon'   => array( 'Telefonnummer', 'So, wie sie auf der Website stehen soll – zum Beispiel 04864 1234. Der Anruf-Link wird daraus automatisch gebildet.' ),
		'email'     => array( 'E-Mail-Adresse', 'Wird fürs Impressum benötigt.' ),
		'strasse'   => array( 'Straße und Hausnummer', '' ),
		'plz'       => array( 'Postleitzahl', '' ),
		'ort'       => array( 'Ort', '' ),
		'instagram' => array( 'Instagram-Adresse', 'Vollständige Adresse inklusive https://. Leer lassen, wenn kein Instagram verlinkt werden soll.' ),
	);

	foreach ( $kontaktfelder as $schluessel => $angaben ) {
		add_settings_field(
			$schluessel,
			esc_html( $angaben[0] ),
			'restaurant_basis_feld_text',
			RESTAURANT_BASIS_SEITE,
			'restaurant_basis_kontakt',
			array(
				'schluessel'  => $schluessel,
				'beschreibung' => $angaben[1],
				'label_for'   => 'restaurant-basis-' . $schluessel,
			)
		);
	}

	add_settings_section(
		'restaurant_basis_zeiten',
		'Öffnungszeiten',
		static function (): void {
			echo '<p>Diese Zeiten erscheinen auf der Startseite und im Footer. Eine Änderung hier wirkt sich überall aus.</p>';
			echo '<p>Schreibe die Zeit so, wie sie beim Gast stehen soll – zum Beispiel <code>17–22 Uhr</code> oder <code>12–14 und 17–22 Uhr</code>. Für einen Ruhetag setze das Häkchen; das Zeitfeld bleibt dann leer.</p>';
		},
		RESTAURANT_BASIS_SEITE
	);

	foreach ( restaurant_basis_wochentage() as $schluessel => $beschriftung ) {
		add_settings_field(
			'tag_' . $schluessel,
			esc_html( $beschriftung ),
			'restaurant_basis_feld_wochentag',
			RESTAURANT_BASIS_SEITE,
			'restaurant_basis_zeiten',
			array(
				'schluessel' => $schluessel,
				'label_for'  => 'restaurant-basis-zeit-' . $schluessel,
			)
		);
	}

	add_settings_section(
		'restaurant_basis_sonderzeiten',
		'Sonderöffnungszeiten',
		static function (): void {
			echo '<p>Für Feiertage, Urlaub oder kurzfristige Änderungen. Dieser Kasten erscheint auf der Website <strong>nur dann</strong>, wenn hier etwas eingetragen ist – ansonsten bleibt er unsichtbar. Zum Entfernen einfach den Text löschen.</p>';
		},
		RESTAURANT_BASIS_SEITE
	);

	add_settings_field(
		'sonderzeiten_titel',
		'Überschrift',
		'restaurant_basis_feld_text',
		RESTAURANT_BASIS_SEITE,
		'restaurant_basis_sonderzeiten',
		array(
			'schluessel'   => 'sonderzeiten_titel',
			'beschreibung' => 'Zum Beispiel: Weihnachten und Neujahr',
			'label_for'    => 'restaurant-basis-sonderzeiten_titel',
		)
	);

	add_settings_field(
		'sonderzeiten_text',
		'Hinweis',
		'restaurant_basis_feld_mehrzeilig',
		RESTAURANT_BASIS_SEITE,
		'restaurant_basis_sonderzeiten',
		array(
			'schluessel'   => 'sonderzeiten_text',
			'beschreibung' => 'Eine Zeile pro Angabe, zum Beispiel: 24.12. geschlossen',
			'label_for'    => 'restaurant-basis-sonderzeiten_text',
		)
	);
}
add_action( 'admin_init', 'restaurant_basis_einstellungen_registrieren' );

/**
 * Liest einen gespeicherten Wert aus der Sammeloption.
 */
function restaurant_basis_wert( string $schluessel, string $standard = '' ): string {
	$einstellungen = get_option( RESTAURANT_BASIS_OPTION, array() );

	if ( ! is_array( $einstellungen ) || ! isset( $einstellungen[ $schluessel ] ) ) {
		return $standard;
	}

	return (string) $einstellungen[ $schluessel ];
}

/**
 * Gibt ein einzeiliges Textfeld aus.
 */
function restaurant_basis_feld_text( array $argumente ): void {
	$schluessel = $argumente['schluessel'];

	printf(
		'<input type="text" id="restaurant-basis-%1$s" name="%2$s[%1$s]" value="%3$s" class="regular-text">',
		esc_attr( $schluessel ),
		esc_attr( RESTAURANT_BASIS_OPTION ),
		esc_attr( restaurant_basis_wert( $schluessel ) )
	);

	if ( ! empty( $argumente['beschreibung'] ) ) {
		printf( '<p class="description">%s</p>', esc_html( $argumente['beschreibung'] ) );
	}
}

/**
 * Gibt ein mehrzeiliges Textfeld aus.
 */
function restaurant_basis_feld_mehrzeilig( array $argumente ): void {
	$schluessel = $argumente['schluessel'];

	printf(
		'<textarea id="restaurant-basis-%1$s" name="%2$s[%1$s]" rows="4" class="large-text">%3$s</textarea>',
		esc_attr( $schluessel ),
		esc_attr( RESTAURANT_BASIS_OPTION ),
		esc_textarea( restaurant_basis_wert( $schluessel ) )
	);

	if ( ! empty( $argumente['beschreibung'] ) ) {
		printf( '<p class="description">%s</p>', esc_html( $argumente['beschreibung'] ) );
	}
}

/**
 * Gibt eine Zeile der Öffnungszeiten aus: Ruhetag-Häkchen plus Zeitfeld.
 */
function restaurant_basis_feld_wochentag( array $argumente ): void {
	$schluessel = $argumente['schluessel'];
	$ist_ruhetag = '1' === restaurant_basis_wert( 'ruhetag_' . $schluessel );

	printf(
		'<label style="margin-right:1.5em"><input type="checkbox" name="%1$s[ruhetag_%2$s]" value="1" %3$s> Ruhetag</label>',
		esc_attr( RESTAURANT_BASIS_OPTION ),
		esc_attr( $schluessel ),
		checked( $ist_ruhetag, true, false )
	);

	printf(
		'<input type="text" id="restaurant-basis-zeit-%2$s" name="%1$s[zeit_%2$s]" value="%3$s" class="regular-text" placeholder="17–22 Uhr">',
		esc_attr( RESTAURANT_BASIS_OPTION ),
		esc_attr( $schluessel ),
		esc_attr( restaurant_basis_wert( 'zeit_' . $schluessel ) )
	);
}

/**
 * Prüft und bereinigt alle Eingaben, bevor sie gespeichert werden.
 *
 * Läuft als sanitize_callback der Settings API, also bei jedem Speichern. Es werden
 * ausschließlich bekannte Schlüssel übernommen – alles andere wird verworfen, statt
 * ungeprüft in die Datenbank zu wandern.
 */
function restaurant_basis_einstellungen_pruefen( $eingabe ): array {
	$sauber = array();

	if ( ! is_array( $eingabe ) ) {
		return $sauber;
	}

	foreach ( array( 'telefon', 'strasse', 'plz', 'ort', 'sonderzeiten_titel' ) as $schluessel ) {
		if ( isset( $eingabe[ $schluessel ] ) ) {
			$sauber[ $schluessel ] = sanitize_text_field( $eingabe[ $schluessel ] );
		}
	}

	if ( isset( $eingabe['email'] ) ) {
		$email = sanitize_email( $eingabe['email'] );

		if ( '' !== trim( (string) $eingabe['email'] ) && '' === $email ) {
			add_settings_error(
				RESTAURANT_BASIS_OPTION,
				'email_ungueltig',
				'Die E-Mail-Adresse sieht nicht gültig aus und wurde nicht gespeichert.',
				'error'
			);
		}

		$sauber['email'] = $email;
	}

	if ( isset( $eingabe['instagram'] ) ) {
		$sauber['instagram'] = esc_url_raw( trim( (string) $eingabe['instagram'] ) );
	}

	if ( isset( $eingabe['sonderzeiten_text'] ) ) {
		// Mehrzeilig, deshalb kein sanitize_text_field (das würde Zeilenumbrüche entfernen).
		$sauber['sonderzeiten_text'] = sanitize_textarea_field( $eingabe['sonderzeiten_text'] );
	}

	foreach ( array_keys( restaurant_basis_wochentage() ) as $tag ) {
		$ist_ruhetag = ! empty( $eingabe[ 'ruhetag_' . $tag ] );

		$sauber[ 'ruhetag_' . $tag ] = $ist_ruhetag ? '1' : '';

		// An einem Ruhetag wird eine eventuell stehengebliebene Zeit verworfen, damit im
		// Frontend nicht „Ruhetag" und eine Uhrzeit gleichzeitig erscheinen können.
		$sauber[ 'zeit_' . $tag ] = $ist_ruhetag
			? ''
			: sanitize_text_field( $eingabe[ 'zeit_' . $tag ] ?? '' );
	}

	return $sauber;
}

/**
 * Gibt die Einstellungsseite aus.
 */
function restaurant_basis_seite_ausgeben(): void {
	// Doppelte Absicherung: add_menu_page prüft die Rechte bereits, aber die
	// Ausgabefunktion darf sich darauf nicht allein verlassen.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Du hast keine Berechtigung, diese Seite zu öffnen.' );
	}

	?>
	<div class="wrap">
		<h1>Kontakt und Öffnungszeiten</h1>
		<p style="max-width:46em">
			Was hier steht, erscheint überall auf der Website – im Footer, auf der Startseite
			und in jedem Anruf-Button. Du musst es also nur an dieser einen Stelle pflegen.
		</p>
		<?php settings_errors( RESTAURANT_BASIS_OPTION ); ?>
		<form action="options.php" method="post">
			<?php
			// settings_fields() setzt Nonce und Rechteprüfung für dieses Formular.
			settings_fields( RESTAURANT_BASIS_GRUPPE );
			do_settings_sections( RESTAURANT_BASIS_SEITE );
			submit_button( 'Speichern' );
			?>
		</form>
	</div>
	<?php
}
