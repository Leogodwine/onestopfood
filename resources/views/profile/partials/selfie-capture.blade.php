@php
    $existingSelfieUrl = !empty($profile->selfie_path)
        ? asset('storage/' . ltrim($profile->selfie_path, '/'))
        : null;
@endphp

<div class="selfie-capture-widget" id="selfie-capture-widget">
    <input type="file"
           name="selfie"
           id="selfie-file-input"
           accept="image/*"
           capture="user"
           class="d-none"
           @error('selfie') aria-invalid="true" @enderror>

    <div id="selfie-camera-panel" class="d-none border rounded p-3 mb-2 bg-dark">
        <video id="selfie-video" autoplay playsinline muted class="w-100 rounded selfie-mirror"></video>
        <div class="d-flex gap-2 mt-2 justify-content-center flex-wrap">
            <button type="button" class="btn btn-light btn-sm" id="selfie-btn-capture">
                <i class="bi bi-camera"></i> Capture photo
            </button>
            <button type="button" class="btn btn-outline-light btn-sm" id="selfie-btn-close">
                <i class="bi bi-x-lg"></i> Cancel
            </button>
        </div>
    </div>

    <div id="selfie-preview-wrap" class="mb-2 {{ $existingSelfieUrl ? '' : 'd-none' }}">
        <img id="selfie-preview-img"
             src="{{ $existingSelfieUrl }}"
             alt="Selfie preview"
             class="rounded border"
             style="max-width: 200px; max-height: 200px; object-fit: cover;">
    </div>

    <button type="button" class="btn btn-primary" id="selfie-btn-open">
        <i class="bi bi-camera-fill me-1"></i>
        <span id="selfie-btn-open-label">{{ $existingSelfieUrl ? 'Retake selfie' : 'Take selfie' }}</span>
    </button>
    <small class="text-muted d-block mt-1">Opens your front camera — no file picker.</small>

    @if($existingSelfieUrl)
        <small class="text-success mt-1 d-block" id="selfie-uploaded-hint">
            <i class="bi bi-check-circle"></i> Selfie on file — tap Retake to replace
        </small>
    @endif

    @error('selfie')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@once
@push('styles')
<style>
    .selfie-mirror {
        max-height: 320px;
        object-fit: cover;
        transform: scaleX(-1);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const widget = document.getElementById('selfie-capture-widget');
    if (!widget || widget.dataset.selfieInit === '1') {
        return;
    }
    widget.dataset.selfieInit = '1';

    const fileInput = document.getElementById('selfie-file-input');
    const video = document.getElementById('selfie-video');
    const panel = document.getElementById('selfie-camera-panel');
    const previewWrap = document.getElementById('selfie-preview-wrap');
    const previewImg = document.getElementById('selfie-preview-img');
    const btnOpen = document.getElementById('selfie-btn-open');
    const btnCapture = document.getElementById('selfie-btn-capture');
    const btnClose = document.getElementById('selfie-btn-close');
    const btnLabel = document.getElementById('selfie-btn-open-label');
    const uploadedHint = document.getElementById('selfie-uploaded-hint');

    let stream = null;
    let previewObjectUrl = null;

    function setOpenLabel(text) {
        if (btnLabel) {
            btnLabel.textContent = text;
        }
    }

    function revokePreviewUrl() {
        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }
    }

    function showPreviewFromBlob(blob) {
        revokePreviewUrl();
        previewObjectUrl = URL.createObjectURL(blob);
        previewImg.src = previewObjectUrl;
        previewWrap.classList.remove('d-none');
        setOpenLabel('Retake selfie');
        if (uploadedHint) {
            uploadedHint.classList.remove('d-none');
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(function (track) {
                track.stop();
            });
            stream = null;
        }
        video.srcObject = null;
        panel.classList.add('d-none');
        btnOpen.classList.remove('d-none');
    }

    function openNativeCameraCapture() {
        fileInput.setAttribute('capture', 'user');
        fileInput.click();
    }

    async function startCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            openNativeCameraCapture();
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'user' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
                audio: false,
            });
            video.srcObject = stream;
            panel.classList.remove('d-none');
            btnOpen.classList.add('d-none');
        } catch (err) {
            openNativeCameraCapture();
        }
    }

    btnOpen.addEventListener('click', startCamera);
    btnClose.addEventListener('click', stopCamera);

    btnCapture.addEventListener('click', function () {
        if (!video.videoWidth) {
            return;
        }

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0);

        canvas.toBlob(function (blob) {
            if (!blob) {
                return;
            }

            const file = new File([blob], 'selfie-' + Date.now() + '.jpg', {
                type: 'image/jpeg',
                lastModified: Date.now(),
            });

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;

            showPreviewFromBlob(blob);
            stopCamera();
        }, 'image/jpeg', 0.92);
    });

    fileInput.addEventListener('change', function () {
        const file = fileInput.files && fileInput.files[0];
        if (!file) {
            return;
        }
        revokePreviewUrl();
        previewObjectUrl = URL.createObjectURL(file);
        previewImg.src = previewObjectUrl;
        previewWrap.classList.remove('d-none');
        setOpenLabel('Retake selfie');
    });

    window.addEventListener('beforeunload', revokePreviewUrl);
});
</script>
@endpush
@endonce
