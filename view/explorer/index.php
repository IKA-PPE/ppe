<?php include('controller/explorerController.php'); ?>
<link rel="stylesheet" href="public/asset/css/explorer.css?v=4">
<div class="ex">
    <section class="liste">
        <h1>Autour de moi</h1>
        <p class="sous">
            <?php if ($campus) { echo texte($campus['nom']); ?> · <?php } ?>
            <?php echo $nb; ?> resto<?php if ($nb > 1) { echo 's'; } ?> à moins de 15 min
            <?php if ($q != '') { ?> pour « <?php echo texte($q); ?> »<?php } ?>
        </p>

        <?php if ($message != '') { ?>
            <p class="message"><?php echo texte($message); ?></p>
        <?php } ?>

        <form action="index.php" method="get">
            <input type="hidden" name="page" value="explorer">
            <?php foreach ($f as $nom => $actif) { ?>
                <?php if ($actif) { ?>
                    <input type="hidden" name="<?php echo $nom; ?>" value="1">
                <?php } ?>
            <?php } ?>
            <input type="search" name="q" value="<?php echo texte($q); ?>" placeholder="Resto, cuisine, plat…">
        </form>

        <div class="filtres">
            <?php foreach (array('halal', 'prix', 'ouvert', 'vege') as $nom) { ?>
                <a class="<?php if ($f[$nom]) { echo 'on'; } ?>" href="<?php echo texte($liens[$nom]); ?>"><?php if ($f[$nom]) { echo '✓ '; } ?><?php echo $noms[$nom]; ?></a>
            <?php } ?>
            <button type="button" id="plusf"><?php if ($suite) { echo 'Moins de filtres'; } else { echo 'Plus de filtres'; } ?></button>
        </div>

        <div class="filtres suite<?php if ($suite) { echo ' on'; } ?>" id="suite">
            <?php foreach (array('pres', 'burger', 'poulet', 'pizza', 'sushi', 'tacos') as $nom) { ?>
                <a class="<?php if ($f[$nom]) { echo 'on'; } ?>" href="<?php echo texte($liens[$nom]); ?>"><?php if ($f[$nom]) { echo '✓ '; } ?><?php echo $noms[$nom]; ?></a>
            <?php } ?>
        </div>

        <div class="tri">
            <span>Trier par <b>distance</b></span>
            <span><?php echo $nb; ?> résultat<?php if ($nb > 1) { echo 's'; } ?></span>
        </div>

        <?php if (count($liste) == 0 && $message == '') { ?>
            <p class="message">Aucun resto près du campus.</p>
        <?php } ?>

        <?php foreach ($liste as $resto) { ?>
            <article class="item" id="row-<?php echo texte($resto['id']); ?>">
                <a class="ouvrir" href="<?php echo texte($lien); ?>" data-id="<?php echo texte($resto['id']); ?>">
                    <span class="av" style="background:<?php echo texte($resto['fond']); ?>;color:<?php echo texte($resto['encre']); ?>">
                        <?php echo texte($resto['sigle']); ?>
                    </span>
                    <span>
                        <b><?php echo texte($resto['nom']); ?></b>
                        <span class="vert">
                            <?php echo $resto['min']; ?> min à pied
                            <?php if ($resto['connu'] && $resto['ouvert']) { echo ' · Ouvert'; } ?>
                        </span>
                        <br>
                        <span class="meta">
                            <?php echo texte($resto['rue']); ?> · <?php echo texte($resto['euro']); ?>
                            <?php if ($resto['halal']) { echo ' · Halal'; } ?>
                            <?php if ($resto['vege']) { echo ' · Végé'; } ?>
                        </span>
                    </span>
                </a>
                <form method="post" action="controller/explorerController.php">
                    <input type="hidden" name="action" value="ajouter">
                    <input type="hidden" name="id" value="<?php echo texte($resto['id']); ?>">
                    <?php if ($q != '') { ?>
                        <input type="hidden" name="q" value="<?php echo texte($q); ?>">
                    <?php } ?>
                    <?php foreach ($f as $nom => $actif) { ?>
                        <?php if ($actif) { ?>
                            <input type="hidden" name="<?php echo $nom; ?>" value="1">
                        <?php } ?>
                    <?php } ?>
                    <button class="plus"><?php if ($resto['dans']) { echo '✓'; } else { echo '+'; } ?></button>
                </form>
            </article>
        <?php } ?>
    </section>

    <section class="zone">
        <div id="carte"></div>
        <button class="ici" id="ici" type="button" title="Revenir au campus">◎</button>

        <div class="fiche" id="fiche">
            <div class="haut">
                <span class="av" id="fiche-av"></span>
                <div>
                    <b id="fiche-nom"></b>
                    <div class="vert" id="fiche-min"></div>
                    <div class="detail" id="fiche-rue"></div>
                </div>
            </div>
            <div class="btns">
                <a class="voir" id="fiche-voir" target="_blank" rel="noopener">Voir la fiche</a>
                <a class="aller" id="fiche-aller" target="_blank" rel="noopener">Y aller</a>
            </div>
        </div>

        <div class="bas">
            <?php foreach ($pastilles as $resto) { ?>
                <b style="background:<?php echo texte($resto['fond']); ?>;color:<?php echo texte($resto['encre']); ?>">
                    <?php echo texte($resto['sigle']); ?>
                </b>
            <?php } ?>
            <span>
                <?php echo count($roulette); ?> resto<?php if (count($roulette) > 1) { echo 's'; } ?> dans ta roulette
            </span>
            <a href="index.php?page=roulette">Lancer</a>
        </div>
    </section>
</div>

<?php if ($campus) { ?>
    <script id="donnees-explorer" type="application/json"><?php echo json_encode(array('campus' => $campus, 'liste' => $liste, 'lieux' => $lieux), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS); ?></script>
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css">
    <script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>
<?php } ?>
<script src="public/asset/js/explorer.js?v=5"></script>
