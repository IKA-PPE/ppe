<?php include('controller/budgetController.php'); ?>
<link rel="stylesheet" href="public/asset/css/budget.css?v=2">

<!-- Sur ordinateur, la page tient exactement dans la hauteur de l'écran -->
<div class="bud-page">

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

<!-- Titre, mois et actions -->
<div class="flex flex-col sm:flex-row sm:items-end gap-4">
    <div>
        <h1 class="text-[32px] font-bold tracking-tight leading-tight">Budget</h1>
        <div class="mt-1 flex items-center gap-2">
            <a href="<?php echo texte($lienAvant); ?>" class="bud-fleche" title="Mois précédent">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="m15 5-7 7 7 7"/></svg>
            </a>
            <span class="min-w-[130px] text-center font-semibold"><?php echo $titreMois; ?></span>
            <?php if ($lienApres) { ?>
                <a href="<?php echo texte($lienApres); ?>" class="bud-fleche" title="Mois suivant">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                </a>
            <?php } else { ?>
                <span class="bud-fleche off">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                </span>
            <?php } ?>
        </div>
    </div>
    <div class="flex gap-2.5 sm:ml-auto">
        <button type="button" data-ouvrir="fenetre-budget" class="flex flex-1 sm:flex-none justify-center items-center gap-2 h-11 rounded-[13px] bg-surface-2 px-3 sm:px-4 whitespace-nowrap text-sm sm:text-[15px] font-semibold hover:bg-surface-3 transition-colors cursor-pointer">
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><path d="M4 7h10M18 7h2M4 17h4M12 17h8"/><circle cx="16" cy="7" r="2"/><circle cx="10" cy="17" r="2"/></svg>
            <span class="sm:hidden">Budget</span><span class="hidden sm:inline">Modifier le budget</span>
        </button>
        <button type="button" data-ouvrir="fenetre-depense" class="flex flex-1 sm:flex-none justify-center items-center gap-2 h-11 rounded-[13px] px-3 sm:px-4 whitespace-nowrap text-sm sm:text-[15px] font-semibold text-white bg-linear-to-b from-[#ff7a4d] to-[#f2582a] shadow-[0_10px_24px_-10px_rgba(255,107,61,.7)] hover:brightness-110 transition cursor-pointer">
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            <span class="sm:hidden">Dépense</span><span class="hidden sm:inline">Ajouter une dépense</span>
        </button>
    </div>
</div>

<!-- Indicateurs du mois -->
<div class="mt-5 grid grid-cols-2 gap-[18px] lg:grid-cols-4">
    <div class="bud-kpi">
        <div class="bud-kpi-titre">Budget du mois</div>
        <?php if ($budget > 0) { ?>
            <div class="bud-kpi-valeur"><?php echo euros($budget); ?> €</div>
        <?php } else { ?>
            <button type="button" data-ouvrir="fenetre-budget" class="mt-2 text-sm font-semibold text-accent-2 hover:underline cursor-pointer">Définir mon budget</button>
        <?php } ?>
    </div>
    <div class="bud-kpi">
        <div class="bud-kpi-titre">Dépensé</div>
        <div class="bud-kpi-valeur"><?php echo euros($depense); ?> €</div>
        <?php if ($budget > 0) { ?>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-surface-3">
                <div class="h-full rounded-full <?php if ($restant < 0) { echo 'bg-danger'; } else { echo 'bg-accent'; } ?>" style="width:<?php echo $pourcent; ?>%"></div>
            </div>
        <?php } ?>
    </div>
    <div class="bud-kpi">
        <div class="bud-kpi-titre">Restant</div>
        <?php if ($budget > 0) { ?>
            <div class="bud-kpi-valeur <?php if ($restant < 0) { echo 'text-danger'; } else { echo 'text-succes'; } ?>"><?php echo euros($restant); ?> €</div>
            <?php if ($restant < 0) { ?><div class="mt-1 text-xs text-danger">Budget dépassé</div><?php } ?>
        <?php } else { ?>
            <div class="bud-kpi-valeur text-texte-3">—</div>
        <?php } ?>
    </div>
    <div class="bud-kpi">
        <div class="bud-kpi-titre">Moyenne par repas</div>
        <?php if ($resume['repas'] > 0) { ?>
            <div class="bud-kpi-valeur"><?php echo number_format($moyenne, 2, ',', ' '); ?> €</div>
            <?php if ($evolution !== null) { ?>
                <div class="mt-1 text-xs <?php if ($evolution <= 0) { echo 'text-succes'; } else { echo 'text-danger'; } ?>">
                    <?php if ($evolution > 0) { echo '+'; } else if ($evolution < 0) { echo '−'; } ?><?php echo abs($evolution); ?> % vs <?php echo $nomMoisAvant; ?>
                </div>
            <?php } else { ?>
                <div class="mt-1 text-xs text-texte-3"><?php echo $resume['repas']; ?> repas ce mois</div>
            <?php } ?>
        <?php } else { ?>
            <div class="bud-kpi-valeur text-texte-3">—</div>
        <?php } ?>
    </div>
</div>

<div class="bud-milieu mt-[18px] grid gap-[18px] lg:grid-cols-3">

    <!-- Dépenses par jour -->
    <div class="bud-carte-graphe rounded-[20px] border border-white/10 bg-surface-1 p-5 lg:col-span-2">
        <div class="flex items-center justify-between">
            <h2 class="font-bold tracking-tight">Dépenses par jour</h2>
            <div class="bud-onglets">
                <button type="button" class="on" data-vue="mois">Mois</button>
                <button type="button" data-vue="semaine">Semaine</button>
            </div>
        </div>
        <div class="bud-graphe" id="graphe" data-mois="<?php echo $moisAffiche; ?>">
            <?php foreach ($barres as $barre) { ?>
                <?php
                $classe = 'bud-barre';
                if ($barre['aujourdhui']) { $classe = $classe . ' auj'; }
                if ($barre['futur']) { $classe = $classe . ' futur'; }
                if ($barre['montant'] == 0) { $classe = $classe . ' vide'; }
                $info = $barre['jour'] . ' ' . $courtsMois[$mois] . ' : ' . number_format($barre['montant'], 2, ',', ' ') . ' €';
                ?>
                <div class="<?php echo $classe; ?>" data-semaine="<?php if ($barre['semaine']) { echo '1'; } else { echo '0'; } ?>" data-jour="<?php echo $barre['jour']; ?>" title="<?php echo texte($info); ?>">
                    <span style="height:<?php echo max(2, $barre['hauteur']); ?>%"></span>
                </div>
            <?php } ?>
        </div>
        <div class="bud-axe" id="axe">
            <?php foreach ($barres as $barre) { ?>
                <span data-semaine="<?php if ($barre['semaine']) { echo '1'; } else { echo '0'; } ?>" data-jour="<?php echo $barre['jour']; ?>"><?php
                    // Pas d'autre étiquette trop près de "Aujourd'hui", sinon elles se chevauchent.
                    $tropPres = ($estCourant && abs($barre['jour'] - $aujourdhui) < 4);
                    if ($barre['aujourdhui']) {
                        echo "Aujourd'hui";
                    } elseif ($tropPres) {
                        echo '';
                    } elseif ($barre['jour'] == 1) {
                        echo $debutMois;
                    } elseif ($barre['jour'] == $nbJours) {
                        echo $finMois;
                    } elseif ($barre['jour'] == 8 || $barre['jour'] == 15 || $barre['jour'] == 22) {
                        echo $barre['jour'];
                    }
                ?></span>
            <?php } ?>
        </div>
    </div>

    <!-- Par type de resto -->
    <div class="bud-types rounded-[20px] border border-white/10 bg-surface-1 p-5">
        <h2 class="font-bold tracking-tight">Par type de resto</h2>
        <?php if (count($types) == 0) { ?>
            <p class="mt-3 text-sm text-texte-2">Aucune dépense ce mois-ci.</p>
        <?php } else { ?>
            <div class="mt-4 flex h-2.5 gap-0.5 overflow-hidden rounded-full">
                <?php foreach ($types as $type) { ?>
                    <span style="width:<?php echo max(2, $type['part']); ?>%;background:<?php echo $type['couleur']; ?>" title="<?php echo texte($type['type']); ?>"></span>
                <?php } ?>
            </div>
            <ul class="mt-4 space-y-2.5 text-sm">
                <?php foreach ($types as $type) { ?>
                    <li class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-[3px]" style="background:<?php echo $type['couleur']; ?>"></span>
                        <span class="flex-1 truncate"><?php echo texte($type['type']); ?></span>
                        <b class="tabular-nums"><?php echo number_format($type['total'], 2, ',', ' '); ?> €</b>
                        <span class="w-12 shrink-0 whitespace-nowrap text-right text-texte-3 tabular-nums"><?php echo $type['part']; ?> %</span>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
    </div>
</div>

<!-- Historique des repas -->
<div class="bud-historique mt-[18px] overflow-x-auto rounded-[20px] border border-white/10 bg-surface-1">
    <table class="bud-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Restaurant</th>
                <th>Type</th>
                <th>Groupe</th>
                <th class="text-right">Montant</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($lignes) == 0) { ?>
                <tr><td colspan="6" class="text-center text-texte-2">Aucun repas enregistré en <?php echo $nomsMois[$mois]; ?>.</td></tr>
            <?php } ?>
            <?php foreach ($lignes as $ligne) { ?>
                <tr>
                    <td class="whitespace-nowrap text-texte-2"><?php echo texte($ligne['quand']); ?></td>
                    <td>
                        <span class="flex items-center gap-2.5">
                            <span class="bud-av" style="background-color:<?php echo texte($ligne['fond']); ?>;color:<?php echo texte($ligne['encre']); ?>"><?php echo texte($ligne['sigle']); ?></span>
                            <span class="font-semibold"><?php echo texte($ligne['nom']); ?></span>
                        </span>
                    </td>
                    <td><span class="bud-badge"><?php echo texte($ligne['type']); ?></span></td>
                    <td class="text-texte-2"><?php if ($ligne['groupe']) { echo texte($ligne['groupe']); } else { echo '—'; } ?></td>
                    <td class="whitespace-nowrap text-right font-semibold tabular-nums">−<?php echo number_format($ligne['montant'], 2, ',', ' '); ?> €</td>
                    <td class="w-10">
                        <form method="post" action="controller/budgetController.php" data-confirmer="Supprimer cette dépense de <?php echo texte(number_format($ligne['montant'], 2, ',', ' ')); ?> € ?">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="depense" value="<?php echo $ligne['id']; ?>">
                            <input type="hidden" name="mois" value="<?php echo $moisAffiche; ?>">
                            <button class="bud-suppr" title="Supprimer">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><path d="M5 7h14M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4h6v3"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</div>

<!-- Fenêtre : modifier le budget -->
<dialog id="fenetre-budget" class="bud-fenetre">
    <form method="post" action="controller/budgetController.php">
        <input type="hidden" name="action" value="budget">
        <input type="hidden" name="mois" value="<?php echo $moisAffiche; ?>">
        <h2>Budget mensuel</h2>
        <p>Combien veux-tu dépenser en resto chaque mois ?</p>
        <label>Montant (€)
            <input type="text" name="montant" inputmode="decimal" required placeholder="180" value="<?php if ($budget > 0) { echo euros($budget); } ?>">
        </label>
        <div class="bud-btns">
            <button type="button" data-fermer>Annuler</button>
            <button class="ok">Enregistrer</button>
        </div>
    </form>
</dialog>

<!-- Fenêtre : ajouter une dépense -->
<dialog id="fenetre-depense" class="bud-fenetre">
    <form method="post" action="controller/budgetController.php">
        <input type="hidden" name="action" value="depense">
        <input type="hidden" name="mois" value="<?php echo $moisAffiche; ?>">
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
        <label>Date du repas
            <input type="datetime-local" name="date" value="<?php echo $maintenant; ?>" max="<?php echo $maintenant; ?>">
        </label>
        <div class="bud-btns">
            <button type="button" data-fermer>Annuler</button>
            <button class="ok">Ajouter</button>
        </div>
    </form>
</dialog>

<script src="public/asset/js/budget.js?v=2"></script>
