<?php

class RouletteModel
{
    private $bdd;

    // Garde la connexion à la base de données pour les requêtes du modèle.
    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    // Couleurs des parts de la roue (même ordre que la maquette).
    public function couleurs()
    {
        return array('#ff6b3d', '#e8a23a', '#22a06b', '#e5484d', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6');
    }

    // READ : régimes suivis par l'utilisateur (ex : Halal, Végétarien).
    public function regimesDe($idUtilisateur)
    {
        $req = $this->bdd->prepare("SELECT r.libelle_regime FROM suivre s JOIN regime r ON r.id_regime = s.id_regime WHERE s.id_utilisateur = :id");
        $req->bindParam(":id", $idUtilisateur);
        $req->execute();

        $regimes = array();
        foreach ($req->fetchAll() as $ligne) {
            $regimes[] = mb_strtolower($ligne['libelle_regime']);
        }
        return $regimes;
    }

    // READ : ce qu'il reste à dépenser par jour ce mois-ci (null si pas de budget).
    public function budgetParJour($idUtilisateur)
    {
        $req = $this->bdd->prepare("SELECT budget_mensuel FROM utilisateur WHERE id_utilisateur = :id");
        $req->bindParam(":id", $idUtilisateur);
        $req->execute();
        $budget = $req->fetchColumn();

        if ($budget === false || $budget === null) {
            return null;
        }

        $req = $this->bdd->prepare("SELECT SUM(montant) FROM depense WHERE id_utilisateur = :id AND MONTH(date_depense) = MONTH(NOW()) AND YEAR(date_depense) = YEAR(NOW())");
        $req->bindParam(":id", $idUtilisateur);
        $req->execute();
        $depense = (float) $req->fetchColumn();

        $joursRestants = (int) date('t') - (int) date('j') + 1;
        $restant = (float) $budget - $depense;
        if ($restant < 0) {
            $restant = 0;
        }
        return $restant / $joursRestants;
    }

    // "Ma sélection" : les restos ajoutés depuis Explorer (leurs id sont en session).
    public function selection($tous, $ids)
    {
        $liste = array();
        foreach ($tous as $resto) {
            if (in_array($resto['id'], $ids)) {
                $liste[] = $resto;
            }
        }
        return $liste;
    }

    // "Aléatoire" : 6 restos au hasard, filtrés par régime, budget et distance.
    public function aleatoire($tous, $regimes, $budgetJour)
    {
        $halal = false;
        $vege = false;
        foreach ($regimes as $regime) {
            if (str_contains($regime, 'halal')) {
                $halal = true;
            }
            if (str_contains($regime, 'veg')) {
                $vege = true;
            }
        }

        $ok = array();
        foreach ($tous as $resto) {
            if ($resto['min'] > 10) {
                continue;
            }
            if ($halal && $resto['halal'] == false) {
                continue;
            }
            if ($vege && $resto['vege'] == false) {
                continue;
            }
            // Moins de 14 € par jour : on ne garde que les petits prix.
            if ($budgetJour !== null && $budgetJour < 14 && $resto['petit'] == false) {
                continue;
            }
            $ok[] = $resto;
        }

        shuffle($ok);// Mélange aléatoire les element d'un tableau
        return array_slice($ok, 0, 6);
    }

    // DELETE : enlève un resto de la sélection.
    public function retirer($ids, $id)
    {
        $garde = array();
        foreach ($ids as $idDeja) {
            if ($idDeja != $id) {
                $garde[] = $idDeja;
            }
        }
        return $garde;
    }

    // Prépare les infos utiles à la roue (couleur, sigle, prix…).
    public function pourLaRoue($restos)
    {
        $couleurs = $this->couleurs();
        $roue = array();
        $i = 0;
        foreach ($restos as $resto) {
            $roue[] = array(
                'id' => $resto['id'],
                'nom' => $resto['nom'],
                'prix' => $resto['euro'],
                'sigle' => $resto['sigle'],
                'min' => $resto['min'],
                'lat' => $resto['lat'],
                'lng' => $resto['lng'],
                'couleur' => $couleurs[$i % count($couleurs)],
            );
            $i++;
        }
        return $roue;
    }
}
