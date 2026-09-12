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
- [ ] Plugin `restaurant-basis`
- [ ] Theme-Grundgeruest + Design-Tokens
- [ ] Templates
- [ ] Reaktivitaet
- [ ] Seiten, Menues und Beispielinhalte
- [ ] Texte und Bilder
- [ ] Pruefungen + unabhaengige Review
- [ ] GitHub

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
