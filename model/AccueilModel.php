<?php
class AccueilModel
{
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    // Enregistre le budget du mois de l'utilisateur.
    public function definirBudget($idUtilisateur, $montant)
    {
        $req = $this->bdd->prepare("UPDATE utilisateur SET budget_mensuel = :montant WHERE id_utilisateur = :id");
        $req->bindParam(':montant', $montant);
        $req->bindParam(':id', $idUtilisateur);
        $req->execute();
    }

    // Ajoute une dépense dans un resto. Sans date, c'est maintenant.
    public function ajouterDepense($idUtilisateur, $idRestaurant, $montant, $date = null)
    {
        if ($date == null) {
            $date = date('Y-m-d H:i:s');
        }
        $req = $this->bdd->prepare("INSERT INTO depense (montant, date_depense, id_utilisateur, id_restaurant) VALUES (:montant, :date, :id, :resto)");
        $req->bindParam(':montant', $montant);
        $req->bindParam(':date', $date);
        $req->bindParam(':id', $idUtilisateur);
        $req->bindParam(':resto', $idRestaurant);
        $req->execute();
    }

    // Familles de cuisine proposées dans le champ "Type" et mots qui les reconnaissent.
    private $familles = array(
        'Resto U' => array('crous', 'resto u', 'restaurant universitaire', 'cafet'),
        'Asiatique' => array('asian', 'chinese', 'japanese', 'sushi', 'ramen', 'thai', 'vietnamese', 'korean', 'noodle', 'dumpling', 'bao', 'poke'),
        'Libanais' => array('lebanese', 'liban', 'middle_eastern', 'falafel', 'pita'),
        'Kebab' => array('kebab', 'turkish', 'greek'),
        'Indien' => array('indian', 'pakistani', 'curry'),
        'Italien' => array('pizza', 'italian', 'pasta'),
        'Fast-food' => array('burger', 'chicken', 'sandwich', 'tacos', 'taco', 'friture', 'fish_and_chips', 'hot_dog'),
        'Africain' => array('african', 'senegal', 'ethiopian', 'moroccan', 'couscous'),
        'Café' => array('coffee', 'cafe', 'bakery', 'crepe', 'brunch', 'breakfast'),
        'Français' => array('french', 'regional', 'bistro', 'brasserie'),
    );

    // Liste des types pour le champ "Type" de la fenêtre dépense.
    public function listeTypes()
    {
        $types = array_keys($this->familles);
        $types[] = 'Autre';
        return $types;
    }

    // Les restos viennent d'OpenStreetMap : on les range dans la table restaurant
    // la première fois qu'on en a besoin (dépense, vote...).
    // $typeChoisi : type choisi à la main ('' = deviné à partir du nom).
    public function enregistrerRestaurant($resto, $ville, $typeChoisi = '')
    {
        $code = 'osm:' . $resto['id'];
        if (isset($resto['code'])) {
            $code = $resto['code'];
        }
        $libelle = $typeChoisi;
        if (in_array($libelle, $this->listeTypes()) == false) {
            $libelle = '';
        }

        $req = $this->bdd->prepare("SELECT id_restaurant FROM restaurant WHERE google_place_id = :code");
        $req->bindParam(':code', $code);
        $req->execute();
        $id = $req->fetchColumn();
        if ($id) {
            // Un resto perso appartient à l'utilisateur : il peut changer son type.
            // Un resto OpenStreetMap est partagé : on garde son type.
            if ($libelle != '' && str_starts_with($code, 'perso:')) {
                $type = $this->idType($libelle);
                $req = $this->bdd->prepare("UPDATE restaurant SET id_type = :type WHERE id_restaurant = :id");
                $req->bindParam(':type', $type);
                $req->bindParam(':id', $id);
                $req->execute();
            }
            return (int) $id;
        }

        if ($libelle == '') {
            $libelle = $this->famille($resto);
        }
        $type = $this->idType($libelle);
        $req = $this->bdd->prepare("INSERT INTO restaurant (google_place_id, nom_restaurant, adresse_restaurant, ville_restaurant, id_type) VALUES (:code, :nom, :adresse, :ville, :type)");
        $req->bindParam(':code', $code);
        $req->bindParam(':nom', $resto['nom']);
        $req->bindParam(':adresse', $resto['rue']);
        $req->bindParam(':ville', $ville);
        $req->bindParam(':type', $type);
        $req->execute();
        return (int) $this->bdd->lastInsertId();
    }

    // Resto d'une dépense à partir du nom tapé.
    // Si le nom est celui d'un resto autour du campus, on prend ce resto.
    // Sinon c'est un resto perso de l'utilisateur (un resto plus loin, un food-truck...).
    // Renvoie array('id' => id_restaurant, 'nom' => nom) ou null si le nom est vide.
    public function restoPourDepense($nom, $restosProches, $ville, $idUtilisateur, $typeChoisi = '')
    {
        $nom = trim(preg_replace('/\s+/', ' ', (string) $nom));
        if ($nom == '' || mb_strlen($nom) > 100) {
            return null;
        }

        foreach ($restosProches as $resto) {
            if (mb_strtolower($resto['nom']) == mb_strtolower($nom)) {
                return array('id' => $this->enregistrerRestaurant($resto, $ville, $typeChoisi), 'nom' => $resto['nom']);
            }
        }

        $perso = array(
            'code' => 'perso:' . (int) $idUtilisateur . ':' . md5(mb_strtolower($nom)),
            'nom' => $nom,
            'rue' => '',
            'cuisine' => '',
            'amenity' => '',
        );
        $id = $this->enregistrerRestaurant($perso, $ville, $typeChoisi);

        // On garde le nom tel qu'il a été écrit la première fois.
        $req = $this->bdd->prepare("SELECT nom_restaurant FROM restaurant WHERE id_restaurant = :id");
        $req->bindParam(':id', $id);
        $req->execute();
        return array('id' => $id, 'nom' => $req->fetchColumn());
    }

    // id de la ligne type_cuisine, créée si besoin.
    private function idType($libelle)
    {
        $req = $this->bdd->prepare("SELECT id_type FROM type_cuisine WHERE libelle_type = :libelle");
        $req->bindParam(':libelle', $libelle);
        $req->execute();
        $id = $req->fetchColumn();
        if ($id) {
            return (int) $id;
        }
        $req = $this->bdd->prepare("INSERT INTO type_cuisine (libelle_type) VALUES (:libelle)");
        $req->bindParam(':libelle', $libelle);
        $req->execute();
        return (int) $this->bdd->lastInsertId();
    }

    // Famille de cuisine à partir du nom et du tag "cuisine" d'OpenStreetMap :
    // "pizza;italian" devient "Italien".
    private function famille($resto)
    {
        $nom = mb_strtolower($resto['nom']);
        $cuisine = mb_strtolower((string) $resto['cuisine']);
        foreach ($this->familles as $libelle => $mots) {
            foreach ($mots as $mot) {
                if (str_contains($cuisine, $mot) || str_contains($nom, $mot)) {
                    return $libelle;
                }
            }
        }
        if (isset($resto['amenity']) && $resto['amenity'] == 'fast_food') {
            return 'Fast-food';
        }
        return 'Autre';
    }

    // Vote en cours dans un des groupes de l'utilisateur (le plus proche de la fin).
    public function voteEnCours($idUtilisateur)
    {
        $sql = "SELECT s.id_session AS id, TIMESTAMPDIFF(SECOND, NOW(), s.date_sortie) AS reste, s.id_restaurant AS gagnant, g.nom_groupe AS groupe
                FROM session_vote s
                JOIN groupe g ON g.id_groupe = s.id_groupe
                JOIN appartenir a ON a.id_groupe = g.id_groupe
                WHERE a.id_utilisateur = :id AND s.date_sortie >= NOW()
                ORDER BY s.date_sortie ASC LIMIT 1";
        $req = $this->bdd->prepare($sql);
        $req->bindParam(':id', $idUtilisateur);
        $req->execute();
        $vote = $req->fetch();
        if ($vote == false) {
            return null;
        }

        $sql = "SELECT p.id_proposition AS id, r.id_restaurant AS id_resto, r.nom_restaurant AS nom,
                       COUNT(v.id_utilisateur) AS voix,
                       SUM(v.id_utilisateur = :id) AS moi
                FROM proposition p
                JOIN restaurant r ON r.id_restaurant = p.id_restaurant
                LEFT JOIN voter v ON v.id_proposition = p.id_proposition
                WHERE p.id_session = :session
                GROUP BY p.id_proposition, r.id_restaurant, r.nom_restaurant
                ORDER BY voix DESC, p.id_proposition ASC";
        $req = $this->bdd->prepare($sql);
        $req->bindParam(':id', $idUtilisateur);
        $req->bindParam(':session', $vote['id']);
        $req->execute();
        $vote['choix'] = $req->fetchAll();

        $total = 0;
        $max = 0;
        $vote['nomGagnant'] = '';
        foreach ($vote['choix'] as $choix) {
            $total = $total + $choix['voix'];
            if ($choix['voix'] > $max) {
                $max = $choix['voix'];
            }
            if ($vote['gagnant'] != null && $choix['id_resto'] == $vote['gagnant']) {
                $vote['nomGagnant'] = $choix['nom'];
            }
        }
        $vote['total'] = $total;
        $vote['max'] = $max;

        // Égalité : au moins deux restos en tête avec le même nombre de voix.
        $enTete = 0;
        foreach ($vote['choix'] as $choix) {
            if ($max > 0 && $choix['voix'] == $max) {
                $enTete = $enTete + 1;
            }
        }
        $vote['egalite'] = ($enTete > 1);
        return $vote;
    }

    // Vérifie que la proposition est bien dans un groupe de l'utilisateur.
    private function propositionAutorisee($idUtilisateur, $idProposition)
    {
        $sql = "SELECT p.id_session FROM proposition p
                JOIN session_vote s ON s.id_session = p.id_session
                JOIN appartenir a ON a.id_groupe = s.id_groupe
                WHERE p.id_proposition = :prop AND a.id_utilisateur = :id AND s.date_sortie >= NOW()";
        $req = $this->bdd->prepare($sql);
        $req->bindParam(':prop', $idProposition);
        $req->bindParam(':id', $idUtilisateur);
        $req->execute();
        return $req->fetchColumn();
    }

    // Un seul vote par personne et par session : on enlève l'ancien avant.
    public function voter($idUtilisateur, $idProposition)
    {
        $session = $this->propositionAutorisee($idUtilisateur, $idProposition);
        if ($session == false) {
            return false;
        }
        $req = $this->bdd->prepare("DELETE v FROM voter v JOIN proposition p ON p.id_proposition = v.id_proposition
                                    WHERE v.id_utilisateur = :id AND p.id_session = :session");
        $req->bindParam(':id', $idUtilisateur);
        $req->bindParam(':session', $session);
        $req->execute();

        $req = $this->bdd->prepare("INSERT INTO voter (id_utilisateur, id_proposition) VALUES (:id, :prop)");
        $req->bindParam(':id', $idUtilisateur);
        $req->bindParam(':prop', $idProposition);
        $req->execute();
        return true;
    }

    // Tire au sort un resto parmi ceux qui sont à égalité, et l'enregistre comme gagnant.
    public function departager($idUtilisateur)
    {
        $vote = $this->voteEnCours($idUtilisateur);
        if ($vote == null || count($vote['choix']) == 0) {
            return null;
        }
        $enTete = array();
        foreach ($vote['choix'] as $choix) {
            if ($choix['voix'] == $vote['max']) {
                $enTete[] = $choix;
            }
        }
        $elu = $enTete[random_int(0, count($enTete) - 1)];

        $req = $this->bdd->prepare("UPDATE session_vote SET id_restaurant = :resto WHERE id_session = :session");
        $req->bindParam(':resto', $elu['id_resto']);
        $req->bindParam(':session', $vote['id']);
        $req->execute();
        return $elu['nom'];
    }
}
