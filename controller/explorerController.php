<?php
include(__DIR__ . '/../model/ExplorerModel.php');
include(__DIR__ . '/../private/bdd.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['roulette']) || !is_array($_SESSION['roulette'])) {
    $_SESSION['roulette'] = array();
}

// 1. Bouton + : le formulaire envoie action = ajouter.
if (isset($_POST['action']) && $_POST['action'] == 'ajouter') {

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php?page=connexion');
        exit;
    }

    $id = '';
    if (isset($_POST['id'])) {
        $id = (string) $_POST['id'];
    }

    $dejaDedans = false;
    $garde = array();
    foreach ($_SESSION['roulette'] as $idDeja) {
        if ($idDeja == $id) {
            $dejaDedans = true;
        } else {
            $garde[] = $idDeja;
        }
    }
    if ($dejaDedans == false && $id != '') {
        $garde[] = $id;
    }
    $_SESSION['roulette'] = $garde;

    $adresse = '../index.php?page=explorer';
    if (isset($_POST['q']) && trim($_POST['q']) != '') {
        $adresse = $adresse . '&q=' . urlencode(trim($_POST['q']));
    }
    $nomsPost = array('halal', 'prix', 'ouvert', 'vege', 'pres', 'burger', 'poulet', 'pizza', 'sushi', 'tacos');
    foreach ($nomsPost as $nom) {
        if (isset($_POST[$nom])) {
            $adresse = $adresse . '&' . $nom . '=1';
        }
    }

    header('Location: ' . $adresse);
    exit;
}

// 2. Page : on prépare la liste, puis la vue l'affiche.
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=connexion');
    exit;
}

$restaurants = new ExplorerModel($bdd);

$q = '';
if (isset($_GET['q'])) {
    $q = trim($_GET['q']);
}

$noms = array(
    'halal' => 'Halal',
    'prix' => '≤ 10 €',
    'ouvert' => 'Ouvert',
    'vege' => 'Végé',
    'pres' => '≤ 5 min',
    'burger' => 'Burger',
    'poulet' => 'Poulet',
    'pizza' => 'Pizza',
    'sushi' => 'Sushi',
    'tacos' => 'Tacos',
);

$f = array();
foreach ($noms as $cle => $label) {
    if (isset($_GET[$cle])) {
        $f[$cle] = true;
    } else {
        $f[$cle] = false;
    }
}

$campus = $restaurants->campusDe($_SESSION['user_id']);
$liste = array();
$lieux = array();
$roulette = array();
$message = '';

if ($campus) {
    $cle = 'pres_' . $campus['id'];
    if (empty($_SESSION[$cle]) || !is_array($_SESSION[$cle])) {
        $charges = $restaurants->autour($campus['lat'], $campus['lng']);
        if (count($charges) > 10) {
            $_SESSION[$cle] = $charges;
        }
    }

    $tous = array();
    if (isset($_SESSION[$cle]) && is_array($_SESSION[$cle])) {
        $tous = $_SESSION[$cle];
    }

    $choix = $restaurants->filtrer($tous, $q, $f, $_SESSION['roulette']);
    $liste = $choix['liste'];
    $roulette = $choix['roulette'];

    // Sur la carte, on espace les prix pour qu'ils ne se touchent pas.
    $lieux = array();
    foreach ($choix['lieux'] as $resto) {
        $loin = true;
        foreach ($lieux as $deja) {
            $ecart = abs($resto['lat'] - $deja['lat']) + abs($resto['lng'] - $deja['lng']);
            if ($ecart < 0.006) {
                $loin = false;
            }
        }
        if ($loin) {
            $lieux[] = $resto;
        }
        if (count($lieux) >= 36) {
            break;
        }
    }
} else {
    $message = "On ne trouve pas l'adresse de ton établissement. Vérifie le nom saisi à l'inscription.";
}

$liens = array();
foreach ($noms as $cle => $label) {
    $liens[$cle] = lienExplorer($q, $f, $cle);
}
$lien = lienExplorer($q, $f, '');
$nb = count($liste);

$pastilles = array();
foreach ($roulette as $resto) {
    if (count($pastilles) < 3) {
        $pastilles[] = $resto;
    }
}

$suite = false;
if ($f['pres'] || $f['burger'] || $f['poulet'] || $f['pizza'] || $f['sushi'] || $f['tacos']) {
    $suite = true;
}

// Construit le lien d'un filtre : garde la recherche et les filtres actifs,
// et allume ou éteint le filtre $changer.
function lienExplorer($q, $f, $changer)
{
    $adresse = 'index.php?page=explorer';
    if ($q != '') {
        $adresse = $adresse . '&q=' . urlencode($q);
    }
    foreach ($f as $nom => $actif) {
        if ($nom == $changer) {
            if ($actif) {
                $actif = false;
            } else {
                $actif = true;
            }
        }
        if ($actif) {
            $adresse = $adresse . '&' . $nom . '=1';
        }
    }
    return $adresse;
}

// Protège un texte avant de l'afficher dans le HTML (évite les failles XSS).
function texte($valeur)
{
    return htmlspecialchars((string) $valeur);
}
