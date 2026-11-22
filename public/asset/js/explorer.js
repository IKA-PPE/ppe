// Ce fichier dessine seulement la carte.
// La liste des restos est déjà affichée par la vue PHP.

var carte = null;
var campus = null;
var lieux = [];
var parId = {};
var pastilles = {};

demarrer();

// On lit les restos, puis on lance la carte.
function demarrer() {
    var bouton = document.getElementById('plusf');
    if (bouton) {
        bouton.onclick = plusDeFiltres;
    }

    var bloc = document.getElementById('donnees-explorer');
    if (!bloc) {
        return;
    }

    var donnees = JSON.parse(bloc.textContent);
    campus = donnees.campus;
    lieux = donnees.lieux;
    var i;
    for (i = 0; i < donnees.liste.length; i++) {
        parId[donnees.liste[i].id] = donnees.liste[i];
    }
    creerCarte();
}

// Montre ou cache Burger, Poulet, Pizza...
function plusDeFiltres() {
    var bloc = document.getElementById('suite');
    if (bloc.classList.contains('on')) {
        bloc.classList.remove('on');
        this.textContent = 'Plus de filtres';
    } else {
        bloc.classList.add('on');
        this.textContent = 'Moins de filtres';
    }
}

function creerCarte() {
    carte = new maplibregl.Map({
        container: 'carte',
        style: 'https://tiles.openfreemap.org/styles/fiord',
        center: [campus.lng, campus.lat],
        zoom: 14.4
    });
    carte.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'top-right');
    carte.dragRotate.disable();
    carte.on('load', cartePrete);
    document.getElementById('ici').onclick = recentrer;
    document.querySelector('.liste').onclick = clicListe;
}

function cartePrete() {
    var pastille = document.createElement('div');
    pastille.className = 'campus';
    pastille.textContent = campus.nom;
    new maplibregl.Marker({ element: pastille, anchor: 'center' }).setLngLat([campus.lng, campus.lat]).addTo(carte);

    var i;
    for (i = 0; i < lieux.length; i++) {
        parId[lieux[i].id] = lieux[i];
        poserPrix(lieux[i]);
    }

    carte.on('moveend', espacer);
    carte.resize();
    carte.jumpTo({ center: [campus.lng, campus.lat], zoom: 14.4 });

    if (lieux.length == 1) {
        ouvrirFiche(lieux[0]);
    } else {
        espacer();
    }
}

// Pose le prix du resto sur la carte.
function poserPrix(resto) {
    var bouton = document.createElement('button');
    bouton.type = 'button';
    bouton.className = 'bulle';
    bouton.textContent = resto.euro;
    bouton.onclick = function () {
        ouvrirFiche(resto);
    };

    pastilles[resto.id] = new maplibregl.Marker({ element: bouton, anchor: 'bottom' })
        .setLngLat([resto.lng, resto.lat])
        .addTo(carte);
}

// Ouvre la fiche du resto cliqué.
function ouvrirFiche(resto) {
    if (!resto) {
        return;
    }

    var bulles = document.querySelectorAll('.bulle');
    var i;
    for (i = 0; i < bulles.length; i++) {
        bulles[i].classList.remove('on');
    }

    if (!pastilles[resto.id]) {
        poserPrix(resto);
    }

    var prix = pastilles[resto.id].getElement();
    prix.classList.add('on');
    if (prix.parentElement) {
        prix.parentElement.style.zIndex = '8';
    }

    espacer();

    var zoom = carte.getZoom();
    if (zoom < 15.4) {
        zoom = 15.4;
    }
    carte.easeTo({ center: [resto.lng, resto.lat], zoom: zoom });

    var lignes = document.querySelectorAll('.item');
    for (i = 0; i < lignes.length; i++) {
        lignes[i].classList.remove('on');
    }
    var ligne = document.getElementById('row-' + resto.id);
    if (ligne) {
        ligne.classList.add('on');
        ligne.scrollIntoView({ block: 'nearest' });
    }

    document.getElementById('fiche').style.display = 'block';

    var avatar = document.getElementById('fiche-av');
    avatar.textContent = resto.sigle;
    avatar.style.background = resto.fond;
    avatar.style.color = resto.encre;

    document.getElementById('fiche-nom').textContent = resto.nom;

    var texteMin = resto.min + ' min à pied';
    if (resto.connu && resto.ouvert) {
        texteMin = texteMin + ' · Ouvert';
    }
    document.getElementById('fiche-min').textContent = texteMin;

    var texteRue = resto.euro;
    if (resto.badges) {
        texteRue = texteRue + ' · ' + resto.badges;
    }
    texteRue = texteRue + ' · ' + resto.rue;
    document.getElementById('fiche-rue').textContent = texteRue;

    var adresse = encodeURIComponent(resto.nom + ' ' + resto.rue + ' ' + campus.ville);
    document.getElementById('fiche-voir').href = 'https://www.google.com/maps/search/?api=1&query=' + adresse;
    document.getElementById('fiche-aller').href = 'https://www.google.com/maps/dir/?api=1&destination=' + adresse;
}

// Cache les prix qui se touchent. Le prix orange reste visible.
function espacer() {
    var gardes = [];
    gardes.push(carte.project([campus.lng, campus.lat]));

    var i;
    for (i = 0; i < lieux.length; i++) {
        if (!pastilles[lieux[i].id]) {
            continue;
        }
        var elu = pastilles[lieux[i].id].getElement();
        if (elu.classList.contains('on')) {
            elu.style.visibility = 'visible';
            gardes.push(carte.project([lieux[i].lng, lieux[i].lat]));
        }
    }

    for (i = 0; i < lieux.length; i++) {
        if (!pastilles[lieux[i].id]) {
            continue;
        }
        var bouton = pastilles[lieux[i].id].getElement();
        if (bouton.classList.contains('on')) {
            continue;
        }

        var point = carte.project([lieux[i].lng, lieux[i].lat]);
        var tropPres = false;
        var j;
        for (j = 0; j < gardes.length; j++) {
            var dx = point.x - gardes[j].x;
            var dy = point.y - gardes[j].y;
            var minimum = 80 * 80;
            if (j == 0) {
                minimum = 160 * 160;
            }
            if ((dx * dx) + (dy * dy) < minimum) {
                tropPres = true;
            }
        }

        if (tropPres) {
            bouton.style.visibility = 'hidden';
        } else {
            bouton.style.visibility = 'visible';
            gardes.push(point);
        }
    }
}

function recentrer() {
    carte.easeTo({ center: [campus.lng, campus.lat], zoom: 14.4 });
}

// Un clic sur une ligne ouvre la fiche, sans changer de page.
function clicListe(evenement) {
    var element = evenement.target;
    while (element) {
        if (element.classList && element.classList.contains('ouvrir')) {
            evenement.preventDefault();
            var id = element.getAttribute('data-id');
            ouvrirFiche(parId[id]);
            return;
        }
        element = element.parentElement;
    }
}
