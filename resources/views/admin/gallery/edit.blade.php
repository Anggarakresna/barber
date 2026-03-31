@extends('layouts.admin')

@section('title', 'Edit Gallery')
@section('page-title', 'Edit Foto Gallery')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
.cropper-box { max-height: 380px; overflow: hidden; background: #111; border-radius: 8px; }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('admin.gallery.update', $gallery) }}" method="POST" id="galleryForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="cropped_image" id="croppedImageData">

                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Judul</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title', $gallery->title) }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Gambar</label>
                        @error('cropped_image')
                            <div class="text-danger small mb-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                        @enderror

                        <div id="currentWrap" class="mb-3">
                            <p class="text-muted small mb-1">Foto saat ini:</p>
                            <img src="{{ asset('storage/' . $gallery->image) }}" alt="{{ $gallery->title }}"
                                 style="max-width: 100%; max-height: 250px; object-fit: contain;" class="rounded border">
                        </div>

                        <input type="file" class="form-control" id="imageFilePicker" accept="image/*">
                        <small class="text-muted">Pilih gambar baru untuk mengubah foto, lalu sesuaikan area crop</small>

                        <div id="croppedPreviewWrap" class="mt-3 d-none">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success"><i class="fas fa-check"></i> Foto baru siap</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick="document.getElementById('imageFilePicker').click()">
                                    <i class="fas fa-edit"></i> Ganti Lagi
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="cancelNewPhoto">
                                    <i class="fas fa-times"></i> Pakai Foto Lama
                                </button>
                            </div>
                            <img id="croppedPreview" class="rounded border"
                                 style="max-width: 100%; max-height: 280px; object-fit: contain;">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <a href="{{ route('admin.gallery.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Cropper Modal --}}
<div class="modal fade" id="cropperModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-crop-alt text-danger"></i> Sesuaikan Gambar</h5>
            </div>
            <div class="modal-body p-3">
                <div class="cropper-box mb-3">
                    <img id="cropperImage" src="" style="display:block; max-width:100%;">
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="text-muted small fw-semibold me-1">Rasio:</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary ratio-btn active" data-ratio="free">Bebas</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary ratio-btn" data-ratio="1">1:1</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary ratio-btn" data-ratio="1.3333">4:3</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary ratio-btn" data-ratio="1.7778">16:9</button>
                    <div class="vr mx-1"></div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="rotateLeft" title="Putar Kiri"><i class="fas fa-undo"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="rotateRight" title="Putar Kanan"><i class="fas fa-redo"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomIn" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomOut" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="resetCrop" title="Reset"><i class="fas fa-sync-alt"></i></button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelCropBtn">Batal</button>
                <button type="button" class="btn btn-danger" id="applyCropBtn">
                    <i class="fas fa-check"></i> Gunakan Foto Ini
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
var cropperInstance = null;
var cropperModal = null;

document.addEventListener('DOMContentLoaded', function () {
    cropperModal = new bootstrap.Modal(document.getElementById('cropperModal'));

    document.getElementById('imageFilePicker').addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (!file) return;
        var ext = file.name.split('.').pop().toLowerCase();
        if (!['jpg','jpeg','png','gif'].includes(ext)) {
            alert('File harus gambar (jpg, jpeg, png, gif).');
            this.value = '';
            return;
        }
        var reader = new FileReader();
        reader.onload = function (ev) {
            var img = document.getElementById('cropperImage');
            img.src = ev.target.result;
            if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
            cropperModal.show();
            document.getElementById('cropperModal').addEventListener('shown.bs.modal', function handler() {
                cropperInstance = new Cropper(img, { viewMode: 1, dragMode: 'move', autoCropArea: 1, responsive: true });
                this.removeEventListener('shown.bs.modal', handler);
            });
        };
        reader.readAsDataURL(file);
    });

    document.querySelectorAll('.ratio-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.ratio-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (!cropperInstance) return;
            var r = this.dataset.ratio;
            cropperInstance.setAspectRatio(r === 'free' ? NaN : parseFloat(r));
        });
    });

    document.getElementById('rotateLeft').addEventListener('click', function () { if (cropperInstance) cropperInstance.rotate(-90); });
    document.getElementById('rotateRight').addEventListener('click', function () { if (cropperInstance) cropperInstance.rotate(90); });
    document.getElementById('zoomIn').addEventListener('click', function () { if (cropperInstance) cropperInstance.zoom(0.1); });
    document.getElementById('zoomOut').addEventListener('click', function () { if (cropperInstance) cropperInstance.zoom(-0.1); });
    document.getElementById('resetCrop').addEventListener('click', function () { if (cropperInstance) cropperInstance.reset(); });

    document.getElementById('cancelCropBtn').addEventListener('click', function () {
        cropperModal.hide();
        document.getElementById('imageFilePicker').value = '';
    });

    document.getElementById('applyCropBtn').addEventListener('click', function () {
        if (!cropperInstance) return;
        var canvas = cropperInstance.getCroppedCanvas({ maxWidth: 1920, maxHeight: 1920 });
        var base64 = canvas.toDataURL('image/jpeg', 0.9);
        document.getElementById('croppedImageData').value = base64;
        document.getElementById('croppedPreview').src = base64;
        document.getElementById('croppedPreviewWrap').classList.remove('d-none');
        document.getElementById('currentWrap').classList.add('d-none');
        cropperModal.hide();
        cropperInstance.destroy();
        cropperInstance = null;
    });

    document.getElementById('cancelNewPhoto').addEventListener('click', function () {
        document.getElementById('croppedImageData').value = '';
        document.getElementById('croppedPreviewWrap').classList.add('d-none');
        document.getElementById('currentWrap').classList.remove('d-none');
        document.getElementById('imageFilePicker').value = '';
    });
});
</script>
@endsection