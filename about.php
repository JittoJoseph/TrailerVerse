<?php
require_once 'config/app.php';
require_once 'config/tmdb_config.php';
require_once 'services/StatsService.php';

$statsService = new StatsService();
$stats = [
  'movies_tracked' => $statsService->getTotalMoviesTracked(),
  'active_users' => $statsService->getTotalUsers(),
  'reviews' => $statsService->getTotalReviews(),
  'movies_watched' => $statsService->getTotalMoviesWatched(),
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>About TrailerVerse</title>
  <?php include 'includes/head.php'; ?>
  <style>
    .glass {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .hero-glow {
      background: radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.25), transparent 45%),
        radial-gradient(circle at 80% 0%, rgba(236, 72, 153, 0.2), transparent 40%),
        radial-gradient(circle at 50% 80%, rgba(14, 165, 233, 0.15), transparent 45%);
    }

    .metric-card {
      position: relative;
      overflow: hidden;
      transition: transform 0.3s ease;
    }

    .metric-card::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at top right, rgba(236, 72, 153, 0.25), transparent 45%),
        radial-gradient(circle at bottom left, rgba(14, 165, 233, 0.2), transparent 50%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .metric-card:hover {
      transform: translateY(-4px);
    }

    .metric-card:hover::before {
      opacity: 1;
    }

    .value-card {
      transition: transform 0.3s ease, border-color 0.3s ease;
    }

    .value-card:hover {
      transform: translateY(-6px);
      border-color: rgba(255, 255, 255, 0.3);
    }
  </style>
</head>

<body class="bg-slate-950 text-white min-h-screen overflow-x-hidden">
  <?php include 'includes/header.php'; ?>

  <main class="pt-6 lg:pt-20 pb-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      <!-- Hero -->
      <section class="relative overflow-hidden rounded-3xl hero-glow border border-white/5">
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-900/70 to-slate-900/40"></div>
        <div class="relative z-10 px-6 sm:px-10 py-12 lg:py-16 grid lg:grid-cols-2 gap-12 items-center">
          <div class="space-y-6">
            <span class="inline-flex items-center text-xs uppercase tracking-[0.3em] text-slate-400">Inside TrailerVerse</span>
            <h1 class="text-4xl sm:text-5xl font-bold leading-tight">
              Crafted for movie lovers who want more than a watchlist.
            </h1>
            <p class="text-slate-300 text-lg leading-relaxed">
              TrailerVerse blends cinematic discovery, community energy, and curated insights into one premium hub. We are
              building the most delightful way to explore film culture from trendsetters to timeless classics.
            </p>
            <div class="flex flex-wrap gap-4">
              <a href="explore.php" class="inline-flex items-center px-6 py-3 bg-white text-slate-950 rounded-xl font-semibold shadow-lg hover:shadow-2xl hover:-translate-y-0.5 transition">
                Start Exploring
                <i class="fas fa-arrow-right ml-3"></i>
              </a>
              <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="auth/signup.php" class="inline-flex items-center px-6 py-3 glass rounded-xl font-semibold text-white hover:bg-white/10 transition">
                  Join the Community
                  <i class="fas fa-users ml-3"></i>
                </a>
              <?php else: ?>
                <a href="profile.php" class="inline-flex items-center px-6 py-3 glass rounded-xl font-semibold text-white hover:bg-white/10 transition">
                  Go to Profile
                  <i class="fas fa-user ml-3"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
          <div class="relative">
            <div class="absolute -inset-6 bg-gradient-to-br from-indigo-500/30 via-purple-500/30 to-pink-500/30 blur-3xl"></div>
            <div class="relative glass rounded-2xl p-6 space-y-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-slate-400 text-sm">Founded</p>
                  <h3 class="text-2xl font-bold">2024</h3>
                </div>
                <i class="fas fa-clapperboard text-3xl text-indigo-300"></i>
              </div>
              <p class="text-slate-300 leading-relaxed">
                Our founding trio set out to build a cinematic companion that feels luxurious yet personal. Every
                component is crafted to keep you inspired between screenings.
              </p>
              <div class="grid grid-cols-2 gap-4 text-center">
                <div class="p-4 bg-white/5 rounded-2xl border border-white/10">
                  <div class="text-3xl font-bold"><?= number_format($stats['active_users']) ?></div>
                  <p class="text-xs uppercase tracking-wide text-slate-400">Members</p>
                </div>
                <div class="p-4 bg-white/5 rounded-2xl border border-white/10">
                  <div class="text-3xl font-bold"><?= number_format($stats['movies_tracked']) ?></div>
                  <p class="text-xs uppercase tracking-wide text-slate-400">Movies Tracked</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Stats -->
      <section class="mt-12 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <?php
        $statTiles = [
          ['label' => 'Movies Tracked', 'value' => $stats['movies_tracked'], 'icon' => 'fas fa-film', 'hint' => 'Living inside our cache'],
          ['label' => 'Community Reviews', 'value' => $stats['reviews'], 'icon' => 'fas fa-feather-alt', 'hint' => 'Authentic reactions logged'],
          ['label' => 'Movies Watched', 'value' => $stats['movies_watched'], 'icon' => 'fas fa-eye', 'hint' => 'Sessions finished with intent'],
          ['label' => 'Film Lovers', 'value' => $stats['active_users'], 'icon' => 'fas fa-users', 'hint' => 'Members crafting rituals'],
        ];
        foreach ($statTiles as $tile): ?>
          <div class="metric-card glass rounded-2xl p-6 border border-white/5 flex flex-col gap-5">
            <div class="relative z-10 flex items-center justify-between">
              <div>
                <p class="text-[0.65rem] uppercase tracking-[0.4em] text-slate-400 mb-1">Stats</p>
                <p class="text-sm font-semibold text-white/80"><?= htmlspecialchars($tile['label']) ?></p>
              </div>
              <span class="w-12 h-12 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center text-lg text-white">
                <i class="<?= $tile['icon'] ?>"></i>
              </span>
            </div>
            <div class="relative z-10">
              <h3 class="text-4xl font-bold tracking-tight">
                <?= number_format($tile['value']) ?>
              </h3>
              <p class="text-sm text-slate-400 mt-2"><?= htmlspecialchars($tile['hint']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </section>

      <!-- Mission -->
      <section class="mt-14">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Why we exist</p>
            <h2 class="text-3xl lg:text-4xl font-bold mt-3">We pair data with heart.</h2>
            <p class="text-slate-300 mt-4 max-w-2xl">
              Trailers spark curiosity, but community sustains passion. TrailerVerse is designed to connect these moments,
              helping cinephiles uncover the right stories, talents, and discussions at the ideal moment.
            </p>
          </div>
          <a href="movie.php?id=<?= htmlspecialchars(7451) ?>" class="hidden lg:inline-flex items-center text-sm glass px-5 py-3 rounded-xl hover:bg-white/10 transition">
            View Experience Principles
            <i class="fas fa-arrow-right ml-3"></i>
          </a>
        </div>
        <div class="grid md:grid-cols-3 gap-6 mt-10">
          <?php
          $values = [
            ['title' => 'Curated Discovery', 'desc' => 'Signal-driven feeds surface the right story at the right moment.', 'icon' => 'fas fa-compass', 'accent' => 'from-indigo-500/30 via-blue-500/10 to-transparent'],
            ['title' => 'Community Energy', 'desc' => 'Achievements, lists, and gentle nudges keep conversations alive.', 'icon' => 'fas fa-meteor', 'accent' => 'from-pink-500/25 via-rose-500/10 to-transparent'],
            ['title' => 'Cinematic Craft', 'desc' => 'Micro-interactions, typography, and lighting echo the cinema.', 'icon' => 'fas fa-wand-magic-sparkles', 'accent' => 'from-emerald-500/25 via-teal-500/10 to-transparent'],
          ];
          foreach ($values as $value): ?>
            <div class="value-card relative overflow-hidden rounded-2xl border border-white/10 p-6">
              <div class="absolute inset-0 bg-gradient-to-br <?= $value['accent'] ?> opacity-70"></div>
              <div class="relative z-10 space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-xl text-white">
                  <i class="<?= $value['icon'] ?>"></i>
                </div>
                <div>
                  <h3 class="text-xl font-semibold"><?= htmlspecialchars($value['title']) ?></h3>
                  <p class="text-slate-200 text-sm leading-relaxed mt-2"><?= htmlspecialchars($value['desc']) ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- Team -->
      <section class="mt-16">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Team TrailerVerse</p>
            <h2 class="text-3xl font-bold mt-3">Meet the creators.</h2>
            <p class="text-slate-300 mt-4 max-w-2xl">A trio of storytellers, product makers, and cinema nerds shaping every pixel for delight.</p>
          </div>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-10">
          <?php
          $team = [
            ['name' => 'Jitto Joseph', 'avatar' => 'fas fa-drafting-compass'],
            ['name' => 'Sidharth S Nair', 'avatar' => 'fas fa-server'],
            ['name' => 'Joel Manoj', 'avatar' => 'fas fa-users-cog'],
          ];
          foreach ($team as $member): ?>
            <div class="glass rounded-2xl p-6 border border-white/5 flex flex-col gap-4">
              <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center text-2xl">
                <i class="<?= $member['avatar'] ?>"></i>
              </div>
              <div>
                <h3 class="text-xl font-semibold"><?= htmlspecialchars($member['name']) ?></h3>
              </div>
              <p class="text-slate-300 text-sm leading-relaxed flex-1">Building what's next for TrailerVerse.</p>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- CTA -->
      <section class="mt-16">
        <div class="glass rounded-3xl p-10 text-center border border-white/10 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/20 via-purple-500/20 to-pink-500/20"></div>
          <div class="relative z-10 space-y-4">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-200">Your next watch awaits</p>
            <h2 class="text-3xl font-bold">Create a ritual around every movie night.</h2>
            <p class="text-slate-300 max-w-2xl mx-auto">
              Keep a living history of everything you watch, spark conversations, and never lose sight of the stories that move you.
            </p>
            <div class="flex flex-wrap gap-4 justify-center">
              <a href="explore.php" class="px-8 py-3 bg-white text-slate-950 rounded-xl font-semibold shadow-lg hover:-translate-y-0.5 transition">
                Explore Catalog
              </a>
              <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="auth/signup.php" class="px-8 py-3 glass rounded-xl font-semibold hover:bg-white/10 transition">
                  Create Account
                </a>
              <?php else: ?>
                <a href="profile.php" class="px-8 py-3 glass rounded-xl font-semibold hover:bg-white/10 transition">
                  View Dashboard
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>
</body>

</html>
