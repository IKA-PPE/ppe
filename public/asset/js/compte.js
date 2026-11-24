// Page Paramètres du compte : fenêtre de confirmation de suppression.

var boutons = document.querySelectorAll('[data-ouvrir]');
var i;
for (i = 0; i < boutons.length; i++) {
    boutons[i].onclick = function () {
        var fenetre = document.getElementById(this.getAttribute('data-ouvrir'));
        fenetre.showModal();
        fenetre.querySelector('input[type=password]').focus();
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

// Photo de profil : envoi dès qu'une image est choisie (vérification rapide avant).
var champPhoto = document.getElementById('champ-photo');
if (champPhoto) {
    champPhoto.onchange = function () {
        var fichier = this.files[0];
        var info = document.getElementById('info-photo');
        if (!fichier) {
            return;
        }
        if (fichier.size > 5 * 1024 * 1024) {
            info.textContent = 'Image trop lourde (5 Mo maximum).';
            info.style.color = '#ff453a';
            this.value = '';
            return;
        }
        info.textContent = 'Envoi en cours…';
        info.style.color = '';
        document.querySelector('.cpt-photo').classList.add('envoi');
        document.getElementById('form-photo').submit();
    };
}
