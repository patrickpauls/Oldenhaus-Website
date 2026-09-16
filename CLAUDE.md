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
- [x] Echte Speisekarte des Kunden eingepflegt (`tools/speisekarte-anlegen.php`)
- [x] Texte als freigabepflichtige Entwuerfe
- [x] Bilder: 15 Uebergangsbilder aus der WordPress-Fotodatenbank (CC0)
- [x] Haertung inkl. XML-RPC-Sperre und Login-Begrenzung
- [x] Unabhaengige Review durch einen Subagenten, Befunde behoben
- [x] Feedback-Runde 1 des Kunden (Design) und Basis-SEO
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
| Fehlerprotokoll | liegt ausserhalb des Webordners (`logs/php/wp-fehler.log`), `wp-content/debug.log` liefert 404 |
| Anmeldename | in Quelltext, Feed, Sitemap, REST und oEmbed nicht mehr auffindbar |
| Inline-Styles | im Frontend keine. Im Backend neun style-Attribute fuer kleine Farbmarkierungen in Uebersichtslisten - dafuer eine eigene Admin-Stylesheet-Datei anzulegen waere unverhaeltnismaessig. Dazu ein dokumentierter style-Block in header.php fuer den Fall ohne JavaScript. |
| SEO-Grundlagen | je Seite genau ein `<title>`, ein Canonical, eine Beschreibung, eine `<h1>`. `lang="de-DE"`. JSON-LD nur auf der Startseite und valides JSON |
| Robots | Inhaltsseiten `index, follow`; Impressum, Datenschutz, 404, Suche und leere Archive `noindex, follow` |
| Sitemap | `/wp-sitemap.xml` liefert 200 (vorher 404, siehe Fund unten). Nicht vorhandene Sitemaps weiterhin 404 |

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

### Zwei Funde aus der Pruefung des Fehlersuchmodus

Anlass war eine Meldung der WordPress-Website-Zustandspruefung zu `WP_DEBUG_LOG`.

1. **Das Fehlerprotokoll lag im oeffentlichen Webordner.** `WP_DEBUG_LOG = true`
   schreibt nach `wp-content/debug.log` – die Datei wurde mit HTTP 200 ausgeliefert und
   nannte den vollstaendigen Pfad der Installation samt Dateinamen und Zeilennummern.
   Drei Aenderungen: Das Protokoll liegt jetzt in `logs/php/wp-fehler.log`, also
   ausserhalb des Webordners. Der Fehlersuchmodus haengt am `WP_ENVIRONMENT_TYPE` und
   schaltet sich bei `production` von selbst ab. Und `display_errors` wird schon in der
   `wp-config.php` abgeschaltet – WordPress selbst fasst die Einstellung bei
   ausgeschaltetem `WP_DEBUG` gar nicht an, ein Hoster mit `display_errors = On` haette
   Pfade sonst direkt auf die Seite geschrieben.

   Dazu ein Waechter im Theme (`inc/sicherheit.php`, Abschnitt 5): Liegt das Protokoll
   im Webordner, laeuft der Fehlersuchmodus im Livebetrieb oder liegt noch eine alte
   `debug.log` herum, erscheint im Backend ein Hinweis. Bewusst ein Hinweis und keine
   stille Korrektur – `WP_DEBUG_LOG` ist eine Konstante und laesst sich zur Laufzeit
   nicht mehr aendern; eine Umleitung wuerde die eigentliche Ursache verdecken.

2. **Die oEmbed-Schnittstelle verriet den Anmeldenamen.** `/wp-json/oembed/1.0/embed`
   lieferte `author_name` und `author_url` aus – also genau die Information, fuer die
   `?author=1` umgeleitet und `/wp-json/wp/v2/users` geschlossen worden war. Die
   Umleitung des Autorenarchivs half nicht, weil der Name schon im JSON stand. Die Route
   wird jetzt gar nicht erst angemeldet; zusaetzlich streicht ein Filter die beiden
   Felder, falls sie ein Plugin spaeter wieder anmeldet.

Ohne Befund geprueft: Sitemaps (keine Autoren-Sitemap), Feeds (kein `dc:creator`),
`/wp-json/wp/v2/users/me` und `/wp/v2/settings` (401), Medien (`author: 0`),
Verzeichnisauflistung (aus), sowie der eigene Code auf `var_dump`, `print_r`, `phpinfo`
und `error_log` (keine Treffer). Alle eigenen PHP-Dateien haben einen ABSPATH-Schutz.

**Nicht behoben, weil LocalWP-eigen:** Ein direkter Aufruf von
`/wp-content/mu-plugins/local-by-flywheel-live-link-helper.php` erzeugt einen
PHP-Fatal-Error und gibt – wegen `display_errors = On` in der LocalWP-`php.ini` – einen
vollstaendigen Stacktrace mit Serverpfaden aus. Die Datei gehoert zur
Entwicklungsumgebung und darf ohnehin nicht mit auf den Zielserver.

---

## Feedback-Runde 1 (Design) und Basis-SEO

Beides in einem Durchgang, von zwei Subagenten mit getrennter Dateihoheit gebaut.

### Wo der Kunde Farben selbst aendert

Alle Farben liegen als Tokens in `style.css`, Abschnitt 1. **Keine Hex-Werte in
einzelnen Selektoren.** Die Namen `--color-*` weichen bewusst von der sonstigen
`--c-*`-Konvention ab: Der Kunde hat sie so benannt bekommen und soll sie wiederfinden.

| Zweck | Token | style.css |
|---|---|---|
| CTA-Button Ruhe | `--color-cta-bg` | 80 |
| CTA-Button Hover | `--color-cta-bg-hover` | 81 |
| CTA-Button Text | `--color-cta-text` | 82 |
| Helle Abschnitte | `--color-section-light` | 99 |
| Beige Abschnitte | `--color-section-alt` | 100 |
| Dunkles Band | `--color-section-dark` | 101 |
| Fusszeile | `--color-footer-bg` | 102 |

`--color-section-dark` ist ergaenzt worden, obwohl der Kunde nur drei Namen genannt
hat: Das dunkle Band und die Fusszeile tragen denselben Ton, haengen ueber ein
gemeinsames Token aber aneinander – ein dunkleres Band haette ungefragt auch die
Fusszeile umgefaerbt. Zwei Entscheidungen, zwei Tokens.

### Der leere Streifen vor der Fusszeile

`.site-footer` hatte `margin-top: var(--abschnitt)`, obwohl jeder `.abschnitt` seinen
Abstand ueber `padding-block` schon selbst mitbringt. Ein Aussenabstand ist durchsichtig,
also stand zwischen dem beigen FAQ-Band und der dunklen Fusszeile ein 72 px hoher
Streifen cremefarbener Body-Hintergrund. Der Abstand war nie falsch platziert – er war
doppelt. Ersatzlos entfernt.

**Daraus wird eine Konvention fuer kuenftige Seitenvorlagen:** Der letzte Inhaltsblock
einer Seite bringt seinen Abstand nach unten selbst mit. Alle Vorlagen enden auf einem
`.abschnitt` und tun das – ausser der Speisekarte, die ihre Liste direkt in `.wrap`
legt; sie hat deshalb `.karte { padding-bottom: var(--abschnitt) }` bekommen.

### Drei latente Fehler, die erst mit der Telefonnummer sichtbar geworden waeren

Der Kunde meldete einen „durchsichtigen Button, der erst beim Hovern rot wird". Ursache
war nicht der Button, sondern sein **Platzhalter**: Ohne hinterlegte Telefonnummer gibt
`oldenhaus_anruf_button()` `<span class="btn btn--ohne-nummer">` aus. Beim Nachgehen
fielen drei Fehler auf, die alle erst aufgefallen waeren, wenn die Nummer eingetragen ist:

1. **`.btn:hover` (0-2-0) schlug `.btn--ohne-nummer` (0-1-0)** – der Platzhalter leuchtete
   beim Ueberfahren rot auf wie ein echter Knopf. Jetzt `.btn.btn--ohne-nummer`.
2. **Der Platzhalter war in der Kopfzeile unsichtbar.** `color: inherit` erbte bis zum
   `body` durch: `--c-ink` auf `--c-walnut` = **1,17:1**. `.site-header` setzt jetzt
   ausdruecklich `color: var(--c-cream)` → 8,20:1.
3. **Der Footer-Button haette apricotfarbene Schrift bekommen.** `.site-footer a` (0-2-0)
   haette die Textfarbe aus `.btn` (0-1-0) ueberschrieben → 3,66:1. Gelöst ueber
   `:not(.btn)` an der Link-Regel, statt die Buttonfarbe mit noch mehr Spezifitaet
   zurueckzuholen.

Merksatz fuer dieses Theme: Zustandsregeln (`:hover`) wiegen schwerer als
Varianten-Klassen. Eine Variante, die einen Zustand ueberstimmen soll, braucht die
doppelte Klasse.

### SEO liegt in `inc/seo.php`, auch die Feldgruppe

Alles in **einer** Datei: Felddefinition, Ausgabe und Filter. Begruendung: Die anderen
ACF-Gruppen in `inc/acf-felder.php` haengen je an genau einer Vorlage und sind ohne sie
sinnlos; die SEO-Gruppe gilt fuer jede Seite und wird nur von Code gelesen, der
zwanzig Zeilen darueber steht. Bekommt die Website eines Tages doch ein SEO-Plugin, wird
eine Datei geloescht und eine `require_once`-Zeile entfernt – ohne Reste. In
`inc/acf-felder.php` steht ein Querverweis, damit niemand raetselt.

**Neu im Backend:** Seiten → Seite bearbeiten → Kasten „Suchmaschinen (SEO)" mit
SEO-Titel, SEO-Beschreibung und dem Schalter „Von Suchmaschinen ausschliessen".
Alle drei duerfen leer bleiben; dann greifen seitenbezogene Entwuerfe aus dem Code.

**Impressum und Datenschutz auf `noindex` – ohne harte IDs.** Drei Wege absteigend nach
Verlaesslichkeit: das Haekchen an der Seite selbst (haengt am Beitrag, uebersteht
Umbenennen und Verschieben), `get_option( 'wp_page_for_privacy_policy' )` fuer den
Datenschutz, und ein Namensmuster als Netz. Harte IDs waeren der einzige Weg gewesen,
bei dem ein Fehler unsichtbar bleibt – eine fehlende Robots-Angabe faellt niemandem auf.

**Keine Oeffnungszeiten in den Beschreibungstexten.** Ein handgepflegtes Feld veraltet,
sobald der Kunde die Zeiten aendert, und eine falsche Uhrzeit im Suchergebnis ist
schlechter als keine. Die Zeiten stehen im JSON-LD und kommen dort live aus
„Kontakt & Zeiten".

### Ein Fund: `/wp-sitemap.xml` lieferte korrektes XML mit Status 404

Die Google Search Console lehnt eine so ausgelieferte Sitemap ab, ohne sie zu lesen –
und die `robots.txt` verweist darauf. Ursache: `WP::handle_404()` setzt den Status,
**bevor** die Sitemap gerendert wird, allein danach, ob die Abfrage Beitraege gefunden
hat. Auf einer Website mit Blog liefert sie die letzten Beitraege; diese hat bewusst
**null Beitraege** → Liste leer → 404. `WP_Sitemaps::render_sitemaps()` setzt danach zwar
`$wp_query->is_404` zurueck, den bereits gesendeten Statuscode holt es nicht mehr ein.
Behoben ueber `pre_handle_404`.

Der erste Versuch dabei war selbst ein Fehler und ist es wert, festgehalten zu werden:
Der Filter winkte anfangs *jede* Sitemap-Adresse durch – prompt antwortete
`/wp-sitemap-users-1.xml` (Anbieter eigentlich abgemeldet) mit **HTTP 200 und einer
indexierbaren HTML-Seite**. Aus einem 404 waere eine Fundstelle geworden. Jetzt wird
geprueft, ob es die angefragte Sitemap ueberhaupt gibt.

### Bewusst nicht gebaut

- **Kein `noindex` auf Anhangseiten.** `oldenhaus_anhangseiten_umleiten()` haengt an
  `template_redirect`, `wp_robots` an `wp_head` – letzteres wird nie erreicht.
  Nachgemessen: `?attachment_id=33` → 301. Ein `noindex` waere nachweislich toter Code.
- **Kein Anhang-Filter fuer die Sitemap.** Der Kern tut das schon, und beide eigenen
  Post Types sind `public => false`.
- **Kein `loading`/`fetchpriority`-Filter.** Das Hero-Bild hat `eager` +
  `fetchpriority="high"` bereits aus dem Template, alle uebrigen Bilder `lazy` mit
  Massen. Ein Filter haette nur bestehendes Verhalten ueberschrieben – und den in
  diesem Handbuch dokumentierten `srcset`-Fallstrick riskiert.
- **Kein Favicon erfunden.** Es liegt kein Logo vor; ein Icon zu erfinden hiesse, eine
  Marke zu erfinden. Stattdessen ein Eintrag im Dashboard-Widget, der von selbst
  verschwindet, sobald ein Website-Icon hinterlegt ist.
- **Kein hreflang.** Die Website ist einsprachig.
- **Hero-Bilder behalten `alt=""`.** Es ist eine rotierende Kreuzblende hinter der `<h1>`;
  die Mediathek-Texte sind gepflegt, wuerden aber ein Bild beschreiben, das nach
  Sekunden wechselt. Umkehrbar in einer Zeile in `template-parts/startseite/hero.php`,
  falls das erste Bild doch seinen Text bekommen soll.

### `lang="de"` → `lang="de-DE"`

Der Ausgangswert `de` war kein Fehler, sondern eine Entscheidung des deutschen
Uebersetzungsteams – dieselbe Uebersetzung laeuft in DE, AT und CH. Diese Website
richtet sich an eine Region, also ist die vollstaendige Kennung genauer, und sie passt
zu `og:locale = de_DE`. Gebildet aus `get_locale()`, nicht fest verdrahtet, und nur wenn
sauberes `xx-YY` herauskommt (`de_DE_formal` ergaebe sonst ungueltiges `de-DE-formal`).

Technische Fussnote, damit es niemand ein zweites Mal versucht: Der naheliegende Filter
`bloginfo` laeuft ins Leere – `get_bloginfo()` wendet ihn nur bei `$filter = 'display'`
an, `get_language_attributes()` fragt unformatiert ab. Der richtige Haken ist
`language_attributes`.

### Offen aus dieser Runde

- **Mobile Navigation von rechts** (Punkt 5 des Feedbacks): auf Ansage nur geprueft,
  nicht gebaut. Empfehlung ist **Variante C** – `.hauptnav` mit `left: auto; right: 0;
  width: min(78vw, 300px)` rechts unter dem Menueknopf verankern, rund sechs CSS-Zeilen.
  Bleibt ein Dropdown, kein Modal: keine Aenderung an `header.php` oder `oldenhaus.js`,
  keine Fokusfalle noetig, der `<noscript>`-Fallback greift unveraendert. Das echte
  Einfahren von rechts (Variante B) waere ein halber Tag, weil `hidden` durch Klasse +
  `inert` ersetzt werden muss, der noscript-Fallback bricht und eine Fokusfalle dann
  Pflicht ist – ein raumhohes Panel ueber dem Inhalt ist ein Dialog.
- **Kontrast der Navigation ueber dem Hero.** Pixelweise gemessen: im Mittel 10,6–15,0:1,
  aber am hellsten Punkt des Ofenscheins in `hero-1-pizza-ofen.jpg` **3,70:1**. Die
  Wortmarke gilt bei 30 px/800 als grosse Schrift (Grenze 3:1) und besteht; die 18-px-
  Navigationslinks brauchen 4,5:1 und erreichen es an dieser einen Stelle nicht.
  Bestandsverhalten, durch die Vergroesserung eher besser geworden, und ab 40 px Scroll
  ist die Kopfzeile ohnehin deckend. Abhilfe waere eine gestalterische Entscheidung:
  weicher dunkler Verlauf hinter der Kopfzeile (~4 CSS-Zeilen, keine Fremdressource)
  oder ein Hero-Motiv mit ruhigem oberen Drittel beim Fototermin.

---

## Offene Punkte (blockieren den Livegang)

| # | Offen | Wirkung |
|---|---|---|
| 1 | Telefonnummer | blockiert alle Anruf-Buttons |
| 2 | E-Mail-Adresse | fuers Impressum erforderlich |
| 3 | Impressum-Inhalte | rechtlich erforderlich |
| 4 | Datenschutzerklaerung | rechtlich erforderlich |
| 5 | Allergenkennzeichnung | liegt vor, aber ohne lesbare Darstellung bewusst weggelassen |
| 6 | FAQ-Antworten 1–4 | liegen als Entwurf bereit, Sachaussage unbestaetigt |
| 7 | Events-Praesentation | Widerspruch im Fragebogen (A2 ja / B7 nein) |
| 8 | Logo | bis dahin Wortmarke |
| 9 | Fotos | Stockfotos als Uebergang |
| 10 | Business-Lunch | Mo–Do erst ab 17 Uhr, Textfrage |
| 11 | Uploads-Schutz | auf dem Zielserver einrichten (lokal nicht testbar) |
| 12 | Getraenkekarte | liegt nicht vor; Kategorie „Getraenke“ steht leer bereit |
| 13 | Vegetarisch/vegan | in der gelieferten Karte nicht angegeben, Haekchen daher ungesetzt |
| 14 | Website-Icon (Favicon) | keins gesetzt, weil kein Logo vorliegt. Dashboard-Hinweis erinnert daran |
| 15 | SEO-Beschreibungen | sechs Entwuerfe stehen im Backend, freigabepflichtig wie alle Texte |
| 16 | `srcset` bei vier Bildern | die eigenen Groessen `oldenhaus-hochkant` (4:5) und `oldenhaus-quer` (3:2) haben kein zweites Bild im selben Seitenverhaeltnis, aus dem WordPress ein `srcset` bauen koennte. Auf Retina-Schirmen werden sie hochskaliert. Behebbar ueber 2×-Varianten in `inc/theme-setup.php`, erfordert aber ein Neuerzeugen aller Vorschaubilder |

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
| **`WP_ENVIRONMENT_TYPE` auf `production` setzen** | Schaltet in der `wp-config.php` Fehlersuchmodus und Protokoll ab. Ohne das laeuft die Fehlersuche live weiter |
| Pfad in `WP_DEBUG_LOG` pruefen | Zeigt auf `logs/php/` der LocalWP-Struktur. Auf dem Zielserver existiert der Ordner nicht - dann greift die eingebaute Pruefung und schaltet das Protokoll ab. Soll live protokolliert werden, gehoert ein Pfad **ausserhalb** des Webordners hinein |
| Protokoll- und Sicherungsdateien sperren | Die Regeln stehen in `app/public/.htaccess` (Apache). Fuer nginx: `location ~* \.(log\|sql\|bak\|old\|orig\|swp\|save\|ini\|env)$ { deny all; }` |
| ~~`DISALLOW_FILE_EDIT`~~ | Erledigt: steht jetzt in der `wp-config.php` und gilt damit auch, wenn das Theme einmal nicht geladen wird |
| `/readme.html` und `/license.txt` loeschen | Verraten die WordPress-Version, die sonst aufwendig versteckt wird |
| `/local-xdebuginfo.php` loeschen | Gehoert zu LocalWP und gibt einen vollstaendigen phpinfo-Dump samt Serverpfaden aus. Darf unter keinen Umstaenden live gehen |
| Automatische Core-Updates aktiv lassen | Sicherheitsaktualisierungen sollen ohne Zutun ankommen |
| HTTPS erzwingen und `upgrade-insecure-requests` zur CSP ergaenzen | Lokal bewusst weggelassen, weil die Entwicklungsumgebung ueber http laeuft |
| Impressum und Datenschutzerklaerung befuellen | Rechtlich zwingend vor der Veroeffentlichung |

### SEO beim Livegang

| # | Aufgabe | Warum |
|---|---|---|
| 1 | **Einstellungen → Lesen: „Suchmaschinen davon abhalten…" muss AUS sein** | Lokal steht `blog_public = 1`. Beim Umzug setzen viele Hoster und Staging-Werkzeuge das auf 0. Dann setzt der Kern `noindex, nofollow` auf **jede** Seite, und der eigene Filter laesst das absichtlich stehen – die ganze SEO-Arbeit waere wirkungslos, ohne dass etwas kaputt aussieht. **Erster Punkt nach dem Livegang, per curl gegenpruefen** |
| 2 | Sitemap in der Google Search Console einreichen | `https://…/wp-sitemap.xml`. Die Domain dabei per **DNS-TXT-Eintrag** bestaetigen, nicht per HTML-Meta-Tag – ein Verifizierungs-Tag im `<head>` waere der erste Fremdbezug der Website und damit das Ende der Banner-Freiheit |
| 3 | Google-Unternehmensprofil anlegen und verknuepfen | Fuer ein Restaurant der groesste SEO-Hebel ueberhaupt. Adresse, Oeffnungszeiten und Telefonnummer dort **identisch** zu Website und JSON-LD halten, sonst wertet Google die Signale gegeneinander ab |
| 4 | `robots.txt` pruefen | WordPress erzeugt sie dynamisch und traegt die Sitemap selbst ein. Liegt auf dem Zielserver eine **physische** `robots.txt`, gewinnt die Datei und die Sitemap-Zeile fehlt |
| 5 | Rich-Results-Test | `search.google.com/test/rich-results` gegen die Startseite. Warnt erwartbar wegen fehlendem `telephone`, solange keine Nummer hinterlegt ist |
| 6 | Telefonnummer eintragen | Fuer lokale Suche und Google Maps die wichtigste Angabe. Sobald sie in „Kontakt & Zeiten" steht, erscheint sie automatisch im JSON-LD – ohne Code-Aenderung |

`canonical`, `og:url` und die URLs im JSON-LD bilden sich aus `home_url()` und wandern
beim Wechsel auf HTTPS automatisch mit. Kein Eingriff noetig.

### Der Fehlersuche-Block fuer die `wp-config.php`

Die `wp-config.php` ist bewusst **nicht** versioniert – sie enthaelt Datenbankzugang und
Sicherheitsschluessel. Der folgende Block muss auf dem Zielserver deshalb von Hand
hinein, sonst faellt die Website dort auf das Standardverhalten zurueck und schreibt das
Protokoll wieder in den Webordner. Er gehoert **ueber** die Zeile
`/* That's all, stop editing! */`:

```php
define( 'WP_ENVIRONMENT_TYPE', 'production' );

define( 'WP_DEBUG',         false );
define( 'WP_DEBUG_LOG',     false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG',     false );

// WordPress fasst display_errors bei ausgeschaltetem WP_DEBUG nicht an -
// ein Hoster mit display_errors = On schriebe Serverpfade auf die Seite.
ini_set( 'display_errors', '0' );
ini_set( 'display_startup_errors', '0' );

define( 'DISALLOW_FILE_EDIT', true );
```

Der Waechter im Theme meldet sich im Backend, falls das vergessen wird.

**Wird damit nicht mehr protokolliert?** Doch – nur an anderer Stelle. Das ist der
wichtigste Punkt zum Verstaendnis des Blocks oben, und er ist leicht falsch zu lesen.

Die gesamte Auswertung von `WP_DEBUG_LOG` steht in `wp_debug_mode()` **innerhalb** von
`if ( WP_DEBUG )` (`wp-includes/load.php`). Ist `WP_DEBUG` aus, ist `WP_DEBUG_LOG`
wirkungslos – egal welcher Pfad darin steht. WordPress ruft dann schlicht kein
`ini_set( 'error_log', … )` auf und ueberlaesst die Protokollierung der `php.ini`, wo
bei praktisch jedem Hoster `log_errors = On` steht.

Nachgestellt und gemessen (mit `WP_ENVIRONMENT_TYPE = 'production'`):

| Ereignis | Besucher sieht | Protokoll |
|---|---|---|
| Fatal Error | WordPress-Fehlerseite, HTTP 500, **kein Pfad, kein Stacktrace** | vollstaendig samt Stacktrace im PHP-Protokoll des Servers |
| `E_USER_WARNING` | nichts | protokolliert |
| `E_USER_DEPRECATED` | nichts | nicht protokolliert – `error_reporting()` filtert es im Livebetrieb weg |
| – | – | **nichts** im Webordner |

Ein Absturz ist also weiterhin lueckenlos nachvollziehbar, nur eben im Serverprotokoll
des Hosters statt in einer oeffentlich abrufbaren Datei. Weggefallen sind ausschliesslich
die Veralterungshinweise – Entwicklungsrauschen, das im Livebetrieb nichts zu suchen hat.

**Wenn der Hoster kein Protokoll bereitstellt** (selten, kommt bei einfachen
Webhosting-Paketen aber vor), ist der Weg *nicht*, `WP_DEBUG_LOG` zu setzen, sondern:

```php
define( 'WP_DEBUG',         true  );   // noetig, sonst greift WP_DEBUG_LOG nicht
define( 'WP_DEBUG_DISPLAY', false );   // nichts auf die Seite - unverzichtbar
define( 'WP_DEBUG_LOG', '/pfad/ausserhalb/des/webordners/wp-fehler.log' );
```

Dabei zwei Dinge beachten: `WP_DEBUG = true` schaltet `error_reporting( E_ALL )` ein, das
Protokoll fuellt sich also auch mit Veralterungshinweisen und muss im Blick behalten
werden. Und der Pfad muss zwingend ausserhalb des Webordners liegen – sonst ist genau
der Fehler zurueck, wegen dem dieser Abschnitt existiert.

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

**Kein Fehlerprotokoll im Webordner:**

```
curl -s -o /dev/null -w '%{http_code}\n' http://oldenhaus-restaurant.local/wp-content/debug.log
```

Erwartung: 404. Ausserdem muss `find app/public -name '*.log'` leer bleiben.

**Anmeldename nirgends auffindbar:**

```
for p in / /wp-json/ /feed/ '/?embed=true' '/wp-json/oembed/1.0/embed?url=http://oldenhaus-restaurant.local/'; do
  curl -s "http://oldenhaus-restaurant.local$p" | grep -ic cuhlen
done
```

Erwartung: ueberall 0.

**Reaktivitaet:** 360, 390, 414, 768, 1024, 1440 px. Kein horizontales Scrollen.

**Gegenprobe Plugin-Entscheidung:** kurz auf `twentytwentyfive` schalten – Speisekarte und
FAQ muessen im Backend erreichbar bleiben.

**Wiederverwendbarkeit:** `grep -ri oldenhaus wp-content/plugins/restaurant-basis/` muss
leer sein.

**SEO-Grundlagen je Seite** (genau einmal `<title>`, Canonical, Beschreibung, `<h1>`):

```
B=http://oldenhaus-restaurant.local
for p in / /speisekarte/ /galerie/ /ueber-uns/ /impressum/ /datenschutzerklaerung/; do
  S=$(curl -s "$B$p")
  printf '%-26s title:%s canonical:%s desc:%s h1:%s\n' "$p" \
    "$(printf '%s' "$S" | grep -c '<title>')" \
    "$(printf '%s' "$S" | grep -c "rel=.canonical")" \
    "$(printf '%s' "$S" | grep -c "name=.description")" \
    "$(printf '%s' "$S" | grep -o '<h1' | wc -l | tr -d ' ')"
done
```

Erwartung: ueberall 1. Auf 404 und Suche **kein** Canonical – eine Fehlerseite darf keine
kanonische Adresse behaupten. Achtung beim Nachbauen: WordPress gibt die Meta-Tags mit
**einfachen** Anfuehrungszeichen aus, ein `grep 'name="robots"'` findet nichts.

**Sitemap liefert 200, Nichtvorhandenes weiterhin 404:**

```
for s in /wp-sitemap.xml /wp-sitemap-posts-page-1.xml /wp-sitemap-users-1.xml; do
  curl -s -o /dev/null -w "$s %{http_code}\n" "http://oldenhaus-restaurant.local$s"
done
```

Erwartung: 200, 200, 404.

**JSON-LD ist gueltiges JSON** (nur Startseite, alles in einer Zeile):

```
curl -s http://oldenhaus-restaurant.local/ | grep 'ld+json' \
  | sed 's|.*<script type="application/ld+json">||; s|</script>.*||' \
  | php -r 'json_decode(stream_get_contents(STDIN)); echo json_last_error_msg(), "\n";'
```

Erwartung: `No error`. `schema.org` darin ist ein Namensraum, **keine** Anfrage – es
taucht seither in der Drittanbieter-Gegenprobe oben auf und ist dort kein Fund.
