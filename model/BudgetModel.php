<?php
class BudgetModel
{
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    // Total dépensé et nombre de repas sur un mois.
    public function resume($idUtilisateur, $annee, $mois)
    {
        $sql = "SELECT COALESCE(SUM(montant), 0) AS total, COUNT(*) AS repas FROM depense
                WHERE id_utilisateur = :id AND YEAR(date_depense) = :annee AND MONTH(date_depense) = :mois";
        $req = $this->bdd->prepare($sql);
        $req->bindParam(':id', $idUtilisateur);
        $req->bindParam(':annee', $annee);
        $req->bindParam(':mois', $mois);
        $req->execute();
        $ligne = $req->fetch();
        return array('total' => (float) $ligne['total'], 'repas' => (int) $ligne['repas']);
    }

    // Montant dépensé chaque jour du mois : tableau jour => montant.
    public function parJour($idUtilisateur, $annee, $mois)
    {
        $sql = "SELECT DAY(date_depense) AS jour, SUM(montant) AS total FROM depense
                WHERE id_utilisateur = :id AND YEAR(date_depense) = :annee AND MONTH(date_depense) = :mois
                GROUP BY DAY(date_depense)";
        $req = $this->bdd->prepare($sql);
        $req->bindParam(':id', $idUtilisateur);
        $req->bindParam(':annee', $annee);
        $req->bindParam(':mois', $mois);
        $req->execute();
        $jours = array();
        foreach ($req->fetchAll() as $ligne) {
            $jours[(int) $ligne['jour']] = (float) $ligne['total'];
        }
        return $jours;
    }

    // Répartition du mois par type de resto, du plus cher au moins cher.
    public function parType($idUtilisateur, $annee, $mois)
    {
        $sql = "SELECT t.libelle_type AS type, SUM(d.montant) AS total FROM depense d
                JOIN restaurant r ON r.id_restaurant = d.id_restaurant
                JOIN type_cuisine t ON t.id_type = r.id_type
                WHERE d.id_utilisateur = :id AND YEAR(d.date_depense) = :annee AND MONTH(d.date_depense) = :mois
                GROUP BY t.libelle_type ORDER BY total DESC";
        $req = $this->bdd->prepare($sql);
        $req->bindParam(':id', $idUtilisateur);
        $req->bindParam(':annee', $annee);
        $req->bindParam(':mois', $mois);
        $req->execute();
        return $req->fetchAll();
    }

    // Tous les repas du mois. Le groupe est retrouvé si un vote de groupe
    // a choisi ce resto le même jour.
    public function historique($idUtilisateur, $annee, $mois)
    {
        $sql = "SELECT d.id_depense AS id, d.montant, d.date_depense AS date, r.nom_restaurant AS nom, t.libelle_type AS type,
                       (SELECT g.nom_groupe FROM session_vote s
                        JOIN groupe g ON g.id_groupe = s.id_groupe
                        JOIN appartenir a ON a.id_groupe = g.id_groupe AND a.id_utilisateur = d.id_utilisateur
                        WHERE s.id_restaurant = d.id_restaurant AND DATE(s.date_sortie) = DATE(d.date_depense)
                        LIMIT 1) AS groupe
                FROM depense d
                JOIN restaurant r ON r.id_restaurant = d.id_restaurant
                JOIN type_cuisine t ON t.id_type = r.id_type
                WHERE d.id_utilisateur = :id AND YEAR(d.date_depense) = :annee AND MONTH(d.date_depense) = :mois
                ORDER BY d.date_depense DESC";
        $req = $this->bdd->prepare($sql);
        $req->bindParam(':id', $idUtilisateur);
        $req->bindParam(':annee', $annee);
        $req->bindParam(':mois', $mois);
        $req->execute();
        return $req->fetchAll();
    }

    // Supprime une dépense, seulement si elle appartient à l'utilisateur.
    public function supprimer($idUtilisateur, $idDepense)
    {
        $req = $this->bdd->prepare("DELETE FROM depense WHERE id_depense = :depense AND id_utilisateur = :id");
        $req->bindParam(':depense', $idDepense);
        $req->bindParam(':id', $idUtilisateur);
        $req->execute();
        return $req->rowCount() > 0;
    }
}
