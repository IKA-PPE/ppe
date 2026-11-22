<?php

require_once __DIR__ . '/../model/ExplorerModel.php';


class explorerController
{
    private $restaurants;

    public function __construct($bdd)
    {
        $this->restaurants = new ExplorerModel($bdd);
    }

    
    public function afficher()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=connexion');
            exit;
        }
        if (!isset($_SESSION['roulette']) || !is_array($_SESSION['roulette'])) {
            $_SESSION['roulette'] = [];
        }

        [$q, $f, $noms] = $this->demande($_GET);
        $campus = $this->restaurants->campusDe($_SESSION['user_id']);
        $message = '';

        if (!$campus) {
            $liste = [];
            $lieux = [];
            $roulette = [];
            $nb = 0;
            $mot = $q;
            $suite = false;
            $message = "On ne trouve pas l'adresse de ton établissement. Vérifie le nom saisi à l'inscription.";
            $liens = [];
            foreach ($noms as $k => $lab) {
                $liens[$k] = $this->lien($q, $f, $k);
            }
            $lien = $this->lien($q, $f);
            include __DIR__ . '/../view/explorer/index.php';
            return;
        }

        $cle = 'pres_' . $campus['id'];
        if (empty($_SESSION[$cle]) || !is_array($_SESSION[$cle])) {
            $charges = $this->restaurants->autour($campus['lat'], $campus['lng']);
            if (count($charges) > 10) {
                $_SESSION[$cle] = $charges;
            }
        }
        if (empty($_SESSION[$cle])) {
            $_SESSION[$cle] = [];
        }

        $filtre = $q !== '';
        foreach ($f as $on) {
            if ($on) {
                $filtre = true;
            }
        }

        $restos = [];
        foreach ($_SESSION[$cle] as $r) {
            if ($r['min'] > 15) {
                continue;
            }
            $r = $this->restaurants->infos($r);
            if (!$this->restaurants->passe($r, $f) || !$this->restaurants->correspond($r, $q)) {
                continue;
            }
            $r['dans'] = in_array($r['id'], $_SESSION['roulette'], true);
            $restos[] = $r;
        }

        $liste = array_slice($restos, 0, $filtre ? 40 : 20);
        $roulette = [];
        foreach ($_SESSION[$cle] as $r) {
            if (in_array($r['id'], $_SESSION['roulette'], true)) {
                $roulette[] = $this->restaurants->infos($r);
            }
        }

        // Quelques restos espacés pour la carte, pour ne pas empiler toutes les pastilles.
        $choix = $liste;
        foreach ($restos as $r) {
            $loin = true;
            foreach ($choix as $d) {
                if (hypot(($r['lat'] - $d['lat']) * 111320, ($r['lng'] - $d['lng']) * 73000) < 220) {
                    $loin = false;
                }
            }
            if ($loin) {
                $choix[] = $r;
            }
            if (count($choix) >= 36) {
                break;
            }
        }

        $lieux = [];
        foreach ($choix as $r) {
            $lieux[] = [
                'id' => $r['id'], 'nom' => $r['nom'], 'rue' => $r['rue'], 'min' => (int) $r['min'],
                'lat' => (float) $r['lat'], 'lng' => (float) $r['lng'], 'sigle' => $r['sigle'],
                'fond' => $r['fond'], 'encre' => $r['encre'], 'euro' => $r['euro'],
                'connu' => $r['connu'] && $r['ouvert'],
                'badges' => trim(($r['halal'] ? 'Halal · ' : '') . ($r['petit'] ? '≤ 10 € · ' : '') . ($r['vege'] ? 'Végé' : ''), ' ·'),
            ];
        }

        $liens = [];
        foreach ($noms as $k => $lab) {
            $liens[$k] = $this->lien($q, $f, $k);
        }
        $lien = $this->lien($q, $f);
        $nb = count($liste);
        $mot = $q;
        $suite = $f['pres'] || $f['burger'] || $f['poulet'] || $f['pizza'] || $f['sushi'] || $f['tacos'];

        include __DIR__ . '/../view/explorer/index.php';
    }

    // Le bouton + poste ici, comme le login poste vers utilisateurController.
    public function roulette()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../index.php?page=connexion');
            exit;
        }
        if (!isset($_SESSION['roulette']) || !is_array($_SESSION['roulette'])) {
            $_SESSION['roulette'] = [];
        }

        [$q, $f] = $this->demande($_POST);
        $id = (string) ($_POST['id'] ?? '');
        $garde = [];
        $dedans = false;
        foreach ($_SESSION['roulette'] as $deja) {
            if ($deja === $id) {
                $dedans = true;
            } else {
                $garde[] = $deja;
            }
        }
        if (!$dedans && $id !== '') {
            $garde[] = $id;
        }
        $_SESSION['roulette'] = $garde;

        header('Location: ' . $this->lien($q, $f, '', true));
        exit;
    }

    private function demande($source)
    {
        $noms = [
            'halal' => 'Halal', 'prix' => '≤ 10 €', 'ouvert' => 'Ouvert', 'vege' => 'Végé',
            'pres' => '≤ 5 min', 'burger' => 'Burger', 'poulet' => 'Poulet', 'pizza' => 'Pizza',
            'sushi' => 'Sushi', 'tacos' => 'Tacos',
        ];
        $q = trim($source['q'] ?? '');
        $f = [];
        foreach ($noms as $k => $lab) {
            $f[$k] = isset($source[$k]);
        }
        return [$q, $f, $noms];
    }

    // Reconstruit l'URL de la page. $changer inverse un filtre, comme un lien On/Off.
    private function lien($q, $f, $changer = '', $retour = false)
    {
        $args = ['page' => 'explorer'];
        if ($q !== '') {
            $args['q'] = $q;
        }
        foreach ($f as $nom => $actif) {
            if ($changer === $nom) {
                $actif = !$actif;
            }
            if ($actif) {
                $args[$nom] = '1';
            }
        }
        return ($retour ? '../index.php?' : 'index.php?') . http_build_query($args);
    }
}

// Point d'entrée du POST, copié sur le switch de utilisateurController.php.
if (isset($_POST['action']) && realpath((string) $_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    session_start();
    require_once __DIR__ . '/../private/bdd.php';

    $explorerController = new explorerController($bdd);
    switch ($_POST['action']) {
        case 'roulette':
            $explorerController->roulette();
            break;
    }

    header('Location: ../index.php?page=explorer');
    exit;
}
