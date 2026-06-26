<?php /** vars: $ap */ ?>
<div class="dynamic-home">
  <?php if ($ap['featured_image']): ?>
    <div class="container"><img class="sales-feat" src="<?= e(base_url($ap['featured_image'])) ?>" alt="<?= e($ap['title']) ?>"></div>
  <?php endif; ?>
  <?= $ap['html'] ?>
</div>
<div class="container section-cta">
  <a href="<?= e(url('dashboard')) ?>" class="btn btn-primary btn-lg">Kembali ke Dashboard</a>
</div>
