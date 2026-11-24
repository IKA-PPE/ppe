<?php
class ExplorerModel
{
    private $bdd;

    // Garde la connexion à la base de données pour les requêtes du modèle.
    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }
    // Etablissement choisi a l'inscription.
    public function campusDe($idUtilisateur)
    {
        $sql = "SELECT e.id_etablissement AS id, e.nom, e.ville, e.latitude AS lat, e.longitude AS lng FROM utilisateur u JOIN etablissement e ON e.id_etablissement = u.id_etablissement WHERE u.id_utilisateur = :id";
        $req = $this->bdd->prepare($sql);
        $req->bindParam(':id', $idUtilisateur);
        $req->execute();
        $campus = $req->fetch();
        if ($campus == false || $campus['lat'] === null || $campus['lng'] === null) {
            return null;
        }
        $campus['id'] = (int) $campus['id'];
        $campus['lat'] = (float) $campus['lat'];
        $campus['lng'] = (float) $campus['lng'];
        return $campus;
    }
    // Restos dans un rayon de 1200 m, du plus proche au plus loin.
    public function autour($latitude, $longitude)
    {
        $zone = 'around:1200,' . $latitude . ',' . $longitude;
        $question = '[out:json][timeout:40];(node["amenity"~"^(restaurant|fast_food)$"]["name"](' . $zone . ');way["amenity"~"^(restaurant|fast_food)$"]["name"](' . $zone . '););out center tags;';
        $curl = curl_init('https://overpass.openstreetmap.fr/api/interpreter');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'data=' . urlencode($question));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 40);
        curl_setopt($curl, CURLOPT_USERAGENT, 'GraillePPE/1.0');
        $texte = curl_exec($curl);
        curl_close($curl);
        $json = json_decode($texte, true);
        if (is_array($json) == false || isset($json['elements']) == false) {
            return array();
        }
        $liste = array();
        $dejaVu = array();
        foreach ($json['elements'] as $element) {
            $tags = array();
            if (isset($element['tags'])) { $tags = $element['tags']; }
            $nom = $this->lire($tags, 'name');
            $lat = null;
            $lng = null;
            if (isset($element['lat'])) { $lat = $element['lat']; $lng = $element['lon']; }
            if (isset($element['center']['lat'])) { $lat = $element['center']['lat']; $lng = $element['center']['lon']; }
            if ($nom == '' || $lat === null || isset($dejaVu[mb_strtolower($nom) . round($lat, 4)])) { continue; }
            $dejaVu[mb_strtolower($nom) . round($lat, 4)] = true;
            $lettre = 'n';
            if (isset($element['type']) && $element['type'] != '') { $lettre = substr($element['type'], 0, 1); }
            $liste[] = array(
                'id' => $lettre . $element['id'], 'nom' => $nom,
                'rue' => trim($this->lire($tags, 'addr:housenumber') . ' ' . $this->lire($tags, 'addr:street')),
                'lat' => $lat, 'lng' => $lng, 'min' => $this->minutes($lat, $lng, $latitude, $longitude),
                'amenity' => $this->lire($tags, 'amenity'), 'cuisine' => $this->lire($tags, 'cuisine'),
                'horaire' => $this->lire($tags, 'opening_hours'),
                'diet_halal' => $this->lire($tags, 'diet:halal'), 'diet_vegan' => $this->lire($tags, 'diet:vegan'),
            );
        }
        usort($liste, function ($a, $b) { return $a['min'] - $b['min']; });
        return $liste;
    }
    // 8 euros pour un fast-food, 14 euros sinon.
    public function infos($resto)
    {
        $texte = $this->simple($resto['nom'] . ' ' . $resto['cuisine']);
        $nom = $this->simple($resto['nom']);
        $resto['halal'] = ($resto['diet_halal'] == 'yes' || $resto['diet_halal'] == 'only' || $this->contient($texte, array('halal', 'kebab', 'taco', 'couscous', 'liban', 'turc', 'indien', 'indian', 'maroc')));
        $resto['vege'] = ($resto['diet_vegan'] == 'yes' || $resto['diet_vegan'] == 'only' || $this->contient($texte, array('salade', 'salad', 'vegan', 'veget')));
        $resto['petit'] = ($resto['amenity'] == 'fast_food' || $this->contient($nom, array('kebab', 'taco', 'snack', 'sandwich', 'crepe')));
        $resto['prix'] = $resto['petit'];
        $resto['ouvert'] = $this->ouvert($resto['horaire']);
        $resto['connu'] = (trim($resto['horaire']) != '');
        $resto['pres'] = ($resto['min'] <= 5);
        $resto['burger'] = $this->contient($texte, array('burger'));
        $resto['poulet'] = $this->contient($texte, array('chicken', 'poulet', 'kfc'));
        $resto['pizza'] = $this->contient($texte, array('pizza'));
        $resto['sushi'] = $this->contient($texte, array('sushi', 'japon'));
        $resto['tacos'] = $this->contient($texte, array('taco'));
        $resto['sigle'] = mb_strtoupper(mb_substr(trim($resto['nom']), 0, 3));
        $couleurs = array('#ff6b3d', '#e11d48', '#e7c4a0', '#f97316', '#fb7185');
        $resto['fond'] = $couleurs[strlen($resto['nom']) % 5];
        $resto['encre'] = '#fff';
        if ($resto['fond'] == '#e7c4a0') { $resto['encre'] = '#3f2a1d'; }
        $resto['euro'] = '14 €';
        if ($resto['petit']) { $resto['euro'] = '8 €'; }
        $badges = array();
        if ($resto['halal']) { $badges[] = 'Halal'; }
        if ($resto['petit']) { $badges[] = '≤ 10 €'; }
        if ($resto['vege']) { $badges[] = 'Végé'; }
        $resto['badges'] = implode(' · ', $badges);
        return $resto;
    }
    // true si le resto correspond a la recherche et aux filtres.
    public function garde($resto, $recherche, $filtres)
    {
        foreach ($filtres as $cle => $actif) {
            if ($actif == true && $resto[$cle] == false) { return false; }
        }
        $mot = $this->simple($recherche);
        if ($mot == '') { return true; }
        if (str_contains($this->simple($resto['nom']), $mot)) { return true; }
        if (str_contains($this->simple($resto['rue']), $mot)) { return true; }
        if (str_contains($this->simple($resto['cuisine']), $mot)) { return true; }
        return false;
    }
    // Garde les restos a moins de 15 min.
    public function filtrer($tous, $recherche, $filtres, $idsRoulette)
    {
        $ok = array();
        $roulette = array();
        foreach ($tous as $resto) {
            $resto = $this->infos($resto);
            $resto['dans'] = in_array($resto['id'], $idsRoulette);
            if ($resto['dans']) { $roulette[] = $resto; }
            if ($resto['min'] > 15) { continue; }
            if ($this->garde($resto, $recherche, $filtres) == false) { continue; }
            $ok[] = $resto;
        }
        $limite = 20;
        if ($recherche != '') { $limite = 40; }
        foreach ($filtres as $actif) {
            if ($actif) { $limite = 40; }
        }
        $liste = array_slice($ok, 0, $limite);
        return array('liste' => $liste, 'lieux' => $ok, 'roulette' => $roulette);
    }
    // Renvoie la valeur d'un tag OpenStreetMap, ou '' s'il n'existe pas.
    private function lire($tags, $cle)
    {
        if (isset($tags[$cle])) { return $tags[$cle]; }
        return '';
    }
    // true si le texte contient au moins un des mots de la liste.
    private function contient($texte, $mots)
    {
        foreach ($mots as $mot) {
            if (str_contains($texte, $mot)) { return true; }
        }
        return false;
    }
    // "O'Tacos" devient "otacos".
    private function simple($valeur)
    {
        $valeur = mb_strtolower((string) $valeur);
        $avec = array('à', 'â', 'ä', 'é', 'è', 'ê', 'ë', 'î', 'ï', 'ô', 'ù', 'û', 'ü', 'ç');
        $sans = array('a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'o', 'u', 'u', 'u', 'c');
        $valeur = str_replace($avec, $sans, $valeur);
        $valeur = str_replace(array("'", '’', '-', ' ', '!', '&'), '', $valeur);
        return $valeur;
    }
    // 80 metres a pied = 1 minute.
    private function minutes($latResto, $lngResto, $latCampus, $lngCampus)
    {
        $nord = ($latResto - $latCampus) * 111320;
        $est = ($lngResto - $lngCampus) * 111320 * cos(deg2rad($latCampus));
        $minutes = (int) round(sqrt(($nord * $nord) + ($est * $est)) / 80);
        if ($minutes < 1) { return 1; }
        return $minutes;
    }
    // Mo lundi, Tu mardi, We mercredi, Th jeudi, Fr vendredi, Sa samedi, Su dimanche.
    private function ouvert($horaire)
    {
        $horaire = trim((string) $horaire);
        if ($horaire == '' || str_contains($horaire, '24/7')) { return true; }
        $jours = array('Mo' => 1, 'Tu' => 2, 'We' => 3, 'Th' => 4, 'Fr' => 5, 'Sa' => 6, 'Su' => 7);
        $aujourdHui = (int) date('N');
        $maintenant = (int) date('G') * 60 + (int) date('i');
        $ferme = false;
        foreach (explode(';', $horaire) as $ligne) {
            $compact = str_replace(' ', '', trim($ligne));
            $code = substr($compact, 0, 2);
            if (isset($jours[$code])) {
                $debut = $jours[$code];
                $fin = $debut;
                if (substr($compact, 2, 1) == '-' && isset($jours[substr($compact, 3, 2)])) { $fin = $jours[substr($compact, 3, 2)]; }
                $bonJour = ($debut <= $fin && $aujourdHui >= $debut && $aujourdHui <= $fin) || ($debut > $fin && ($aujourdHui >= $debut || $aujourdHui <= $fin));
                if ($bonJour == false) { continue; }
            }
            if (str_contains(' ' . strtolower($ligne) . ' ', ' off ') && isset($jours[$code])) { $ferme = true; continue; }
            preg_match_all('/(\d{1,2}):(\d{2})/', $ligne, $trouve);
            if (count($trouve[1]) < 2) { continue; }
            $dedans = false;
            for ($i = 0; $i + 1 < count($trouve[1]); $i = $i + 2) {
                $a = ($trouve[1][$i] * 60) + $trouve[2][$i];
                $b = ($trouve[1][$i + 1] * 60) + $trouve[2][$i + 1];
                if (($b > $a && $maintenant >= $a && $maintenant < $b) || ($b <= $a && ($maintenant >= $a || $maintenant < $b))) {
                    $dedans = true;
                }
            }
            $ferme = ($dedans == false);
        }
        return ($ferme == false);
    }
}
