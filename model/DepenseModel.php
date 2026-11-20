<?php

class DepenseModel
{
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    // Total dépensé par l'utilisateur pendant le mois en cours
    public function totalDuMois($idUtilisateur)
    {
        $req = $this->bdd->prepare("SELECT COALESCE(SUM(montant), 0) FROM depense
                                    WHERE id_utilisateur = :id
                                    AND YEAR(date_depense) = YEAR(CURDATE()) AND MONTH(date_depense) = MONTH(CURDATE())");
        $req->execute(['id' => $idUtilisateur]);
        return (float) $req->fetchColumn();
    }

    // Dernières dépenses de l'utilisateur
    public function dernieres($idUtilisateur, $limite = 3)
    {
        $req = $this->bdd->prepare("SELECT * FROM depense WHERE id_utilisateur = :id ORDER BY date_depense DESC LIMIT " . (int) $limite);
        $req->execute(['id' => $idUtilisateur]);
        return $req->fetchAll();
    }
}
