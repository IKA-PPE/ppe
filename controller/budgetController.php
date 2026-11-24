<?php
include_once(__DIR__ . '/../model/BudgetModel.php');
include_once(__DIR__ . '/../model/AccueilModel.php');
include_once(__DIR__ . '/../model/ExplorerModel.php');
include_once(__DIR__ . '/../model/utilisateur.php');
include(__DIR__ . '/../private/bdd.php');
date_default_timezone_set('Europe/Paris');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Formulaires : action = budget, depense ou supprimer.
if (isset($_POST['action'])) {

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php?page=connexion');
        exit;
    }

    $idUtilisateur = (int) $_SESSION['user_id'];
    $accueil = new AccueilModel($bdd);
    $budgetModel = new BudgetModel($bdd);

    // On revient sur le mois qui était affiché.
    $retour = '../index.php?page=budget';
    if (isset($_POST['mois']) && preg_match('/^\d{4}-\d{2}$/', $_POST['mois'])) {
        $retour = $retour . '&mois=' . $_POST['mois'];
    }

    if ($_POST['action'] == 'budget') {
        $montant = montantBudget('montant');
        if ($montant <= 0 || $montant > 99999) {
            flashBudget('erreur', 'Entre un budget valide (par exemple 180).', $retour);
        }
        $accueil->definirBudget($idUtilisateur, $montant);
        flashBudget('succes', 'Budget mensuel enregistré : ' . number_format($montant, 2, ',', ' ') . ' €.', $retour);
    }

    if ($_POST['action'] == 'depense') {
        $montant = montantBudget('montant');
        if ($montant <= 0 || $montant > 9999) {
            flashBudget('erreur', 'Entre un montant valide.', $retour);
        }

        // Date du repas : pas dans le futur, pas plus d'un an en arrière.
        $date = null;
        if (isset($_POST['date']) && $_POST['date'] != '') {
            $lue = DateTime::createFromFormat('Y-m-d\TH:i', $_POST['date']);
            if ($lue == false || $lue->getTimestamp() > time() + 300 || $lue->getTimestamp() < strtotime('-1 year')) {
                flashBudget('erreur', 'La date du repas n\'est pas valide.', $retour);
            }
            $date = $lue->format('Y-m-d H:i:s');
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
            flashBudget('erreur', 'Entre le nom du resto (100 caractères max).', $retour);
        }

        $accueil->ajouterDepense($idUtilisateur, $choisi['id'], $montant, $date);

        // On affiche le mois de la dépense ajoutée.
        $retour = '../index.php?page=budget';
        if ($date != null) {
            $retour = $retour . '&mois=' . substr($date, 0, 7);
        }
        flashBudget('succes', 'Dépense de ' . number_format($montant, 2, ',', ' ') . ' € ajoutée chez ' . $choisi['nom'] . '.', $retour);
    }

    if ($_POST['action'] == 'supprimer') {
        $idDepense = 0;
        if (isset($_POST['depense'])) {
            $idDepense = (int) $_POST['depense'];
        }
        if ($budgetModel->supprimer($idUtilisateur, $idDepense)) {
            flashBudget('succes', 'Dépense supprimée.', $retour);
        }
        flashBudget('erreur', 'Cette dépense n\'existe plus.', $retour);
    }

    header('Location: ' . $retour);
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
$idUtilisateur = (int) $utilisateur['id'];

$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// Mois affiché : ?mois=2026-11, jamais après le mois en cours.
$moisCourant = date('Y-m');
$moisAffiche = $moisCourant;
if (isset($_GET['mois']) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $_GET['mois']) && $_GET['mois'] <= $moisCourant) {
    $moisAffiche = $_GET['mois'];
}
$annee = (int) substr($moisAffiche, 0, 4);
$mois = (int) substr($moisAffiche, 5, 2);
$estCourant = ($moisAffiche == $moisCourant);
$nbJours = (int) date('t', mktime(0, 0, 0, $mois, 1, $annee));

$nomsMois = array('', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
$courtsMois = array('', 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.');
$titreMois = ucfirst($nomsMois[$mois]) . ' ' . $annee;
$lienAvant = 'index.php?page=budget&mois=' . date('Y-m', mktime(0, 0, 0, $mois - 1, 1, $annee));
$lienApres = null;
if ($estCourant == false) {
    $lienApres = 'index.php?page=budget&mois=' . date('Y-m', mktime(0, 0, 0, $mois + 1, 1, $annee));
}

// Indicateurs du mois
$budgetModel = new BudgetModel($bdd);
$budget = (float) $utilisateur['budget_mensuel'];
$resume = $budgetModel->resume($idUtilisateur, $annee, $mois);
$depense = $resume['total'];
$restant = $budget - $depense;
$pourcent = 0;
if ($budget > 0) {
    $pourcent = min(100, round(100 * $depense / $budget));
}
$moyenne = 0;
if ($resume['repas'] > 0) {
    $moyenne = $depense / $resume['repas'];
}

// Comparaison avec le mois d'avant
$moisAvant = (int) date('n', mktime(0, 0, 0, $mois - 1, 1, $annee));
$anneeAvant = (int) date('Y', mktime(0, 0, 0, $mois - 1, 1, $annee));
$resumeAvant = $budgetModel->resume($idUtilisateur, $anneeAvant, $moisAvant);
$evolution = null;
if ($resumeAvant['repas'] > 0 && $resume['repas'] > 0) {
    $moyenneAvant = $resumeAvant['total'] / $resumeAvant['repas'];
    $evolution = round(100 * ($moyenne - $moyenneAvant) / $moyenneAvant);
}
$nomMoisAvant = $nomsMois[$moisAvant];

// Dépenses par jour : une barre par jour du mois.
$parJour = $budgetModel->parJour($idUtilisateur, $annee, $mois);
$plusHaut = 1;
foreach ($parJour as $montant) {
    if ($montant > $plusHaut) {
        $plusHaut = $montant;
    }
}
$aujourdhui = (int) date('j');
// Semaine : la semaine en cours, ou les 7 derniers jours d'un mois passé.
$debutSemaine = $nbJours - 6;
if ($estCourant) {
    $debutSemaine = $aujourdhui - ((int) date('N') - 1);
}
$barres = array();
for ($jour = 1; $jour <= $nbJours; $jour++) {
    $montant = 0;
    if (isset($parJour[$jour])) {
        $montant = $parJour[$jour];
    }
    $barres[] = array(
        'jour' => $jour,
        'montant' => $montant,
        'hauteur' => round(100 * $montant / $plusHaut),
        'futur' => ($estCourant && $jour > $aujourdhui),
        'aujourdhui' => ($estCourant && $jour == $aujourdhui),
        'semaine' => ($jour >= $debutSemaine && $jour < $debutSemaine + 7),
    );
}
$finMois = $nbJours . ' ' . $courtsMois[$mois];
$debutMois = '1 ' . $courtsMois[$mois];

// Répartition par type de resto
$couleursTypes = array('#ff6b3d', '#bf5af2', '#5ac8fa', '#32d74b', '#ffd60a', '#ff375f', '#64d2ff', '#ac8e68');
$types = array();
foreach ($budgetModel->parType($idUtilisateur, $annee, $mois) as $i => $ligne) {
    $part = 0;
    if ($depense > 0) {
        $part = round(100 * $ligne['total'] / $depense);
    }
    $types[] = array(
        'type' => $ligne['type'],
        'total' => (float) $ligne['total'],
        'part' => $part,
        'couleur' => $couleursTypes[$i % count($couleursTypes)],
    );
}

// Historique des repas
$joursCourts = array('Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.');
$couleurs = array('#ff6b3d', '#e11d48', '#e7c4a0', '#f97316', '#fb7185');
$lignes = array();
foreach ($budgetModel->historique($idUtilisateur, $annee, $mois) as $repas) {
    $moment = strtotime($repas['date']);
    $quand = $joursCourts[(int) date('w', $moment)] . ' ' . date('j', $moment);
    if (date('Y-m-d', $moment) == date('Y-m-d')) {
        $quand = "Aujourd'hui";
    } elseif (date('Y-m-d', $moment) == date('Y-m-d', strtotime('-1 day'))) {
        $quand = 'Hier';
    }
    $fond = $couleurs[strlen($repas['nom']) % 5];
    $encre = '#fff';
    if ($fond == '#e7c4a0') {
        $encre = '#3f2a1d';
    }
    $lignes[] = array(
        'id' => (int) $repas['id'],
        'quand' => $quand . ' · ' . date('H:i', $moment),
        'nom' => $repas['nom'],
        'type' => $repas['type'],
        'groupe' => $repas['groupe'],
        'montant' => (float) $repas['montant'],
        'sigle' => mb_strtoupper(mb_substr(trim($repas['nom']), 0, 3)),
        'fond' => $fond,
        'encre' => $encre,
    );
}

// Restos proposés dans "Ajouter une dépense" (même liste que la page Explorer)
$explorer = new ExplorerModel($bdd);
$campus = $explorer->campusDe($idUtilisateur);
$choixDepense = array();
if ($campus) {
    $cle = 'pres_' . $campus['id'];
    if (empty($_SESSION[$cle]) || !is_array($_SESSION[$cle])) {
        $charges = $explorer->autour($campus['lat'], $campus['lng']);
        if (count($charges) > 10) {
            $_SESSION[$cle] = $charges;
        }
    }
    if (isset($_SESSION[$cle]) && is_array($_SESSION[$cle])) {
        $choix = $explorer->filtrer($_SESSION[$cle], '', array(), array());
        $choixDepense = array_slice($choix['lieux'], 0, 40);
    }
}
$maintenant = date('Y-m-d\TH:i');
$accueilModel = new AccueilModel($bdd);
$typesResto = $accueilModel->listeTypes();

function montantBudget($champ)
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

function flashBudget($type, $message, $retour)
{
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
    header('Location: ' . $retour);
    exit;
}

// 180 -> "180", 7.5 -> "7,50"
function euros($valeur)
{
    if (round($valeur, 2) == round($valeur)) {
        return number_format($valeur, 0, ',', ' ');
    }
    return number_format($valeur, 2, ',', ' ');
}

if (!function_exists('texte')) {
    function texte($valeur)
    {
        return htmlspecialchars((string) $valeur);
    }
}
