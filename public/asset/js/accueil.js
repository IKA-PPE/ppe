// Page Accueil : carte des restos, filtres, fenêtres et compte à rebours du vote.
// Les données sont déjà préparées par le contrôleur PHP.

var carte = null;
var campus = null;
var lieux = [];
var parId = {};
var pastilles = {};
var filtres = { halal: false, prix: false, ouvert: false };
var choisi = null;

demarrer();

function demarrer() {
    brancherFenetres();
    compteARebours();

    var bloc = document.getElementById('donnees-accueil');
    if (!bloc || typeof maplibregl == 'undefined') {
        return;
    }
    var donnees = JSON.parse(bloc.textContent);
    campus = donnees.campus;
    lieux = donnees.lieux;
    var i;
    for (i = 0; i < lieux.length; i++) {
        parId[lieux[i].id] = lieux[i];
    }
    creerCarte();
}

// Boutons "Ajouter une dépense", "Définir mon budget"... ouvrent une fenêtre.
function brancherFenetres() {
    var boutons = document.querySelectorAll('[data-ouvrir]');
    var i;
    for (i = 0; i < boutons.length; i++) {
        boutons[i].onclick = function () {
            var fenetre = document.getElementById(this.getAttribute('data-ouvrir'));
            fenetre.showModal();
            var champ = fenetre.querySelector('input[type=text]');
            if (champ) {
                champ.focus();
            }
        };
    }
    var fermer = document.querySelectorAll('[data-fermer]');
    for (i = 0; i < fermer.length; i++) {
        fermer[i].onclick = function () {
            this.closest('dialog').close();
        };
    }
    // Clic à côté de la fenêtre = fermer.
    var fenetres = document.querySelectorAll('dialog');
    for (i = 0; i < fenetres.length; i++) {
        fenetres[i].onclick = function (evenement) {
            if (evenement.target == this) {
                this.close();
            }
        };
    }
}

// Temps restant avant la fin du vote de groupe.
function compteARebours() {
    var zone = document.getElementById('compte-vote');
    if (!zone) {
        return;
    }
    var fin = Date.now() + parseInt(zone.getAttribute('data-reste'), 10) * 1000;

    function afficher() {
        var reste = Math.max(0, Math.floor((fin - Date.now()) / 1000));
        var heures = Math.floor(reste / 3600);
        var minutes = Math.floor((reste % 3600) / 60);
        var secondes = reste % 60;
        if (heures > 0) {
            zone.textContent = heures + ' h ' + deuxChiffres(minutes);
        } else {
            zone.textContent = deuxChiffres(minutes) + ':' + deuxChiffres(secondes);
        }
        if (reste == 0) {
            zone.textContent = 'Terminé';
            clearInterval(minuteur);
        }
    }
    var minuteur = setInterval(afficher, 1000);
    afficher();
}

function deuxChiffres(nombre) {
    if (nombre < 10) {
        return '0' + nombre;
    }
    return '' + nombre;
}

function creerCarte() {
    carte = new maplibregl.Map({
        container: 'carte-accueil',
        style: 'https://tiles.openfreemap.org/styles/fiord',
        center: [campus.lng, campus.lat],
        zoom: 15,
        attributionControl: { compact: true }
    });
    carte.dragRotate.disable();
    carte.on('load', cartePrete);

    // La carte suit la taille de son cadre (fenêtre redimensionnée, etc.).
    if (typeof ResizeObserver != 'undefined') {
        new ResizeObserver(function () {
            carte.resize();
        }).observe(document.getElementById('carte-accueil'));
    }

    document.getElementById('zoom-plus').onclick = function () {
        carte.zoomIn();
    };
    document.getElementById('zoom-moins').onclick = function () {
        carte.zoomOut();
    };

    var boutons = document.querySelectorAll('[data-filtre]');
    var i;
    for (i = 0; i < boutons.length; i++) {
        boutons[i].onclick = changerFiltre;
    }

    // Clic sur un resto "Près du campus" : on le montre sur la carte.
    var lignes = document.querySelectorAll('[data-resto]');
    for (i = 0; i < lignes.length; i++) {
        lignes[i].onclick = function (evenement) {
            var resto = parId[this.getAttribute('data-resto')];
            if (resto && carte.loaded()) {
                evenement.preventDefault();
                choisir(resto);
            }
        };
    }
}

function cartePrete() {
    var bloc = document.createElement('div');
    bloc.className = 'acc-nous';
    var point = document.createElement('div');
    point.className = 'acc-point';
    var pastille = document.createElement('div');
    pastille.className = 'acc-campus';
    pastille.textContent = campus.nom;
    bloc.appendChild(point);
    bloc.appendChild(pastille);
    new maplibregl.Marker({ element: bloc, anchor: 'center' }).setLngLat([campus.lng, campus.lat]).addTo(carte);

    var i;
    for (i = 0; i < lieux.length; i++) {
        poserPrix(lieux[i]);
    }
    carte.resize();

    // Le resto le plus proche est mis en avant, comme sur la maquette.
    if (lieux.length > 0) {
        mettreEnAvant(lieux[0]);
    }
}

function poserPrix(resto) {
    var bouton = document.createElement('button');
    bouton.type = 'button';
    bouton.className = 'acc-bulle';
    bouton.textContent = resto.euro;
    bouton.title = resto.nom + ' · ' + resto.min + ' min à pied';
    bouton.onclick = function () {
        choisir(resto);
    };
    pastilles[resto.id] = new maplibregl.Marker({ element: bouton, anchor: 'bottom' })
        .setLngLat([resto.lng, resto.lat])
        .addTo(carte);
}

// Le prix orange affiche aussi le nom du resto.
function mettreEnAvant(resto) {
    if (choisi && pastilles[choisi.id]) {
        var ancien = pastilles[choisi.id].getElement();
        ancien.classList.remove('on');
        ancien.textContent = choisi.euro;
        ancien.style.zIndex = '';
    }
    choisi = resto;
    if (!pastilles[resto.id]) {
        poserPrix(resto);
    }
    var bouton = pastilles[resto.id].getElement();
    bouton.classList.add('on');
    bouton.textContent = resto.nom + ' · ' + resto.euro;
    bouton.style.display = '';
    bouton.style.zIndex = '5';

    var lignes = document.querySelectorAll('[data-resto]');
    var i;
    for (i = 0; i < lignes.length; i++) {
        if (lignes[i].getAttribute('data-resto') == resto.id) {
            lignes[i].classList.add('on');
        } else {
            lignes[i].classList.remove('on');
        }
    }
}

function choisir(resto) {
    mettreEnAvant(resto);
    var zoom = carte.getZoom();
    if (zoom < 15.5) {
        zoom = 15.5;
    }
    carte.easeTo({ center: [resto.lng, resto.lat], zoom: zoom });
}

// Allume ou éteint un filtre, puis cache les restos qui ne correspondent pas.
function changerFiltre() {
    var nom = this.getAttribute('data-filtre');
    filtres[nom] = !filtres[nom];
    this.classList.toggle('on', filtres[nom]);

    var i;
    for (i = 0; i < lieux.length; i++) {
        var resto = lieux[i];
        var garde = true;
        if (filtres.halal && !resto.halal) {
            garde = false;
        }
        if (filtres.prix && !resto.prix) {
            garde = false;
        }
        if (filtres.ouvert && !(resto.connu && resto.ouvert)) {
            garde = false;
        }
        if (pastilles[resto.id]) {
            pastilles[resto.id].getElement().style.display = garde ? '' : 'none';
        }
    }
}
