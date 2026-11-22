<?php
session_start();

ob_start();
$page = isset($_GET['page']) ? $_GET['page'] : 'index';

if ($page === 'deconnexion') {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php?page=index');
    exit;
}

include __DIR__ . '/view/layout/header.php';

    switch ($page) {
        case 'index':
            include __DIR__ . '/view/accueil/index.php';
            break;
        case 'inscription':
            include __DIR__ . '/view/login/inscription.php';
            break;
        case 'connexion':
            include __DIR__ . '/view/login/connexion.php';
            break;
        case 'dashboard':
            include __DIR__ . '/view/dashboard/index.php';
            break;
        case 'explorer':
            require_once __DIR__ . '/private/bdd.php';
            require_once __DIR__ . '/controller/explorerController.php';
            $explorerController = new explorerController($bdd);
            $explorerController->afficher();
            break;
        case 'roulette':
            include __DIR__ . '/view/roulette/index.php';
            break;
        case 'groupes':
            include __DIR__ . '/view/groupes/index.php';
            break;
        case 'budget':
            include __DIR__ . '/view/budget/index.php';
            break;

        default:
            include __DIR__ . '/view/accueil/index.php';
            break;
    }
include __DIR__ . '/view/layout/footer.php';
