<?php /** vars: $ticket */ ?>
<section class="container ticket-page">
  <div class="ticket-actions no-print">
    <a href="<?= e(url('dashboard')) ?>" class="btn btn-ghost btn-sm">Kembali ke Dashboard</a>
    <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">Download / Print E-Ticket</button>
  </div>

  <article class="event-ticket-card">
    <div class="event-ticket-head">
      <div>
        <span class="ticket-kicker">E-Ticket Workshop</span>
        <h1><?= e($ticket['product_title']) ?></h1>
        <p class="muted">Terima kasih sudah ikut workshop bersama <?= e(setting('site_name', 'The Travel CEO')) ?>.</p>
      </div>
      <div class="ticket-code-box">
        <span>Kode Tiket</span>
        <strong><?= e($ticket['ticket_code']) ?></strong>
      </div>
    </div>

    <div class="event-ticket-body">
      <div class="ticket-visual">
        <?php if (!empty($ticket['thumbnail'])): ?>
          <img src="<?= e(base_url($ticket['thumbnail'])) ?>" alt="<?= e($ticket['product_title']) ?>">
        <?php else: ?>
          <div class="ticket-visual-ph">WORKSHOP</div>
        <?php endif; ?>
      </div>

      <div class="ticket-details">
        <div class="ticket-grid">
          <div class="ticket-item"><span>Nama Peserta</span><strong><?= e($ticket['attendee_name']) ?></strong></div>
          <div class="ticket-item"><span>Email</span><strong><?= e($ticket['attendee_email']) ?></strong></div>
          <div class="ticket-item"><span>WhatsApp</span><strong><?= e($ticket['attendee_wa'] ?: '-') ?></strong></div>
          <div class="ticket-item"><span>Kode Order</span><strong><?= e($ticket['order_code']) ?></strong></div>
          <?php if (!empty($ticket['event_start_at'])): ?><div class="ticket-item"><span>Jadwal Event</span><strong><?= e(date('d M Y, H:i', strtotime((string)$ticket['event_start_at']))) ?> WIB</strong></div><?php endif; ?>
          <?php if (!empty($ticket['event_location']) || !empty($ticket['event_city'])): ?><div class="ticket-item"><span>Lokasi</span><strong><?= e(trim(($ticket['event_location'] ?? '') . (!empty($ticket['event_city']) ? ', ' . $ticket['event_city'] : ''), ', ')) ?></strong></div><?php endif; ?>
          <div class="ticket-item"><span>Status</span><strong><?= e(strtoupper($ticket['status'])) ?></strong></div>
          <div class="ticket-item"><span>Tanggal Terbit</span><strong><?= e(date('d M Y H:i', strtotime((string)$ticket['issued_at']))) ?> WIB</strong></div>
        </div>

        <div class="ticket-qr-box">
          <img src="<?= e(ticket_qr_image_url($ticket['ticket_token'])) ?>" alt="QR Validasi Ticket">
          <div>
            <h2>QR Validasi Tiket</h2>
            <p>Panitia dapat scan QR ini untuk membuka halaman validasi tiket.</p>
            <a href="<?= e(ticket_verify_url($ticket['ticket_token'])) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">Buka Halaman Validasi</a>
          </div>
        </div>

        <div class="ticket-note">
          <h2>Ucapan Terima Kasih</h2>
          <p>Terima kasih sudah ikut workshop <strong><?= e($ticket['product_title']) ?></strong>. Simpan e-ticket ini sebagai bukti kepesertaan program Anda.</p>
          <?php if (!empty($ticket['event_notes'])): ?><p><?= nl2br(e($ticket['event_notes'])) ?></p><?php endif; ?>
          <?php if (!empty($ticket['event_maps_url'])): ?><p><a href="<?= e($ticket['event_maps_url']) ?>" target="_blank" rel="noopener">Buka lokasi / maps event</a></p><?php endif; ?>
          <p>Jika membutuhkan bantuan, silakan hubungi admin melalui WhatsApp atau email yang tertera di website.</p>
        </div>
      </div>
    </div>
  </article>
</section>
