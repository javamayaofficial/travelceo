/* The Travel CEO — script.js (ringan, tanpa library) */
(function () {
  'use strict';

  // Tutup sidebar admin otomatis setelah memilih menu (tampilan HP)
  var toggle = document.getElementById('navtoggle');
  if (toggle) {
    document.querySelectorAll('.aside-link').forEach(function (a) {
      a.addEventListener('click', function () { toggle.checked = false; });
    });
    // Tutup saat klik area konten
    var main = document.querySelector('.admin-main');
    if (main) main.addEventListener('click', function () {
      if (window.innerWidth < 920) toggle.checked = false;
    });
  }

  // Auto-isi slug dari judul (jika ada field slug kosong)
  var titleInput = document.querySelector('input[name="title"]');
  var slugInput = document.querySelector('input[name="slug"]');
  if (titleInput && slugInput && slugInput.value === '') {
    titleInput.addEventListener('input', function () {
      slugInput.value = titleInput.value.toLowerCase()
        .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    });
  }

  // Tampilkan nama file yang dipilih pada input upload
  document.querySelectorAll('input[type="file"]').forEach(function (inp) {
    inp.addEventListener('change', function () {
      if (inp.files && inp.files.length) {
        var hint = inp.parentNode.querySelector('.hint');
        if (hint) hint.textContent = '✓ ' + inp.files[0].name;
      }
    });
  });
})();
