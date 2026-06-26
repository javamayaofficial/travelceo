<?php /** vars: $sp, $is_home_sales */ ?>
<div class="dynamic-home">
  <?php if ($sp['featured_image']): ?>
    <div class="container"><img class="sales-feat" src="<?= e(base_url($sp['featured_image'])) ?>" alt="<?= e($sp['title']) ?>"></div>
  <?php endif; ?>
  <?= $sp['html'] ?>
</div>
<?php if (empty($is_home_sales)): ?>
<div class="container section-cta">
  <a href="<?= e(url('products')) ?>" class="btn btn-primary btn-lg">Lihat Kelas Kami</a>
</div>
<?php endif; ?>
