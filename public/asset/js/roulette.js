// Page Roulette : dessine la roue, la fait tourner et affiche le resto choisi.

var restos = JSON.parse(document.getElementById('donnees-roulette').textContent);

var canvas = document.getElementById('roue');
var ctx = canvas.getContext('2d');
var bouton = document.getElementById('lancer');

var rotation = 0;      // rotation totale de la roue, en degrés
var gagnant = -1;      // numéro du resto tiré
var enTrain = false;   // true pendant que la roue tourne

// Coupe les noms trop longs pour qu'ils tiennent dans une part.
function raccourcir(nom) {
    if (nom.length > 14) {
        return nom.substring(0, 13) + '…';
    }
    return nom;
}

// Dessine la roue dans le canvas : une part de couleur par resto, avec son nom.
// Si la roue est vide, on dessine 6 parts grises.
function dessinerRoue() {
    var centre = canvas.width / 2;
    var rayon = canvas.width / 2;

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Roue vide : 6 parts grises comme sur le wireframe
    if (restos.length == 0) {
        for (var v = 0; v < 6; v++) {
            ctx.beginPath();
            ctx.moveTo(centre, centre);
            ctx.arc(centre, centre, rayon, -Math.PI / 2 + v * Math.PI / 3, -Math.PI / 2 + (v + 1) * Math.PI / 3);
            ctx.closePath();
            if (v % 2 == 0) {
                ctx.fillStyle = '#1c1c21';
            } else {
                ctx.fillStyle = '#232328';
            }
            ctx.fill();
        }
        return;
    }

    var part = (Math.PI * 2) / restos.length;

    for (var i = 0; i < restos.length; i++) {
        var debut = -Math.PI / 2 + i * part;

        // La part de couleur
        ctx.beginPath();
        ctx.moveTo(centre, centre);
        ctx.arc(centre, centre, rayon, debut, debut + part);
        ctx.closePath();
        ctx.fillStyle = restos[i].couleur;
        ctx.fill();

        // Un trait clair entre deux parts
        if (restos.length > 1) {
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.25)';
            ctx.lineWidth = 2;
            ctx.stroke();
        }

        // Le nom, écrit au milieu de la part
        ctx.save();
        ctx.translate(centre, centre);
        ctx.rotate(debut + part / 2);
        ctx.translate(rayon * 0.66, 0);
        ctx.rotate(Math.PI / 2);
        ctx.fillStyle = '#ffffff';
        ctx.font = '600 30px -apple-system, BlinkMacSystemFont, Inter, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.shadowColor = 'rgba(0, 0, 0, 0.35)';
        ctx.shadowBlur = 4;
        ctx.fillText(raccourcir(restos[i].nom), 0, 0);
        ctx.restore();
    }
}

// Clic sur "Lancer" : tire un resto au hasard et fait tourner la roue
// (l'animation est faite en CSS grâce à la transition sur transform).
function lancer() {
    if (enTrain || restos.length < 2) {
        return;
    }
    enTrain = true;
    bouton.disabled = true;

    // On remet la carte résultat à zéro
    document.getElementById('resultat').classList.remove('gagne');
    document.getElementById('resultat-tuile').textContent = '?';
    document.getElementById('resultat-tuile').style.background = '';
    document.getElementById('resultat-nom').textContent = 'La roue tourne…';
    document.getElementById('resultat-info').textContent = 'Croise les doigts';

    // On tire le resto au hasard
    gagnant = Math.floor(Math.random() * restos.length);

    // Angle (depuis le haut) du milieu de la part gagnante, avec un petit décalage
    var part = 360 / restos.length;
    var decalage = (Math.random() - 0.5) * part * 0.6;
    var cible = 360 - ((gagnant + 0.5) * part + decalage);

    // On ajoute 5 tours complets puis on s'arrête sur la part gagnante
    var actuel = rotation % 360;
    var ecart = cible - actuel;
    while (ecart < 0) {
        ecart = ecart + 360;
    }
    rotation = rotation + 5 * 360 + ecart;

    canvas.style.transform = 'rotate(' + rotation + 'deg)';
}

// Quand la roue s'arrête, on affiche le résultat
function afficherResultat() {
    if (gagnant < 0) {
        return;
    }
    var resto = restos[gagnant];

    document.getElementById('resultat').classList.add('gagne');
    document.getElementById('resultat-tuile').textContent = resto.sigle;
    document.getElementById('resultat-tuile').style.background = resto.couleur;
    document.getElementById('resultat-nom').textContent = resto.nom;
    document.getElementById('resultat-info').textContent = resto.min + ' min à pied · ' + resto.prix;
    document.getElementById('resultat-aller').href = 'https://www.google.com/maps/dir/?api=1&travelmode=walking&destination=' + resto.lat + ',' + resto.lng;

    enTrain = false;
    bouton.disabled = false;
}

dessinerRoue();
bouton.addEventListener('click', lancer);
canvas.addEventListener('transitionend', afficherResultat);
