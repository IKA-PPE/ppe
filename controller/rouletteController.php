<?php
class rouletteController
{
    private $roulette;
    private $explorer;

    // Crée les deux modèles utilisés par la page (Roulette et Explorer).
    public function __construct($bdd)
    {
        $this->roulette = new RouletteModel($bdd);
        $this->explorer = new ExplorerModel($bdd);
    }

    // Bouton × : retire de la sélection le resto dont l'id est envoyé par le formulaire.
    public function retirer()
    {
        $id = trim($_POST['id'] ?? '');
        $_SESSION['roulette'] = $this->roulette->retirer($_SESSION['roulette'], $id);
    }

    // Bouton "Tout retirer" : vide complètement la sélection en session.
    public function vider()
    {
        $_SESSION['roulette'] = array();
    }

    // Prépare tout ce que la vue doit afficher : les parts de la roue,
    // le nombre de restos sélectionnés et un éventuel message.
    // $mode vaut 'selection' (ma sélection) ou 'aleatoire' (tirage auto).
    public function afficher($mode)
    {
        $idUtilisateur = $_SESSION['user_id'];
        $campus = $this->explorer->campusDe($idUtilisateur);

        if (!$campus) {
            return array(
                'roue' => array(),
                'nbSelection' => 0,
                'message' => "On ne trouve pas l'adresse de ton établissement.",
            );
        }

        $tous = $this->restosAutour($campus);
        $selection = $this->roulette->selection($tous, $_SESSION['roulette']);

        $message = '';
        if ($mode == 'aleatoire') {
            $regimes = $this->roulette->regimesDe($idUtilisateur);
            $budgetJour = $this->roulette->budgetParJour($idUtilisateur);
            $restos = $this->roulette->aleatoire($tous, $regimes, $budgetJour);
            if (count($restos) == 0) {
                $message = 'Aucun resto ne correspond à ton régime, ton budget et ta distance.';
            }
        } else {
            $restos = $selection;
            if (count($restos) == 0) {
                $message = 'Ta roue est vide. Ajoute des restos depuis Explorer.';
            }
        }

        return array(
            'roue' => $this->roulette->pourLaRoue($restos),
            'nbSelection' => count($selection),
            'message' => $message,
        );
    }

    // Restos autour du campus : on réutilise ceux déjà chargés par la page Explorer.
    private function restosAutour($campus)
    {
        $cle = 'pres_' . $campus['id'];

        if (empty($_SESSION[$cle]) || !is_array($_SESSION[$cle])) {
            $charges = $this->explorer->autour($campus['lat'], $campus['lng']);
            if (count($charges) > 10) {
                $_SESSION[$cle] = $charges;
            }
        } else {
            $charges = $_SESSION[$cle];
        }

        $tous = array();
        foreach ($charges as $resto) {
            $tous[] = $this->explorer->infos($resto);
        }
        return $tous;
    }
}
