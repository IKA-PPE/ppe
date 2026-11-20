<?php
if (isset($_SESSION['user_id'])) { header('Location: index.php?page=dashboard'); exit; }
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$ancien = $_SESSION['ancien'] ?? []; unset($_SESSION['ancien']);
?>
<div class="min-h-screen grid lg:grid-cols-[1.15fr_1fr]">

    <!-- Panneau de marque -->
    <section class="relative hidden lg:flex flex-col overflow-hidden border-r border-white/10 px-14 py-10 bg-[#050506]">
        <div class="absolute inset-0 bg-[radial-gradient(60%_50%_at_75%_30%,rgba(255,107,61,.45),transparent_70%),radial-gradient(50%_45%_at_10%_80%,rgba(191,90,242,.25),transparent_70%)]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(255,255,255,.025)_1px,transparent_1px),linear-gradient(0deg,rgba(255,255,255,.025)_1px,transparent_1px)] bg-size-[64px_64px]"></div>

        <a href="index.php?page=index" class="relative flex items-center gap-2.5">
            <span class="w-9 h-9 rounded-[10px] bg-linear-to-br from-accent-2 to-[#e8461b] shadow-lg shadow-accent/30 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
            </span>
            <span class="text-xl font-bold tracking-tight">Graille</span>
        </a>

        <div class="relative mt-20">
            <h2 class="text-5xl font-extrabold tracking-tight leading-[1.03] max-w-xl">Rejoins Graille en moins d'une minute.</h2>
            <p class="text-lg text-texte-2 leading-relaxed mt-4 max-w-md">Ensuite, tu choisis ton budget du mois et ton régime alimentaire : on s'occupe de trouver où manger.</p>
        </div>

        <ol class="relative mt-auto space-y-4 text-texte-2">
            <li class="flex items-center gap-3"><span class="w-7 h-7 rounded-full bg-accent text-white text-sm font-bold flex items-center justify-center">1</span>Crée ton compte</li>
            <li class="flex items-center gap-3"><span class="w-7 h-7 rounded-full bg-surface-3 text-sm font-bold flex items-center justify-center">2</span>Indique ton budget et ton régime</li>
            <li class="flex items-center gap-3"><span class="w-7 h-7 rounded-full bg-surface-3 text-sm font-bold flex items-center justify-center">3</span>Trouve un resto près du campus</li>
        </ol>
    </section>

    <!-- Formulaire -->
    <section class="flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-sm">

            <a href="index.php?page=index" class="lg:hidden flex items-center gap-2.5 mb-10">
                <span class="w-9 h-9 rounded-[10px] bg-linear-to-br from-accent-2 to-[#e8461b] flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                </span>
                <span class="text-xl font-bold tracking-tight">Graille</span>
            </a>

            <h1 class="text-[32px] font-bold tracking-tight leading-tight">Créer un compte</h1>
            <p class="text-texte-2 mt-1.5">Utilise ton e-mail étudiant.</p>

            <div class="flex bg-surface-2 rounded-[10px] p-0.5 mt-7 text-sm font-medium">
                <a href="index.php?page=connexion" class="flex-1 text-center py-1.5 rounded-lg text-texte-2 hover:text-texte">Connexion</a>
                <a href="index.php?page=inscription" class="flex-1 text-center py-1.5 rounded-lg bg-surface-3 shadow">Inscription</a>
            </div>

            <?php if ($flash): ?>
                <div class="mt-5 rounded-xl p-3 text-sm <?= $flash['type'] === 'erreur' ? 'bg-danger/10 text-danger border border-danger/30' : 'bg-succes/10 text-succes border border-succes/30' ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form action="controller/utilisateurController.php" method="POST" class="mt-4">
                <!-- Identité -->
                <div class="rounded-[14px] bg-surface-1 border border-white/10 overflow-hidden focus-within:border-accent/60 transition-colors">
                    <label class="flex items-center gap-3 px-4 min-h-12">
                        <span class="w-28 shrink-0">Prénom</span>
                        <input type="text" name="prenom" required autocomplete="given-name" placeholder="Amina"
                            value="<?= htmlspecialchars($ancien['prenom'] ?? '') ?>"
                            class="flex-1 min-w-0 bg-transparent py-3 outline-none placeholder:text-texte-3">
                    </label>
                    <label class="flex items-center gap-3 min-h-12 border-t border-white/10 ml-4 pr-4">
                        <span class="w-28 shrink-0">Nom</span>
                        <input type="text" name="nom" required autocomplete="family-name" placeholder="Koné"
                            value="<?= htmlspecialchars($ancien['nom'] ?? '') ?>"
                            class="flex-1 min-w-0 bg-transparent py-3 outline-none placeholder:text-texte-3">
                    </label>
                    <label class="flex items-center gap-3 min-h-12 border-t border-white/10 ml-4 pr-4">
                        <span class="w-28 shrink-0">E-mail</span>
                        <input type="email" name="email" required autocomplete="email" placeholder="prenom.nom@etu.univ.fr"
                            value="<?= htmlspecialchars($ancien['email'] ?? '') ?>"
                            class="flex-1 min-w-0 bg-transparent py-3 outline-none placeholder:text-texte-3">
                    </label>
                    <label class="flex items-center gap-3 min-h-12 border-t border-white/10 ml-4 pr-4">
                        <span class="w-28 shrink-0">Établissement</span>
                        <input type="text" name="etablissement" required autocomplete="organization" placeholder="IUT Paris Rives"
                            value="<?= htmlspecialchars($ancien['etablissement'] ?? '') ?>"
                            class="flex-1 min-w-0 bg-transparent py-3 outline-none placeholder:text-texte-3">
                    </label>
                </div>

                <!-- Mot de passe -->
                <div class="mt-4 rounded-[14px] bg-surface-1 border border-white/10 overflow-hidden focus-within:border-accent/60 transition-colors">
                    <label class="flex items-center gap-3 px-4 min-h-12">
                        <span class="w-28 shrink-0">Mot de passe</span>
                        <input type="password" name="mdp" required minlength="8" autocomplete="new-password" placeholder="8 caractères min."
                            class="flex-1 min-w-0 bg-transparent py-3 outline-none placeholder:text-texte-3">
                    </label>
                    <label class="flex items-center gap-3 min-h-12 border-t border-white/10 ml-4 pr-4">
                        <span class="w-28 shrink-0">Confirmation</span>
                        <input type="password" name="mdp_confirm" required minlength="8" autocomplete="new-password" placeholder="Retape-le"
                            class="flex-1 min-w-0 bg-transparent py-3 outline-none placeholder:text-texte-3">
                    </label>
                </div>

                <input type="hidden" value="inscrire" name="action">
                <input type="submit" value="Créer mon compte" name="inscrire"
                    class="w-full mt-6 h-[50px] cursor-pointer rounded-2xl bg-linear-to-b from-[#ff7a4d] to-[#f2582a] font-semibold text-white shadow-[0_10px_24px_-10px_rgba(255,107,61,.7),inset_0_1px_0_rgba(255,255,255,.25)] hover:brightness-110 transition">
            </form>

            <p class="text-sm text-texte-2 text-center mt-5">
                Tu as déjà un compte ?
                <a href="index.php?page=connexion" class="text-accent font-medium hover:underline">Connecte-toi</a>
            </p>

            <p class="text-xs text-texte-3 text-center leading-relaxed mt-14">
                Projet soutenu par le Ministère de la Santé.<br>
                En créant un compte, tu acceptes les conditions d'utilisation.
            </p>
        </div>
    </section>
</div>
