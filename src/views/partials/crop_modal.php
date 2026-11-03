<!-- Cropper.js CSS & JS via CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>

<!-- Modal Crop Foto Profil -->
<div id="crop-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm transition-all duration-200">
  <div class="relative w-full max-w-xl rounded-3xl border border-slate-700 bg-slate-900 p-5 sm:p-6 shadow-2xl space-y-4 max-h-[95vh] flex flex-col justify-between">
    
    <!-- Header Modal -->
    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
      <div>
        <h3 class="text-base sm:text-lg font-black text-white flex items-center gap-2">
          <span>✂️</span>
          <span>Sesuaikan &amp; Potong Foto Panitia</span>
        </h3>
        <p class="text-xs text-slate-400">Geser, perbesar (zoom), atau putar gambar agar pas dengan rasio lingkaran profil panitia (1:1).</p>
      </div>
      <button type="button" onclick="cancelCrop()" class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white transition" aria-label="Tutup">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Area Crop Utama -->
    <div class="relative w-full overflow-hidden rounded-2xl bg-black/70 border border-slate-800 flex items-center justify-center min-h-[260px] max-h-[380px]">
      <img id="crop-image-target" src="" alt="Area Potong" class="max-w-full block">
    </div>

    <!-- Toolbar Kontrol & Live Preview Lingkaran -->
    <div class="flex flex-wrap items-center justify-between gap-3 pt-1 border-t border-slate-800/80">
      <!-- Toolbar Tombol (Zoom, Rotate, Reset) -->
      <div class="flex items-center gap-1.5 flex-wrap">
        <button type="button" onclick="cropAction('zoom', 0.1)" title="Perbesar (Zoom In)" class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-bold text-slate-200 hover:bg-slate-700 hover:text-white transition">
          🔍+
        </button>
        <button type="button" onclick="cropAction('zoom', -0.1)" title="Perkecil (Zoom Out)" class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-bold text-slate-200 hover:bg-slate-700 hover:text-white transition">
          🔎-
        </button>
        <button type="button" onclick="cropAction('rotate', -90)" title="Putar Kiri 90 Derajat" class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-bold text-slate-200 hover:bg-slate-700 hover:text-white transition">
          ↺ 90°
        </button>
        <button type="button" onclick="cropAction('rotate', 90)" title="Putar Kanan 90 Derajat" class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-bold text-slate-200 hover:bg-slate-700 hover:text-white transition">
          ↻ 90°
        </button>
        <button type="button" onclick="cropAction('reset')" title="Kembalikan ke Awal" class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-bold text-slate-400 hover:bg-slate-700 hover:text-white transition">
          ⟲ Reset
        </button>
      </div>

      <!-- Live Preview Lingkaran Avatar -->
      <div class="flex items-center gap-2">
        <span class="text-[11px] font-semibold text-slate-400">Preview:</span>
        <div id="crop-circle-preview" class="h-12 w-12 rounded-full overflow-hidden border-2 border-blue-500/60 bg-slate-950 shadow-md"></div>
      </div>
    </div>

    <!-- Tombol Aksi Bawah -->
    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
      <button type="button" onclick="cancelCrop()" class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition">
        Batal
      </button>
      <button type="button" onclick="applyCrop()" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2 text-xs font-bold text-white shadow-lg shadow-blue-600/30 hover:bg-blue-500 transition">
        <span>✅</span>
        <span>Gunakan Foto Ini</span>
      </button>
    </div>

  </div>
</div>

<script>
let currentCropper = null;
let currentFileInput = null;
let currentPreviewBox = null;
let currentHiddenInput = null;

/**
 * Panggil fungsi ini saat admin memilih foto dari input file.
 * @param {HTMLInputElement} input
 * @param {string} previewBoxId
 * @param {string} hiddenInputId
 */
function handlePhotoSelectWithCrop(input, previewBoxId = 'photo-preview-box', hiddenInputId = 'cropped-photo-data') {
  if (!input.files || !input.files[0]) return;

  const file = input.files[0];
  if (!file.type.match(/^image\/(jpeg|png|jpg)$/i)) {
    alert('Silakan pilih file gambar dengan format JPG, JPEG, atau PNG.');
    input.value = '';
    return;
  }

  currentFileInput = input;
  currentPreviewBox = document.getElementById(previewBoxId);
  currentHiddenInput = document.getElementById(hiddenInputId);

  const reader = new FileReader();
  reader.onload = function(e) {
    const modal = document.getElementById('crop-modal');
    const imageTarget = document.getElementById('crop-image-target');

    // Hancurkan cropper sebelumnya jika ada
    if (currentCropper) {
      currentCropper.destroy();
      currentCropper = null;
    }

    imageTarget.src = e.target.result;
    modal.classList.remove('hidden');

    // Inisialisasi Cropper.js dengan rasio 1:1 persegi pas untuk lingkaran avatar
    currentCropper = new Cropper(imageTarget, {
      aspectRatio: 1,
      viewMode: 1,
      dragMode: 'move',
      autoCropArea: 0.9,
      responsive: true,
      restore: false,
      guides: true,
      center: true,
      highlight: false,
      cropBoxMovable: true,
      cropBoxResizable: true,
      toggleDragModeOnDblclick: false,
      preview: '#crop-circle-preview',
    });
  };
  reader.readAsDataURL(file);
}

function cropAction(action, param) {
  if (!currentCropper) return;
  if (action === 'zoom') {
    currentCropper.zoom(param);
  } else if (action === 'rotate') {
    currentCropper.rotate(param);
  } else if (action === 'reset') {
    currentCropper.reset();
  }
}

function cancelCrop() {
  const modal = document.getElementById('crop-modal');
  modal.classList.add('hidden');
  if (currentCropper) {
    currentCropper.destroy();
    currentCropper = null;
  }
  // Jika belum ada foto cropped sebelumnya, reset input
  if (currentFileInput && (!currentHiddenInput || !currentHiddenInput.value)) {
    currentFileInput.value = '';
  }
}

function applyCrop() {
  if (!currentCropper) return;

  // Render canvas persegi beresolusi tinggi (600x600 px)
  const canvas = currentCropper.getCroppedCanvas({
    width: 600,
    height: 600,
    imageSmoothingEnabled: true,
    imageSmoothingQuality: 'high',
  });

  if (!canvas) {
    alert('Gagal memproses potongan foto.');
    return;
  }

  // 1. Ambil data URL base64 JPEG berkualitas tinggi
  const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.92);

  // 2. Update preview lingkaran di form utama
  if (currentPreviewBox) {
    currentPreviewBox.innerHTML = `<img src="${croppedDataUrl}" alt="Preview Foto" class="h-full w-full object-cover rounded-full">`;
  }

  // 3. Simpan data base64 ke hidden input (fallback backend)
  if (currentHiddenInput) {
    currentHiddenInput.value = croppedDataUrl;
  }

  // 4. Masukkan hasil Blob ke input file asli via DataTransfer API
  canvas.toBlob(function(blob) {
    if (blob && currentFileInput) {
      try {
        const croppedFile = new File([blob], 'cropped-profile.jpg', {
          type: 'image/jpeg',
          lastModified: Date.now()
        });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(croppedFile);
        currentFileInput.files = dataTransfer.files;
      } catch (err) {
        console.warn('DataTransfer not supported, falling back to hidden base64 input.', err);
      }
    }
  }, 'image/jpeg', 0.92);

  // Tutup modal
  const modal = document.getElementById('crop-modal');
  modal.classList.add('hidden');
  currentCropper.destroy();
  currentCropper = null;
}
</script>

