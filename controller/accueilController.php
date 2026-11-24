<?php
include_once(__DIR__ . '/../model/AccueilModel.php');
include_once(__DIR__ . '/../model/ExplorerModel.php');
include_once(__DIR__ . '/../model/DepenseModel.php');
include_once(__DIR__ . '/../model/utilisateur.php');
include(__DIR__ . '/../private/bdd.php');
date_default_timezone_set('Europe/Paris');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Formulaires de la page : action = budget, depense, voter ou departager.
if (isset($_POST['action'])) {

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php?page=connexion');
        exit;
    }

    $accueil = new AccueilModel($bdd);
    $idUtilisateur = (int) $_SESSION['user_id'];

    if ($_POST['action'] == 'budget') {
        $montant = montantSaisi('montant');
        if ($montant <= 0 || $montant > 99999) {
            flashAccueil('erreur', 'Entre un budget valide (par exemple 150).');
        }
        $accueil->definirBudget($idUtilisateur, $montant);
        flashAccueil('succes', 'Budget du mois enregistré : ' . number_format($montant, 2, ',', ' ') . ' €.');
    }

    if ($_POST['action'] == 'depense') {
        $montant = montantSaisi('montant');
        if ($montant <= 0 || $montant > 9999) {
            flashAccueil('erreur', 'Entre un montant valide.');
        }

        // Nom libre : un resto proposé autour du campus ou un resto perso.
        $explorer = new ExplorerModel($bdd);
        $campus = $explorer->campusDe($idUtilisateur);
        $proches = array();
        $ville = 'France';
        if ($campus) {
            $ville = $campus['ville'];
            if (isset($_SESSION['pres_' . $campus['id']])) {
                $proches = $_SESSION['pres_' . $campus['id']];
            }
        }
        $nomResto = '';
        if (isset($_POST['resto'])) {
            $nomResto = $_POST['resto'];
        }
        $typeResto = '';
        if (isset($_POST['type'])) {
            $typeResto = $_POST['type'];
        }
        $choisi = $accueil->restoPourDepense($nomResto, $proches, $ville, $idUtilisateur, $typeResto);
        if ($choisi == null) {
            flashAccueil('erreur', 'Entre le nom du resto (100 caractères max).');
        }

        $accueil->ajouterDepense($idUtilisateur, $choisi['id'], $montant);
        flashAccueil('succes', 'Dépense de ' . number_format($montant, 2, ',', ' ') . ' € ajoutée chez ' . $choisi['nom'] . '.');
    }

    if ($_POST['action'] == 'voter') {
        $idProposition = 0;
        if (isset($_POST['proposition'])) {
            $idProposition = (int) $_POST['proposition'];
        }
        if ($accueil->voter($idUtilisateur, $idProposition)) {
            flashAccueil('succes', 'Ton vote est enregistré.');
        }
        flashAccueil('erreur', "Ce vote n'est plus disponible.");
    }

    if ($_POST['action'] == 'departager') {
        $nom = $accueil->departager($idUtilisateur);
        if ($nom) {
            flashAccueil('succes', 'La roulette a choisi : ' . $nom . ' !');
        }
        flashAccueil('erreur', 'Aucun vote à départager.');
    }

    header('Location: ../index.php?page=dashboard');
    exit;
}

// 2. Page : on prépare les données, puis la vue les affiche.
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=connexion');
    exit;
}

$utilisateurModel = new utilisateur($bdd);
$utilisateur = $utilisateurModel->trouverParId((int) $_SESSION['user_id']);
if (!$utilisateur) {
    session_destroy();
    header('Location: index.php?page=connexion');
    exit;
}

$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// Budget du mois
$budget = (float) $utilisateur['budget_mensuel'];
$depenseModel = new DepenseModel($bdd);
$depense = $depenseModel->totalDuMois((int) $utilisateur['id']);
$restant = max(0, $budget - $depense);
$joursRestants = (int) date('t') - (int) date('j') + 1;
$parJour = $restant / $joursRestants;
// Anneau : part du budget déjà dépensée (périmètre du cercle = 276.5)
$anneau = 276.5;
if ($budget > 0) {
    $anneau = 276.5 * ($restant / $budget);
}
$anneau = 276.5 - $anneau;

// Date du jour en français
$jours = array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi');
$mois = array('', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
$aujourdhui = $jours[(int) date('w')] . ' ' . date('j') . ' ' . $mois[(int) date('n')];

// Restos autour du campus (même liste et même cache que la page Explorer)
$explorer = new ExplorerModel($bdd);
$campus = $explorer->campusDe((int) $utilisateur['id']);
$proches = array();
$lieux = array();
$choixDepense = array();
$message = '';

if ($campus) {
    $cle = 'pres_' . $campus['id'];
    if (empty($_SESSION[$cle]) || !is_array($_SESSION[$cle])) {
        $charges = $explorer->autour($campus['lat'], $campus['lng']);
        if (count($charges) > 10) {
            $_SESSION[$cle] = $charges;
        }
    }
    $tous = array();
    if (isset($_SESSION[$cle]) && is_array($_SESSION[$cle])) {
        $tous = $_SESSION[$cle];
    }
    $idsRoulette = array();
    if (isset($_SESSION['roulette']) && is_array($_SESSION['roulette'])) {
        $idsRoulette = $_SESSION['roulette'];
    }
    $choix = $explorer->filtrer($tous, '', array(), $idsRoulette);

    $proches = array_slice($choix['lieux'], 0, 12);
    $choixDepense = array_slice($choix['lieux'], 0, 30);

    // Sur la carte, on espace les prix pour qu'ils ne se touchent pas.
    foreach ($choix['lieux'] as $resto) {
        $loin = true;
        foreach ($lieux as $deja) {
            $ecart = abs($resto['lat'] - $deja['lat']) + abs($resto['lng'] - $deja['lng']);
            if ($ecart < 0.004) {
                $loin = false;
            }
        }
        if ($loin) {
            $lieux[] = $resto;
        }
        if (count($lieux) >= 30) {
            break;
        }
    }
    if (count($tous) == 0) {
        $message = 'Impossible de charger les restos pour le moment. Réessaie dans un instant.';
    }
} else {
    $message = "On ne trouve pas l'adresse de ton établissement.";
}

// Vote de groupe en cours
$accueil = new AccueilModel($bdd);
$vote = $accueil->voteEnCours((int) $utilisateur['id']);

// Types proposés dans la fenêtre "Ajouter une dépense"
$typesResto = $accueil->listeTypes();

function montantSaisi($champ)
{
    if (!isset($_POST[$champ])) {
        return 0;
    }
    $texte = str_replace(array(',', ' ', '€'), array('.', '', ''), trim($_POST[$champ]));
    if (!is_numeric($texte)) {
        return 0;
    }
    return round((float) $texte, 2);
}

function flashAccueil($type, $message)
{
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
    header('Location: ../index.php?page=dashboard');
    exit;
}

function prix($valeur)
{
    return number_format($valeur, 2, ',', ' ');
}

if (!function_exists('texte')) {
    function texte($valeur)
    {
        return htmlspecialchars((string) $valeur);
    }
}
