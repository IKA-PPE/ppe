<?php
    include_once(__DIR__ . '/../../model/RouletteModel.php');
    include_once(__DIR__ . '/../../model/ExplorerModel.php');
    include_once(__DIR__ . '/../../controller/rouletteController.php');
    include(__DIR__ . '/../../private/bdd.php');

    // Il faut être connecté pour voir la roulette.
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?page=connexion');
        exit();
    }

    // La sélection de la roulette est remplie depuis la page Explorer (bouton +).
    if (!isset($_SESSION['roulette']) || !is_array($_SESSION['roulette'])) {
        $_SESSION['roulette'] = array();
    }

    $rouletteController = new rouletteController($bdd);

    // 1. Formulaires de la page (bouton × et "Tout retirer")
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'retirer':
                $rouletteController->retirer();
                break;
            case 'vider':
                $rouletteController->vider();
                break;
        }
        header('Location: index.php?page=roulette');
        exit();
    }

    // 2. Affichage
    $mode = 'selection';
    if (isset($_GET['mode']) && $_GET['mode'] == 'aleatoire') {
        $mode = 'aleatoire';
    }

    $donnees = $rouletteController->afficher($mode);
    $roue        = $donnees['roue'];
    $nbSelection = $donnees['nbSelection'];
    $message     = $donnees['message'];
?>
<div class="rl">

    <!-- Barre supérieure : recherche + notifications -->
    <div class="rl-haut">
        <form class="rl-recherche" action="index.php" method="get">
            <input type="hidden" name="page" value="explorer">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="search" name="q" placeholder="Rechercher un resto, une cuisine…">
            <span class="rl-touche">⌘K</span>
        </form>
        <button class="rl-cloche" type="button" title="Notifications">
            <svg viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg>
            <span class="rl-point"></span>
        </button>
    </div>

    <!-- Titre + mode sélection / aléatoire -->
    <div class="rl-titre">
        <div>
            <h1>Roulette</h1>
            <p>Laisse le hasard choisir parmi tes restos.</p>
        </div>
        <div class="rl-segment">
            <a class="<?php if ($mode == 'selection') { echo 'on'; } ?>" href="index.php?page=roulette">Ma sélection · <?php echo $nbSelection; ?></a>
            <a class="<?php if ($mode == 'aleatoire') { echo 'on'; } ?>" href="index.php?page=roulette&amp;mode=aleatoire">
                <svg viewBox="0 0 24 24"><path d="M3 17h3l9-10h6"/><path d="m18 4 3 3-3 3"/><path d="M3 7h3l3 3.5"/><path d="M13 14l2 3h6"/><path d="m18 14 3 3-3 3"/></svg>
                Aléatoire
            </a>
        </div>
    </div>

    <div class="rl-grille">

        <!-- La roue -->
        <section class="rl-carte rl-zone-roue">
            <div class="rl-roue-bloc">
                <span class="rl-repere"></span>
                <canvas id="roue" width="800" height="800"></canvas>
                <button type="button" id="lancer" class="rl-lancer" <?php if (count($roue) < 2) { echo 'disabled'; } ?>>Lancer</button>
            </div>
        </section>

        <div class="rl-colonne">

            <!-- Restos dans la roue -->
            <section class="rl-carte rl-liste">
                <div class="rl-liste-haut">
                    <h2>Dans la roue</h2>
                    <?php if ($mode == 'selection' && count($roue) > 0) { ?>
                        <form method="post" action="index.php?page=roulette">
                            <input type="hidden" name="action" value="vider">
                            <button class="rl-lien" type="submit">Tout retirer</button>
                        </form>
                    <?php } ?>
                    <?php if ($mode == 'aleatoire') { ?>
                        <a class="rl-lien" href="index.php?page=roulette&amp;mode=aleatoire">Mélanger</a>
                    <?php } ?>
                </div>

                <?php if ($message != '') { ?>
                    <p class="rl-message"><?php echo htmlspecialchars($message); ?></p>
                <?php } ?>

                <ul>
                    <?php foreach ($roue as $resto) { ?>
                        <li>
                            <span class="rl-pastille" style="background:<?php echo htmlspecialchars($resto['couleur']); ?>"></span>
                            <span class="rl-nom"><?php echo htmlspecialchars($resto['nom']); ?></span>
                            <span class="rl-prix"><?php echo htmlspecialchars($resto['prix']); ?></span>
                            <?php if ($mode == 'selection') { ?>
                                <form method="post" action="index.php?page=roulette">
                                    <input type="hidden" name="action" value="retirer">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($resto['id']); ?>">
                                    <button class="rl-retirer" type="submit" title="Retirer">×</button>
                                </form>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>

                <?php if (count($roue) == 1) { ?>
                    <p class="rl-message">Ajoute au moins 2 restos pour lancer la roue.</p>
                <?php } ?>

                <a class="rl-ajouter" href="index.php?page=explorer">+ Ajouter depuis Explorer</a>
            </section>

            <!-- Résultat -->
            <section class="rl-carte rl-resultat" id="resultat">
                <span class="rl-tuile" id="resultat-tuile">?</span>
                <div class="rl-resultat-texte">
                    <small>RÉSULTAT</small>
                    <b id="resultat-nom">Lance la roue</b>
                    <span id="resultat-info">Le resto choisi s'affichera ici</span>
                </div>
                <a class="rl-aller" id="resultat-aller" target="_blank" rel="noopener">Y aller</a>
            </section>
        </div>
    </div>
</div>

<script id="donnees-roulette" type="application/json"><?php echo json_encode($roue, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS); ?></script>
