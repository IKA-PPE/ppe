<?php
    include("../model/utilisateur.php");
    include("../private/bdd.php");
    session_start();

    if (isset($_POST["action"])) {

        $utilisateurController = new utilisateurController($bdd);
        switch($_POST["action"]) {
            case "inscrire":
                $utilisateurController->create();
            break;
            case "login":
                $utilisateurController->login();
            break;
            case "modifier":
            break;
        }
    }

    header('Location: ../index.php?page=index');
    exit();

    class utilisateurController {
        private $utilisateur;

        public function __construct($bdd) {
            $this->utilisateur = new utilisateur($bdd);
        }

        public function create()
        {
            $nom           = trim($_POST['nom'] ?? '');
            $prenom        = trim($_POST['prenom'] ?? '');
            $email         = trim($_POST['email'] ?? '');
            $etablissement = trim($_POST['etablissement'] ?? '');
            $mdp           = $_POST['mdp'] ?? '';
            $mdpConfirm    = $_POST['mdp_confirm'] ?? '';

            // On garde les champs saisis pour les réafficher en cas d'erreur (sauf les mots de passe)
            $_SESSION['ancien'] = ['nom' => $nom, 'prenom' => $prenom, 'email' => $email, 'etablissement' => $etablissement];

            if ($nom === '' || $prenom === '' || $email === '' || $etablissement === '' || $mdp === '') {
                $this->erreur('inscription', 'Tous les champs sont obligatoires.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->erreur('inscription', "L'adresse e-mail n'est pas valide.");
            }
            if (strlen($mdp) < 8) {
                $this->erreur('inscription', 'Le mot de passe doit contenir au moins 8 caractères.');
            }
            if ($mdp !== $mdpConfirm) {
                $this->erreur('inscription', 'Les deux mots de passe ne sont pas identiques.');
            }
            if ($this->utilisateur->emailExiste($email)) {
                $this->erreur('inscription', 'Un compte existe déjà avec cette adresse e-mail.');
            }

            $id = $this->utilisateur->ajouterUtilisateur($nom, $prenom, $email, $mdp, $etablissement);
            unset($_SESSION['ancien']);

            // Connexion automatique après l'inscription
            $this->ouvrirSession($this->utilisateur->trouverParId($id));
            $_SESSION['flash'] = ['type' => 'succes', 'message' => 'Bienvenue sur Graille, ' . $prenom . ' !'];
            header('Location: ../index.php?page=dashboard');
            exit();
        }

        public function login()
        {
            $email = trim($_POST['email'] ?? '');
            $mdp   = $_POST['mdp'] ?? '';

            $_SESSION['ancien'] = ['email' => $email];

            $user = $this->utilisateur->login($email, $mdp);

            if ($user) {
                unset($_SESSION['ancien']);
                session_regenerate_id(true);
                $this->ouvrirSession($user);
                header('Location: ../index.php?page=dashboard');
                exit();
            }

            $this->erreur('connexion', 'E-mail ou mot de passe incorrect.');
        }

        private function ouvrirSession($user)
        {
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['user_email']  = $user['email'];
            $_SESSION['user_role']   = $user['role'];
            $_SESSION['user_prenom'] = $user['prenom'];
        }

        // Enregistre le message d'erreur et renvoie vers la page du formulaire
        private function erreur($page, $message)
        {
            $_SESSION['flash'] = ['type' => 'erreur', 'message' => $message];
            header('Location: ../index.php?page=' . $page);
            exit();
        }
    }
