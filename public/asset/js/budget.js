// Page Budget : fenêtres, onglets Mois / Semaine du graphe, confirmation de suppression.
// Les chiffres sont déjà calculés par le contrôleur PHP.

var joursSemaine = ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'];

brancherFenetres();
brancherOnglets();
brancherSuppressions();

// Boutons "Modifier le budget", "Ajouter une dépense" : ouvrent une fenêtre.
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
    var fenetres = document.querySelectorAll('dialog');
    for (i = 0; i < fenetres.length; i++) {
        fenetres[i].onclick = function (evenement) {
            if (evenement.target == this) {
                this.close();
            }
        };
    }
}

// Mois : toutes les barres. Semaine : seulement les 7 jours de la semaine.
function brancherOnglets() {
    var onglets = document.querySelectorAll('[data-vue]');
    var i;
    for (i = 0; i < onglets.length; i++) {
        onglets[i].onclick = function () {
            var j;
            for (j = 0; j < onglets.length; j++) {
                onglets[j].classList.remove('on');
            }
            this.classList.add('on');
            afficherVue(this.getAttribute('data-vue'));
        };
    }
}

function afficherVue(vue) {
    var semaine = (vue == 'semaine');
    document.getElementById('graphe').classList.toggle('semaine', semaine);
    document.getElementById('axe').classList.toggle('semaine', semaine);

    var barres = document.querySelectorAll('#graphe [data-semaine]');
    var etiquettes = document.querySelectorAll('#axe [data-semaine]');
    var i;
    for (i = 0; i < barres.length; i++) {
        var dedans = barres[i].getAttribute('data-semaine') == '1';
        barres[i].style.display = (semaine && !dedans) ? 'none' : '';
    }
    for (i = 0; i < etiquettes.length; i++) {
        var etiquette = etiquettes[i];
        if (etiquette.getAttribute('data-texte') === null) {
            etiquette.setAttribute('data-texte', etiquette.textContent.trim());
        }
        var dansSemaine = etiquette.getAttribute('data-semaine') == '1';
        etiquette.style.display = (semaine && !dansSemaine) ? 'none' : '';
        if (semaine && dansSemaine) {
            // En vue semaine, chaque jour a son étiquette : "Lun. 23".
            var barre = barres[i];
            if (barre.classList.contains('auj')) {
                etiquette.textContent = "Aujourd'hui";
            } else {
                etiquette.textContent = nomDuJour(barre) + ' ' + etiquette.getAttribute('data-jour');
            }
            etiquette.style.justifyContent = 'center';
        } else {
            etiquette.textContent = etiquette.getAttribute('data-texte');
            etiquette.style.justifyContent = '';
        }
    }
}

// Le titre de la barre commence par le jour, ex : "23 nov. : 8,90 €".
function nomDuJour(barre) {
    var mois = document.getElementById('graphe').getAttribute('data-mois');
    var date = new Date(mois + '-' + ('0' + barre.getAttribute('data-jour')).slice(-2) + 'T12:00:00');
    return joursSemaine[date.getDay()];
}

// Demande confirmation avant de supprimer une dépense.
function brancherSuppressions() {
    var formulaires = document.querySelectorAll('form[data-confirmer]');
    var i;
    for (i = 0; i < formulaires.length; i++) {
        formulaires[i].onsubmit = function () {
            return confirm(this.getAttribute('data-confirmer'));
        };
    }
}
