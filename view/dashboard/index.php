<?php
if (!isset($_SESSION['user_id'])) { header('Location: index.php?page=connexion'); exit; }
require_once __DIR__ . '/../../private/bdd.php';
require_once __DIR__ . '/../../model/utilisateur.php';
require_once __DIR__ . '/../../model/DepenseModel.php';

$utilisateurModel = new utilisateur($bdd);
$utilisateur = $utilisateurModel->trouverParId((int) $_SESSION['user_id']);
if (!$utilisateur) { session_destroy(); header('Location: index.php?page=connexion'); exit; }
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);

// Budget du mois
$budget        = (float) ($utilisateur['budget_mensuel'] ?? 0);
$depense       = (new DepenseModel($bdd))->totalDuMois((int) $utilisateur['id']);
$restant       = max(0, $budget - $depense);
$joursRestants = (int) date('t') - (int) date('j') + 1;
$parJour       = $restant / $joursRestants;
// Anneau : part du budget qui reste (périmètre du cercle = 276.5)
$anneau        = $budget > 0 ? 276.5 * (1 - $restant / $budget) : 276.5;

// Date du jour en français
$jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
$mois  = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$aujourdhui = $jours[(int) date('w')] . ' ' . date('j') . ' ' . $mois[(int) date('n')];

// Restaurants : données d'exemple en attendant la table restaurant et l'API Google Maps
$restos = [
    ['nom' => 'Bao Bar',         'court' => 'Bao',  'temps' => '4 min', 'prix' => '7–11 €', 'couleur' => 'from-[#FF9A5A] to-[#C2410C]'],
    ['nom' => 'Pita Lab',        'court' => 'Pita', 'temps' => '6 min', 'prix' => '6–9 €',  'couleur' => 'from-[#F6C453] to-[#B45309]'],
    ['nom' => 'Resto U Censier', 'court' => 'RU',   'temps' => '3 min', 'prix' => '3,30 €', 'couleur' => 'from-[#C4B5FD] to-[#6D28D9]'],
];
?>

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

<?php if ($flash): ?>
    <div class="mb-6 rounded-xl p-3.5 text-sm <?= $flash['type'] === 'erreur' ? 'bg-danger/10 text-danger border border-danger/30' : 'bg-succes/10 text-succes border border-succes/30' ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<!-- Salutation + actions -->
<div class="flex flex-col sm:flex-row sm:items-end gap-4">
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-texte-2"><?= $aujourdhui ?></p>
        <h1 class="text-[32px] font-bold tracking-tight leading-tight">Salut <?= htmlspecialchars($utilisateur['prenom']) ?></h1>
    </div>
    <div class="flex gap-2.5 sm:ml-auto">
        <a href="index.php?page=budget" class="flex flex-1 sm:flex-none justify-center items-center gap-2 h-11 rounded-[13px] bg-surface-2 px-3 sm:px-4 whitespace-nowrap text-sm sm:text-[15px] font-semibold hover:bg-surface-3 transition-colors">
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/></svg>
            <span class="sm:hidden">Dépense</span><span class="hidden sm:inline">Ajouter une dépense</span>
        </a>
        <a href="index.php?page=roulette" class="flex flex-1 sm:flex-none justify-center items-center gap-2 h-11 rounded-[13px] px-3 sm:px-4 whitespace-nowrap text-sm sm:text-[15px] font-semibold text-white bg-linear-to-b from-[#ff7a4d] to-[#f2582a] shadow-[0_10px_24px_-10px_rgba(255,107,61,.7)] hover:brightness-110 transition">
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="2.4"/><path d="M12 3v6.6M12 14.4V21M3 12h6.6M14.4 12H21M5.6 5.6l4.7 4.7M13.7 13.7l4.7 4.7"/></svg>
            <span class="sm:hidden">Roulette</span><span class="hidden sm:inline">Lancer la roulette</span>
        </a>
    </div>
</div>

<div class="grid gap-[18px] mt-5 lg:grid-cols-3">

    <!-- Carte budget -->
    <div class="rounded-[20px] border border-white/10 bg-linear-to-br from-[#1d1512] to-surface-1 to-60% p-5 flex items-center gap-5">
        <?php if ($budget > 0): ?>
            <svg width="108" height="108" viewBox="0 0 104 104" class="shrink-0">
                <defs><linearGradient id="anneau" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FFB38A"/><stop offset="1" stop-color="#F2582A"/></linearGradient></defs>
                <circle cx="52" cy="52" r="44" fill="none" stroke="#2A2A31" stroke-width="11"/>
                <circle cx="52" cy="52" r="44" fill="none" stroke="url(#anneau)" stroke-width="11" stroke-linecap="round" stroke-dasharray="276.5" stroke-dashoffset="<?= round($anneau, 1) ?>" transform="rotate(-90 52 52)"/>
                <text x="52" y="50" text-anchor="middle" fill="#F5F5F7" font-size="20" font-weight="700"><?= number_format($restant, 0, ',', ' ') ?> €</text>
                <text x="52" y="67" text-anchor="middle" fill="rgba(235,235,245,.6)" font-size="11">restants</text>
            </svg>
            <div class="flex flex-col gap-2">
                <div>
                    <div class="text-xs text-texte-2">Dépensé</div>
                    <div class="font-semibold"><?= number_format($depense, 2, ',', ' ') ?> € <span class="text-sm font-normal text-texte-3">/ <?= number_format($budget, 0, ',', ' ') ?> €</span></div>
                </div>
                <div>
                    <div class="text-xs text-texte-2">Tu peux dépenser</div>
                    <div class="font-semibold <?= $parJour > 0 ? 'text-succes' : 'text-danger' ?>"><?= number_format($parJour, 2, ',', ' ') ?> € / jour</div>
                </div>
                <div class="text-xs text-texte-3"><?= $joursRestants ?> jour<?= $joursRestants > 1 ? 's' : '' ?> restant<?= $joursRestants > 1 ? 's' : '' ?></div>
            </div>
        <?php else: ?>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-texte-2">Budget du mois</div>
                <p class="mt-2 text-texte-2 text-sm">Tu n'as pas encore défini ton budget mensuel.</p>
                <a href="index.php?page=budget" class="inline-flex mt-4 h-10 items-center rounded-xl bg-accent/15 px-4 text-sm font-semibold text-accent-2 hover:bg-accent/25 transition-colors">Définir mon budget</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Carte des restos (aperçu en attendant Google Maps) -->
    <div class="relative min-h-[440px] overflow-hidden rounded-[20px] border border-white/10 bg-[#15171c] lg:col-span-2 lg:row-span-3">
        <svg class="absolute inset-0 h-full w-full" viewBox="0 0 700 460" preserveAspectRatio="none">
            <rect x="420" y="40" width="200" height="110" rx="10" fill="#1b2a20"/>
            <rect x="40" y="300" width="140" height="90" rx="10" fill="#1b2a20"/>
            <path d="M-10 230 C200 200 450 270 710 220" stroke="#2a2d35" stroke-width="20" fill="none"/>
            <path d="M260 -10 L300 470" stroke="#2a2d35" stroke-width="16" fill="none"/>
            <path d="M-10 380 L710 330" stroke="#24272e" stroke-width="10" fill="none"/>
            <path d="M560 -10 L500 470" stroke="#24272e" stroke-width="10" fill="none"/>
            <path d="M-10 110 L710 140" stroke="#212329" stroke-width="7" fill="none"/>
            <path d="M-10 300 Q350 330 710 290" stroke="#1d2a3a" stroke-width="26" fill="none"/>
        </svg>

        <div class="absolute left-4 top-4 z-10 flex gap-2">
            <span class="flex h-8 items-center gap-1.5 rounded-full bg-accent px-3 text-[13px] font-medium text-white">Halal</span>
            <span class="flex h-8 items-center rounded-full bg-surface-2/85 px-3 text-[13px] font-medium">≤ 10 €</span>
            <span class="flex h-8 items-center rounded-full bg-surface-2/85 px-3 text-[13px] font-medium">Ouvert</span>
        </div>

        <!-- Position et campus -->
        <span class="absolute left-[38%] top-[57%] z-10 -translate-x-1/2 -translate-y-1/2 flex items-center gap-1.5 whitespace-nowrap rounded-lg bg-blue-500/90 px-2 py-1 text-[11.5px] font-bold text-white"><?= htmlspecialchars($utilisateur['etablissement']) ?></span>
        <span class="absolute left-[44%] top-[49%] z-10 h-[18px] w-[18px] -translate-x-1/2 -translate-y-1/2 rounded-full border-[3px] border-white bg-blue-500 shadow-[0_0_0_10px_rgba(10,132,255,.2),0_0_0_22px_rgba(10,132,255,.08)]"></span>

        <!-- Restaurants -->
        <span class="absolute left-[63%] top-[36%] z-10 -translate-x-1/2 -translate-y-full whitespace-nowrap rounded-[10px] bg-accent px-2.5 py-1 text-[12.5px] font-bold text-white shadow-lg">Bao Bar · 8,90 €</span>
        <span class="absolute left-[22%] top-[75%] z-10 -translate-x-1/2 -translate-y-full rounded-[10px] border border-white/15 bg-surface-2 px-2.5 py-1 text-[12.5px] font-bold shadow-lg">6 €</span>
        <span class="absolute left-[84%] top-[58%] z-10 -translate-x-1/2 -translate-y-full rounded-[10px] border border-white/15 bg-surface-2 px-2.5 py-1 text-[12.5px] font-bold shadow-lg">11 €</span>
        <span class="absolute left-[17%] top-[32%] z-10 -translate-x-1/2 -translate-y-full rounded-[10px] border border-white/15 bg-surface-2 px-2.5 py-1 text-[12.5px] font-bold shadow-lg">3,30 €</span>
        <span class="absolute left-[60%] top-[86%] z-10 -translate-x-1/2 -translate-y-full rounded-[10px] border border-white/15 bg-surface-2 px-2.5 py-1 text-[12.5px] font-bold shadow-lg">9 €</span>

        <div class="absolute right-4 top-4 z-10 flex flex-col overflow-hidden rounded-xl border border-white/15 bg-surface-2/85 backdrop-blur">
            <span class="grid h-9 w-9 place-items-center text-lg">+</span>
            <span class="grid h-9 w-9 place-items-center border-t border-white/10 text-lg">−</span>
        </div>
    </div>

    <!-- Restos près du campus -->
    <div class="rounded-[20px] border border-white/10 bg-surface-1 py-1.5">
        <div class="flex items-center justify-between px-4 pb-1.5 pt-2.5">
            <h2 class="font-bold tracking-tight">Près du campus</h2>
            <a href="index.php?page=explorer" class="text-sm font-medium text-accent">Tout voir</a>
        </div>
        <?php foreach ($restos as $i => $resto): ?>
            <a href="index.php?page=explorer" class="relative flex items-center gap-3 px-4 py-2.5 hover:bg-white/5 transition-colors">
                <?php if ($i > 0): ?><span class="absolute left-4 right-0 top-0 h-px bg-white/10"></span><?php endif; ?>
                <span class="grid h-[42px] w-[42px] shrink-0 place-items-center rounded-xl bg-linear-to-br <?= $resto['couleur'] ?> text-xs font-extrabold"><?= $resto['court'] ?></span>
                <span class="flex-1">
                    <span class="block font-semibold"><?= $resto['nom'] ?></span>
                    <span class="block text-xs text-texte-2"><?= $resto['temps'] ?> · <?= $resto['prix'] ?></span>
                </span>
                <svg class="h-4 w-4 text-texte-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Vote de groupe (aperçu en attendant la table groupe) -->
    <div class="rounded-[20px] border border-white/10 bg-surface-1 p-4">
        <div class="flex items-center justify-between">
            <h2 class="font-bold tracking-tight">Vote · BTS SIO 2</h2>
            <span class="text-sm font-semibold text-accent-2">00:42</span>
        </div>
        <div class="mt-3 space-y-2.5 text-sm">
            <div>
                <div class="flex justify-between"><span>Bao Bar</span><b>2</b></div>
                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-surface-3"><div class="h-full w-1/2 rounded-full bg-accent"></div></div>
            </div>
            <div>
                <div class="flex justify-between"><span>Pita Lab</span><b>2</b></div>
                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-surface-3"><div class="h-full w-1/2 rounded-full bg-texte-2"></div></div>
            </div>
        </div>
        <a href="index.php?page=roulette" class="mt-3.5 flex h-[38px] items-center justify-center gap-2 rounded-xl bg-alerte text-sm font-semibold text-black hover:brightness-105 transition">
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="2.4"/><path d="M12 3v6.6M12 14.4V21M3 12h6.6M14.4 12H21M5.6 5.6l4.7 4.7M13.7 13.7l4.7 4.7"/></svg>
            Départager à la roulette
        </a>
    </div>
</div>
