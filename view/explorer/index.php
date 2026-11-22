<link rel="stylesheet" href="public/asset/css/explorer.css">
<div class="ex">
    <section class="liste">
        <h1 class="text-2xl font-bold">Autour de moi</h1>
        <p class="mt-1 text-sm text-texte-2">
            <?php if ($campus): ?><?= htmlspecialchars($campus['nom']) ?> · <?php endif; ?>
            <?= (int) $nb ?> resto<?= $nb > 1 ? 's' : '' ?> à moins de 15 min
            <?= $mot !== '' ? ' pour « ' . htmlspecialchars($q) . ' »' : '' ?>
        </p>
        <?php if (!empty($message)): ?>
            <p class="mt-4 text-sm text-texte-2"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form action="index.php" method="get">
            <input type="hidden" name="page" value="explorer">
            <?php foreach ($f as $nom => $actif): ?>
                <?php if ($actif): ?><input type="hidden" name="<?= $nom ?>" value="1"><?php endif; ?>
            <?php endforeach; ?>
            <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Resto, cuisine, plat…">
        </form>

        <div class="filtres">
            <?php foreach (['halal', 'prix', 'ouvert', 'vege'] as $nom): ?>
                <a class="<?= $f[$nom] ? 'on' : '' ?>" href="<?= htmlspecialchars($liens[$nom]) ?>"><?= $f[$nom] ? '✓ ' : '' ?><?= $noms[$nom] ?></a>
            <?php endforeach; ?>
            <button type="button" id="plusf"><?= $suite ? 'Moins de filtres' : 'Plus de filtres' ?></button>
        </div>
        <div class="filtres suite<?= $suite ? ' on' : '' ?>" id="suite">
            <?php foreach (['pres', 'burger', 'poulet', 'pizza', 'sushi', 'tacos'] as $nom): ?>
                <a class="<?= $f[$nom] ? 'on' : '' ?>" href="<?= htmlspecialchars($liens[$nom]) ?>"><?= $f[$nom] ? '✓ ' : '' ?><?= $noms[$nom] ?></a>
            <?php endforeach; ?>
        </div>

        <div class="tri">
            <span>Trier par <b>distance</b></span>
            <span><?= (int) $nb ?> résultat<?= $nb > 1 ? 's' : '' ?></span>
        </div>

        <?php if (!$liste && empty($message)): ?>
            <p class="mt-4 text-sm text-texte-2">Aucun resto près du campus.</p>
        <?php endif; ?>

        <?php foreach ($liste as $resto): ?>
            <article class="item" id="row-<?= htmlspecialchars($resto['id']) ?>">
                <a class="ouvrir" href="<?= htmlspecialchars($lien) ?>" data-id="<?= htmlspecialchars($resto['id']) ?>">
                    <span class="av" style="background:<?= htmlspecialchars($resto['fond']) ?>;color:<?= htmlspecialchars($resto['encre']) ?>"><?= htmlspecialchars($resto['sigle']) ?></span>
                    <span>
                        <b class="block truncate"><?= htmlspecialchars($resto['nom']) ?></b>
                        <span class="vert"><?= (int) $resto['min'] ?> min à pied</span>
                        <?php if ($resto['connu'] && $resto['ouvert']): ?><span class="vert">· Ouvert</span><?php endif; ?>
                        <br>
                        <span class="meta"><?= htmlspecialchars($resto['rue']) ?> · <?= htmlspecialchars($resto['euro']) ?><?= $resto['halal'] ? ' · Halal' : '' ?><?= $resto['vege'] ? ' · Végé' : '' ?></span>
                    </span>
                </a>
                <form method="post" action="controller/explorerController.php">
                    <input type="hidden" name="action" value="roulette">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($resto['id']) ?>">
                    <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
                    <?php foreach ($f as $nom => $actif): ?>
                        <?php if ($actif): ?><input type="hidden" name="<?= $nom ?>" value="1"><?php endif; ?>
                    <?php endforeach; ?>
                    <button class="plus"><?= $resto['dans'] ? '✓' : '+' ?></button>
                </form>
            </article>
        <?php endforeach; ?>
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
            <?php foreach (array_slice($roulette, 0, 3) as $resto): ?>
                <b style="background:<?= htmlspecialchars($resto['fond']) ?>;color:<?= htmlspecialchars($resto['encre']) ?>"><?= htmlspecialchars($resto['sigle']) ?></b>
            <?php endforeach; ?>
            <span><?= count($roulette) ?> resto<?= count($roulette) > 1 ? 's' : '' ?> dans ta roulette</span>
            <a href="index.php?page=roulette">Lancer</a>
        </div>
    </section>
</div>
<?php if ($campus): ?>
    <script id="donnees-explorer" type="application/json"><?= json_encode(['campus' => $campus, 'lieux' => $lieux], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) ?></script>
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css">
    <script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>
<?php endif; ?>
<script src="public/asset/js/explorer.js"></script>
