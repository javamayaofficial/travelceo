<?php
/** app/views/home.php — vars: $home_sales, $products, $latest_posts */
$site = setting('site_name', 'The Travel CEO');
$wa   = setting('site_wa', '');
$waLink = $wa ? 'https://wa.me/' . wa_normalize($wa) . '?text=' . rawurlencode("Halo $site, saya ingin apply program The Travel CEO Masterclass.") : url('register');
$featuredSalesLink = !empty($home_sales['slug']) ? url('sales', ['slug' => $home_sales['slug']]) : url('products');
$featuredSalesLink = setting_url('home_link_featured_sales', $featuredSalesLink);
$heroApplyLink = setting_url('home_link_apply', $waLink);
$heroProgramsLink = setting_url('home_link_programs', $featuredSalesLink);
$consultLink = setting_url('home_link_consult', $waLink);
$productsMoreLink = setting_url('home_link_products_more', url('products'));
$blogMoreLink = setting_url('home_link_blog_more', url('blog'));
$finalApplyLink = setting_url('home_link_final_apply', $waLink);
$finalRegisterLink = setting_url('home_link_final_register', url('register'));

$iconSvg = static function ($name) {
    $icons = [
        'growth' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19h16M7 16V9m5 7V6m5 10v-4M6 8l3-3 4 4 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'profit' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M8.5 7.5c.6-1 1.8-1.7 3.5-1.7 2.3 0 3.8 1 3.8 2.8 0 4-7.3 1.9-7.3 5.8 0 1.8 1.6 3 4 3 1.8 0 3.3-.7 4.1-1.9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'team' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16.5 19v-1.2c0-1.8-1.8-3.3-4.5-3.3s-4.5 1.5-4.5 3.3V19m9-11.3a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5Zm-9 1.1a2 2 0 1 0 0 4m13 6.2v-.8c0-1.3-1-2.4-2.7-2.9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'system' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8.2A3.8 3.8 0 1 1 8.2 12 3.8 3.8 0 0 1 12 8.2Zm0-5.2v2.2m0 13.6V21m9-9h-2.2M5.2 12H3m15.3-6.3-1.6 1.6M7.3 16.7l-1.6 1.6m0-12.6 1.6 1.6m9.4 9.4 1.6 1.6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'target' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4.5a7.5 7.5 0 1 0 7.5 7.5M12 8.3a3.7 3.7 0 1 0 3.7 3.7M14.8 9.2 20 4m0 0v4.2M20 4h-4.2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'strategy' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 18.5h14M7.5 15V9m4.5 6V6.5m4.5 8.5V11M6.8 8.2 10 5l3 3 4.2-4.3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'shield' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.8 18 6v5.2c0 4.1-2.6 7.8-6 9-3.4-1.2-6-4.9-6-9V6l6-2.2Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="m9.5 12 1.7 1.7 3.5-3.7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3v3m10-3v3M4.5 8h15M5.5 5.5h13a1 1 0 0 1 1 1v11a2 2 0 0 1-2 2h-11a2 2 0 0 1-2-2v-11a1 1 0 0 1 1-1Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'clock' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 7.8v4.6l3 1.8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'briefcase' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1m-11 3h14m-14 0v6.5A1.5 1.5 0 0 0 6.5 18h11a1.5 1.5 0 0 0 1.5-1.5V10m-14 0V8.5A1.5 1.5 0 0 1 6.5 7h11A1.5 1.5 0 0 1 19 8.5V10" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ];
    return $icons[$name] ?? '';
};

$heroStats = [
    ['label' => 'Bisnis', 'value' => 'Bertumbuh', 'icon' => 'growth'],
    ['label' => 'Profit', 'value' => 'Meningkat', 'icon' => 'profit'],
    ['label' => 'Tim', 'value' => 'Tangguh', 'icon' => 'team'],
    ['label' => 'Sistem', 'value' => 'Efisien', 'icon' => 'system'],
    ['label' => 'Keputusan', 'value' => 'Berbasis Data', 'icon' => 'target'],
];
$painPoints = [
    'Owner menjadi sales',
    'Owner menjadi admin',
    'Owner menjadi tour leader',
    'Semua keputusan lewat owner',
    'Omzet naik tetapi profit stagnan',
    'Tim tidak berkembang',
    'Bisnis berhenti saat owner berhenti',
];
$transformationSteps = [
    ['title' => 'Agen', 'desc' => 'Menjual produk untuk mendapatkan komisi.'],
    ['title' => 'Owner', 'desc' => 'Mengelola bisnis, terlibat dalam operasional.'],
    ['title' => 'Business Builder', 'desc' => 'Membangun sistem, tim, dan proses yang terstruktur.'],
    ['title' => 'Travel CEO', 'desc' => 'Memimpin perusahaan, menciptakan pertumbuhan, dan mewariskan legacy.'],
];
$pillars = [
    ['title' => 'Build', 'tag' => 'Membangun fondasi bisnis', 'items' => ['Produk', 'Profit', 'Margin', 'Pricing', 'Business model']],
    ['title' => 'Scale', 'tag' => 'Mempercepat pertumbuhan', 'items' => ['Marketing', 'Sales', 'Branding', 'Market expansion', 'Revenue engine']],
    ['title' => 'Lead', 'tag' => 'Membangun organisasi', 'items' => ['Leadership', 'KPI dashboard', 'Team structure', 'SOP', 'CEO decision making']],
    ['title' => 'Implement', 'tag' => '90 day growth blueprint', 'items' => ['Action plan 90 hari', 'Prioritas mingguan', 'Eksekusi terukur', 'Review hasil', 'Scale up roadmap']],
];
$fitAudience = ['Owner Travel', 'CEO Travel', 'Direktur Travel', 'Founder Travel', 'PPIU', 'BPW', 'Tour Operator', 'Investor Travel'];
$notFitAudience = ['Agen baru', 'Pencari motivasi', 'Orang yang tidak siap berubah'];
$outcomes = [
    'Build: Business Foundation Framework',
    'Scale: Revenue Growth Strategy',
    'Lead: Organization Blueprint',
    'Implement: 90 Day CEO Action Plan',
];
$caseStudy = [
    ['before' => 'Travel bergantung pada owner', 'after' => 'Travel memiliki sistem yang jalan.'],
    ['before' => 'Omzet tidak stabil', 'after' => 'Growth lebih terukur dan bisa diprediksi.'],
    ['before' => 'Keputusan berdasarkan intuisi', 'after' => 'Keputusan berdasarkan dashboard dan data.'],
];
$framework = [
    ['step' => '01', 'title' => 'Build', 'desc' => 'Membangun fondasi bisnis yang kuat dan menguntungkan.'],
    ['step' => '02', 'title' => 'Scale', 'desc' => 'Menciptakan mesin pertumbuhan yang konsisten dan terukur.'],
    ['step' => '03', 'title' => 'Lead', 'desc' => 'Membangun organisasi dan tim yang solid, profesional, dan visioner.'],
    ['step' => '04', 'title' => 'Execute', 'desc' => 'Menjalankan strategi dengan disiplin, fokus, dan akuntabilitas.'],
    ['step' => '05', 'title' => 'Growth', 'desc' => 'Meningkatkan profit, valuasi, dan dampak secara berkelanjutan.'],
    ['step' => '06', 'title' => 'Sustainability', 'desc' => 'Membangun sistem dan budaya bisnis yang tahan lama dan adaptif.'],
    ['step' => '07', 'title' => 'Legacy', 'desc' => 'Menciptakan warisan bisnis yang bermakna dan memberi inspirasi.'],
];
$speakerRoles = [
    'CEO YES Global Holdings',
    'Chairman PT. Muhandis Qurani Wisata',
    'Direktur Utama PT. Amr LandPro Haramain',
    'Owner Raja Traveling Indonesia',
    'Ketua Yayasan Al Fatih Mulia Haramain',
];
$speakerExpertise = [
    'Travel business development',
    'Umroh & haji business strategy',
    'Pricing & profit management',
    'Business system & SOP development',
    'Branding & business growth',
    'Leadership & team development',
];
$eventInfo = [
    ['label' => 'Format', 'value' => 'Full Day Intensive Workshop', 'icon' => 'calendar'],
    ['label' => 'Fokus', 'value' => 'Build • Scale • Lead', 'icon' => 'clock'],
    ['label' => 'Admission', 'value' => 'Executive Admission', 'icon' => 'briefcase'],
];
$rundown = [
    ['session' => 'Sesi 1: Build', 'time' => '09.00 - 10.30 WIB', 'items' => ['Mindset CEO Travel vs Agen Travel', 'Struktur Bisnis Travel yang Sehat & Pilar Utama Bisnis Travel', 'Merancang Produk Umroh & Wisata yang Kompetitif', 'Sistem Perhitungan Paket yang Profitable']],
    ['session' => 'Sesi 2: Scale', 'time' => '10.45 - 12.30 WIB', 'items' => ['Travel Marketing Framework', 'Membangun Mesin Penjualan Travel', 'Membangun Brand Travel yang Dipercaya', 'Menemukan Peluang Pasar Baru & Business Growth Mapping']],
    ['session' => 'Sesi 3: Lead', 'time' => '13.30 - 15.15 WIB', 'items' => ['Membangun Organisasi Bisnis Travel', 'Sistem Operasional Tour & Umroh', 'Dashboard CEO Travel', 'Strategic Decision Making for Travel CEO']],
    ['session' => 'Sesi 4: CEO Implementation Workshop', 'time' => '15.30 - 16.30 WIB', 'items' => ['Praktik', 'Diskusi', 'Implementasi', 'Menyusun strategi dan action plan untuk diterapkan di bisnis Anda']],
    ['session' => 'Grand Closing', 'time' => '16.30 - 17.00 WIB', 'items' => ['Penutup & CEO Reflection Session', 'Refleksi, komitmen, dan langkah selanjutnya sebagai CEO Travel']],
];
$pricing = [
    ['title' => 'Offline Class', 'price' => 'IDR 2.500.000', 'desc' => 'Belajar langsung, diskusi interaktif, networking eksklusif dengan sesama owner travel dan praktisi industri.'],
    ['title' => 'Online Class', 'price' => 'IDR 1.950.000', 'desc' => 'Mengikuti seluruh materi dan framework The Travel CEO Masterclass dari mana saja secara live online.'],
    ['title' => 'Alumni Re-seat', 'price' => 'IDR 1.250.000', 'desc' => 'Khusus alumni YES Class atau program yang pernah mengikuti workshop Yudha Eris Setiawan sebelumnya.'],
];
$indicators = [
    'Transformasi mindset dari operator menjadi CEO.',
    'Kemampuan menentukan harga dan profit dengan tepat.',
    'Kemampuan membangun produk travel yang kompetitif.',
    'Sistem bisnis yang lebih terstruktur.',
    'Peningkatan penjualan dan kinerja marketing.',
    'Kemampuan mengembangkan dan scale-up bisnis.',
    'Kepemimpinan sebagai CEO Travel.',
];
$faqs = [
    ['q' => 'Apakah ini program terbuka untuk umum?', 'a' => 'Tidak. Program ini ditujukan untuk owner, CEO, direktur, founder, dan pelaku bisnis travel yang serius membangun perusahaan, bukan sekadar mencari motivasi.'],
    ['q' => 'Apa hasil yang akan saya bawa pulang?', 'a' => 'Anda pulang dengan framework build-scale-lead, cara membaca profit dengan lebih sehat, peta organisasi, serta action plan 90 hari yang bisa langsung dieksekusi.'],
    ['q' => 'Apakah bisa ikut secara online?', 'a' => 'Bisa. Tersedia kelas online live sehingga peserta di luar kota tetap bisa mengikuti keseluruhan materi dan framework program.'],
    ['q' => 'Bagaimana cara apply admission?', 'a' => 'Anda bisa klik tombol apply atau hubungi admin WhatsApp. Tim akan membantu proses seleksi, konfirmasi seat, dan pengiriman detail teknis acara.'],
];
$visuals = [
    'hero' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0001.jpg',
    'overview' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0002.jpg',
    'transformation' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0003.jpg',
    'problem' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0004.jpg',
    'build' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0005.jpg',
    'pillars' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0006.jpg',
    'audience' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0007.jpg',
    'outcome' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0008.jpg',
    'case' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0009.jpg',
    'framework' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0010.jpg',
    'event' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0013.jpg',
    'rundown' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0013.jpg',
    'pricing' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0014.jpg',
    'admission' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0015.jpg',
    'indicators' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0016.jpg',
    'closing' => 'https://ik.imagekit.io/nsuadasd7/TRAVEL%20CEO/Landing%20Page_The%20Travel%20CEO%20Masterclass_page-0017.jpg',
];
?>

<section class="ceo-home">
  <section class="ceo-hero">
    <div class="container ceo-hero-grid">
      <div class="ceo-hero-copy">
        <span class="ceo-kicker">Fullday Workshop</span>
        <h1>THE TRAVEL CEO MASTERCLASS</h1>
        <p class="ceo-subtitle">BUILD • SCALE • LEAD</p>
        <h2 class="ceo-headline">BERHENTI BERPIKIR SEBAGAI AGEN, MULAILAH MEMIMPIN SEBAGAI CEO</h2>
        <p class="ceo-lead">Dari Travel Owner Menjadi Travel CEO. Membangun bisnis travel yang bertumbuh, menghasilkan profit, dan tidak bergantung pada owner.</p>
        <div class="ceo-actions">
          <a href="<?= e($heroApplyLink) ?>"<?= strpos($heroApplyLink, 'http') === 0 ? ' target="_blank" rel="noopener"' : '' ?> class="btn btn-gold btn-lg">Apply For Admission</a>
          <a href="<?= e($heroProgramsLink) ?>"<?= strpos($heroProgramsLink, 'http') === 0 ? ' target="_blank" rel="noopener"' : '' ?> class="btn btn-ghost btn-lg">Executive Admission</a>
        </div>
        <div class="ceo-stat-grid">
          <?php foreach ($heroStats as $stat): ?>
            <div class="ceo-stat">
              <span class="ceo-stat-icon"><?= $iconSvg($stat['icon'] ?? '') ?></span>
              <span><?= e($stat['label']) ?></span>
              <strong><?= e($stat['value']) ?></strong>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="ceo-admission-card">
        <div class="ceo-card-poster">
          <img src="<?= e($visuals['hero']) ?>" alt="Poster The Travel CEO Masterclass" loading="eager">
        </div>
        <span class="ceo-card-kicker">Siap Apply</span>
        <ul class="ceo-checklist">
          <li>Strategi level CEO.</li>
          <li>Implementasi 90 hari.</li>
          <li>Fokus pada hasil nyata.</li>
          <li>Komunitas CEO travel.</li>
        </ul>
        <a href="<?= e($consultLink) ?>"<?= strpos($consultLink, 'http') === 0 ? ' target="_blank" rel="noopener"' : '' ?> class="btn btn-primary btn-block">Konsultasi Seat Sekarang</a>
      </div>
    </div>
  </section>

  <section class="ceo-section ceo-overview">
    <div class="container">
      <div class="ceo-feature-visual">
        <img src="<?= e($visuals['overview']) ?>" alt="Visual executive program overview The Travel CEO Masterclass" loading="lazy">
      </div>
      <div class="ceo-overview-grid">
        <article class="ceo-panel">
          <span class="ceo-icon-badge"><?= $iconSvg('target') ?></span>
          <h3>Executive Program Overview</h3>
          <p>The Travel CEO Masterclass adalah program executive learning yang dirancang untuk membantu pemilik dan pengelola travel membangun perusahaan yang profitable, sistematis, dan berkelanjutan.</p>
        </article>
        <article class="ceo-panel">
          <span class="ceo-icon-badge"><?= $iconSvg('strategy') ?></span>
          <h3>Kerangka Kerja Program</h3>
          <p>Melalui kombinasi pengalaman industri, strategi bisnis, dan studi kasus praktis, peserta akan memperoleh kerangka kerja yang teruji untuk meningkatkan profit, memperkuat operasional, membangun tim yang efektif, serta mengembangkan bisnis travel secara profesional.</p>
        </article>
        <article class="ceo-panel">
          <span class="ceo-icon-badge"><?= $iconSvg('shield') ?></span>
          <h3>Wadah Transformasi</h3>
          <p>Program ini menjadi wadah transformasi bagi pelaku industri yang siap beralih dari sekadar menjual paket perjalanan menjadi pemimpin perusahaan travel yang memiliki visi, sistem, dan daya saing jangka panjang.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="ceo-section ceo-dark-section">
    <div class="container ceo-two-col">
      <div class="ceo-card-poster">
        <img src="<?= e($visuals['problem']) ?>" alt="Visual masalah utama bisnis travel" loading="lazy">
      </div>
      <div class="ceo-problem-box">
        <span class="ceo-kicker">The Reality</span>
        <h2>Mengapa Banyak Travel Sulit Naik Kelas?</h2>
        <ul class="ceo-bullet-list">
          <?php foreach ($painPoints as $item): ?>
            <li><?= e($item) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="ceo-quote-box">
        <p>Masalahnya bukan pada produk. Masalahnya ada pada cara bisnis dikelola.</p>
        <div class="ceo-mini-grid">
          <span>Bisnis tidak sistematis</span>
          <span>Operasional tidak efisien</span>
          <span>Tim tidak berkembang</span>
          <span>Profit tidak optimal</span>
          <span>Bergantung pada owner</span>
        </div>
      </div>
    </div>
  </section>

  <section class="ceo-section">
    <div class="container">
      <div class="ceo-feature-visual">
        <img src="<?= e($visuals['transformation']) ?>" alt="Visual transformasi dari owner menjadi travel CEO" loading="lazy">
      </div>
      <div class="ceo-section-head center">
        <span class="ceo-kicker">The CEO Transformation</span>
        <h2>Bangun bisnis. Pimpin sistem. Ciptakan legacy.</h2>
      </div>
      <div class="ceo-transform-grid">
        <?php foreach ($transformationSteps as $step): ?>
          <article class="ceo-stage-card">
            <h3><?= e($step['title']) ?></h3>
            <p><?= e($step['desc']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="ceo-build-grid">
        <div class="ceo-build-card"><h3>Sistem</h3><p>Sistem yang terstruktur, terstandarisasi, untuk efisiensi dan skalabilitas.</p></div>
        <div class="ceo-build-card"><h3>Organisasi</h3><p>Organisasi yang kuat dengan tim yang tepat di posisi yang tepat.</p></div>
        <div class="ceo-build-card"><h3>Profitabilitas</h3><p>Bisnis yang menguntungkan dengan arus kas sehat dan margin optimal.</p></div>
        <div class="ceo-build-card"><h3>Leadership</h3><p>Kepemimpinan CEO yang visioner, strategis, dan mampu membawa bisnis bertumbuh.</p></div>
        <div class="ceo-build-card"><h3>Sustainable Growth</h3><p>Pertumbuhan berkelanjutan yang tidak bergantung pada kehadiran owner.</p></div>
      </div>
      <div class="ceo-feature-visual">
        <img src="<?= e($visuals['build']) ?>" alt="Visual fondasi sistem, organisasi, profitabilitas, dan leadership" loading="lazy">
      </div>
    </div>
  </section>

  <section class="ceo-section ceo-pillars">
    <div class="container">
      <div class="ceo-feature-visual">
        <img src="<?= e($visuals['pillars']) ?>" alt="Visual pilar The Travel CEO Masterclass" loading="lazy">
      </div>
      <p class="ceo-image-note center">What You Will Master. 4 Executive Pillars untuk membangun bisnis travel yang bertumbuh, menghasilkan profit, dan berkelanjutan.</p>
    </div>
  </section>

  <section class="ceo-section ceo-dark-section">
    <div class="container">
      <div class="ceo-feature-visual">
        <img src="<?= e($visuals['audience']) ?>" alt="Visual siapa yang cocok ikut program" loading="lazy">
      </div>
      <div class="ceo-section-head center">
        <span class="ceo-kicker">Who Should Apply?</span>
        <h2>Program ini tidak cocok untuk agen baru, pencari motivasi, dan orang yang tidak siap berubah.</h2>
      </div>
      <div class="ceo-audience-grid">
        <?php foreach ($fitAudience as $item): ?>
          <div class="ceo-audience-item"><?= e($item) ?></div>
        <?php endforeach; ?>
      </div>
      <div class="ceo-notfit">
        <span>Program ini tidak cocok untuk</span>
        <?php foreach ($notFitAudience as $item): ?>
          <strong><?= e($item) ?></strong>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="ceo-section">
    <div class="container">
      <div class="ceo-feature-visual">
        <img src="<?= e($visuals['outcome']) ?>" alt="Visual outcome setelah mengikuti program" loading="lazy">
      </div>
      <div class="ceo-section-head center">
        <span class="ceo-kicker">The Outcome</span>
        <h2>Setelah program ini Anda akan memiliki:</h2>
      </div>
      <div class="ceo-outcome-grid">
        <?php foreach ($outcomes as $item): ?>
          <article class="ceo-outcome-card"><p><?= e($item) ?></p></article>
        <?php endforeach; ?>
      </div>
      <div class="ceo-bonus-row">
        <span>Bonus Travel CEO Growth Blueprint</span>
        <span>Travel CEO KPI Dashboard</span>
        <span>Executive SOP Framework</span>
        <span>CEO Decision Framework</span>
      </div>
    </div>
  </section>

  <section class="ceo-section ceo-dark-section">
    <div class="container">
      <div class="ceo-feature-visual">
        <img src="<?= e($visuals['case']) ?>" alt="Visual case study perubahan bisnis travel" loading="lazy">
      </div>
      <div class="ceo-section-head center">
        <span class="ceo-kicker">CEO Case Study</span>
        <h2>Dari business yang bergantung pada Anda, menjadi business yang bertumbuh tanpa Anda.</h2>
      </div>
      <div class="ceo-case-grid">
        <?php foreach ($caseStudy as $case): ?>
          <div class="ceo-case-row">
            <div class="ceo-case-card">
              <span>Before</span>
              <p><?= e($case['before']) ?></p>
            </div>
            <div class="ceo-case-arrow">to</div>
            <div class="ceo-case-card active">
              <span>After</span>
              <p><?= e($case['after']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="ceo-section">
    <div class="container">
      <div class="ceo-feature-visual">
        <img src="<?= e($visuals['framework']) ?>" alt="Visual framework The Travel CEO" loading="lazy">
      </div>
      <div class="ceo-section-head center">
        <span class="ceo-kicker">The Travel CEO Framework</span>
        <h2>Kami tidak mengajarkan cara menjual paket. Kami mengajarkan cara membangun perusahaan travel.</h2>
      </div>
      <div class="ceo-framework-grid">
        <?php foreach ($framework as $item): ?>
          <article class="ceo-framework-card">
            <span class="ceo-step"><?= e($item['step']) ?></span>
            <h3><?= e($item['title']) ?></h3>
            <p><?= e($item['desc']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="ceo-section ceo-dark-section">
    <div class="container ceo-two-col">
      <div class="ceo-speaker-card">
        <p class="ceo-image-note">Workshop dibawakan oleh praktisi aktif yang memahami tantangan bisnis travel dari sisi strategi, sistem, profit, dan pengembangan organisasi.</p>
      </div>
      <div class="ceo-speaker-side">
        <article class="ceo-panel">
          <h3>Yang Akan Anda Dapat</h3>
          <ul class="ceo-plain-list">
            <?php foreach ($speakerExpertise as $item): ?>
              <li><?= e($item) ?></li>
            <?php endforeach; ?>
          </ul>
        </article>
        <article class="ceo-panel">
          <h3>Sudut Pandang Program</h3>
          <ul class="ceo-plain-list">
            <?php foreach ($speakerRoles as $item): ?>
              <li><?= e($item) ?></li>
            <?php endforeach; ?>
          </ul>
        </article>
      </div>
    </div>
  </section>

  <section class="ceo-section">
    <div class="container ceo-two-col">
      <div>
        <div class="ceo-card-poster">
          <img src="<?= e($visuals['event']) ?>" alt="Visual event The Travel CEO Masterclass" loading="lazy">
        </div>
        <p class="ceo-image-note">Format workshop dirancang padat dan fokus, sehingga peserta bisa belajar, berdiskusi, lalu langsung menerjemahkan materi menjadi action plan bisnis.</p>
        <div class="ceo-event-grid">
          <?php foreach ($eventInfo as $item): ?>
            <article class="ceo-event-card">
              <span class="ceo-event-icon"><?= $iconSvg($item['icon'] ?? '') ?></span>
              <span><?= e($item['label']) ?></span>
              <strong><?= e($item['value']) ?></strong>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="ceo-impact-card">
        <h3>Apply For Admission</h3>
        <ul class="ceo-bullet-list compact">
          <li>Program ini tidak terbuka untuk umum.</li>
          <li>Peserta akan melalui proses seleksi terlebih dahulu.</li>
          <li>Bukan form pendaftaran, ini adalah proses seleksi masuk program CEO.</li>
          <li>Hanya peserta yang memenuhi kriteria yang menerima undangan resmi.</li>
        </ul>
      </div>
    </div>
  </section>

  <?php if ($products): ?>
  <section class="ceo-section ceo-dark-section">
    <div class="container">
      <div class="section-head ceo-section-head">
        <div>
          <span class="ceo-kicker">Lanjutan Setelah Workshop</span>
          <h2>Setelah mengikuti workshop, peserta bisa melanjutkan pembelajaran lewat program dan kelas pendukung berikut.</h2>
        </div>
        <a href="<?= e($productsMoreLink) ?>"<?= strpos($productsMoreLink, 'http') === 0 ? ' target="_blank" rel="noopener"' : '' ?> class="link-more">Lihat semua</a>
      </div>
      <div class="product-grid">
        <?php foreach ($products as $p): ?>
          <a class="pcard" href="<?= e(url('product', ['slug' => $p['slug']])) ?>">
            <div class="pthumb">
              <?php if ($p['thumbnail']): ?><img src="<?= e(base_url($p['thumbnail'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy"><?php else: ?><span class="pthumb-ph"><?= e(ucfirst($p['type'])) ?></span><?php endif; ?>
            </div>
            <div class="pbody">
              <span class="ptype"><?= e(ucfirst($p['type'])) ?></span>
              <h3><?= e($p['title']) ?></h3>
              <p class="muted"><?= e($p['short_desc']) ?></p>
              <div class="pprice"><?= (int)$p['price'] === 0 ? 'GRATIS' : rupiah($p['price']) ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="ceo-section">
    <div class="container">
      <div class="ceo-feature-visual">
        <img src="<?= e($visuals['rundown']) ?>" alt="Visual rundown acara The Travel CEO Masterclass" loading="lazy">
      </div>
      <div class="ceo-section-head center">
        <span class="ceo-kicker">Rundown Acara</span>
        <h2>Full day intensive workshop, Build • Scale • Lead.</h2>
      </div>
      <div class="ceo-rundown-grid">
        <?php foreach ($rundown as $item): ?>
          <article class="ceo-rundown-card">
            <div class="ceo-rundown-head">
              <h3><?= e($item['session']) ?></h3>
              <span><?= e($item['time']) ?></span>
            </div>
            <ul class="ceo-plain-list">
              <?php foreach ($item['items'] as $lesson): ?>
                <li><?= e($lesson) ?></li>
              <?php endforeach; ?>
            </ul>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="ceo-section ceo-dark-section">
    <div class="container">
      <div class="ceo-feature-visual">
        <img src="<?= e($visuals['pricing']) ?>" alt="Visual investasi program The Travel CEO Masterclass" loading="lazy">
      </div>
      <div class="ceo-section-head center">
        <span class="ceo-kicker">Investasi Program</span>
        <h2>Harga normal IDR 5.000.000. Special promo dengan limited seat.</h2>
      </div>
      <div class="ceo-pricing-grid">
        <?php foreach ($pricing as $item): ?>
          <article class="ceo-pricing-card">
            <h3><?= e($item['title']) ?></h3>
            <p><?= e($item['desc']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="ceo-seat-note">Limited seat. Kuota terbatas untuk menjaga kualitas diskusi dan interaksi peserta.</div>
    </div>
  </section>

  <section class="ceo-section">
    <div class="container">
      <div class="ceo-feature-visual">
        <img src="<?= e($visuals['indicators']) ?>" alt="Visual indikator keberhasilan peserta" loading="lazy">
      </div>
      <div class="ceo-section-head center">
        <span class="ceo-kicker">7 Indikator Keberhasilan Peserta</span>
        <h2>Kesuksesan The Travel CEO Masterclass diukur dari kemampuan peserta membangun bisnis travel yang profitable, scalable, dan sustainable.</h2>
      </div>
      <div class="ceo-indicator-grid">
        <?php foreach ($indicators as $idx => $item): ?>
          <article class="ceo-indicator-card">
            <span class="ceo-step"><?= $idx + 1 ?></span>
            <p><?= e($item) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if (!empty($latest_posts)): ?>
  <section class="ceo-section ceo-dark-section">
    <div class="container">
      <div class="section-head ceo-section-head">
        <div>
          <span class="ceo-kicker">Insight Sebelum Workshop</span>
          <h2>Artikel terbaru untuk membantu Anda masuk ke workshop dengan perspektif yang lebih siap.</h2>
        </div>
        <a href="<?= e($blogMoreLink) ?>"<?= strpos($blogMoreLink, 'http') === 0 ? ' target="_blank" rel="noopener"' : '' ?> class="link-more">Lihat semua</a>
      </div>
      <div class="post-grid">
        <?php foreach ($latest_posts as $post): ?>
          <article class="post-card">
            <a class="post-thumb" href="<?= e(url('post', ['slug' => $post['slug']])) ?>">
              <?php if (!empty($post['featured_image'])): ?>
                <img src="<?= e(base_url($post['featured_image'])) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
              <?php else: ?>
                <span class="post-thumb-ph">Artikel</span>
              <?php endif; ?>
            </a>
            <div class="post-body">
              <span class="post-meta"><?= e(date('d M Y', strtotime($post['published_at'] ?: $post['created_at']))) ?></span>
              <h3><a href="<?= e(url('post', ['slug' => $post['slug']])) ?>"><?= e($post['title']) ?></a></h3>
              <p class="muted"><?= e($post['excerpt'] ?: excerpt_text($post['html'], 140)) ?></p>
              <a href="<?= e(url('post', ['slug' => $post['slug']])) ?>" class="link-more">Baca artikel</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($home_sales && trim($home_sales['html']) !== ''): ?>
  <section class="ceo-section">
    <div class="container">
      <div class="ceo-featured-banner">
        <div>
          <span class="ceo-kicker">Detail Event Lengkap</span>
          <h2><?= e($home_sales['title']) ?></h2>
          <p>Buka halaman promosi lengkap untuk melihat detail event, penawaran, dan informasi tambahan lainnya.</p>
        </div>
        <a href="<?= e($featuredSalesLink) ?>"<?= strpos($featuredSalesLink, 'http') === 0 ? ' target="_blank" rel="noopener"' : '' ?> class="btn btn-primary">Buka Salespage</a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="ceo-section ceo-dark-section">
    <div class="container faq">
      <div class="ceo-section-head center">
        <span class="ceo-kicker">FAQ</span>
        <h2>Pertanyaan yang paling sering ditanyakan sebelum reservasi seat workshop.</h2>
      </div>
      <?php foreach ($faqs as $faq): ?>
        <details class="faq-item">
          <summary><?= e($faq['q']) ?></summary>
          <p><?= e($faq['a']) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="ceo-final-cta">
    <div class="container">
      <div class="ceo-feature-visual">
        <img src="<?= e($visuals['closing']) ?>" alt="Visual penutup The Travel CEO Masterclass" loading="lazy">
      </div>
      <span class="ceo-kicker">Apply For Admission</span>
      <h2>Travel yang besar tidak dibangun oleh owner yang bekerja lebih keras. Travel yang besar dibangun oleh CEO yang membangun sistem.</h2>
      <p>Great travel companies are not built by chance. They are built by system, strategy, and leadership.</p>
      <div class="ceo-actions center">
        <a href="<?= e($finalApplyLink) ?>"<?= strpos($finalApplyLink, 'http') === 0 ? ' target="_blank" rel="noopener"' : '' ?> class="btn btn-gold btn-lg">Apply via WhatsApp</a>
        <a href="<?= e($finalRegisterLink) ?>"<?= strpos($finalRegisterLink, 'http') === 0 ? ' target="_blank" rel="noopener"' : '' ?> class="btn btn-ghost btn-lg">Daftar Sekarang</a>
      </div>
    </div>
  </section>
</section>
