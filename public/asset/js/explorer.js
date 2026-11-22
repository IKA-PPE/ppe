// Carte de la page Explorer.
// Les données (campus et restos) sont dans la balise #donnees-explorer.

var carte = null;
var campus = null;
var lieux = [];
var parId = {};
var pastilles = {};

demarrer();

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
    creerCarte();
}

// Affiche ou cache Burger, Poulet, Pizza…
function plusDeFiltres() {
    var bloc = document.getElementById('suite');
    bloc.classList.toggle('on');
    if (bloc.classList.contains('on')) {
        this.textContent = 'Moins de filtres';
    } else {
        this.textContent = 'Plus de filtres';
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
    carte.on('moveend', espacer);
    document.getElementById('ici').onclick = recentrer;
    document.querySelector('.liste').addEventListener('click', clicListe);
}

function cartePrete() {
    var pastille = document.createElement('div');
    pastille.className = 'campus';
    pastille.textContent = campus.nom;
    new maplibregl.Marker({ element: pastille }).setLngLat([campus.lng, campus.lat]).addTo(carte);

    var i;
    for (i = 0; i < lieux.length; i++) {
        parId[lieux[i].id] = lieux[i];
        poserPrix(lieux[i]);
    }

    if (lieux.length === 1) {
        ouvrirFiche(lieux[0]);
    } else {
        espacer();
    }
}

// Un bouton avec le prix, posé sur la carte.
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

// Ouvre la fiche et colore le prix en orange.
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
    var el = pastilles[resto.id].getElement();
    el.classList.add('on');
    if (el.parentElement) {
        el.parentElement.style.zIndex = '8';
    }
    espacer();
    carte.easeTo({ center: [resto.lng, resto.lat], zoom: Math.max(carte.getZoom(), 15.4) });

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
    document.getElementById('fiche-min').textContent = resto.min + ' min à pied' + (resto.connu ? ' · Ouvert' : '');
    document.getElementById('fiche-rue').textContent = resto.euro + (resto.badges ? ' · ' + resto.badges : '') + ' · ' + resto.rue;

    var adresse = encodeURIComponent(resto.nom + ' ' + resto.rue + ' ' + campus.ville);
    document.getElementById('fiche-voir').href = 'https://www.google.com/maps/search/?api=1&query=' + adresse;
    document.getElementById('fiche-aller').href = 'https://www.google.com/maps/dir/?api=1&destination=' + adresse;
}

// Cache les prix qui se chevauchent. Le prix sélectionné reste visible.
function espacer() {
    var pris = [carte.project([campus.lng, campus.lat])];
    var i;
    for (i = 0; i < lieux.length; i++) {
        var el = pastilles[lieux[i].id].getElement();
        if (!el.classList.contains('on')) {
            continue;
        }
        el.style.visibility = 'visible';
        pris.push(carte.project([lieux[i].lng, lieux[i].lat]));
    }
    for (i = 0; i < lieux.length; i++) {
        var bouton = pastilles[lieux[i].id].getElement();
        if (bouton.classList.contains('on')) {
            continue;
        }
        var point = carte.project([lieux[i].lng, lieux[i].lat]);
        var tropPres = false;
        var j;
        for (j = 0; j < pris.length; j++) {
            var dx = point.x - pris[j].x;
            var dy = point.y - pris[j].y;
            if (dx * dx + dy * dy < 6200) {
                tropPres = true;
            }
        }
        if (tropPres) {
            bouton.style.visibility = 'hidden';
        } else {
            bouton.style.visibility = 'visible';
            pris.push(point);
        }
    }
}

function recentrer() {
    carte.easeTo({ center: [campus.lng, campus.lat], zoom: 14.4 });
}

// Un clic sur une ligne de la liste ouvre la fiche, sans changer de page.
function clicListe(e) {
    var lien = e.target.closest('.ouvrir');
    if (!lien) {
        return;
    }
    e.preventDefault();
    ouvrirFiche(parId[lien.getAttribute('data-id')]);
}
