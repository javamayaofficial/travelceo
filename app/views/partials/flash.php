<?php
$fs = flash('success'); $fe = flash('error');
if ($fs || $fe):
?>
<div class="container flash-wrap">
  <?php if ($fs): ?><div class="flash flash-ok"><span>✅</span><div><?= e($fs) ?></div></div><?php endif; ?>
  <?php if ($fe): ?><div class="flash flash-err"><span>⚠️</span><div><?= e($fe) ?></div></div><?php endif; ?>
</div>
<?php endif; ?>
