<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graille</title>
    <link rel="stylesheet" href="public/asset/css/output.css">
</head>
<body class="bg-fond text-texte font-sans min-h-screen <?= isset($_SESSION['user_id']) ? 'lg:flex' : '' ?>">

<?php if (isset($_SESSION['user_id'])): ?>
    <?php include __DIR__ . '/../../controller/menuController.php'; ?>
    <!-- Utilisateur connecté : barre latérale -->
    <aside class="bg-surface-1 border-b lg:border-b-0 lg:border-r border-white/10 sticky top-0 z-50 lg:h-screen lg:w-62 shrink-0 flex lg:flex-col items-center lg:items-stretch gap-1 px-4 py-3 lg:px-3.5 lg:py-5.5">

        <a href="index.php?page=dashboard" class="flex items-center gap-2.5 px-2 lg:mb-5 mr-auto lg:mr-0">
            <span class="w-9 h-9 rounded-[10px] bg-linear-to-br from-accent-2 to-[#e8461b] shadow-lg shadow-accent/30 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>
                </svg>
            </span>
            <span class="text-xl font-bold tracking-tight">Graille</span>
        </a>

        <nav class="flex lg:flex-col gap-1 overflow-x-auto">
            <a href="index.php?page=dashboard" class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition-colors <?= $page === 'dashboard' ? 'bg-accent/15 text-accent-2' : 'text-texte-2 hover:bg-white/5 hover:text-texte' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M3.5 10.5 12 3.5l8.5 7V19a1.5 1.5 0 0 1-1.5 1.5h-4.5v-6h-5v6H5A1.5 1.5 0 0 1 3.5 19z"/>
                </svg>
                <span class="hidden sm:inline">Accueil</span>
            </a>
            <a href="index.php?page=explorer" class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition-colors <?= $page === 'explorer' ? 'bg-accent/15 text-accent-2' : 'text-texte-2 hover:bg-white/5 hover:text-texte' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/><path d="m15.8 8.2-2.3 5.3-5.3 2.3 2.3-5.3z"/>
                </svg>
                <span class="hidden sm:inline">Explorer</span>
            </a>
            <a href="index.php?page=roulette" class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition-colors <?= $page === 'roulette' ? 'bg-accent/15 text-accent-2' : 'text-texte-2 hover:bg-white/5 hover:text-texte' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="2.4"/><path d="M12 3v6.6M12 14.4V21M3 12h6.6M14.4 12H21M5.6 5.6l4.7 4.7M13.7 13.7l4.7 4.7"/>
                </svg>
                <span class="hidden sm:inline">Roulette</span>
            </a>
            <a href="index.php?page=groupes" class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition-colors <?= $page === 'groupes' ? 'bg-accent/15 text-accent-2' : 'text-texte-2 hover:bg-white/5 hover:text-texte' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.6 3.3-5.5 6.5-5.5s5.9 1.9 6.5 5.5M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14.8c2 .7 3.2 2.5 3.5 5.2"/>
                </svg>
                <span class="hidden sm:inline">Groupes</span>
            </a>
            <a href="index.php?page=budget" class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition-colors <?= $page === 'budget' ? 'bg-accent/15 text-accent-2' : 'text-texte-2 hover:bg-white/5 hover:text-texte' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <rect x="3" y="6" width="18" height="14" rx="3"/><path d="M3 10h18M16 15h2M6 6l9-3 1.5 3"/>
                </svg>
                <span class="hidden sm:inline">Budget</span>
            </a>
        </nav>

        <!-- Restant ce mois (budget du mois en cours) -->
        <a href="index.php?page=budget" class="hidden lg:block mt-auto mb-3 rounded-2xl border border-white/10 bg-surface-2/60 p-3.5 hover:bg-surface-2 transition-colors">
            <?php if ($menuBudget > 0): ?>
                <span class="block text-xs text-texte-2">Restant ce mois</span>
                <span class="mt-0.5 block text-[22px] font-bold tracking-tight tabular-nums <?= $menuRestant < 0 ? 'text-danger' : 'text-texte' ?>"><?= menuEuros($menuRestant) ?> €</span>
                <span class="mt-2.5 block h-1.5 overflow-hidden rounded-full bg-surface-3">
                    <span class="block h-full rounded-full <?= $menuRestant < 0 ? 'bg-danger' : 'bg-accent' ?>" style="width:<?= $menuPourcent ?>%"></span>
                </span>
                <span class="mt-2 block text-xs text-texte-3"><?= menuEuros($menuDepense) ?> € dépensés sur <?= menuEuros($menuBudget) ?> €</span>
            <?php else: ?>
                <span class="block text-xs text-texte-2">Budget du mois</span>
                <span class="mt-1 block text-sm font-semibold text-accent-2">Définir mon budget</span>
                <span class="mt-1 block text-xs text-texte-3"><?= menuEuros($menuDepense) ?> € dépensés ce mois</span>
            <?php endif ?>
        </a>

        <!-- Utilisateur : lien vers les paramètres du compte -->
        <div class="lg:pt-3 lg:border-t border-white/10">
            <a href="index.php?page=compte" title="Paramètres du compte" class="group flex items-center gap-2.5 rounded-xl px-2 py-2 transition-colors <?= $page === 'compte' ? 'bg-accent/15' : 'hover:bg-white/5' ?>">
                <?php if ($menuPhoto): ?>
                    <img src="<?= htmlspecialchars($menuPhoto) ?>" alt="" class="w-9 h-9 shrink-0 rounded-full object-cover">
                <?php else: ?>
                    <span class="w-9 h-9 shrink-0 rounded-full bg-linear-to-br from-[#bf5af2] to-[#5e5ce6] flex items-center justify-center text-xs font-semibold text-white">
                        <?= htmlspecialchars($menuInitiales) ?>
                    </span>
                <?php endif ?>
                <span class="hidden lg:block flex-1 min-w-0">
                    <span class="block truncate text-sm font-semibold <?= $page === 'compte' ? 'text-accent-2' : 'text-texte' ?>"><?= htmlspecialchars($menuNom) ?></span>
                    <span class="block truncate text-xs text-texte-3"><?= htmlspecialchars($menuEtablissement) ?></span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="hidden lg:block h-[18px] w-[18px] shrink-0 <?= $page === 'compte' ? 'text-accent-2' : 'text-texte-3 group-hover:text-texte-2' ?>" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>
                </svg>
            </a>
        </div>
        <a href="index.php?page=deconnexion" class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium text-texte-2 hover:bg-white/5 hover:text-danger transition-colors lg:mt-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 16l-4-4 4-4M6 12h10"/>
            </svg>
            <span class="hidden sm:inline">Déconnexion</span>
        </a>
    </aside>

    <!-- Conteneur principal -->
    <main class="flex-1 min-w-0 px-6 py-10 lg:px-8">

<?php elseif ($page === 'connexion' || $page === 'inscription'): ?>
    <!-- Connexion / inscription : page plein écran, le logo est dans la vue -->
    <main>

<?php else: ?>
    <!-- Visiteur : pas de barre latérale, juste le logo -->
    <header class="max-w-5xl mx-auto px-6 pt-8 flex items-center justify-between">
        <a href="index.php?page=index" class="flex items-center gap-2.5">
            <span class="w-9 h-9 rounded-[10px] bg-linear-to-br from-accent-2 to-[#e8461b] shadow-lg shadow-accent/30 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>
                </svg>
            </span>
            <span class="text-xl font-bold tracking-tight">Graille</span>
        </a>
        <div class="flex items-center gap-2">
            <a href="index.php?page=connexion" class="rounded-xl px-4 py-2 font-medium text-texte-2 hover:text-texte transition-colors">Connexion</a>
            <a href="index.php?page=inscription" class="rounded-xl px-4 py-2 font-semibold bg-accent text-white hover:bg-accent-2 transition-colors">S'inscrire</a>
        </div>
    </header>

    <!-- Conteneur principal -->
    <main class="px-6 py-8">
<?php endif ?>
