# Oldenhaus Restaurant – Projekt-Handbuch

Arbeitsstand, Architekturentscheidungen und Konventionen. Wird waehrend des Baus
fortgeschrieben, damit auch in einer spaeteren Sitzung klar ist, was warum so gebaut wurde.

**Kunde:** Oldenhaus Restaurant – Pizzeria, Dorfstrasse 19, 25870 Oldenswort
**Vorlagen:** `reference/spec-oldenhaus.md` und `reference/moodboard-oldenhaus.html`
Das Moodboard ist verbindlich: Farben, Schriften und Komponenten stammen 1:1 daraus.

---

## Stack

| | |
|---|---|
| WordPress | 7.1 |
| PHP | 8.2.29 (LocalWP-eigenes Binary) |
| MySQL | 8.4 (Socket, nicht TCP) |
| Theme | Custom Classic Theme, kein Page-Builder, kein FSE |
| Fremd-Plugins | **nur ACF Free** |
| Lokale URL | http://oldenhaus-restaurant.local |

Pfade des LocalWP-Binaries (fuer WP-CLI):

```
PHP:    ~/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php
Socket: ~/Library/Application Support/Local/run/sn9qWWakH/mysql/mysqld.sock
```

---

## Architekturentscheidungen

### Ein generisches Plugin, alles andere im Theme

`wp-content/plugins/restaurant-basis/` – Speisekarte, FAQ, Kontaktdaten, Oeffnungszeiten.
**Bewusst ohne jeden Oldenhaus-Bezug**, damit es auf der naechsten Restaurant-Website
unveraendert laeuft. Auch die Oeffnungszeiten sind Inhalt dieser Installation, keine
Standardwerte im Code.

*Warum nicht im Theme:* Wirft das Theme einen PHP-Fatal-Error – etwa nach einem
PHP-Versionssprung beim Hoster – schaltet WordPress seit 5.2 selbsttaetig auf ein
Standard-Theme um. Ein im Theme registrierter Post Type waere dann auch aus dem Backend
verschwunden: Die Gerichte laegen weiter in der Datenbank, waeren aber unsichtbar.
Sicherheitstechnisch ist die Ablage egal – Plugin und Theme laufen im selben PHP-Prozess
mit identischen Rechten. Es geht ausschliesslich um Ausfallsicherheit.

### Sicherheit im Theme, nicht als zweites Plugin

`inc/sicherheit.php`. Bewusste Entscheidung gegen ein weiteres Plugin.

### Zentrale Daten ueber die Settings API

Telefon, Adresse und Oeffnungszeiten muessen aus **einer** Quelle kommen (Startseite und
Footer). ACF-Options-Pages sind Pro-pflichtig; eine normale Seite waere fragil, weil der
Kunde sie umbenennen oder loeschen kann. Die Settings API ist Kernfunktionalitaet und
seit Jahren stabil.

### ACF-Feldgruppen im Code

Registrierung ueber `acf_add_local_field_group()`, nicht ueber die ACF-Oberflaeche. Die
Felder liegen damit in Git, wandern bei einem Umzug mit und koennen im Backend nicht
versehentlich veraendert werden.

### Gerichtname = WordPress-Titelfeld

Kein eigenes ACF-Namensfeld. Ein zweites Namensfeld erzeugte zwei konkurrierende Quellen,
und der Name fehlte in Uebersichtsliste, Suche und URL. Das Titelfeld wird per
`enter_title_here` auf „Name des Gerichts" umbeschriftet.

### Kein Cookie-Banner – und warum das haelt

Es laedt **keine einzige Ressource von einem fremden Server**. Schriften liegen lokal,
Instagram ist ein reiner Link, kein Maps, kein Feed, kein Tracking.

Wichtig: WordPress laedt ab Werk Emoji-Grafiken von `s.w.org` nach
(`wp-includes/formatting.php:6032`) – das ist abgeschaltet, ebenso Gravatar und die
oEmbed-Erkennung. Wird bei jeder Abnahme neu geprueft, siehe „Pruefungen".

---

## Konventionen

- **Praefixe:** Plugin `restaurant_basis_`, Theme `oldenhaus_`. WordPress kennt keine
  Namespaces – ohne Praefix drohen Kollisionen.
- **Post Types:** `restaurant_gericht`, `restaurant_faq`. Taxonomie
  `restaurant_gericht_kategorie`.
- **Sprache:** Code und Kommentare deutsch, Backend-Bezeichnungen deutsch und
  selbsterklaerend („Speisekarte", nicht „Dishes").
- **Templates:** „Template Name"-Dateien statt slug-basierter `page-{slug}.php`, damit
  nichts bricht, wenn ein Seitentitel geaendert wird.
- **Escaping:** jede Ausgabe (`esc_html`, `esc_attr`, `esc_url`), jede Eingabe sanitized.

---

## Abweichungen von der Spec (bewusst, auf Ansage)

1. **Keine eigene Seite „Oeffnungszeiten & Anfahrt".** Oeffnungszeiten, Adresse und
   Parkhinweis stehen im Abschnitt unter dem Hero sowie im Footer.
2. **Navigation: Speisekarte · Galerie · Ueber uns** (drei Punkte, Galerie vor Ueber uns).
3. **Impressum und Datenschutz** nur ueber den Footer erreichbar.

---

## Arbeitsstand

- [x] `reference/` in die Projektwurzel, Spec daneben abgelegt
- [x] Schriften aus dem Moodboard extrahiert (4 × woff2, 79 KB) + OFL-Lizenzen
- [x] Git-Repository, `.gitignore` (nur eigener Code, kein Core, keine Zugangsdaten)
- [x] Plugin `restaurant-basis` (generisch, wiederverwendbar)
- [x] Theme-Geruest, Design-Tokens, alle Seitenvorlagen
- [x] Reaktivitaet (mobile first, 2 gezielte max-width-Ausnahmen)
- [x] Seiten, Menues, Beispielinhalte (`tools/*.php`)
- [x] Texte als freigabepflichtige Entwuerfe
- [x] Bilder: 15 Uebergangsbilder aus der WordPress-Fotodatenbank (CC0)
- [x] Haertung inkl. XML-RPC-Sperre und Login-Begrenzung
- [x] Unabhaengige Review durch einen Subagenten, Befunde behoben
- [ ] GitHub (SSH-Schluessel erzeugt, wartet auf Hinterlegung bei GitHub)

### Gepruefte Ergebnisse

| Pruefung | Ergebnis |
|---|---|
| Alle Seiten | HTTP 200, 404 greift, keine PHP-Fehler |
| Externe Anfragen | keine. `s.w.org` abgeschaltet; `api.w.org` ist nur ein rel-Namensraum, Instagram ein Klick-Link |
| Sicherheits-Header | alle gesetzt, CSP `default-src 'self'` - auch auf `wp-login.php` (dort feuert `send_headers` nicht, deshalb zusaetzlich an `login_init`) |
| `?author=1` | 301 auf die Startseite, kein Benutzername |
| `/wp-json/wp/v2/users` | abgewiesen (404) |
| XML-RPC | 403, bevor eine Methode laeuft |
| WordPress-Version | nicht im Quelltext |
| Login-Sperre | greift ab dem 5. Fehlversuch, Hinweis sichtbar, verraet keine Konten |
| Gericht „nicht verfuegbar" | verschwindet im Frontend, bleibt im Backend, umkehrbar |
| Theme-Wechsel | mit Standard-Theme bleiben 12 Gerichte, 5 Fragen, 5 Kategorien und die Kontaktdaten erreichbar |
| Inline-Styles | im Frontend keine. Im Backend neun style-Attribute fuer kleine Farbmarkierungen in Uebersichtslisten - dafuer eine eigene Admin-Stylesheet-Datei anzulegen waere unverhaeltnismaessig. Dazu ein dokumentierter style-Block in header.php fuer den Fall ohne JavaScript. |

### Unabhaengige Review

Ein Subagent mit frischem Kontext hat den fertigen Stand gegen Sicherheits-, Design-,
Code-Struktur- und Drittanbieter-Richtlinien geprueft (Schritt 6 des Skills). Er hat die
Drittanbieter-Freiheit eigenstaendig nachgewiesen und die Design-Treue Token fuer Token
bestaetigt - aber auch drei Mangel gefunden, die beim Bauen durchgerutscht waren:

| Fund | Behoben durch |
|---|---|
| Die Website lief auf **Englisch** (`lang="en-US"`, englisches Backend, englischer 404-Titel) - Verstoss gegen die Spec und genau die Huerde fuer den Kunden, die der Rest des Projekts abbaut | `de_DE` installiert und aktiviert, Zeitzone Europe/Berlin |
| Die **Galerie zeigte 150-px-Vorschaubilder**, quadratisch beschnitten und auf Spaltenbreite hochskaliert. Die Mauerwerk-Optik war damit unmoeglich, weil alle Kacheln gleich hoch waren | Bildgroesse `large` im Galerie-Filter erzwungen |
| Die **Sicherheits-Header fehlten auf `wp-login.php`** - ausgerechnet auf der einzigen Seite, die Eingaben entgegennimmt. `send_headers` feuert dort nicht, weil `wp-login.php` niemals `wp()` aufruft | zusaetzlich an `login_init` gehaengt |

Beim Beheben kam ein vierter Fehler ans Licht: `wp_kses_post()` entfernt `srcset` aus
img-Elementen. Das eigene Escaping warf also die responsiven Bildquellen weg, die
WordPress korrekt erzeugt hatte - jedes Geraet haette dieselbe grosse Datei geladen.
Statt auf das Escaping zu verzichten, ist die Erlaubnisliste jetzt um genau die
Attribute erweitert, die WordPress selbst ausgibt.

Weitere behobene Punkte: eine gueltige E-Mail-Adresse wurde bei einem Tippfehler
geloescht; ausgeblendete Gerichte blieben ueber die REST-Schnittstelle sichtbar;
Hochkant-Bloecke auf "Ueber uns" holten die quere Bildgroesse und wurden hochskaliert;
Rechtslinks in der Fusszeile waren zu kleine Trefferflaechen; automatisch eingebettete
Inhalte waeren von der CSP wortlos blockiert worden; rund 9 KB ungenutztes Block-CSS
auf jeder Seite (Startseite jetzt 14,9 statt 24,4 KB).

### Zwei Funde aus der eigenen Pruefung

1. **XML-RPC blieb trotz der ueblichen Filter offen.** `xmlrpc_enabled` betrifft nur
   angemeldete Methoden, und `xmlrpc_methods` kann `system.multicall` nicht entfernen,
   weil `IXR_Server::setCallbacks()` die `system.*`-Methoden erst *nach* dem Filter
   hinzufuegt. Die Schnittstelle beantwortete `system.listMethods` weiterhin. Jetzt
   wird die Anfrage per `XMLRPC_REQUEST` mit 403 abgewiesen.
2. **Die Login-Sperre war unsichtbar.** Sie griff korrekt, aber die vereinheitlichte
   Fehlermeldung ueberschrieb den Hinweis – ein ausgesperrter Nutzer haette endlos
   weiterprobiert. Der Sperrhinweis ist jetzt die einzige Ausnahme von der
   Vereinheitlichung; ueber vorhandene Konten verraet er nichts.

---

## Offene Punkte (blockieren den Livegang)

| # | Offen | Wirkung |
|---|---|---|
| 1 | Telefonnummer | blockiert alle Anruf-Buttons |
| 2 | E-Mail-Adresse | fuers Impressum erforderlich |
| 3 | Impressum-Inhalte | rechtlich erforderlich |
| 4 | Datenschutzerklaerung | rechtlich erforderlich |
| 5 | Speisekarten-Inhalte | Kunde liefert Foto/Scan |
| 6 | FAQ-Antworten 1–4 | liegen als Entwurf bereit, Sachaussage unbestaetigt |
| 7 | Events-Praesentation | Widerspruch im Fragebogen (A2 ja / B7 nein) |
| 8 | Logo | bis dahin Wortmarke |
| 9 | Fotos | Stockfotos als Uebergang |
| 10 | Business-Lunch | Mo–Do erst ab 17 Uhr, Textfrage |
| 11 | Uploads-Schutz | auf dem Zielserver einrichten (lokal nicht testbar) |

**Grundsatz bei fehlenden Inhalten:** kein Lorem Ipsum. Fehlt eine Sachaussage – etwa ob
Hunde erlaubt sind – wird sie nicht erfunden, sondern der Eintrag bleibt Entwurf und
damit im Frontend unsichtbar.

---

## Vor dem Livegang auf dem Zielserver

Diese Punkte lassen sich lokal nicht abschliessen, weil LocalWP mit nginx laeuft und
manche Dateien zur Entwicklungsumgebung gehoeren.

| Aufgabe | Warum |
|---|---|
| `wp-content/uploads/.htaccess` pruefen | Verhindert PHP-Ausfuehrung im Upload-Ordner. Wirkt nur unter **Apache**. Laeuft der Zielserver mit nginx, gehoert stattdessen in die Server-Konfiguration: `location ~* /wp-content/uploads/.*\.(php\|phar\|phtml)$ { deny all; }` |
| `DISALLOW_FILE_EDIT` in die `wp-config.php` | Steht aktuell im Theme. Dort greift es nur, solange das Theme geladen wird - in der wp-config gilt es immer |
| `/readme.html` und `/license.txt` loeschen | Verraten die WordPress-Version, die sonst aufwendig versteckt wird |
| `/local-xdebuginfo.php` loeschen | Gehoert zu LocalWP und gibt einen vollstaendigen phpinfo-Dump samt Serverpfaden aus. Darf unter keinen Umstaenden live gehen |
| Automatische Core-Updates aktiv lassen | Sicherheitsaktualisierungen sollen ohne Zutun ankommen |
| HTTPS erzwingen und `upgrade-insecure-requests` zur CSP ergaenzen | Lokal bewusst weggelassen, weil die Entwicklungsumgebung ueber http laeuft |
| Impressum und Datenschutzerklaerung befuellen | Rechtlich zwingend vor der Veroeffentlichung |

Nicht behoben, bewusst: `/wp-content/plugins/advanced-custom-fields/readme.txt` nennt
die ACF-Version. Das liesse sich nur serverseitig sperren und gilt als geringfuegig -
es steht hier, damit es eine Entscheidung ist und kein Versehen.

## Pruefungen

**Keine Drittanbieter** (Bauvorgabe):

```
curl -s http://oldenhaus-restaurant.local | grep -oE 'https?://[a-zA-Z0-9.-]+' \
  | grep -v 'oldenhaus-restaurant.local' | sort -u
```

Erwartung: leer. `w3.org` und `api.w.org` sind XML-Namensraeume, keine Anfragen –
`s.w.org` dagegen waere ein echter Fund.

**Reaktivitaet:** 360, 390, 414, 768, 1024, 1440 px. Kein horizontales Scrollen.

**Gegenprobe Plugin-Entscheidung:** kurz auf `twentytwentyfive` schalten – Speisekarte und
FAQ muessen im Backend erreichbar bleiben.

**Wiederverwendbarkeit:** `grep -ri oldenhaus wp-content/plugins/restaurant-basis/` muss
leer sein.
