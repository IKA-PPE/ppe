<?php
include_once(__DIR__ . '/../model/utilisateur.php');
include_once(__DIR__ . '/../model/ExplorerModel.php');
include(__DIR__ . '/../private/bdd.php');
date_default_timezone_set('Europe/Paris');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Formulaires : action = profil, etablissement, budget, regimes, mdp ou supprimer.
if (isset($_POST['action'])) {

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php?page=connexion');
        exit;
    }

    $utilisateur = new utilisateur($bdd);
    $id = (int) $_SESSION['user_id'];

    if ($_POST['action'] == 'profil') {
        $nom = trim(champCompte('nom'));
        $prenom = trim(champCompte('prenom'));
        $email = trim(champCompte('email'));

        if ($nom == '' || $prenom == '' || $email == '') {
            flashCompte('erreur', 'Le prénom, le nom et l\'e-mail sont obligatoires.');
        }
        if (mb_strlen($nom) > 255 || mb_strlen($prenom) > 255) {
            flashCompte('erreur', 'Le prénom ou le nom est trop long.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flashCompte('erreur', 'L\'adresse e-mail n\'est pas valide.');
        }
        if ($utilisateur->emailPrisParAutre($email, $id)) {
            flashCompte('erreur', 'Un autre compte utilise déjà cette adresse e-mail.');
        }

        $utilisateur->update($id, $nom, $prenom, $email);
        $_SESSION['user_email'] = $email;
        $_SESSION['user_prenom'] = $prenom;
        flashCompte('succes', 'Ton profil est à jour.');
    }

    if ($_POST['action'] == 'photo') {
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] == UPLOAD_ERR_NO_FILE) {
            flashCompte('erreur', 'Choisis une image.');
        }
        $erreur = '';
        $chemin = enregistrerPhoto($_FILES['photo'], $id, $erreur);
        if ($chemin == null) {
            flashCompte('erreur', $erreur);
        }
        $ancienne = $utilisateur->trouverParId($id)['photo'];
        $utilisateur->changerPhoto($id, $chemin);
        supprimerFichierPhoto($ancienne);
        $_SESSION['user_photo'] = $chemin;
        flashCompte('succes', 'Photo de profil mise à jour.');
    }

    if ($_POST['action'] == 'retirer_photo') {
        $ancienne = $utilisateur->trouverParId($id)['photo'];
        $utilisateur->changerPhoto($id, null);
        supprimerFichierPhoto($ancienne);
        $_SESSION['user_photo'] = null;
        flashCompte('succes', 'Photo de profil retirée.');
    }

    if ($_POST['action'] == 'etablissement') {
        $nomEtablissement = trim(champCompte('etablissement'));
        if ($nomEtablissement == '' || mb_strlen($nomEtablissement) > 255) {
            flashCompte('erreur', 'Entre le nom de ton établissement.');
        }
        $utilisateur->changerEtablissement($id, $nomEtablissement);

        $explorer = new ExplorerModel($bdd);
        if ($explorer->campusDe($id) == null) {
            flashCompte('erreur', 'Établissement enregistré, mais son adresse est introuvable : essaie un nom plus précis (ex : « IUT Paris Rives de Seine »).');
        }
        flashCompte('succes', 'Établissement mis à jour : la carte et les restos proches suivent ton nouveau campus.');
    }

    if ($_POST['action'] == 'budget') {
        $texte = str_replace(array(',', ' ', '€'), array('.', '', ''), trim(champCompte('montant')));
        if ($texte == '') {
            $utilisateur->changerBudget($id, null);
            flashCompte('succes', 'Budget mensuel retiré.');
        }
        if (!is_numeric($texte) || (float) $texte <= 0 || (float) $texte > 99999) {
            flashCompte('erreur', 'Entre un budget valide (par exemple 180), ou laisse vide pour le retirer.');
        }
        $montant = round((float) $texte, 2);
        $utilisateur->changerBudget($id, $montant);
        flashCompte('succes', 'Budget mensuel enregistré : ' . number_format($montant, 2, ',', ' ') . ' €.');
    }

    if ($_POST['action'] == 'regimes') {
        $ids = array();
        if (isset($_POST['regimes']) && is_array($_POST['regimes'])) {
            $ids = array_unique(array_map('intval', $_POST['regimes']));
        }
        $utilisateur->definirRegimes($id, $ids);
        flashCompte('succes', 'Préférences alimentaires enregistrées.');
    }

    if ($_POST['action'] == 'mdp') {
        $actuel = champCompte('mdp_actuel');
        $nouveau = champCompte('mdp');
        $confirmation = champCompte('mdp_confirm');

        if (!$utilisateur->verifierMotDePasse($id, $actuel)) {
            flashCompte('erreur', 'Le mot de passe actuel est incorrect.');
        }
        if (strlen($nouveau) < 8) {
            flashCompte('erreur', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
        }
        if ($nouveau !== $confirmation) {
            flashCompte('erreur', 'Les deux nouveaux mots de passe ne sont pas identiques.');
        }
        $utilisateur->changerMotDePasse($id, $nouveau);
        session_regenerate_id(true);
        flashCompte('succes', 'Mot de passe modifié.');
    }

    if ($_POST['action'] == 'supprimer') {
        if (!$utilisateur->verifierMotDePasse($id, champCompte('mdp_actuel'))) {
            flashCompte('erreur', 'Mot de passe incorrect : le compte n\'a pas été supprimé.');
        }
        if (champCompte('confirmation') !== 'SUPPRIMER') {
            flashCompte('erreur', 'Tape SUPPRIMER en majuscules pour confirmer.');
        }
        $photo = $utilisateur->trouverParId($id)['photo'];
        $utilisateur->delete($id);
        supprimerFichierPhoto($photo);
        $_SESSION = array();
        session_destroy();
        header('Location: ../index.php?page=index');
        exit;
    }

    header('Location: ../index.php?page=compte');
    exit;
}

// 2. Page : on prépare les données, puis la vue les affiche.
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=connexion');
    exit;
}

$utilisateurModel = new utilisateur($bdd);
$moi = $utilisateurModel->trouverParId((int) $_SESSION['user_id']);
if (!$moi) {
    session_destroy();
    header('Location: index.php?page=connexion');
    exit;
}

// La photo du menu suit toujours la base (utile pour une session ouverte avant l'ajout des photos).
$_SESSION['user_photo'] = $moi['photo'];

$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$explorer = new ExplorerModel($bdd);
$campus = $explorer->campusDe((int) $moi['id']);
$regimes = $utilisateurModel->listeRegimes();
$mesRegimes = $utilisateurModel->regimesDe((int) $moi['id']);

$nomsMois = array('', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
$inscrit = strtotime($moi['date_inscription']);
$membreDepuis = $nomsMois[(int) date('n', $inscrit)] . ' ' . date('Y', $inscrit);
$initiales = mb_strtoupper(mb_substr($moi['prenom'], 0, 1) . mb_substr($moi['nom'], 0, 1));
$budgetActuel = '';
if ((float) $moi['budget_mensuel'] > 0) {
    $budgetActuel = number_format((float) $moi['budget_mensuel'], 2, ',', ' ');
}

// Vérifie l'image envoyée, la recadre en carré 256 x 256 et l'enregistre en JPEG.
// L'image est redessinée avec GD : le fichier gardé ne contient que des pixels.
// Renvoie le chemin à mettre en base, ou null avec $erreur rempli.
function enregistrerPhoto($fichier, $idUtilisateur, &$erreur)
{
    if ($fichier['error'] == UPLOAD_ERR_INI_SIZE || $fichier['error'] == UPLOAD_ERR_FORM_SIZE || $fichier['size'] > 5 * 1024 * 1024) {
        $erreur = 'Image trop lourde (5 Mo maximum).';
        return null;
    }
    if ($fichier['error'] != UPLOAD_ERR_OK || !is_uploaded_file($fichier['tmp_name'])) {
        $erreur = "L'envoi de l'image a échoué, réessaie.";
        return null;
    }

    $types = array('image/jpeg', 'image/png', 'image/webp', 'image/gif');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $type = $finfo->file($fichier['tmp_name']);
    $taille = @getimagesize($fichier['tmp_name']);
    if (!in_array($type, $types) || $taille == false) {
        $erreur = 'Format non reconnu : envoie une image JPG, PNG, WebP ou GIF.';
        return null;
    }
    if ($taille[0] > 8000 || $taille[1] > 8000) {
        $erreur = 'Image trop grande (8000 pixels maximum de côté).';
        return null;
    }

    $source = false;
    if ($type == 'image/jpeg') {
        $source = @imagecreatefromjpeg($fichier['tmp_name']);
    } elseif ($type == 'image/png') {
        $source = @imagecreatefrompng($fichier['tmp_name']);
    } elseif ($type == 'image/webp') {
        $source = @imagecreatefromwebp($fichier['tmp_name']);
    } elseif ($type == 'image/gif') {
        $source = @imagecreatefromgif($fichier['tmp_name']);
    }
    if ($source == false) {
        $erreur = 'Image illisible, essaie avec une autre.';
        return null;
    }

    // Photo prise avec un téléphone : on la remet à l'endroit.
    $source = redresserPhoto($source, lireOrientation($fichier['tmp_name']));

    // Carré pris au centre de l'image.
    $largeur = imagesx($source);
    $hauteur = imagesy($source);
    $cote = min($largeur, $hauteur);
    $x = (int) (($largeur - $cote) / 2);
    $y = (int) (($hauteur - $cote) / 2);

    $photo = imagecreatetruecolor(256, 256);
    imagefill($photo, 0, 0, imagecolorallocate($photo, 255, 255, 255)); // fond blanc sous la transparence
    imagecopyresampled($photo, $source, 0, 0, $x, $y, 256, 256, $cote, $cote);

    $dossier = __DIR__ . '/../public/upload/photos';
    if (!is_dir($dossier)) {
        mkdir($dossier, 0755, true);
    }
    $nom = (int) $idUtilisateur . '-' . bin2hex(random_bytes(8)) . '.jpg';
    $ok = imagejpeg($photo, $dossier . '/' . $nom, 85);
    if (!$ok) {
        $erreur = "Impossible d'enregistrer l'image.";
        return null;
    }
    return 'public/upload/photos/' . $nom;
}

// Orientation EXIF de la photo (1 = déjà à l'endroit, 2 à 8 = tournée et/ou en miroir).
// Un téléphone enregistre souvent la photo "couchée" et note juste le sens dans l'EXIF.
// On lit l'EXIF à la main pour que ça marche avec JPEG, WebP et PNG.
function lireOrientation($chemin)
{
    $donnees = file_get_contents($chemin);
    if ($donnees === false) {
        return 1;
    }

    // Début du bloc EXIF : "Exif\0\0" (JPEG), chunk "EXIF" (WebP) ou chunk "eXIf" (PNG).
    $debut = false;
    $position = strpos($donnees, "Exif\0\0");
    if ($position !== false) {
        $debut = $position + 6;
    } else {
        $position = strpos($donnees, 'EXIF');
        if ($position !== false && substr($donnees, 0, 4) == 'RIFF') {
            $debut = $position + 8;
        }
        $position = strpos($donnees, 'eXIf');
        if ($position !== false && substr($donnees, 1, 3) == 'PNG') {
            $debut = $position + 4;
        }
    }
    if ($debut === false) {
        return 1;
    }

    // En-tête TIFF : "II" = petit-boutiste (Intel), "MM" = gros-boutiste (Motorola).
    $ordre = substr($donnees, $debut, 2);
    if ($ordre == 'II') {
        $court = 'v';
        $long = 'V';
    } elseif ($ordre == 'MM') {
        $court = 'n';
        $long = 'N';
    } else {
        return 1;
    }

    $ifd = unpack($long, substr($donnees, $debut + 4, 4))[1];
    $nombre = unpack($court, substr($donnees, $debut + $ifd, 2))[1];
    for ($i = 0; $i < $nombre && $i < 200; $i++) {
        $entree = $debut + $ifd + 2 + ($i * 12);
        if ($entree + 12 > strlen($donnees)) {
            break;
        }
        $tag = unpack($court, substr($donnees, $entree, 2))[1];
        if ($tag == 0x0112) {
            $valeur = unpack($court, substr($donnees, $entree + 8, 2))[1];
            if ($valeur >= 1 && $valeur <= 8) {
                return $valeur;
            }
            return 1;
        }
    }
    return 1;
}

// Tourne / retourne l'image selon l'orientation EXIF.
// imagerotate tourne dans le sens inverse des aiguilles d'une montre.
function redresserPhoto($image, $orientation)
{
    if ($orientation == 2) {
        imageflip($image, IMG_FLIP_HORIZONTAL);
    } elseif ($orientation == 3) {
        $image = imagerotate($image, 180, 0);
    } elseif ($orientation == 4) {
        imageflip($image, IMG_FLIP_VERTICAL);
    } elseif ($orientation == 5) {
        $image = imagerotate($image, 270, 0);
        imageflip($image, IMG_FLIP_HORIZONTAL);
    } elseif ($orientation == 6) {
        $image = imagerotate($image, 270, 0);
    } elseif ($orientation == 7) {
        $image = imagerotate($image, 90, 0);
        imageflip($image, IMG_FLIP_HORIZONTAL);
    } elseif ($orientation == 8) {
        $image = imagerotate($image, 90, 0);
    }
    return $image;
}

// Efface l'ancien fichier photo (seulement dans le dossier des photos).
function supprimerFichierPhoto($chemin)
{
    if ($chemin && preg_match('#^public/upload/photos/[0-9]+-[0-9a-f]{16}\.jpg$#', $chemin)) {
        $fichier = __DIR__ . '/../' . $chemin;
        if (is_file($fichier)) {
            unlink($fichier);
        }
    }
}

function champCompte($nom)
{
    if (isset($_POST[$nom]) && is_string($_POST[$nom])) {
        return $_POST[$nom];
    }
    return '';
}

function flashCompte($type, $message)
{
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
    header('Location: ../index.php?page=compte');
    exit;
}

if (!function_exists('texte')) {
    function texte($valeur)
    {
        return htmlspecialchars((string) $valeur);
    }
}
