# Technische Spec – Oldenhaus Restaurant – Pizzeria

*Diese Spec beschreibt, WAS für die Website gebraucht wird (aus dem Kunden-Fragebogen). WIE es technisch als WordPress-Theme umgesetzt wird, entscheidet der Skill `wordpress-theme-bauer` anhand seiner eigenen festen Vorgehensweise.*

- Quelle: Fragebogen vom 11.09.2026, Ansprechpartner Kunde: Egli, ausgefüllt mit Patrick
- Design-Vorlage: `moodboard-oldenhaus.html` (CSS-Custom-Properties dort sind verbindlich)
- Status: **wartet auf Kundenfreigabe** – offene Punkte siehe unten

---

## Basisdaten

- Name (Anzeige): Oldenhaus · Zusatz: Restaurant – Pizzeria
- Adresse: Dorfstraße 19, 25870 Oldenswort
- Telefon: **OFFEN** (global an einer Stelle pflegbar, wird von allen Anruf-Buttons genutzt)
- E-Mail: keine vorhanden (**OFFEN**, fürs Impressum nötig)
- Instagram: https://www.instagram.com/oldenhaus/ (nur Link, kein Feed)
- Google Business Profil: vorhanden
- Bestehende Website: keine (keine Migration, keine Weiterleitungen)
- Logo: keins – Platzhalter = Wortmarke „Oldenhaus" im Headline-Font; Logo wird nachgereicht und muss später ohne Code-Änderung austauschbar sein
- Sprache: nur Deutsch

## Farben

| Rolle | Token | Hex | Anteil | Einsatz |
|---|---|---|---|---|
| Primär | `--c-cream` „Teigcreme" | #F5EEE2 | 60 % | Seitenhintergrund, helle Flächen, Text auf dunklem Grund |
| Sekundär | `--c-walnut` „Walnuss" | #3A2A20 | 30 % | Header (gescrollt), Footer, dunkle Bänder, Headlines |
| Akzent | `--c-tomato` „Tomate" | #B0432A | 10 % | Reservieren/Anrufen-Button, Links, aktive Nav |

Ergänzende Töne (keine eigenen Flächenanteile):

| Token | Hex | Einsatz |
|---|---|---|
| `--c-ink` | #2B1F18 | Fließtext auf Creme (Kontrast 13,9:1) |
| `--c-ink-muted` | #6B5646 | Gericht-Beschreibungen, Nebeninfos (6,0:1) |
| `--c-wood` | #E9DCC6 | abgesetzte Flächen (z. B. FAQ, Menü-Kategorien-Wechsel), Bild-Platzhalter hell |
| `--c-olive` | #5C6632 | Kennzeichnung vegetarisch/vegan (5,4:1 auf Creme) |
| `--c-tomato-dark` | #9E3B24 | Hover/Focus des Akzent-Buttons |
| `--c-apricot` | #E8B48E | Hervorhebungen auf Walnuss-Grund (7,4:1) |

Herleitung: B1 keine Markenfarben → Erdtöne + warme Farben; A3 warm & gemütlich + rustikal; A6-Kritik „Hintergrund wirkt wie hartes Weiß" → Creme statt Weiß. Kein reines #000/#FFF.

## Typografie

- Headline-Font: **Alegreya Sans**, 800 (H1/H2/Wortmarke), 700 (H3) – humanistische Grotesk mit kalligrafischer, leicht handgemachter Anmutung; erfüllt „modern-clean, serifenlos" (B1) und bringt die Wärme von „rustikal" (A3) mit, ohne Holzschild-Kitsch
- Fließtext-Font: **Source Sans 3**, 400 / 600 (+ 400 italic nur falls nötig)
- Schriften **lokal ausliefern**, kein Laden von externen Font-Servern (keine Drittdienste, siehe unten)
- Größen (aus Moodboard übernehmen):
  - H1 Hero: `clamp(2.6rem, 5.2vw, 4.4rem)`, line-height 1.02
  - H2: `clamp(2rem, 3.4vw, 2.75rem)`, line-height 1.1
  - H3 / Gerichtname: 1.375rem
  - Fließtext: **1.125rem (18 px), line-height 1.6** – Mindestgröße für Text 1rem (A6-Kritik „Schrift zu klein")
- Keine Versal-Labels mit Sperrung

## Layout-Grundsätze (aus A3/A6 abgeleitet – verbindlich)

1. **Mehrseitige Website, kein One-Pager** (A6, Ref. 1 ausdrücklich abgelehnt)
2. Inhalte **zentriert** in einem Container (max. ca. 1120 px), gleiche Außenabstände links/rechts (A6-Kritik „leicht nach links versetzt")
3. **Fotos mit abgerundeten Ecken** (Radius 18 px), Buttons 8 px, Karten/Flächen 12 px bzw. kantig – nicht überall derselbe Radius
4. Bilder nicht im starren Raster: Text-Bild-Blöcke abwechselnd, Bilder teils hochkant (Ref. 2 „Anordnung der Bilder")
5. **Bewegung: ruhig & statisch** (A3). Erlaubt sind nur: langsamer Crossfade der Hero-Slideshow, Farbwechsel des Headers beim Scrollen, schlichte Hover-Farbwechsel. **Keine** Scroll-Animationen, **keine** sich drehenden Kacheln/Flip-Cards (A6-Kritik Ref. 1), kein Parallax. `prefers-reduced-motion` → Slideshow steht still
6. Kein Dropdown/keine Untermenüs in der Navigation (B19)

## Globale Elemente

### Header
- Startzustand auf der Startseite: transparent über dem Hero, Wortmarke + Nav in Creme
- Beim Scrollen (sobald Hero verlassen wird): Hintergrund Walnuss, und der **Anruf-/Reservieren-Button erscheint im Header** (B2 Prio 1, Ref. 1)
- Unterseiten ohne Hero: Header direkt im gescrollten Zustand
- Header bleibt fixiert (sticky) → Reservieren-Button **dauerhaft sichtbar** (B2)
- Navigation (flach, nebeneinander): Speisekarte · Über uns · Galerie · Öffnungszeiten & Anfahrt (Startseite über Wortmarke)
- Mobil: flache Menüliste ohne Untermenüs; Anruf-Button bleibt auch mobil jederzeit sichtbar

### Reservieren-/Anruf-Button
- Aktion: **Telefon-Link** (öffnet Telefon-App mit hinterlegter Nummer, B19) – kein Formular, kein Buchungstool, keine eigene Reservierungsseite (B5)
- Beschriftung-Vorschlag: „Tisch reservieren" + Nummer sichtbar bzw. „Anrufen" im kompakten Header
- Farbe: Akzent Tomate

### Footer
- Adresse, Telefon, Öffnungszeiten kompakt, Instagram-Link
- Links: Impressum · Datenschutzerklärung

## Seitenstruktur

### Startseite (Sections in Reihenfolge)
1. **Hero – Bild-Slideshow** (B2): 3–5 Fotos (Food-Nahaufnahmen + Gastraum), langsamer Crossfade (~6–7 s Standzeit). Darauf: H1 = Leitspruch (**OFFEN**, 3 Vorschläge in Konzept), Unterzeile mit Küche + Ort, Reservieren-/Anruf-Button, Kurzinfo „Heute geöffnet …" *(Vorschlag, im Moodboard gezeigt)*
2. **Öffnungszeiten** (Prio 2) – komplette Woche, inkl. Hinweis auf aktuelle Sonderöffnungszeiten, falls eingetragen
3. **Abholung** (Prio 3) – Hinweis „Alle Gerichte auch zum Mitnehmen", telefonisch bestellen, Anruf-Button (B8: nur Hinweis, keine eigene Seite, kein Lieferportal)
4. **Feiern & Events** (Prio 4) – Hinweis-Block: Firmenfeiern, Weihnachtsfeiern, Hochzeiten, Raum für ca. 70 Personen, Anfrage telefonisch (kein Formular). *Präsentationsform **OFFEN**, s. u.*
5. **Gutscheine** – kurzer Hinweis „Gutscheine gibt's bei uns vor Ort" (B10: kein Online-Verkauf)
6. **FAQ** – letzter Abschnitt der Startseite, nicht in der Navigation (B14). Aufklappbar oder offen – ruhig, ohne Animation
   - Sind Hunde willkommen?
   - Gibt es auch was für den kleinen Hunger?
   - Kann man mit Karte zahlen?
   - Darf man auch nur was trinken?
   - Kann man bei euch parken? (Antwort bekannt: kostenlose Parkplätze vorhanden)
   - Antworten 1–4 **OFFEN**

Nicht auf der Startseite (in B2 nicht gewählt): Speisekarten-Ausschnitt, Karte/Maps, Bewertungen, Presse, Aktionen.

### Speisekarte
1. Seitentitel + kurzer Einleitungssatz
2. **Alle Kategorien untereinander auf einer scrollbaren Seite**, Kategorie = Zwischenüberschrift (A6-Kritik Ref. 3: **kein** Kategorie-Auswahlmenü/keine Tabs/kein Filter)
3. Getränke als Kategorie(n) am Ende derselben Seite (B4: keine separate Getränkekarte)
4. Hinweis-Zeile zur Kennzeichnung (Legende vegetarisch/vegan)

### Über uns
1. Gemeinsamer Teamtext (keine Einzelporträts, B3)
2. Küchenkonzept: italienische Küche, Pizza; USP persönlicher Service/Nähe zu Stammgästen + Preis-Leistung (A5)
3. Ambiente-Fotos im Wechsel mit Text
- Keine Entstehungsgeschichte (B3 = Nein)

### Galerie
- Bildarten: Food-Fotos, Ambiente/Innenraum (B6)
- Vom Kunden selbst befüllbar und bearbeitbar
- Abgerundete Bilder, ruhige Darstellung; Vergrößerung per Klick ist ok, ohne Effekt-Animationen

### Öffnungszeiten & Anfahrt
1. Öffnungszeiten-Tabelle (s. Funktionen)
2. Sonderöffnungszeiten (Feiertage/Urlaub) – nur sichtbar, wenn eingetragen
3. Adresse als Text – **kein Google-Maps-Embed** (B9)
4. Parken: kostenlose Parkplätze vorhanden
5. Telefon mit Anruf-Button; Instagram-Link

### Impressum / Datenschutzerklärung
- Seiten anlegen, mit Platzhaltertext befüllen (B19); Inhalte folgen (Generator, kommt vom Auftragnehmer nach Datenlieferung)

## Funktionen

### Speisekarte
- Darstellung: je Gericht **Name, Beschreibung, Preis** direkt auf der Seite (B4) – **ohne Fotos je Gericht**, kein PDF-Download
- Preise anzeigen: ja
- Kennzeichnung: vegetarisch, vegan (keine Allergene, kein glutenfrei)
- Änderungsfrequenz: selten
- Getränkekarte separat: nein (Teil der Speisekarte)
- Kunde muss Gerichte, Kategorien, Preise und Reihenfolge selbst pflegen können
- Umfang (Anzahl Gerichte/Kategorien): **OFFEN** – Karte liegt nicht digital vor

### Reservierung
- Weg: nur Telefon (tel:-Link, B5/B19)
- Abgefragte Felder: keine (kein Formular)

### Öffnungszeiten
| Tag | Zeit |
|---|---|
| Montag | 17–22 Uhr |
| Dienstag | Ruhetag |
| Mittwoch | 17–22 Uhr |
| Donnerstag | 17–22 Uhr |
| Freitag | 12–22 Uhr |
| Samstag | 12–22 Uhr |
| Sonntag | 12–22 Uhr |

- Regelzeiten **und** Sonderöffnungszeiten vom Kunden selbst pflegbar (B9 = ja)
- Dieselben Daten speisen Startseite, Öffnungszeiten-Seite und Footer (eine Quelle)

### Galerie
- Kunde lädt Bilder selbst hoch / sortiert / löscht (Zugriff nur mit Backend-Login)

### Events & Feiern
- Präsentation: Hinweis auf Startseite (Annahme, **OFFEN** zur Bestätigung)
- Arten: Firmenfeiern/Business-Events, Weihnachtsfeiern/saisonale Feiern, Hochzeiten
- Kapazität: ca. 70 Personen
- Anfrageformular: nein, nur Kontaktdaten/Anruf
- Text des Hinweises vom Kunden anpassbar

### Lieferservice / Abholung
- Präsentation: nur Hinweis/Button auf Startseite
- Weg: nur telefonische Bestellung (keine Lieferportale, kein Bestellsystem)

### Gutscheine
- Präsentation: nur Hinweis auf Startseite
- Kauf: nur vor Ort, kein Online-Verkauf

### FAQ
- Fragen + Antworten vom Kunden selbst pflegbar (Hinzufügen/Ändern/Reihenfolge)

### Nicht gewünscht (B5, B11–B13, B15)
Reservierungsseite/-formular · Jobs · Presse/Bewertungen/Google-Reviews-Widget · Blog · Instagram-Feed · WhatsApp-Button · Newsletter · Kontaktformular · Mehrsprachigkeit

### Drittdienste
- **Keine** eingebundenen Drittdienste vorgesehen (kein Maps, kein Feed, keine externen Fonts, kein Buchungstool, kein Tracking gewünscht). Instagram nur als einfacher Link.

## Content & Rechtliches

- Ansprache: **Du**
- Sprachstil: humorvoll/verspielt + herzlich/familiär (B18) – Humor als Augenzwinkern in Überschriften/Mikrotexten, Infotexte (Zeiten, Adresse) klar
- Texte: **Auftragnehmer schreibt** (B17 „Unterstützung gewünscht"), Kunde gibt frei
- Bilder: noch keine Profi-Fotos; **übergangsweise Stockfotos ok** bzw. Platzhalter (B17/B19). Bildwelt: Food-Nahaufnahmen + Ambiente/Innenraum (B1), keine Team-Fotos nötig
- Zielgruppen (A4): Familien mit Kindern, Paare, Business-Lunch, Stammgäste aus der Nachbarschaft
- Kernbotschaft (A5): „Das sieht gemütlich, einladend und lecker aus. Da geh ich essen."
- USP (A5): persönlicher Service/Nähe zu Stammgästen, Preis-Leistung
- Küche (B3): italienisch, Pizza
- Impressum-Daten (B16): **komplett OFFEN** (Firmenname, Rechtsform, Vertretungsberechtigter, ggf. Register, USt-IdNr./Steuernr., ggf. Aufsichtsbehörde)

## Offene Punkte

1. **Telefonnummer** – blockiert alle Anruf-Buttons (Platzhalter bis dahin)
2. **E-Mail-Adresse** – nicht vorhanden, für Impressum erforderlich
3. **Speisekarten-Inhalte** – nicht digital vorhanden; Kunde liefert Foto/Scan, Auftragnehmer überträgt; Veggie-/Vegan-Markierung durch Kunden
4. **Events-Präsentation** – Widerspruch im Bogen (A2 angekreuzt, B7 „Nein", Details trotzdem ausgefüllt, B2 Prio 4). Annahme: Hinweis auf Startseite, keine Unterseite – Bestätigung ausstehend
5. **FAQ-Antworten** zu Fragen 1–4
6. **Impressum-Angaben** (B16 leer)
7. **Logo** – kommt später, bis dahin Wortmarke
8. **Fotos** – Platzhalter/Stock bis Fototermin; Termin offen
9. **Leitspruch** – 3 Vorschläge im Konzept, Auswahl ausstehend
10. **Business-Lunch vs. Öffnungszeiten** – Mo–Do erst ab 17 Uhr; Klärung, ob Geschäftsessen textlich angesprochen werden soll
11. **Referenz-Farben** – Referenz-Websites konnten nur als Text ausgewertet werden (Struktur/Verhalten übernommen); Farbabgleich mit den Referenzen visuell durch Auftragnehmer prüfen
