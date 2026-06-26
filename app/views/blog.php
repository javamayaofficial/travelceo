<?php /** vars: $posts */ ?>
<section class="container blog-page">
  <div class="section-head">
    <div>
      <h1>Blog</h1>
      <p class="muted">Insight, tutorial, dan update terbaru dari The Travel CEO.</p>
    </div>
  </div>

  <?php if (!$posts): ?>
    <div class="empty">
      <div class="empty-ic">📝</div>
      <h3>Belum ada artikel</h3>
      <p>Artikel terbaru akan muncul di sini setelah dipublikasikan admin.</p>
    </div>
  <?php else: ?>
    <div class="post-grid">
      <?php foreach ($posts as $post): ?>
        <article class="post-card">
          <a class="post-thumb" href="<?= e(url('post', ['slug' => $post['slug']])) ?>">
            <?php if (!empty($post['featured_image'])): ?>
              <img src="<?= e(base_url($post['featured_image'])) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
            <?php else: ?>
              <span class="post-thumb-ph">📝 Artikel</span>
            <?php endif; ?>
          </a>
          <div class="post-body">
            <span class="post-meta"><?= e(date('d M Y', strtotime($post['published_at'] ?: $post['created_at']))) ?></span>
            <h2><a href="<?= e(url('post', ['slug' => $post['slug']])) ?>"><?= e($post['title']) ?></a></h2>
            <p class="muted"><?= e($post['excerpt'] ?: excerpt_text($post['html'], 160)) ?></p>
            <a href="<?= e(url('post', ['slug' => $post['slug']])) ?>" class="link-more">Baca selengkapnya →</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
