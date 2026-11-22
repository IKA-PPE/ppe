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
        $req = $this->bdd->prepare("SELECT id_utilisateur AS id, nom, prenom, email, mdp, role FROM utilisateur WHERE email = :email");
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
        $req = $this->bdd->prepare("SELECT u.id_utilisateur AS id, u.nom, u.prenom, u.email, e.nom AS etablissement, u.budget_mensuel, u.role
                                    FROM utilisateur u
                                    LEFT JOIN etablissement e ON e.id_etablissement = u.id_etablissement
                                    WHERE u.id_utilisateur = ?");
        $req->execute([$id]);
        return $req->fetch() ?: null;
    }

    public function update()
    {
    }

    public function delete()
    {
    }
}
