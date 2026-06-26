<?php /** vars: $post, $related_posts */ ?>
<article class="container post-detail">
  <a href="<?= e(url('blog')) ?>" class="back-link">← Kembali ke Blog</a>
  <header class="post-head">
    <span class="post-meta"><?= e(date('d M Y', strtotime($post['published_at'] ?: $post['created_at']))) ?></span>
    <h1><?= e($post['title']) ?></h1>
    <?php if ($post['excerpt']): ?><p class="muted post-lead"><?= e($post['excerpt']) ?></p><?php endif; ?>
  </header>

  <?php if (!empty($post['featured_image'])): ?>
    <div class="post-hero">
      <img src="<?= e(base_url($post['featured_image'])) ?>" alt="<?= e($post['title']) ?>">
    </div>
  <?php endif; ?>

  <div class="post-content prose">
    <?= $post['html'] ?>
  </div>
</article>

<?php if (!empty($related_posts)): ?>
<section class="container posts-section">
  <div class="section-head">
    <h2>Post Lainnya</h2>
    <a href="<?= e(url('blog')) ?>" class="link-more">Lihat semua →</a>
  </div>
  <div class="post-grid">
    <?php foreach ($related_posts as $item): ?>
      <article class="post-card">
        <a class="post-thumb" href="<?= e(url('post', ['slug' => $item['slug']])) ?>">
          <?php if (!empty($item['featured_image'])): ?>
            <img src="<?= e(base_url($item['featured_image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
          <?php else: ?>
            <span class="post-thumb-ph">📝 Artikel</span>
          <?php endif; ?>
        </a>
        <div class="post-body">
          <span class="post-meta"><?= e(date('d M Y', strtotime($item['published_at'] ?: $item['created_at']))) ?></span>
          <h3><a href="<?= e(url('post', ['slug' => $item['slug']])) ?>"><?= e($item['title']) ?></a></h3>
          <p class="muted"><?= e($item['excerpt'] ?: excerpt_text($item['html'], 140)) ?></p>
          <a href="<?= e(url('post', ['slug' => $item['slug']])) ?>" class="link-more">Baca artikel →</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
