<?php /** app/views/member/learn.php — vars: $product, $lessons, $current, $done, $unlocked, $prev, $next, $current_done */
$embed = youtube_embed_url($current['youtube']);
$videoId = youtube_video_id($current['youtube']);
$total = count($lessons);
$doneCount = 0; foreach ($lessons as $l) { if (in_array((int)$l['id'], $done, true)) $doneCount++; }
$pct = $total ? round($doneCount / $total * 100) : 0;
?>
<div class="learn">
  <aside class="learn-side">
    <a href="<?= e(url('dashboard')) ?>" class="back-link">← Dashboard</a>
    <h2 class="learn-course"><?= e($product['title']) ?></h2>
    <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
    <span class="muted small"><?= $doneCount ?>/<?= $total ?> materi selesai (<?= $pct ?>%)</span>
    <ol class="learn-list">
      <?php foreach ($lessons as $i => $l):
        $active = (int)$l['id'] === (int)$current['id'];
        $isDone = in_array((int)$l['id'], $done, true);
        $isUnlocked = in_array((int)$l['id'], $unlocked, true) || $isDone; ?>
        <li>
          <?php if ($isUnlocked): ?>
            <a class="learn-item <?= $active ? 'active' : '' ?>" href="<?= e(url('learn', ['product' => $product['id'], 'lesson' => $l['id']])) ?>">
              <span class="li-state"><?= $isDone ? '✓' : ($i + 1) ?></span>
              <span class="li-title"><?= e($l['title']) ?></span>
            </a>
          <?php else: ?>
            <span class="learn-item locked">
              <span class="li-state">🔒</span>
              <span class="li-title"><?= e($l['title']) ?></span>
            </span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
  </aside>

  <div class="learn-main">
    <div class="video-frame<?= $embed ? '' : ' empty' ?>">
      <?php if ($embed): ?>
        <iframe id="learn-player" src="<?= e($embed) ?>" title="<?= e($current['title']) ?>" data-yt-id="<?= e($videoId) ?>" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" referrerpolicy="strict-origin-when-cross-origin" loading="lazy"></iframe>
      <?php else: ?>
        <div class="video-ph">Video belum tersedia untuk materi ini.</div>
      <?php endif; ?>
    </div>
    <div class="learn-body">
      <h1><?= e($current['title']) ?></h1>
      <?php if ($current['short_desc']): ?><p class="muted"><?= e($current['short_desc']) ?></p><?php endif; ?>
      <p class="video-note">Video diputar langsung di dalam platform. Materi berikutnya akan terbuka setelah video sebelumnya selesai ditonton.</p>

      <div class="learn-nav">
        <?php if ($prev): ?>
          <a class="btn btn-ghost" href="<?= e(url('learn', ['product' => $product['id'], 'lesson' => $prev['id']])) ?>">← Sebelumnya</a>
        <?php else: ?><span></span><?php endif; ?>

        <form method="post" action="<?= e(url('complete')) ?>" style="display:inline" id="lesson-complete-form">
          <?= csrf_field() ?>
          <input type="hidden" name="lesson" value="<?= (int)$current['id'] ?>">
          <input type="hidden" name="product" value="<?= (int)$product['id'] ?>">
          <input type="hidden" name="next" value="<?= $next ? (int)$next['id'] : (int)$current['id'] ?>">
          <?php if ($embed): ?>
            <button class="btn btn-gold" id="lesson-complete-button"<?= $current_done ? '' : ' disabled' ?>><?= $next ? 'Lanjut ke Materi Berikutnya →' : 'Tandai Selesai ✓' ?></button>
          <?php else: ?>
            <button class="btn btn-gold"><?= $next ? 'Selesai &amp; Lanjut →' : 'Tandai Selesai ✓' ?></button>
          <?php endif; ?>
        </form>
      </div>
      <?php if ($embed && !$current_done): ?>
        <p class="muted small">Tombol lanjut aktif otomatis setelah video ini selesai diputar.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php if ($embed): ?>
<script src="https://www.youtube.com/iframe_api"></script>
<script>
window._ttcLessonPlayerReady = window._ttcLessonPlayerReady || false;
window.onYouTubeIframeAPIReady = function () {
  var iframe = document.getElementById('learn-player');
  var button = document.getElementById('lesson-complete-button');
  if (!iframe || !button || !window.YT || !YT.Player) return;
  if (window._ttcLessonPlayerReady) return;
  window._ttcLessonPlayerReady = true;

  new YT.Player('learn-player', {
    events: {
      onStateChange: function (event) {
        if (event.data === YT.PlayerState.ENDED) {
          button.disabled = false;
          button.focus();
        }
      }
    }
  });
};
</script>
<?php endif; ?>
