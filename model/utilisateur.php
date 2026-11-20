<?php

class utilisateur
{
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    // CREATE : ajoute un étudiant. Le mot de passe est haché avant d'être enregistré.
    public function ajouterUtilisateur($nom, $prenom, $email, $mdp, $etablissement)
    {
        $mdpHache = password_hash($mdp, PASSWORD_DEFAULT);

        $req = $this->bdd->prepare("INSERT INTO utilisateur (nom, prenom, email, mdp, etablissement) VALUES (:nom, :prenom, :email, :mdp, :etablissement)");
        $req->bindParam(":nom", $nom);
        $req->bindParam(":prenom", $prenom);
        $req->bindParam(":email", $email);
        $req->bindParam(":mdp", $mdpHache);
        $req->bindParam(":etablissement", $etablissement);
        $req->execute();

        return $this->bdd->lastInsertId();
    }

    // READ : vérifie l'e-mail et le mot de passe. Renvoie l'utilisateur ou false.
    public function login($email, $mdp)
    {
        $req = $this->bdd->prepare("SELECT * FROM utilisateur WHERE email = :email");
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
        $req = $this->bdd->prepare("SELECT id, nom, prenom, email, etablissement, budget_mensuel, role FROM utilisateur WHERE id = ?");
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
