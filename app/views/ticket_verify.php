<?php /** vars: $ticket */ ?>
<section class="container ticket-page">
  <?php if (!$ticket): ?>
    <article class="ticket-verify-card invalid">
      <span class="ticket-kicker">Validasi Tiket</span>
      <h1>Tiket tidak ditemukan</h1>
      <p class="muted">QR code atau link tiket ini tidak valid, sudah berubah, atau tiket tidak lagi tersedia di sistem.</p>
    </article>
  <?php else: ?>
    <article class="ticket-verify-card">
      <div class="ticket-verify-head">
        <div>
          <span class="ticket-kicker">Validasi Tiket</span>
          <h1>Tiket valid dan aktif</h1>
          <p class="muted">Tunjukkan halaman ini kepada panitia sebagai bukti bahwa tiket workshop terdaftar di sistem.</p>
        </div>
        <div class="ticket-verify-status">VALID</div>
      </div>

      <div class="ticket-grid">
        <div class="ticket-item"><span>Kode Tiket</span><strong><?= e($ticket['ticket_code']) ?></strong></div>
        <div class="ticket-item"><span>Nama Peserta</span><strong><?= e($ticket['attendee_name']) ?></strong></div>
        <div class="ticket-item"><span>Program</span><strong><?= e($ticket['product_title']) ?></strong></div>
        <div class="ticket-item"><span>Status</span><strong><?= e(strtoupper($ticket['status'])) ?></strong></div>
        <?php if (!empty($ticket['event_start_at'])): ?><div class="ticket-item"><span>Jadwal</span><strong><?= e(date('d M Y, H:i', strtotime((string)$ticket['event_start_at']))) ?> WIB</strong></div><?php endif; ?>
        <?php if (!empty($ticket['event_location']) || !empty($ticket['event_city'])): ?><div class="ticket-item"><span>Lokasi</span><strong><?= e(trim(($ticket['event_location'] ?? '') . (!empty($ticket['event_city']) ? ', ' . $ticket['event_city'] : ''), ', ')) ?></strong></div><?php endif; ?>
      </div>

      <?php if (!empty($ticket['event_maps_url']) || !empty($ticket['event_notes'])): ?>
        <div class="ticket-note">
          <h2>Info Event</h2>
          <?php if (!empty($ticket['event_notes'])): ?><p><?= nl2br(e($ticket['event_notes'])) ?></p><?php endif; ?>
          <?php if (!empty($ticket['event_maps_url'])): ?><p><a href="<?= e($ticket['event_maps_url']) ?>" target="_blank" rel="noopener">Buka lokasi / maps event</a></p><?php endif; ?>
        </div>
      <?php endif; ?>
    </article>
  <?php endif; ?>
</section>
