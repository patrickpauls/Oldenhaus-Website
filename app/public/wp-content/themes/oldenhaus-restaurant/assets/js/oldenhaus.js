/**
 * Oldenhaus Restaurant – Frontend-Verhalten.
 *
 * Vier kleine Dinge, ohne Bibliothek:
 *   1. Kopfzeile färbt sich beim Scrollen, der Anruf-Button fährt ein
 *   2. Ruhige Bild-Slideshow im Hero
 *   3. Menü am Telefon
 *   4. Galerie vergrössern
 *
 * Verhalten und Zeiten stammen unverändert aus moodboard-oldenhaus.html.
 * Bewegung ist bewusst sparsam: Wer im Betriebssystem weniger Bewegung eingestellt
 * hat, bekommt eine stehende Slideshow.
 */
(function () {
	'use strict';

	var wenigerBewegung = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ---------------------------------------------------------------------
	 * 1 · Kopfzeile
	 * ------------------------------------------------------------------ */

	function kopfzeileBeobachten() {
		var kopf = document.querySelector('.site-header--ueber-hero');
		var hero = document.querySelector('.hero');

		// Unterseiten haben keinen Hero; ihre Kopfzeile steht ohnehin fest.
		if (!kopf || !hero) {
			return;
		}

		var wartet = false;

		function aktualisieren() {
			var y = window.scrollY;

			kopf.classList.toggle('is-solid', y > 40);
			// Der Anruf-Button erscheint, sobald gut die Hälfte des Heros durch ist.
			kopf.classList.toggle('show-call', y > hero.offsetHeight * 0.55);

			wartet = false;
		}

		function angefordert() {
			if (!wartet) {
				wartet = true;
				window.requestAnimationFrame(aktualisieren);
			}
		}

		window.addEventListener('scroll', angefordert, { passive: true });
		window.addEventListener('resize', angefordert, { passive: true });
		aktualisieren();
	}

	/* ---------------------------------------------------------------------
	 * 2 · Slideshow
	 * ------------------------------------------------------------------ */

	function slideshowStarten() {
		var bilder = document.querySelectorAll('.hero__bild');

		// Mit nur einem Bild gibt es nichts zu wechseln.
		if (bilder.length < 2 || wenigerBewegung) {
			return;
		}

		var aktuell = 0;

		window.setInterval(function () {
			bilder[aktuell].classList.remove('is-active');
			aktuell = (aktuell + 1) % bilder.length;
			bilder[aktuell].classList.add('is-active');
		}, 6500);
	}

	/* ---------------------------------------------------------------------
	 * 3 · „Heute geöffnet"
	 * ------------------------------------------------------------------ */

	/**
	 * Welcher Wochentag heute ist, gezählt ab Montag.
	 *
	 * getDay() zählt ab Sonntag (0). Die Öffnungszeiten stehen aber nach deutscher
	 * Lesegewohnheit ab Montag, deshalb die Verschiebung.
	 */
	function heuteIndex() {
		return (new Date().getDay() + 6) % 7;
	}

	/**
	 * Setzt den Hinweis „Heute geöffnet" im Hero.
	 *
	 * Die Zeiten kommen als JSON am Element selbst und stammen damit aus derselben
	 * Quelle wie Liste und Fusszeile. Bewusst im Browser berechnet statt auf dem
	 * Server: So bleibt der Hinweis auch dann richtig, wenn die Seite später aus
	 * einem Cache ausgeliefert wird.
	 */
	function heuteAnzeigen() {
		var element = document.querySelector('[data-zeiten]');

		if (!element) {
			return;
		}

		var zeiten;

		try {
			zeiten = JSON.parse(element.getAttribute('data-zeiten'));
		} catch (fehler) {
			return;
		}

		if (!Array.isArray(zeiten) || zeiten.length !== 7) {
			return;
		}

		var heute = zeiten[heuteIndex()];

		if (!heute) {
			return;
		}

		if (heute.ruhetag) {
			element.innerHTML = 'Heute ist <em>Ruhetag</em> – morgen wieder für dich da';
		} else if (heute.zeit) {
			element.textContent = 'Heute geöffnet: ';
			var hervorhebung = document.createElement('em');
			hervorhebung.textContent = heute.zeit;
			element.appendChild(hervorhebung);
		} else {
			// Ohne gepflegte Zeit lieber nichts behaupten.
			element.remove();
			return;
		}

		element.hidden = false;
	}

	/**
	 * Hebt den heutigen Tag in allen Öffnungszeiten-Listen hervor.
	 */
	function heutigenTagMarkieren() {
		var index = heuteIndex();

		document.querySelectorAll('.zeiten').forEach(function (liste) {
			var zeilen = liste.querySelectorAll('li');

			if (zeilen[index]) {
				zeilen[index].setAttribute('data-heute', 'ja');
			}
		});
	}

	/* ---------------------------------------------------------------------
	 * 4 · Menü am Telefon
	 * ------------------------------------------------------------------ */

	function menueVorbereiten() {
		var knopf = document.querySelector('.menue-knopf');
		var navigation = document.getElementById('hauptnavigation');

		if (!knopf || !navigation) {
			return;
		}

		function schliessen() {
			knopf.setAttribute('aria-expanded', 'false');
			navigation.hidden = true;
		}

		function oeffnen() {
			knopf.setAttribute('aria-expanded', 'true');
			navigation.hidden = false;
		}

		knopf.addEventListener('click', function () {
			if (knopf.getAttribute('aria-expanded') === 'true') {
				schliessen();
			} else {
				oeffnen();
			}
		});

		document.addEventListener('keydown', function (ereignis) {
			if (ereignis.key === 'Escape' && knopf.getAttribute('aria-expanded') === 'true') {
				schliessen();
				knopf.focus();
			}
		});

		// Ein Klick daneben schliesst das Menü wieder.
		document.addEventListener('click', function (ereignis) {
			if (knopf.getAttribute('aria-expanded') !== 'true') {
				return;
			}

			if (!navigation.contains(ereignis.target) && !knopf.contains(ereignis.target)) {
				schliessen();
			}
		});

		/*
		 * Wird das Fenster breit genug für die waagerechte Navigation, muss das
		 * hidden-Attribut weg – sonst bliebe die Navigation am Desktop verborgen,
		 * wenn sie am Telefon zuletzt geschlossen war.
		 */
		var breit = window.matchMedia('(min-width: 820px)');

		function anBreiteAnpassen(abfrage) {
			if (abfrage.matches) {
				navigation.hidden = false;
				knopf.setAttribute('aria-expanded', 'false');
			} else if (knopf.getAttribute('aria-expanded') !== 'true') {
				navigation.hidden = true;
			}
		}

		breit.addEventListener('change', anBreiteAnpassen);
		anBreiteAnpassen(breit);
	}

	/* ---------------------------------------------------------------------
	 * 5 · Galerie vergrössern
	 * ------------------------------------------------------------------ */

	function lightboxVorbereiten() {
		var galerie = document.querySelector('.galerie');

		if (!galerie) {
			return;
		}

		var ausloeser = null;
		var kasten = null;
		var bild = null;
		var beschriftung = null;
		var schliessknopf = null;

		function aufbauen() {
			kasten = document.createElement('div');
			kasten.className = 'lightbox';
			kasten.hidden = true;
			kasten.setAttribute('role', 'dialog');
			kasten.setAttribute('aria-modal', 'true');
			kasten.setAttribute('aria-label', 'Vergrössertes Bild');

			schliessknopf = document.createElement('button');
			schliessknopf.type = 'button';
			schliessknopf.className = 'lightbox__schliessen';
			schliessknopf.innerHTML = '<span aria-hidden="true">×</span><span class="nur-sr">Schliessen</span>';

			var rahmen = document.createElement('div');
			rahmen.className = 'lightbox__bild';

			bild = document.createElement('img');
			beschriftung = document.createElement('p');
			beschriftung.className = 'lightbox__text';

			rahmen.appendChild(bild);
			rahmen.appendChild(beschriftung);
			kasten.appendChild(schliessknopf);
			kasten.appendChild(rahmen);
			document.body.appendChild(kasten);

			schliessknopf.addEventListener('click', schliessen);

			kasten.addEventListener('click', function (ereignis) {
				// Klick auf den dunklen Rand schliesst, Klick aufs Bild nicht.
				if (ereignis.target === kasten) {
					schliessen();
				}
			});

			document.addEventListener('keydown', function (ereignis) {
				if (kasten.hidden) {
					return;
				}

				if (ereignis.key === 'Escape') {
					schliessen();
				}

				// Der einzige bedienbare Knopf im Dialog behält den Fokus.
				if (ereignis.key === 'Tab') {
					ereignis.preventDefault();
					schliessknopf.focus();
				}
			});
		}

		function oeffnen(adresse, text, link) {
			if (!kasten) {
				aufbauen();
			}

			ausloeser = link;
			bild.src = adresse;
			bild.alt = text || '';
			beschriftung.textContent = text || '';
			kasten.hidden = false;
			schliessknopf.focus();
		}

		function schliessen() {
			kasten.hidden = true;
			bild.removeAttribute('src');

			// Der Fokus geht dorthin zurück, wo er hergekommen ist.
			if (ausloeser) {
				ausloeser.focus();
				ausloeser = null;
			}
		}

		galerie.addEventListener('click', function (ereignis) {
			var link = ereignis.target.closest('a');

			if (!link || !galerie.contains(link)) {
				return;
			}

			// Nur Links, die direkt auf eine Bilddatei zeigen.
			if (!/\.(jpe?g|png|gif|webp|avif)$/i.test(link.getAttribute('href') || '')) {
				return;
			}

			var innenbild = link.querySelector('img');

			ereignis.preventDefault();
			oeffnen(
				link.getAttribute('href'),
				innenbild ? innenbild.getAttribute('alt') : '',
				link
			);
		});
	}

	/* ------------------------------------------------------------------ */

	/*
	 * Die Navigation zuerst verdrahten.
	 *
	 * Wirft eine der anderen Funktionen eine Ausnahme, bliebe das Menue am Telefon
	 * sonst unerreichbar - und das noscript-Netz greift nicht, weil JavaScript ja
	 * aktiv ist. Alles Weitere ist Beiwerk und darf notfalls ausfallen.
	 */
	menueVorbereiten();

	kopfzeileBeobachten();
	slideshowStarten();
	heuteAnzeigen();
	heutigenTagMarkieren();
	lightboxVorbereiten();
}());
