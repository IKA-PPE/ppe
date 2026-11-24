<?php include('controller/accueilController.php'); ?>
<link rel="stylesheet" href="public/asset/css/accueil.css?v=3">

<!-- Sur ordinateur, la page tient exactement dans la hauteur de l'écran -->
<div class="acc-page">

<!-- Barre du haut : recherche -->
<div class="flex items-center gap-3 mb-6">
    <form action="index.php" method="GET" class="w-full max-w-md">
        <input type="hidden" name="page" value="explorer">
        <label class="flex items-center gap-2 h-10 rounded-xl bg-surface-2 px-3 text-texte-3">
            <svg class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="search" name="q" placeholder="Rechercher un resto, une cuisine…" class="flex-1 min-w-0 bg-transparent text-[15px] text-texte outline-none placeholder:text-texte-3">
        </label>
    </form>
</div>

<?php if ($flash) { ?>
    <div class="mb-6 rounded-xl p-3.5 text-sm <?php if ($flash['type'] == 'erreur') { echo 'bg-danger/10 text-danger border border-danger/30'; } else { echo 'bg-succes/10 text-succes border border-succes/30'; } ?>">
        <?php echo texte($flash['message']); ?>
    </div>
<?php } ?>

<!-- Salutation + actions -->
<div class="flex flex-col sm:flex-row sm:items-end gap-4">
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-texte-2"><?php echo $aujourdhui; ?></p>
        <h1 class="text-[32px] font-bold tracking-tight leading-tight">Salut <?php echo texte($utilisateur['prenom']); ?></h1>
    </div>
    <div class="flex gap-2.5 sm:ml-auto">
        <button type="button" data-ouvrir="fenetre-depense" class="flex flex-1 sm:flex-none justify-center items-center gap-2 h-11 rounded-[13px] bg-surface-2 px-3 sm:px-4 whitespace-nowrap text-sm sm:text-[15px] font-semibold hover:bg-surface-3 transition-colors cursor-pointer">
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/></svg>
            <span class="sm:hidden">Dépense</span><span class="hidden sm:inline">Ajouter une dépense</span>
        </button>
        <a href="index.php?page=roulette" class="flex flex-1 sm:flex-none justify-center items-center gap-2 h-11 rounded-[13px] px-3 sm:px-4 whitespace-nowrap text-sm sm:text-[15px] font-semibold text-white bg-linear-to-b from-[#ff7a4d] to-[#f2582a] shadow-[0_10px_24px_-10px_rgba(255,107,61,.7)] hover:brightness-110 transition">
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="2.4"/><path d="M12 3v6.6M12 14.4V21M3 12h6.6M14.4 12H21M5.6 5.6l4.7 4.7M13.7 13.7l4.7 4.7"/></svg>
            <span class="sm:hidden">Roulette</span><span class="hidden sm:inline">Lancer la roulette</span>
        </a>
    </div>
</div>

<div class="acc-grille grid gap-[18px] mt-5 lg:grid-cols-3">

    <!-- Carte budget -->
    <div class="rounded-[20px] border border-white/10 bg-linear-to-br from-[#1d1512] to-surface-1 to-60% p-5 flex items-center gap-5">
        <?php if ($budget > 0) { ?>
            <svg width="108" height="108" viewBox="0 0 104 104" class="shrink-0">
                <defs><linearGradient id="anneau" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FFB38A"/><stop offset="1" stop-color="#F2582A"/></linearGradient></defs>
                <circle cx="52" cy="52" r="44" fill="none" stroke="#2A2A31" stroke-width="11"/>
                <circle cx="52" cy="52" r="44" fill="none" stroke="url(#anneau)" stroke-width="11" stroke-linecap="round" stroke-dasharray="276.5" stroke-dashoffset="<?php echo round($anneau, 1); ?>" transform="rotate(-90 52 52)"/>
                <text x="52" y="50" text-anchor="middle" fill="#F5F5F7" font-size="20" font-weight="700"><?php echo number_format($restant, 0, ',', ' '); ?> €</text>
                <text x="52" y="67" text-anchor="middle" fill="rgba(235,235,245,.6)" font-size="11">restants</text>
            </svg>
            <div class="flex flex-col gap-2 min-w-0">
                <div>
                    <div class="text-xs text-texte-2">Dépensé</div>
                    <div class="font-semibold"><?php echo prix($depense); ?> € <span class="text-sm font-normal text-texte-3">/ <?php echo number_format($budget, 0, ',', ' '); ?> €</span></div>
                </div>
                <div>
                    <div class="text-xs text-texte-2">Tu peux dépenser</div>
                    <div class="font-semibold <?php if ($parJour > 0) { echo 'text-succes'; } else { echo 'text-danger'; } ?>"><?php echo prix($parJour); ?> € / jour</div>
                </div>
                <div class="text-xs text-texte-3">
                    <?php echo $joursRestants; ?> jour<?php if ($joursRestants > 1) { echo 's'; } ?> restant<?php if ($joursRestants > 1) { echo 's'; } ?>
                    · <button type="button" data-ouvrir="fenetre-budget" class="text-accent-2 hover:underline cursor-pointer">Modifier</button>
                </div>
            </div>
        <?php } else { ?>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-texte-2">Budget du mois</div>
                <p class="mt-2 text-texte-2 text-sm">Tu n'as pas encore défini ton budget mensuel.</p>
                <button type="button" data-ouvrir="fenetre-budget" class="inline-flex mt-4 h-10 items-center rounded-xl bg-accent/15 px-4 text-sm font-semibold text-accent-2 hover:bg-accent/25 transition-colors cursor-pointer">Définir mon budget</button>
            </div>
        <?php } ?>
    </div>

    <!-- Carte des restos autour du campus -->
    <div class="acc-zone relative min-h-[440px] overflow-hidden rounded-[20px] border border-white/10 bg-[#15171c] lg:col-span-2 lg:row-span-3">
        <div id="carte-accueil"></div>

        <div class="absolute left-4 top-4 z-10 flex gap-2">
            <button type="button" class="acc-filtre" data-filtre="halal">Halal</button>
            <button type="button" class="acc-filtre" data-filtre="prix">≤ 10 €</button>
            <button type="button" class="acc-filtre" data-filtre="ouvert">Ouvert</button>
        </div>

        <div class="absolute right-4 top-4 z-10 flex flex-col overflow-hidden rounded-xl border border-white/15 bg-surface-2/85 backdrop-blur">
            <button type="button" id="zoom-plus" class="grid h-9 w-9 place-items-center text-lg cursor-pointer hover:bg-white/10" title="Zoomer">+</button>
            <button type="button" id="zoom-moins" class="grid h-9 w-9 place-items-center border-t border-white/10 text-lg cursor-pointer hover:bg-white/10" title="Dézoomer">−</button>
        </div>

        <?php if ($message != '') { ?>
            <p class="absolute inset-x-4 bottom-4 z-10 rounded-xl bg-surface-2/90 p-3 text-sm text-texte-2"><?php echo texte($message); ?></p>
        <?php } ?>
    </div>

    <!-- Restos près du campus -->
    <div class="acc-proches rounded-[20px] border border-white/10 bg-surface-1 py-1.5">
        <div class="flex items-center justify-between px-4 pb-1.5 pt-2.5">
            <h2 class="font-bold tracking-tight">Près du campus</h2>
            <a href="index.php?page=explorer" class="text-sm font-medium text-accent">Tout voir</a>
        </div>
        <?php if (count($proches) == 0) { ?>
            <p class="px-4 py-3 text-sm text-texte-2">Aucun resto trouvé autour du campus.</p>
        <?php } ?>
        <div class="acc-proches-liste">
        <?php foreach ($proches as $i => $resto) { ?>
            <a href="index.php?page=explorer&amp;q=<?php echo urlencode($resto['nom']); ?>" data-resto="<?php echo texte($resto['id']); ?>" class="acc-ligne relative flex items-center gap-3 px-4 py-2.5 hover:bg-white/5 transition-colors<?php if ($i >= 3) { echo ' acc-plus'; } ?>">
                <?php if ($i > 0) { ?><span class="absolute left-4 right-0 top-0 h-px bg-white/10"></span><?php } ?>
                <span class="acc-av" style="background-color:<?php echo texte($resto['fond']); ?>;color:<?php echo texte($resto['encre']); ?>"><?php echo texte($resto['sigle']); ?></span>
                <span class="flex-1 min-w-0">
                    <span class="block truncate font-semibold"><?php echo texte($resto['nom']); ?></span>
                    <span class="block text-xs text-texte-2"><?php echo $resto['min']; ?> min · <?php echo texte($resto['euro']); ?><?php if ($resto['halal']) { echo ' · Halal'; } ?></span>
                </span>
                <svg class="h-4 w-4 text-texte-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
            </a>
        <?php } ?>
        </div>
    </div>

    <!-- Vote de groupe -->
    <div class="rounded-[20px] border border-white/10 bg-surface-1 p-4">
        <?php if ($vote) { ?>
            <div class="flex items-center justify-between gap-3">
                <h2 class="truncate font-bold tracking-tight">Vote · <?php echo texte($vote['groupe']); ?></h2>
                <span class="text-sm font-semibold text-accent-2 tabular-nums" id="compte-vote" data-reste="<?php echo (int) $vote['reste']; ?>">--:--</span>
            </div>
            <?php if (count($vote['choix']) == 0) { ?>
                <p class="mt-3 text-sm text-texte-2">Personne n'a encore proposé de resto.</p>
            <?php } ?>
            <div class="mt-3 space-y-1 text-sm">
                <?php foreach ($vote['choix'] as $j => $choix) { ?>
                    <?php
                    $largeur = 0;
                    if ($vote['total'] > 0) {
                        $largeur = round(100 * $choix['voix'] / $vote['total']);
                    }
                    ?>
                    <form method="post" action="controller/accueilController.php">
                        <input type="hidden" name="action" value="voter">
                        <input type="hidden" name="proposition" value="<?php echo (int) $choix['id']; ?>">
                        <button class="block w-full rounded-lg px-1 py-1 text-left hover:bg-white/5 cursor-pointer" title="Voter pour <?php echo texte($choix['nom']); ?>">
                            <span class="flex justify-between">
                                <span><?php echo texte($choix['nom']); ?><?php if ($choix['moi']) { echo ' <span class="text-xs text-accent-2">· ton vote</span>'; } ?></span>
                                <b><?php echo (int) $choix['voix']; ?></b>
                            </span>
                            <span class="mt-1.5 block h-1.5 overflow-hidden rounded-full bg-surface-3">
                                <span class="block h-full rounded-full <?php if ($j == 0) { echo 'bg-accent'; } else { echo 'bg-texte-2'; } ?>" style="width:<?php echo $largeur; ?>%"></span>
                            </span>
                        </button>
                    </form>
                <?php } ?>
            </div>
            <?php if ($vote['nomGagnant'] != '') { ?>
                <p class="mt-3.5 flex h-[38px] items-center justify-center rounded-xl bg-succes/15 text-sm font-semibold text-succes">On mange chez <?php echo texte($vote['nomGagnant']); ?></p>
            <?php } elseif ($vote['egalite']) { ?>
                <form method="post" action="controller/accueilController.php">
                    <input type="hidden" name="action" value="departager">
                    <button class="mt-3.5 flex h-[38px] w-full items-center justify-center gap-2 rounded-xl bg-alerte text-sm font-semibold text-black hover:brightness-105 transition cursor-pointer">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="2.4"/><path d="M12 3v6.6M12 14.4V21M3 12h6.6M14.4 12H21M5.6 5.6l4.7 4.7M13.7 13.7l4.7 4.7"/></svg>
                        Départager à la roulette
                    </button>
                </form>
            <?php } ?>
        <?php } else { ?>
            <h2 class="font-bold tracking-tight">Vote de groupe</h2>
            <p class="mt-2 text-sm text-texte-2">Aucun vote en cours dans tes groupes.</p>
            <a href="index.php?page=groupes" class="mt-3.5 flex h-[38px] items-center justify-center gap-2 rounded-xl bg-surface-2 text-sm font-semibold hover:bg-surface-3 transition-colors">Voir mes groupes</a>
        <?php } ?>
    </div>
</div>

</div>

<!-- Fenêtre : définir le budget -->
<dialog id="fenetre-budget" class="acc-fenetre">
    <form method="post" action="controller/accueilController.php">
        <input type="hidden" name="action" value="budget">
        <h2>Budget du mois</h2>
        <p>Combien veux-tu dépenser en resto ce mois-ci ?</p>
        <label>Montant (€)
            <input type="text" name="montant" inputmode="decimal" required placeholder="150" value="<?php if ($budget > 0) { echo prix($budget); } ?>">
        </label>
        <div class="acc-btns">
            <button type="button" data-fermer>Annuler</button>
            <button class="ok">Enregistrer</button>
        </div>
    </form>
</dialog>

<!-- Fenêtre : ajouter une dépense -->
<dialog id="fenetre-depense" class="acc-fenetre">
    <form method="post" action="controller/accueilController.php">
        <input type="hidden" name="action" value="depense">
        <h2>Ajouter une dépense</h2>
        <label>Resto
            <input type="text" name="resto" list="restos-proches" required maxlength="100" autocomplete="off" placeholder="Choisis ou tape un nom">
        </label>
        <datalist id="restos-proches">
            <?php foreach ($choixDepense as $resto) { ?>
                <option value="<?php echo texte($resto['nom']); ?>"><?php echo $resto['min']; ?> min</option>
            <?php } ?>
        </datalist>
        <label>Type
            <select name="type">
                <option value="">Détecter automatiquement</option>
                <?php foreach ($typesResto as $typeResto) { ?>
                    <option value="<?php echo texte($typeResto); ?>"><?php echo texte($typeResto); ?></option>
                <?php } ?>
            </select>
        </label>
        <label>Montant (€)
            <input type="text" name="montant" inputmode="decimal" required placeholder="8,90">
        </label>
        <div class="acc-btns">
            <button type="button" data-fermer>Annuler</button>
            <button class="ok">Ajouter</button>
        </div>
    </form>
</dialog>

<?php if ($campus) { ?>
    <script id="donnees-accueil" type="application/json"><?php echo json_encode(array('campus' => $campus, 'lieux' => $lieux), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS); ?></script>
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css">
    <script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>
<?php } ?>
<script src="public/asset/js/accueil.js?v=3"></script>
