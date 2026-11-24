<?php
// Données de la barre latérale (incluse par view/layout/header.php sur chaque page) :
// l'encart "Restant ce mois" et le bloc utilisateur en bas du menu.
include_once(__DIR__ . '/../model/utilisateur.php');
include_once(__DIR__ . '/../model/DepenseModel.php');
include(__DIR__ . '/../private/bdd.php');

$menuUtilisateur = null;
$menuBudget = 0;
$menuDepense = 0;
$menuRestant = 0;
$menuPourcent = 0;
$menuNom = '';
$menuInitiales = '?';
$menuPhoto = null;
$menuEtablissement = '';

if (isset($_SESSION['user_id'])) {
    $menuModel = new utilisateur($bdd);
    $menuUtilisateur = $menuModel->trouverParId((int) $_SESSION['user_id']);
}

if ($menuUtilisateur) {
    // Budget du mois en cours
    $menuBudget = (float) $menuUtilisateur['budget_mensuel'];
    $menuDepenses = new DepenseModel($bdd);
    $menuDepense = $menuDepenses->totalDuMois((int) $menuUtilisateur['id']);
    $menuRestant = $menuBudget - $menuDepense;
    if ($menuBudget > 0) {
        $menuPourcent = min(100, round(100 * $menuDepense / $menuBudget));
    }

    // "Amina K." et "AK"
    $menuNom = $menuUtilisateur['prenom'];
    $menuInitiales = mb_strtoupper(mb_substr($menuUtilisateur['prenom'], 0, 1));
    if (trim($menuUtilisateur['nom']) != '') {
        $menuNom = $menuNom . ' ' . mb_strtoupper(mb_substr($menuUtilisateur['nom'], 0, 1)) . '.';
        $menuInitiales = $menuInitiales . mb_strtoupper(mb_substr($menuUtilisateur['nom'], 0, 1));
    }
    $menuPhoto = $menuUtilisateur['photo'];
    $menuEtablissement = (string) $menuUtilisateur['etablissement'];
}

// 180 -> "180", 67.9 -> "67,90"
function menuEuros($valeur)
{
    if (round($valeur, 2) == round($valeur)) {
        return number_format($valeur, 0, ',', ' ');
    }
    return number_format($valeur, 2, ',', ' ');
}
