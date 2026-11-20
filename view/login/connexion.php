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
            <h2 class="text-5xl font-extrabold tracking-tight leading-[1.03] max-w-xl">Où manger près du campus, sans exploser ton budget.</h2>
            <p class="text-lg text-texte-2 leading-relaxed mt-4 max-w-md">Les restos autour de ton établissement, filtrés selon ton régime et ce qu'il te reste à dépenser.</p>
        </div>

        <!-- Aperçus de l'application -->
        <div class="relative mt-10 flex gap-4">
            <div class="w-64 flex items-center gap-3.5 rounded-2xl border border-white/15 bg-surface-1/70 backdrop-blur-xl p-4">
                <svg width="60" height="60" viewBox="0 0 104 104" class="shrink-0">
                    <circle cx="52" cy="52" r="44" fill="none" stroke="#2A2A31" stroke-width="12"/>
                    <circle cx="52" cy="52" r="44" fill="none" stroke="#FF6B3D" stroke-width="12" stroke-linecap="round" stroke-dasharray="276.5" stroke-dashoffset="104" transform="rotate(-90 52 52)"/>
                </svg>
                <div>
                    <div class="text-xs text-texte-2">Restant ce mois</div>
                    <div class="text-2xl font-bold">112 €</div>
                    <div class="text-xs text-succes">8,60 € / jour</div>
                </div>
            </div>
            <div class="w-60 mt-9 flex items-center gap-3 rounded-2xl border border-white/15 bg-surface-1/70 backdrop-blur-xl p-3">
                <span class="w-13 h-13 rounded-xl bg-linear-to-br from-[#FF9A5A] to-[#C2410C] flex items-center justify-center text-sm font-extrabold">Bao</span>
                <div>
                    <div class="font-semibold">Bao Bar</div>
                    <div class="text-xs text-texte-2">4 min · menu 8,90 €</div>
                </div>
            </div>
        </div>

        <div class="relative mt-auto flex gap-7 text-sm text-texte-2">
            <span class="flex items-center gap-2"><svg class="h-4.5 w-4.5 text-accent" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>Géolocalisation</span>
            <span class="flex items-center gap-2"><svg class="h-4.5 w-4.5 text-accent" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="14" rx="3"/><path d="M3 10h18M16 15h2M6 6l9-3 1.5 3"/></svg>Budget maîtrisé</span>
            <span class="flex items-center gap-2"><svg class="h-4.5 w-4.5 text-accent" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.6 3.3-5.5 6.5-5.5s5.9 1.9 6.5 5.5M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14.8c2 .7 3.2 2.5 3.5 5.2"/></svg>Votes entre amis</span>
        </div>
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

            <h1 class="text-[32px] font-bold tracking-tight leading-tight">Content de te revoir</h1>
            <p class="text-texte-2 mt-1.5">Connecte-toi avec ton e-mail étudiant.</p>

            <div class="flex bg-surface-2 rounded-[10px] p-0.5 mt-7 text-sm font-medium">
                <a href="index.php?page=connexion" class="flex-1 text-center py-1.5 rounded-lg bg-surface-3 shadow">Connexion</a>
                <a href="index.php?page=inscription" class="flex-1 text-center py-1.5 rounded-lg text-texte-2 hover:text-texte">Inscription</a>
            </div>

            <?php if ($flash): ?>
                <div class="mt-5 rounded-xl p-3 text-sm <?= $flash['type'] === 'erreur' ? 'bg-danger/10 text-danger border border-danger/30' : 'bg-succes/10 text-succes border border-succes/30' ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form action="controller/utilisateurController.php" method="POST" class="mt-4">
                <!-- Champs groupés façon iOS -->
                <div class="rounded-[14px] bg-surface-1 border border-white/10 overflow-hidden focus-within:border-accent/60 transition-colors">
                    <label class="flex items-center gap-3 px-4 min-h-12">
                        <span class="w-28 shrink-0">E-mail</span>
                        <input type="email" name="email" required autocomplete="email" placeholder="prenom.nom@etu.univ.fr"
                            value="<?= htmlspecialchars($ancien['email'] ?? '') ?>"
                            class="flex-1 min-w-0 bg-transparent py-3 outline-none placeholder:text-texte-3">
                    </label>
                    <label class="flex items-center gap-3 px-4 min-h-12 border-t border-white/10 ml-4 pl-0">
                        <span class="w-28 shrink-0">Mot de passe</span>
                        <input type="password" name="mdp" required autocomplete="current-password" placeholder="Obligatoire"
                            class="flex-1 min-w-0 bg-transparent py-3 outline-none placeholder:text-texte-3">
                    </label>
                </div>

                <input type="hidden" value="login" name="action">
                <input type="submit" value="Se connecter" name="login"
                    class="w-full mt-6 h-[50px] cursor-pointer rounded-2xl bg-linear-to-b from-[#ff7a4d] to-[#f2582a] font-semibold text-white shadow-[0_10px_24px_-10px_rgba(255,107,61,.7),inset_0_1px_0_rgba(255,255,255,.25)] hover:brightness-110 transition">
            </form>

            <p class="text-sm text-texte-2 text-center mt-5">
                Pas encore de compte ?
                <a href="index.php?page=inscription" class="text-accent font-medium hover:underline">Inscris-toi</a>
            </p>

            <p class="text-xs text-texte-3 text-center leading-relaxed mt-14">
                Projet soutenu par le Ministère de la Santé.<br>
                En continuant, tu acceptes les conditions d'utilisation.
            </p>
        </div>
    </section>
</div>
