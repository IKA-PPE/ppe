<?php include('controller/compteController.php'); ?>
<link rel="stylesheet" href="public/asset/css/compte.css?v=8">

<div class="cpt-page mx-auto max-w-4xl">

    <h1 class="text-[32px] font-bold tracking-tight leading-tight">Paramètres du compte</h1>
    <p class="mt-1 text-texte-2">Ton profil, ton campus, ton budget et ta sécurité.</p>

    <?php if ($flash) { ?>
        <div class="mt-5 rounded-xl p-3.5 text-sm <?php if ($flash['type'] == 'erreur') { echo 'bg-danger/10 text-danger border border-danger/30'; } else { echo 'bg-succes/10 text-succes border border-succes/30'; } ?>">
            <?php echo texte($flash['message']); ?>
        </div>
    <?php } ?>


    <!-- Sur grand écran : 3 colonnes qui remplissent la hauteur. Sinon les cartes s'enchaînent sur 2 colonnes. -->
    <div class="cpt-grille mt-6 grid gap-[18px] lg:grid-cols-2">

        <div class="cpt-col">
        <!-- Carte d'identité -->
        <div class="cpt-carte cpt-ligne cpt-identite lg:col-span-2">
            <!-- Photo de profil : un clic sur l'avatar ouvre le choix d'image, l'envoi est automatique -->
            <form method="post" action="controller/compteController.php" enctype="multipart/form-data" id="form-photo">
                <input type="hidden" name="action" value="photo">
                <input type="file" name="photo" id="champ-photo" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
                <label for="champ-photo" class="cpt-photo" title="Changer la photo de profil">
                    <?php if ($moi['photo']) { ?>
                        <img src="<?php echo texte($moi['photo']); ?>" alt="Photo de profil">
                    <?php } else { ?>
                        <span><?php echo texte($initiales); ?></span>
                    <?php } ?>
                    <span class="cpt-photo-icone">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.5"/></svg>
                    </span>
                </label>
            </form>
            <div class="min-w-0 flex-1">
                <div class="truncate text-lg font-bold"><?php echo texte($moi['prenom'] . ' ' . $moi['nom']); ?></div>
                <div class="truncate text-sm text-texte-2"><?php echo texte($moi['email']); ?></div>
                <div class="mt-1 text-xs text-texte-3">
                    <?php echo texte($moi['etablissement']); ?> · Membre depuis <?php echo $membreDepuis; ?>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <label for="champ-photo" class="cpt-ok cpt-petit"><?php if ($moi['photo']) { echo 'Changer la photo'; } else { echo 'Ajouter une photo'; } ?></label>
                    <?php if ($moi['photo']) { ?>
                        <form method="post" action="controller/compteController.php">
                            <input type="hidden" name="action" value="retirer_photo">
                            <button class="cpt-gris cpt-petit">Retirer</button>
                        </form>
                    <?php } ?>
                    <span class="text-xs text-texte-3" id="info-photo">JPG, PNG, WebP ou GIF · 5 Mo max.</span>
                </div>
            </div>
        </div>

            <!-- Profil -->
            <form method="post" action="controller/compteController.php" class="cpt-carte cpt-etire">
                <input type="hidden" name="action" value="profil">
                <h2>Profil</h2>
                <div class="grid grid-cols-2 gap-3">
                    <label>Prénom
                        <input type="text" name="prenom" required maxlength="255" autocomplete="given-name" value="<?php echo texte($moi['prenom']); ?>">
                    </label>
                    <label>Nom
                        <input type="text" name="nom" required maxlength="255" autocomplete="family-name" value="<?php echo texte($moi['nom']); ?>">
                    </label>
                </div>
                <label>E-mail
                    <input type="email" name="email" required maxlength="255" autocomplete="email" value="<?php echo texte($moi['email']); ?>">
                </label>
                <div class="cpt-bas"><button class="cpt-ok">Enregistrer</button></div>
            </form>

            <!-- Zone sensible -->
            <div class="cpt-carte cpt-danger lg:col-span-2">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex-1">
                        <h2>Supprimer mon compte</h2>
                        <p class="cpt-aide" style="margin-bottom:0">Tes dépenses, votes et groupes suivis seront effacés définitivement.</p>
                    </div>
                    <button type="button" data-ouvrir="fenetre-supprimer" class="cpt-rouge">Supprimer le compte</button>
                </div>
            </div>
        </div>

        <div class="cpt-col">
            <!-- Établissement -->
            <form method="post" action="controller/compteController.php" class="cpt-carte">
                <input type="hidden" name="action" value="etablissement">
                <h2>Établissement</h2>
                <p class="cpt-aide">Les restos proches et la carte sont calculés autour de ton campus.</p>
                <label>Nom de l'établissement
                    <input type="text" name="etablissement" required maxlength="255" value="<?php echo texte($moi['etablissement']); ?>">
                </label>
                <?php if ($campus) { ?>
                    <p class="cpt-info">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                        Localisé à <?php echo texte($campus['ville']); ?>
                    </p>
                <?php } else { ?>
                    <p class="cpt-info text-danger">Adresse introuvable : essaie un nom plus précis.</p>
                <?php } ?>
                <div class="cpt-bas"><button class="cpt-ok">Changer de campus</button></div>
            </form>

            <!-- Budget -->
            <form method="post" action="controller/compteController.php" class="cpt-carte">
                <input type="hidden" name="action" value="budget">
                <h2>Budget mensuel</h2>
                <p class="cpt-aide">Ce que tu veux dépenser en resto chaque mois. Laisse vide pour ne pas suivre de budget.</p>
                <label>Montant (€)
                    <input type="text" name="montant" inputmode="decimal" maxlength="10" placeholder="180" value="<?php echo texte($budgetActuel); ?>">
                </label>
                <div class="cpt-bas">
                    <a href="index.php?page=budget" class="cpt-lien">Voir mon budget</a>
                    <button class="cpt-ok">Enregistrer</button>
                </div>
            </form>
        </div>

        <div class="cpt-col">
            <!-- Préférences alimentaires -->
            <form method="post" action="controller/compteController.php" class="cpt-carte">
                <input type="hidden" name="action" value="regimes">
                <h2>Préférences alimentaires</h2>
                <p class="cpt-aide">Coche ce qui te correspond.</p>
                <div class="cpt-puces">
                    <?php foreach ($regimes as $regime) { ?>
                        <label class="cpt-puce">
                            <input type="checkbox" name="regimes[]" value="<?php echo (int) $regime['id']; ?>" <?php if (in_array((int) $regime['id'], $mesRegimes)) { echo 'checked'; } ?>>
                            <span><?php echo texte($regime['libelle']); ?></span>
                        </label>
                    <?php } ?>
                </div>
                <div class="cpt-bas"><button class="cpt-ok">Enregistrer</button></div>
            </form>

            <!-- Mot de passe -->
            <form method="post" action="controller/compteController.php" class="cpt-carte cpt-mdp lg:col-span-2">
                <input type="hidden" name="action" value="mdp">
                <h2>Mot de passe</h2>
                <div class="cpt-mdp-champs grid gap-3 sm:grid-cols-3">
                    <label>Mot de passe actuel
                        <input type="password" name="mdp_actuel" required autocomplete="current-password">
                    </label>
                    <label>Nouveau mot de passe
                        <input type="password" name="mdp" required minlength="8" autocomplete="new-password" placeholder="8 caractères min.">
                    </label>
                    <label>Confirmation
                        <input type="password" name="mdp_confirm" required minlength="8" autocomplete="new-password">
                    </label>
                </div>
                <div class="cpt-bas"><button class="cpt-ok">Changer le mot de passe</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Fenêtre : confirmer la suppression -->
<dialog id="fenetre-supprimer" class="cpt-fenetre">
    <form method="post" action="controller/compteController.php">
        <input type="hidden" name="action" value="supprimer">
        <h2>Supprimer ton compte ?</h2>
        <p>Cette action est définitive. Toutes tes données seront effacées.</p>
        <label>Ton mot de passe
            <input type="password" name="mdp_actuel" required autocomplete="current-password">
        </label>
        <label>Tape SUPPRIMER pour confirmer
            <input type="text" name="confirmation" required autocomplete="off" pattern="SUPPRIMER" placeholder="SUPPRIMER">
        </label>
        <div class="cpt-btns">
            <button type="button" data-fermer>Annuler</button>
            <button class="rouge">Supprimer définitivement</button>
        </div>
    </form>
</dialog>

<script src="public/asset/js/compte.js?v=2"></script>
