<?php


class ExplorerModel
{
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    
    public function campusDe($idUtilisateur)
    {
        $req = $this->bdd->prepare("SELECT e.id_etablissement AS id, e.nom, e.ville, e.latitude AS lat, e.longitude AS lng FROM utilisateur u JOIN etablissement e ON e.id_etablissement = u.id_etablissement WHERE u.id_utilisateur = ?");
        $req->execute([$idUtilisateur]);
        $campus = $req->fetch();

        if (!$campus || $campus['lat'] === null || $campus['lng'] === null) {
            return null;
        }

        return [
            'id' => (int) $campus['id'],
            'nom' => $campus['nom'],
            'ville' => $campus['ville'],
            'lat' => (float) $campus['lat'],
            'lng' => (float) $campus['lng'],
        ];
    }

    // Restaurants nommés dans un rayon de 1 200 m (environ 15 min à pied).
    public function autour($lat, $lng)
    {
        $q = '[out:json][timeout:40];(node["amenity"~"^(restaurant|fast_food)$"]["name"](around:1200,' . $lat . ',' . $lng . ');way["amenity"~"^(restaurant|fast_food)$"]["name"](around:1200,' . $lat . ',' . $lng . '););out center tags;';
        $ch = curl_init('https://overpass.openstreetmap.fr/api/interpreter');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'data=' . urlencode($q),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_USERAGENT => 'GraillePPE/1.0',
        ]);
        $json = json_decode((string) curl_exec($ch), true);
        curl_close($ch);

        $liste = [];
        $deja = [];
        foreach ($json['elements'] ?? [] as $el) {
            $nom = $el['tags']['name'] ?? '';
            $plat = $el['lat'] ?? ($el['center']['lat'] ?? null);
            $plng = $el['lon'] ?? ($el['center']['lon'] ?? null);
            if ($nom === '' || $plat === null || isset($deja[$k = mb_strtolower($nom) . round($plat, 4)])) {
                continue;
            }
            $deja[$k] = true;
            $rue = trim(($el['tags']['addr:housenumber'] ?? '') . ' ' . ($el['tags']['addr:street'] ?? ''));
            $liste[] = [
                'id' => ($el['type'][0] ?? 'n') . $el['id'],
                'nom' => $nom,
                'rue' => $rue,
                'lat' => $plat,
                'lng' => $plng,
                'min' => $this->kmMin($plat, $plng, $lat, $lng),
                'amenity' => $el['tags']['amenity'] ?? '',
                'cuisine' => $el['tags']['cuisine'] ?? '',
                'horaire' => $el['tags']['opening_hours'] ?? '',
                'diet_halal' => $el['tags']['diet:halal'] ?? '',
                'diet_vegan' => $el['tags']['diet:vegan'] ?? '',
            ];
        }
        usort($liste, function ($a, $b) { return $a['min'] <=> $b['min']; });
        return $liste;
    }

    // Complète un restaurant : filtres, prix indicatif, pastille de 3 lettres.
    public function infos($r)
    {
        $n = $this->txt($r['nom'] . ' ' . $r['cuisine']);
        $r['halal'] = in_array($r['diet_halal'], ['yes', 'only'], true) || preg_match('/halal|kebab|taco|couscous|liban|turc|indien|indian|maroc/', $n);
        $r['vege'] = in_array($r['diet_vegan'], ['yes', 'only'], true) || preg_match('/salade|salad|vegan|veget/', $n);
        $r['petit'] = $r['amenity'] === 'fast_food' || (bool) preg_match('/kebab|taco|snack|sandwich|crepe/', $this->txt($r['nom']));
        $r['prix'] = $r['petit'];
        $r['ouvert'] = $this->ouvert($r['horaire']);
        $r['connu'] = trim((string) $r['horaire']) !== '';
        $r['pres'] = $r['min'] <= 5;
        $r['burger'] = (bool) preg_match('/burger/', $n);
        $r['poulet'] = (bool) preg_match('/chicken|poulet|fried|kfc/', $n);
        $r['pizza'] = (bool) preg_match('/pizza/', $n);
        $r['sushi'] = (bool) preg_match('/sushi|japon/', $n);
        $r['tacos'] = (bool) preg_match('/taco/', $n);
        $r['sigle'] = mb_strtoupper(mb_substr(trim($r['nom']), 0, 3));
        $couleurs = ['#ff6b3d', '#e11d48', '#e7c4a0', '#f97316', '#fb7185'];
        $r['fond'] = $couleurs[strlen($r['nom']) % 5];
        $r['encre'] = $r['fond'] === '#e7c4a0' ? '#3f2a1d' : '#fff';
        // OpenStreetMap ne donne pas le prix du menu.
        $r['euro'] = $r['petit'] ? '8 €' : '14 €';
        return $r;
    }

    public function passe($r, $f)
    {
        foreach ($f as $k => $on) {
            if ($on && empty($r[$k])) {
                return false;
            }
        }
        return true;
    }

    // "otacos" trouve "O'Tacos" : on enlève accents, apostrophes et espaces.
    public function correspond($r, $q)
    {
        $mot = $this->txt($q);
        if ($mot === '') {
            return true;
        }
        return str_contains($this->txt($r['nom']), $mot)
            || str_contains($this->txt($r['rue']), $mot)
            || str_contains($this->txt($r['cuisine']), $mot);
    }

    public function txt($v)
    {
        $v = strtr(mb_strtolower((string) $v), ['à'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','î'=>'i','ï'=>'i','ô'=>'o','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c']);
        return str_replace(["'", '’', '-', ' ', '!', '&'], '', $v);
    }

    public function kmMin($lat, $lng, $a, $b)
    {
        return max(1, (int) round(hypot(($lat - $a) * 111320, ($lng - $b) * 111320 * cos(deg2rad($a))) / 80));
    }

    // Horaire OpenStreetMap, par exemple "Mo-Fr 11:00-22:00". Sans horaire, on laisse le resto.
    private function ouvert($h)
    {
        $h = trim((string) $h);
        if ($h === '' || str_contains($h, '24/7')) {
            return true;
        }
        $o = ['Mo'=>1,'Tu'=>2,'We'=>3,'Th'=>4,'Fr'=>5,'Sa'=>6,'Su'=>7];
        $auj = $o[['Su','Mo','Tu','We','Th','Fr','Sa'][(int) date('w')]];
        $now = (int) date('G') * 60 + (int) date('i');
        $ok = null;
        foreach (explode(';', $h) as $regle) {
            if (preg_match('/(Mo|Tu|We|Th|Fr|Sa|Su)(?:\s*-\s*(Mo|Tu|We|Th|Fr|Sa|Su))?/', $regle, $m)) {
                $a = $o[$m[1]];
                $b = !empty($m[2]) ? $o[$m[2]] : $a;
                $dansLaSemaine = ($a <= $b && $auj >= $a && $auj <= $b) || ($a > $b && ($auj >= $a || $auj <= $b));
                if (!$dansLaSemaine) {
                    continue;
                }
            }
            if (preg_match('/\boff\b/i', $regle)) {
                $ok = false;
                continue;
            }
            if (!preg_match('/(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})/', $regle, $x)) {
                continue;
            }
            $d = $x[1] * 60 + $x[2];
            $fin = $x[3] * 60 + $x[4];
            $ok = $fin > $d ? ($now >= $d && $now < $fin) : ($now >= $d || $now < $fin);
        }
        return $ok === null ? true : $ok;
    }
}
