/* The Travel CEO — script.js (ringan, tanpa library) */
(function () {
  'use strict';

  function playButtonClick() {
    var AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;
    try {
      if (!window.__tcButtonAudioCtx) window.__tcButtonAudioCtx = new AudioCtx();
      var ctx = window.__tcButtonAudioCtx;
      if (ctx.state === 'suspended') ctx.resume();

      var oscillator = ctx.createOscillator();
      var gain = ctx.createGain();
      oscillator.type = 'triangle';
      oscillator.frequency.setValueAtTime(720, ctx.currentTime);
      oscillator.frequency.exponentialRampToValueAtTime(520, ctx.currentTime + 0.07);
      gain.gain.setValueAtTime(0.0001, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.035, ctx.currentTime + 0.01);
      gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.09);
      oscillator.connect(gain);
      gain.connect(ctx.destination);
      oscillator.start(ctx.currentTime);
      oscillator.stop(ctx.currentTime + 0.09);
    } catch (err) {
      // Abaikan jika browser atau device memblokir audio otomatis.
    }
  }

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

  document.querySelectorAll('.btn').forEach(function (button) {
    button.addEventListener('click', function () {
      if (button.disabled || button.getAttribute('aria-disabled') === 'true') return;
      playButtonClick();
    }, { passive: true });
  });

  document.querySelectorAll('[data-copy-text]').forEach(function (button) {
    var originalText = button.textContent;
    button.addEventListener('click', function () {
      var copyText = button.getAttribute('data-copy-text') || '';
      if (!copyText) return;

      function markCopied() {
        button.textContent = 'Tersalin';
        window.setTimeout(function () {
          button.textContent = originalText;
        }, 1400);
      }

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(copyText).then(markCopied).catch(function () {});
        return;
      }

      var temp = document.createElement('textarea');
      temp.value = copyText;
      temp.setAttribute('readonly', 'readonly');
      temp.style.position = 'absolute';
      temp.style.left = '-9999px';
      document.body.appendChild(temp);
      temp.select();
      try {
        document.execCommand('copy');
        markCopied();
      } catch (err) {
        // Abaikan jika browser menolak clipboard fallback.
      }
      document.body.removeChild(temp);
    });
  });
})();
