# Bildquellen – Oldenhaus Restaurant

**Alle Bilder auf dieser Website sind Übergangsbilder bis zum Fototermin.**
Sie sollen anschließend durch eigene Aufnahmen ersetzt werden. Der Austausch läuft
über die Mediathek im Backend und erfordert keine Änderung am Code.

## Herkunft und Lizenz

Sämtliche Bilder stammen aus der **WordPress-Fotodatenbank**
(<https://wordpress.org/photos/>) und stehen unter **CC0 1.0 – Public Domain**.

Das bedeutet: Die Urheber haben auf alle Rechte verzichtet. Die Bilder dürfen
kommerziell genutzt, bearbeitet und weitergegeben werden, **eine Namensnennung ist
nicht erforderlich**. Die Urheber sind unten trotzdem aufgeführt – aus Anstand und
damit nachvollziehbar bleibt, woher jedes Bild stammt.

Aus demselben Grund steht die Herkunft bei jedem Bild in der Mediathek im Feld
*Beschreibung*, nicht in der Bildunterschrift: Unter jedem Galeriebild eine
Quellenangabe wäre für Gäste nur Rauschen.

Die Bilder wurden auf Webgröße verkleinert (Hero max. 2400 px, übrige max. 1800 px)
und als JPEG komprimiert.

## Einzelnachweis

| Datei | Verwendung | Urheber | Lizenz | Original |
|---|---|---|---|---|
| `hero-1-pizza-ofen.jpg` | Hero-Slideshow | ChrisEdwardsCE | CC0 | [Link](https://wordpress.org/photos/photo/914636e748/) |
| `hero-2-gastraum.jpg` | Hero-Slideshow | Nilo Velez | CC0 | [Link](https://wordpress.org/photos/photo/61567a139e/) |
| `hero-3-pasta.jpg` | Hero-Slideshow | Nilo Velez | CC0 | [Link](https://wordpress.org/photos/photo/407685725f/) |
| `abholung-pizza.jpg` | Abschnitt Abholung | shirishpoudel07 | CC0 | [Link](https://wordpress.org/photos/photo/637683ef21/) |
| `feier-tafel.jpg` | Abschnitt Feiern & Events | Sagar Pansuriya | CC0 | [Link](https://wordpress.org/photos/photo/906686175a/) |
| `ueberuns-1-raum.jpg` | Seite Über uns | Nilo Velez | CC0 | [Link](https://wordpress.org/photos/photo/46677d64d9/) |
| `ueberuns-2-tische.jpg` | Seite Über uns | Manjil Aryal | CC0 | [Link](https://wordpress.org/photos/photo/86868c398c/) |
| `ueberuns-3-gnocchi.jpg` | Seite Über uns | Ericka Barboza | CC0 | [Link](https://wordpress.org/photos/photo/92166d2334/) |
| `galerie-1-pizza.jpg` | Seite Galerie | Bappy | CC0 | [Link](https://wordpress.org/photos/photo/53769fc610/) |
| `galerie-2-ziti.jpg` | Seite Galerie | Bigul Malayi | CC0 | [Link](https://wordpress.org/photos/photo/99767ddc00/) |
| `galerie-3-tiramisu.jpg` | Seite Galerie | ChrisEdwardsCE | CC0 | [Link](https://wordpress.org/photos/photo/514632a8d0/) |
| `galerie-4-wein.jpg` | Seite Galerie | Yam B Chhetri | CC0 | [Link](https://wordpress.org/photos/photo/564679e1f1/) |
| `galerie-5-raum.jpg` | Seite Galerie | Olesja Debrova | CC0 | [Link](https://wordpress.org/photos/photo/852693837c/) |
| `galerie-6-bruschetta.jpg` | Seite Galerie | ChrisEdwardsCE | CC0 | [Link](https://wordpress.org/photos/photo/9076329d5f/) |
| `galerie-7-arancini.jpg` | Seite Galerie | ChrisEdwardsCE | CC0 | [Link](https://wordpress.org/photos/photo/326329df56/) |

## Erneut beschaffen

`bildquellen.json` enthält zu jedem Bild die Original-Adresse. Damit lassen sich die
Dateien jederzeit neu laden und über `tools/bilder-importieren.php` einspielen:

```
wp eval-file tools/bilder-importieren.php /pfad/zum/bilderordner
```

Das Skript überspringt Bilder, die bereits in der Mediathek liegen, und überschreibt
keine Felder, die schon gefüllt sind.
