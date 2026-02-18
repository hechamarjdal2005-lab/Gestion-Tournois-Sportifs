<?php
session_start();
$pageTitle = 'Accueil - Système de Gestion des Tournois';
include 'includes/templates/header.php';
// ❌ navigation.php supprimée — pas de sidebar sur la page d'accueil

// Stats réelles depuis la DB
require_once 'includes/config/database.php';
try {
    $db = getDB();
    $nb_equipes  = $db->fetchColumn("SELECT COUNT(*) FROM equipe");
    $nb_tournois = $db->fetchColumn("SELECT COUNT(*) FROM tournoi WHERE statut = 'en_cours'");
    $nb_matches  = $db->fetchColumn("SELECT COUNT(*) FROM `match` WHERE statut = 'termine'");
    $nb_joueurs  = $db->fetchColumn("SELECT COUNT(*) FROM joueur");
} catch (Exception $e) {
    $nb_equipes = $nb_tournois = $nb_matches = $nb_joueurs = 0;
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap');

:root {
    --green:   #00e676;
    --dark:    #090910;
    --card:    #10101a;
    --border:  rgba(255,255,255,0.06);
    --text:    #e2e2f0;
    --muted:   #5a5a72;
}

body { margin: 0 !important; padding: 0 !important; }
.main-content, .content-wrapper, #content, #main { margin-left: 0 !important; padding: 0 !important; }

.page-wrapper * { box-sizing: border-box; }
.page-wrapper {
    background: var(--dark);
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    overflow-x: hidden;
    min-height: 100vh;
}

/* ── TOPBAR PUBLIC ── */
.public-topbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 7vw;
    height: 64px;
    background: rgba(9,9,16,0.90);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--border);
}

.topbar-logo {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 22px;
    color: #fff;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}
.topbar-logo i { color: var(--green); }

.topbar-links {
    display: flex;
    align-items: center;
    gap: 28px;
}
.topbar-links a {
    color: var(--muted);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: color 0.2s;
}
.topbar-links a:hover { color: var(--text); }

.topbar-btn {
    background: var(--green) !important;
    color: #000 !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    padding: 8px 20px !important;
    border-radius: 8px;
    text-decoration: none !important;
    transition: all 0.2s !important;
}
.topbar-btn:hover { background: #00ff96 !important; transform: translateY(-1px); }

/* ── HERO ── */
.hero-section {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 100px 7vw 60px;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 55% 60% at 75% 45%, rgba(0,230,118,0.09) 0%, transparent 65%),
        radial-gradient(ellipse 35% 50% at 15% 75%, rgba(30,80,255,0.07) 0%, transparent 60%);
    pointer-events: none;
}

.hero-rings {
    position: absolute;
    right: -8vw; top: 50%;
    transform: translateY(-50%);
    width: 52vw; height: 52vw;
    pointer-events: none;
}
.hero-rings span {
    position: absolute; inset: 0;
    border-radius: 50%;
    border: 1px solid rgba(0,230,118,0.10);
    animation: pulse-ring 4s ease-in-out infinite;
}
.hero-rings span:nth-child(2) { inset: 8%;  border-color: rgba(0,230,118,0.07); animation-delay: 0.8s; }
.hero-rings span:nth-child(3) { inset: 16%; border-color: rgba(0,230,118,0.04); animation-delay: 1.6s; }

.hero-bg-icon {
    position: absolute;
    right: 10vw; top: 50%;
    transform: translateY(-50%);
    font-size: clamp(160px, 20vw, 300px);
    color: rgba(0,230,118,0.04);
    pointer-events: none;
    line-height: 1;
}

@keyframes pulse-ring {
    0%, 100% { transform: scale(1); opacity: 1; }
    50%       { transform: scale(1.02); opacity: 0.5; }
}

.hero-content { position: relative; z-index: 2; max-width: 620px; }

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0,230,118,0.08);
    border: 1px solid rgba(0,230,118,0.20);
    color: var(--green);
    padding: 6px 16px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 28px;
    animation: fade-up 0.6s ease both;
}
.hero-badge i { font-size: 8px; }

.hero-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(56px, 8vw, 100px);
    line-height: 0.95;
    color: #fff;
    margin-bottom: 24px;
    animation: fade-up 0.6s 0.1s ease both;
}
.hero-title span { color: var(--green); display: block; }

.hero-desc {
    font-size: 17px;
    color: var(--muted);
    line-height: 1.75;
    font-weight: 300;
    margin-bottom: 40px;
    max-width: 480px;
    animation: fade-up 0.6s 0.2s ease both;
}

.hero-actions {
    display: flex; gap: 14px; flex-wrap: wrap;
    animation: fade-up 0.6s 0.3s ease both;
}

.btn-primary-hero {
    display: inline-flex; align-items: center; gap: 10px;
    background: var(--green); color: #000;
    font-weight: 600; font-size: 15px;
    padding: 14px 28px; border-radius: 10px;
    text-decoration: none; transition: all 0.25s;
}
.btn-primary-hero:hover {
    background: #00ff96; color: #000;
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(0,230,118,0.25);
}

.btn-ghost-hero {
    display: inline-flex; align-items: center; gap: 10px;
    background: transparent; color: var(--text);
    font-weight: 500; font-size: 15px;
    padding: 14px 28px; border-radius: 10px;
    text-decoration: none; border: 1px solid var(--border);
    transition: all 0.25s;
}
.btn-ghost-hero:hover {
    border-color: rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.04);
    color: #fff; transform: translateY(-2px);
}

/* ── STATS BAND ── */
.stats-band {
    background: var(--card);
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    padding: 36px 7vw;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    animation: fade-up 0.6s 0.4s ease both;
}
.stat-item {
    display: flex; flex-direction: column;
    align-items: center; gap: 4px;
    padding: 10px 0;
    border-right: 1px solid var(--border);
}
.stat-item:last-child { border-right: none; }
.stat-num { font-family: 'Bebas Neue', sans-serif; font-size: 44px; color: var(--green); line-height: 1; }
.stat-label { font-size: 12px; color: var(--muted); font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase; }

/* ── FEATURES ── */
.features-section { padding: 90px 7vw; }
.section-tag { font-size: 12px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--green); margin-bottom: 12px; }
.section-title { font-family: 'Bebas Neue', sans-serif; font-size: clamp(36px, 4vw, 56px); color: #fff; line-height: 1; margin-bottom: 50px; }

.features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }

.feature-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 16px; padding: 36px 32px;
    position: relative; overflow: hidden;
    transition: all 0.3s; text-decoration: none;
    color: inherit; display: block;
}
.feature-card::before {
    content: ''; position: absolute;
    top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, transparent, var(--green), transparent);
    opacity: 0; transition: opacity 0.3s;
}
.feature-card:hover { border-color: rgba(0,230,118,0.20); transform: translateY(-4px); box-shadow: 0 20px 48px rgba(0,0,0,0.4); color: inherit; }
.feature-card:hover::before { opacity: 1; }

.feature-icon { width: 52px; height: 52px; background: rgba(0,230,118,0.08); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; font-size: 20px; color: var(--green); transition: background 0.3s; }
.feature-card:hover .feature-icon { background: rgba(0,230,118,0.15); }
.feature-title { font-size: 20px; font-weight: 600; color: #fff; margin-bottom: 12px; }
.feature-desc  { font-size: 14px; color: var(--muted); line-height: 1.7; font-weight: 300; margin-bottom: 28px; }
.feature-link  { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: var(--green); transition: gap 0.2s; }
.feature-card:hover .feature-link { gap: 10px; }
.feature-num   { position: absolute; bottom: 16px; right: 20px; font-family: 'Bebas Neue', sans-serif; font-size: 72px; color: rgba(255,255,255,0.025); line-height: 1; pointer-events: none; }

/* ── CTA ── */
.cta-section {
    margin: 0 7vw 90px;
    background: linear-gradient(135deg, rgba(0,230,118,0.08) 0%, rgba(30,80,255,0.06) 100%);
    border: 1px solid rgba(0,230,118,0.15);
    border-radius: 20px; padding: 60px 48px;
    display: flex; align-items: center;
    justify-content: space-between; gap: 40px; flex-wrap: wrap;
}
.cta-text h2 { font-family: 'Bebas Neue', sans-serif; font-size: 42px; color: #fff; margin-bottom: 8px; }
.cta-text p  { font-size: 15px; color: var(--muted); font-weight: 300; margin: 0; }

/* ── ANIMATIONS ── */
@keyframes fade-up {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
.fade-up { animation: fade-up 0.6s ease both; }
.delay-1 { animation-delay: 0.1s; }
.delay-2 { animation-delay: 0.2s; }
.delay-3 { animation-delay: 0.3s; }

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
    .features-grid, .stats-band { grid-template-columns: repeat(2, 1fr); }
    .stat-item { border-right: none; border-bottom: 1px solid var(--border); }
    .stat-item:last-child { border-bottom: none; }
    .hero-rings, .hero-bg-icon { display: none; }
}
@media (max-width: 600px) {
    .features-grid, .stats-band { grid-template-columns: 1fr; }
    .cta-section { flex-direction: column; text-align: center; }
    .topbar-links a:not(.topbar-btn) { display: none; }
}
</style>

<div class="page-wrapper">

    <!-- ══ TOPBAR PUBLIC ══ -->
    <nav class="public-topbar">
        <a href="index.php" class="topbar-logo">
            <i class="fas fa-trophy"></i> SPORTMANAGER
        </a>
        <div class="topbar-links">
            <a href="modules/equipes/index.php">Équipes</a>
            <a href="modules/tournois/index.php">Tournois</a>
            <a href="modules/matches/index.php">Matches</a>
            <?php if(!isset($_SESSION['user'])): ?>
                <a href="modules/auth/login.php" class="topbar-btn">Se connecter</a>
            <?php else: ?>
                <a href="dashboard.php" class="topbar-btn">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- ══ HERO ══ -->
    <section class="hero-section">
        <div class="hero-rings"><span></span><span></span><span></span></div>
        <div class="hero-bg-icon">⚽</div>

        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-circle"></i>
                Système actif &amp; opérationnel
            </div>
            <h1 class="hero-title">
                Gérez vos
                <span>Tournois</span>
                Sportifs
            </h1>
            <p class="hero-desc">
                Une plateforme complète pour organiser équipes, matches et compétitions.
                Suivez les résultats en temps réel et gérez les classements automatiquement.
            </p>
            <div class="hero-actions">
                <?php if(!isset($_SESSION['user'])): ?>
                    <a href="modules/auth/login.php" class="btn-primary-hero">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </a>
                    <a href="modules/tournois/index.php" class="btn-ghost-hero">
                        Voir les tournois <i class="fas fa-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn-primary-hero">
                        <i class="fas fa-th-large"></i> Tableau de bord
                    </a>
                    <a href="modules/matches/index.php" class="btn-ghost-hero">
                        Derniers matches <i class="fas fa-arrow-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ══ STATS BAND ══ -->
    <div class="stats-band">
        <div class="stat-item">
            <span class="stat-num"><?= $nb_equipes ?></span>
            <span class="stat-label">Équipes</span>
        </div>
        <div class="stat-item">
            <span class="stat-num"><?= $nb_tournois ?></span>
            <span class="stat-label">Tournois actifs</span>
        </div>
        <div class="stat-item">
            <span class="stat-num"><?= $nb_matches ?></span>
            <span class="stat-label">Matches joués</span>
        </div>
        <div class="stat-item">
            <span class="stat-num"><?= $nb_joueurs ?></span>
            <span class="stat-label">Joueurs</span>
        </div>
    </div>

    <!-- ══ FEATURES ══ -->
    <section class="features-section">
        <p class="section-tag fade-up">Fonctionnalités</p>
        <h2 class="section-title fade-up delay-1">Tout ce dont vous<br>avez besoin</h2>
        <div class="features-grid">
            <a href="modules/equipes/index.php" class="feature-card fade-up">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h3 class="feature-title">Gestion des Équipes</h3>
                <p class="feature-desc">Ajoutez et gérez toutes vos équipes avec joueurs, coaches et informations détaillées.</p>
                <span class="feature-link">Voir les équipes <i class="fas fa-arrow-right"></i></span>
                <span class="feature-num">01</span>
            </a>
            <a href="modules/tournois/index.php" class="feature-card fade-up delay-1">
                <div class="feature-icon"><i class="fas fa-trophy"></i></div>
                <h3 class="feature-title">Gestion des Tournois</h3>
                <p class="feature-desc">Organisez des compétitions élimination directe, poules ou format mixte avec brackets automatiques.</p>
                <span class="feature-link">Voir les tournois <i class="fas fa-arrow-right"></i></span>
                <span class="feature-num">02</span>
            </a>
            <a href="modules/matches/index.php" class="feature-card fade-up delay-2">
                <div class="feature-icon"><i class="fas fa-futbol"></i></div>
                <h3 class="feature-title">Suivi des Matches</h3>
                <p class="feature-desc">Enregistrez scores, prolongations et tirs au but. Classements mis à jour automatiquement.</p>
                <span class="feature-link">Voir les matches <i class="fas fa-arrow-right"></i></span>
                <span class="feature-num">03</span>
            </a>
        </div>
    </section>

    <!-- ══ CTA ══ -->
    <?php if(!isset($_SESSION['user'])): ?>
    <div class="cta-section fade-up">
        <div class="cta-text">
            <h2>Prêt à commencer ?</h2>
            <p>Connectez-vous pour accéder à toutes les fonctionnalités de la plateforme.</p>
        </div>
        <a href="/login.php" class="btn-primary-hero">
            <i class="fas fa-sign-in-alt"></i> Se connecter maintenant
        </a>
    </div>
    <?php endif; ?>

</div>

<?php include 'includes/templates/footer.php'; ?>