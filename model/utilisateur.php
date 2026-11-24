<?php

class utilisateur
{
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    // CREATE : ajoute un étudiant. Le mot de passe est haché avant d'être enregistré.
    // $etablissement est le nom tapé à l'inscription (CFA, IUT, lycée…).
    public function ajouterUtilisateur($nom, $prenom, $email, $mdp, $etablissement)
    {
        $mdpHache = password_hash($mdp, PASSWORD_DEFAULT);
        $idEtablissement = $this->enregistrerEtablissement($etablissement);

        $req = $this->bdd->prepare("INSERT INTO utilisateur (nom, prenom, email, mdp, id_etablissement) VALUES (:nom, :prenom, :email, :mdp, :etablissement)");
        $req->bindParam(":nom", $nom);
        $req->bindParam(":prenom", $prenom);
        $req->bindParam(":email", $email);
        $req->bindParam(":mdp", $mdpHache);
        $req->bindParam(":etablissement", $idEtablissement);
        $req->execute();

        return $this->bdd->lastInsertId();
    }

    // Réutilise l'établissement s'il existe déjà, sinon on cherche son adresse.
    public function enregistrerEtablissement($nom)
    {
        $req = $this->bdd->prepare("SELECT id_etablissement FROM etablissement WHERE LOWER(nom) = LOWER(?)");
        $req->execute([$nom]);
        $id = $req->fetchColumn();
        if ($id) {
            return $id;
        }

        $lieu = $this->chercherLieu($nom);
        if (!$lieu) {
            $lieu = $this->chercherLieu($nom . ', France');
        }
        if (!$lieu) {
            $lieu = ['adresse' => $nom, 'ville' => 'France', 'lat' => null, 'lng' => null];
        }

        $req = $this->bdd->prepare("INSERT INTO etablissement (nom, adresse, ville, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
        $req->execute([$nom, $lieu['adresse'], $lieu['ville'], $lieu['lat'], $lieu['lng']]);
        return $this->bdd->lastInsertId();
    }

    // OpenStreetMap donne la latitude et la longitude à partir du nom de l'école.
    private function chercherLieu($nom)
    {
        $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=1&q=' . urlencode($nom);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'GraillePPE/1.0');
        $liste = json_decode((string) curl_exec($ch), true);
        curl_close($ch);

        if (empty($liste[0]['lat'])) {
            return null;
        }

        $adresse = $liste[0]['address'] ?? [];
        $rue = $adresse['road'] ?? $nom;
        if (!empty($adresse['house_number'])) {
            $rue = $adresse['house_number'] . ' ' . $rue;
        }
        $ville = $adresse['city'] ?? $adresse['town'] ?? $adresse['village'] ?? 'France';

        return [
            'adresse' => mb_substr($rue, 0, 250),
            'ville' => mb_substr($ville, 0, 100),
            'lat' => $liste[0]['lat'],
            'lng' => $liste[0]['lon'],
        ];
    }

    // READ : vérifie l'e-mail et le mot de passe. Renvoie l'utilisateur ou false.
    public function login($email, $mdp)
    {
        $req = $this->bdd->prepare("SELECT id_utilisateur AS id, nom, prenom, email, mdp, role, photo FROM utilisateur WHERE email = :email");
        $req->execute(['email' => $email]);
        $user = $req->fetch();

        if ($user && password_verify($mdp, $user['mdp'])) {
            return $user;
        }
        return false;
    }

    public function emailExiste($email)
    {
        $req = $this->bdd->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email");
        $req->execute(['email' => $email]);
        return $req->fetchColumn() > 0;
    }

    public function trouverParId($id)
    {
        $req = $this->bdd->prepare("SELECT u.id_utilisateur AS id, u.nom, u.prenom, u.email, e.nom AS etablissement, u.budget_mensuel, u.role, u.date_inscription, u.photo
                                    FROM utilisateur u
                                    LEFT JOIN etablissement e ON e.id_etablissement = u.id_etablissement
                                    WHERE u.id_utilisateur = ?");
        $req->execute([$id]);
        return $req->fetch() ?: null;
    }

    // UPDATE : nom, prénom et e-mail.
    public function update($id, $nom, $prenom, $email)
    {
        $req = $this->bdd->prepare("UPDATE utilisateur SET nom = :nom, prenom = :prenom, email = :email WHERE id_utilisateur = :id");
        $req->bindParam(':nom', $nom);
        $req->bindParam(':prenom', $prenom);
        $req->bindParam(':email', $email);
        $req->bindParam(':id', $id);
        $req->execute();
    }

    // true si l'e-mail est déjà pris par un autre compte.
    public function emailPrisParAutre($email, $id)
    {
        $req = $this->bdd->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email AND id_utilisateur <> :id");
        $req->bindParam(':email', $email);
        $req->bindParam(':id', $id);
        $req->execute();
        return $req->fetchColumn() > 0;
    }

    // Change l'établissement (on cherche son adresse s'il est nouveau).
    public function changerEtablissement($id, $nomEtablissement)
    {
        $idEtablissement = $this->enregistrerEtablissement($nomEtablissement);
        $req = $this->bdd->prepare("UPDATE utilisateur SET id_etablissement = :etablissement WHERE id_utilisateur = :id");
        $req->bindParam(':etablissement', $idEtablissement);
        $req->bindParam(':id', $id);
        $req->execute();
    }

    // Budget mensuel ; null = pas de budget.
    public function changerBudget($id, $montant)
    {
        $req = $this->bdd->prepare("UPDATE utilisateur SET budget_mensuel = :montant WHERE id_utilisateur = :id");
        $req->bindValue(':montant', $montant, $montant === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $req->bindParam(':id', $id);
        $req->execute();
    }

    public function verifierMotDePasse($id, $mdp)
    {
        $req = $this->bdd->prepare("SELECT mdp FROM utilisateur WHERE id_utilisateur = :id");
        $req->bindParam(':id', $id);
        $req->execute();
        $hache = $req->fetchColumn();
        return $hache && password_verify($mdp, $hache);
    }

    public function changerMotDePasse($id, $mdp)
    {
        $mdpHache = password_hash($mdp, PASSWORD_DEFAULT);
        $req = $this->bdd->prepare("UPDATE utilisateur SET mdp = :mdp WHERE id_utilisateur = :id");
        $req->bindParam(':mdp', $mdpHache);
        $req->bindParam(':id', $id);
        $req->execute();
    }

    // Régimes proposés. La table est remplie la première fois.
    public function listeRegimes()
    {
        $nb = (int) $this->bdd->query("SELECT COUNT(*) FROM regime")->fetchColumn();
        if ($nb == 0) {
            $req = $this->bdd->prepare("INSERT INTO regime (libelle_regime) VALUES (:libelle)");
            foreach (array('Halal', 'Végétarien', 'Végan', 'Sans porc', 'Sans gluten', 'Casher') as $libelle) {
                $req->bindValue(':libelle', $libelle);
                $req->execute();
            }
        }
        return $this->bdd->query("SELECT id_regime AS id, libelle_regime AS libelle FROM regime ORDER BY id_regime")->fetchAll();
    }

    // id des régimes suivis par l'utilisateur.
    public function regimesDe($id)
    {
        $req = $this->bdd->prepare("SELECT id_regime FROM suivre WHERE id_utilisateur = :id");
        $req->bindParam(':id', $id);
        $req->execute();
        return array_map('intval', $req->fetchAll(PDO::FETCH_COLUMN));
    }

    // Remplace les régimes suivis par ceux cochés.
    public function definirRegimes($id, $idsRegimes)
    {
        $req = $this->bdd->prepare("DELETE FROM suivre WHERE id_utilisateur = :id");
        $req->bindParam(':id', $id);
        $req->execute();

        $req = $this->bdd->prepare("INSERT INTO suivre (id_utilisateur, id_regime)
                                    SELECT :id, id_regime FROM regime WHERE id_regime = :regime");
        foreach ($idsRegimes as $idRegime) {
            $req->bindValue(':id', $id);
            $req->bindValue(':regime', (int) $idRegime);
            $req->execute();
        }
    }

    // Chemin de la photo de profil ; null = pas de photo.
    public function changerPhoto($id, $chemin)
    {
        $req = $this->bdd->prepare("UPDATE utilisateur SET photo = :photo WHERE id_utilisateur = :id");
        $req->bindValue(':photo', $chemin, $chemin === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $req->bindParam(':id', $id);
        $req->execute();
    }

    // DELETE : supprime le compte. Dépenses, votes, groupes suivis... partent avec (ON DELETE CASCADE).
    public function delete($id)
    {
        $req = $this->bdd->prepare("DELETE FROM utilisateur WHERE id_utilisateur = :id");
        $req->bindParam(':id', $id);
        $req->execute();
    }
}
