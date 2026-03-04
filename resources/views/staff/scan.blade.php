@extends('layouts.app')

@section('title', 'Scan QR Code')

@section('content')
<div class="p-6">



        @if (session('success'))
            <div id="success-alert"
                class="flex items-start justify-between bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg p-4 text-sm shadow-sm">
                <div class="flex items-start gap-2">
                    <i class="fa-solid fa-circle-check mt-0.5"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="closeAlert()" class="text-black text-lg cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Scan QR Code</h2>
            <a href="{{ route('staff.dashboard') }}"
               class="text-sm text-gray-600 hover:text-gray-900">
                ← Back to Dashboard
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Scanner --}}
            <div class="bg-gray-50 rounded-lg p-4 flex flex-col items-center">
                <h3 class="font-medium text-gray-900 mb-4 self-start">Scan QR Code</h3>

                <div class="relative w-full max-w-sm aspect-square rounded-lg overflow-hidden bg-black">
                    <video id="qr-video" class="w-full h-full object-cover" playsinline muted></video>

                    {{-- Targeting overlay --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-52 h-52 border-2 border-white/70 rounded-lg relative">
                            <span class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-blue-400 rounded-tl"></span>
                            <span class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-blue-400 rounded-tr"></span>
                            <span class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-blue-400 rounded-bl"></span>
                            <span class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-blue-400 rounded-br"></span>
                            <div id="scan-line" class="absolute left-1 right-1 h-0.5 bg-blue-400/80 top-0"></div>
                        </div>
                    </div>

                    {{-- Camera status overlay --}}
                    <div id="camera-status" class="absolute inset-0 flex flex-col items-center justify-center bg-black/60 text-white text-sm text-center px-4 gap-2">
                        <svg class="animate-spin h-6 w-6 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        Starting camera…
                    </div>
                </div>

                <canvas id="qr-canvas" class="hidden"></canvas>
                <p id="qr-status" class="mt-3 text-sm text-gray-500 text-center">Point your camera at a QR code.</p>

                <button id="rescan-btn"
                        onclick="restartScanner()"
                        class="hidden mt-3 w-full max-w-sm px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                    Scan Another
                </button>
            </div>

            {{-- Manual Entry --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-4">Manual Entry</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reservation ID</label>
                    <input type="text"
                           id="reservation_id"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter reservation ID">
                </div>
                <button onclick="handleManualEntry()"
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Process Reservation
                </button>
            </div>
        </div>

        {{-- Result modal --}}
        <div id="result-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-2xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="p-6">
                    <div id="modal-content"></div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button onclick="closeModal()"
                                class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg text-sm">
                            Close
                        </button>
                        {{-- Print button — shown only for checked_out via JS --}}
                        <button id="print-receipt-btn"
                                onclick="printReceiptModal()"
                                class="hidden px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4H8v4a1 1 0 001 1zm1-12V4a1 1 0 00-1-1H9a1 1 0 00-1 1v1"/>
                            </svg>
                            Print Receipt
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes scanline {
    0%   { top: 4px; opacity: 1; }
    50%  { opacity: 0.4; }
    100% { top: calc(100% - 4px); opacity: 1; }
}
#scan-line { animation: scanline 2s linear infinite; }
</style>
@endsection

@push('scripts')
<script>
// ─── Camera state ─────────────────────────────────────────────────────────────
let videoStream  = null;
let scanInterval = null;
let isProcessing = false;
let detector     = null;

const video         = document.getElementById('qr-video');
const canvas        = document.getElementById('qr-canvas');
const ctx           = canvas.getContext('2d');
const statusEl      = document.getElementById('qr-status');
const cameraOverlay = document.getElementById('camera-status');
const rescanBtn     = document.getElementById('rescan-btn');

// Last successful scan result — used by printReceiptModal()
let lastReservation = null;

document.addEventListener('DOMContentLoaded', () => startScanner());
window.addEventListener('beforeunload', stopCamera);

// ─── Camera + BarcodeDetector init ───────────────────────────────────────────
async function startScanner() {
    isProcessing = false;
    rescanBtn.classList.add('hidden');
    statusEl.textContent = 'Point your camera at a QR code.';
    showOverlay('Starting camera…', true);

    if ('BarcodeDetector' in window) {
        try { detector = new BarcodeDetector({ formats: ['qr_code'] }); } catch (_) { detector = null; }
    }

    try {
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } },
            audio: false,
        });
        video.srcObject = videoStream;
        await video.play();
        hideOverlay();
        beginScanning();
    } catch (err) {
        let msg = 'Camera access denied.';
        if (err.name === 'NotFoundError')    msg = 'No camera found on this device.';
        if (err.name === 'NotAllowedError')  msg = 'Camera permission denied. Please allow access and refresh.';
        if (err.name === 'NotReadableError') msg = 'Camera is in use by another app.';
        showOverlay(msg, false);
        statusEl.textContent = msg;
    }
}

function stopCamera() {
    clearInterval(scanInterval);
    scanInterval = null;
    videoStream?.getTracks().forEach(t => t.stop());
    videoStream = null;
}

function restartScanner() {
    stopCamera();
    startScanner();
}

function beginScanning() {
    if (detector) {
        scanInterval = setInterval(async () => {
            if (isProcessing || video.readyState < 2) return;
            try {
                const codes = await detector.detect(video);
                if (codes.length > 0) onDetected(codes[0].rawValue);
            } catch (_) {}
        }, 200);
        return;
    }
    // Canvas fallback (jsQR)
    scanInterval = setInterval(() => {
        if (isProcessing || video.readyState < 2) return;
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = window.jsQR?.(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' });
        if (code) onDetected(code.data);
    }, 250);
}

function onDetected(rawValue) {
    if (isProcessing) return;
    isProcessing = true;
    clearInterval(scanInterval);
    scanInterval = null;
    statusEl.textContent = 'QR code detected — processing…';
    rescanBtn.classList.remove('hidden');
    submitToBackend(rawValue);
}

// ─── Overlay helpers ──────────────────────────────────────────────────────────
function showOverlay(message, spinner = false) {
    cameraOverlay.classList.remove('hidden');
    cameraOverlay.innerHTML = spinner
        ? `<svg class="animate-spin h-6 w-6 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
               <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
               <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
           </svg><span>${message}</span>`
        : `<svg class="h-6 w-6 mb-2 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
           </svg><span>${message}</span>`;
}
function hideOverlay() { cameraOverlay.classList.add('hidden'); }

// ─── Manual entry ─────────────────────────────────────────────────────────────
function handleManualEntry() {
    const id = document.getElementById('reservation_id').value.trim();
    if (!id) { alert('Please enter a Reservation ID.'); return; }
    submitToBackend(id);
}

// ─── API call ─────────────────────────────────────────────────────────────────
async function submitToBackend(qrData) {
    try {
        const response = await fetch('{{ route("staff.scan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept':       'application/json',
            },
            body: JSON.stringify({ qr_data: qrData }),
        });

        const result = await response.json();
        statusEl.textContent = result.success ? '✅ ' + result.message : '❌ ' + result.message;
        showModal(result);

    } catch (err) {
        console.error(err);
        statusEl.textContent = '❌ Network error. Please try again.';
        showModal({ success: false, message: 'Network error. Please try again.' });
        isProcessing = false;
    }
}

// ─── Modal ────────────────────────────────────────────────────────────────────
function showModal(result) {
    const modal      = document.getElementById('result-modal');
    const content    = document.getElementById('modal-content');
    const printBtn   = document.getElementById('print-receipt-btn');

    // Reset print button
    printBtn.classList.add('hidden');
    lastReservation = null;

    if (!result.success) {
        content.innerHTML = `
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-xl">✕</div>
                <div>
                    <h3 class="font-semibold text-gray-900">Error</h3>
                    <p class="text-sm text-red-600">${escHtml(result.message)}</p>
                </div>
            </div>`;
    } else {
        const r     = result.reservation ?? {};
        const badge = actionBadge(result.action);

        // Show print button only on checkout
        if (result.action === 'checked_out') {
            lastReservation = r;
            printBtn.classList.remove('hidden');
        }

        content.innerHTML = `
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-xl">✓</div>
                <div>
                    <h3 class="font-semibold text-gray-900">${escHtml(result.message)}</h3>
                    <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full ${badge.cls}">${badge.label}</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                ${row('Reservation #', r.id)}
                ${row('Status',        r.status)}
                ${row('Guest',         r.user)}
                ${row('Vehicle',       r.vehicle)}
                ${row('Slot',          r.slot)}
                ${row('Location',      r.location)}
                ${row('Check-in',      r.start_time   ?? '—')}
                ${row('Check-out',     r.end_time     ?? '—')}
                ${row('Free hours',    r.free_hours   != null ? r.free_hours  + ' hr(s)' : '—')}
                ${row('Paid hours',    r.paid_hours   != null ? r.paid_hours  + ' hr(s)' : '—')}
                ${row('Total',         r.total_amount != null ? (r.is_free ? 'FREE' : '₱' + r.total_amount) : '—')}
                ${row('Payment',       r.payment_method ? r.payment_method + ' · ' + r.payment_status : '—')}
            </div>`;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    document.getElementById('result-modal').classList.replace('flex', 'hidden') ||
    document.getElementById('result-modal').classList.add('hidden');
    document.getElementById('result-modal').classList.remove('flex');
}

// ─── Print receipt ────────────────────────────────────────────────────────────
function printReceiptModal() {
    const r = lastReservation;
    if (!r) return;

    const total = r.is_free ? 'FREE' : (r.total_amount != null ? '₱' + Number(r.total_amount).toFixed(2) : '—');

    const win = window.open('', '_blank', 'width=420,height=700');
    win.document.write(`<!DOCTYPE html>
<html>
<head>
    <title>Receipt #${r.id}</title>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; font-size: 13px; color: #374151; }
        .header { background: #22c55e; color: white; padding: 24px 20px; text-align: center; }
        .header .icon { width: 48px; height: 48px; border-radius: 50%; background: rgba(255,255,255,.2);
                        display: flex; align-items: center; justify-content: center;
                        font-size: 22px; margin: 0 auto 8px; }
        .header h2 { font-size: 17px; font-weight: 700; }
        .header p  { font-size: 11px; color: #dcfce7; margin-top: 2px; }
        .body { padding: 20px; }
        .section { margin-bottom: 12px; }
        .row { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 7px; }
        .label { color: #9ca3af; }
        .value { font-weight: 500; text-align: right; }
        hr { border: none; border-top: 1px dashed #e5e7eb; margin: 12px 0; }
        .total-row { display: flex; justify-content: space-between; font-size: 15px; font-weight: 700; }
        .total-value { color: #16a34a; }
        .footer { text-align: center; font-size: 11px; color: #9ca3af; margin-top: 16px; }
    </style>
</head>
<body>
<div class="header">
    <div class="icon">🅿</div>
    <h2>Smart Parking</h2>
    <p>Parking Receipt</p>
</div>
<div class="body">
    <div class="section">
        <div class="row"><span class="label">Reservation #</span><span class="value">#${escHtml(String(r.id))}</span></div>
        <div class="row"><span class="label">Guest</span><span class="value">${escHtml(String(r.user ?? '—'))}</span></div>
        <div class="row"><span class="label">Vehicle</span><span class="value">${escHtml(String(r.vehicle ?? '—'))}</span></div>
        <div class="row"><span class="label">Slot</span><span class="value">${escHtml(String(r.slot ?? '—'))}</span></div>
        <div class="row"><span class="label">Location</span><span class="value">${escHtml(String(r.location ?? '—'))}</span></div>
    </div>
    <hr>
    <div class="section">
        <div class="row"><span class="label">Check-in</span><span class="value">${escHtml(String(r.start_time ?? '—'))}</span></div>
        <div class="row"><span class="label">Check-out</span><span class="value">${escHtml(String(r.end_time ?? '—'))}</span></div>
        ${r.free_hours ? `<div class="row"><span class="label">Free hours</span><span class="value">${r.free_hours} hr(s)</span></div>` : ''}
        ${r.paid_hours ? `<div class="row"><span class="label">Paid hours</span><span class="value">${r.paid_hours} hr(s)</span></div>` : ''}
    </div>
    <hr>
    <div class="section">
        <div class="row"><span class="label">Payment method</span><span class="value">${escHtml(String(r.payment_method ?? '—'))}</span></div>
        <div class="row"><span class="label">Payment status</span><span class="value">${escHtml(String(r.payment_status ?? '—'))}</span></div>
    </div>
    <hr>
    <div class="total-row">
        <span>Total</span>
        <span class="total-value">${total}</span>
    </div>
    <p class="footer">Thank you for using Smart Parking!</p>
</div>
</body></html>`);
    win.document.close();
    win.focus();
    win.print();
    win.close();
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function row(label, value) {
    return `<div class="bg-gray-50 rounded p-2">
        <span class="block text-xs text-gray-500">${label}</span>
        <span class="font-medium text-gray-900">${escHtml(String(value ?? '—'))}</span>
    </div>`;
}

function actionBadge(action) {
    const map = {
        checked_in:     { label: 'Checked In',    cls: 'bg-blue-100 text-blue-700' },
        checked_out:    { label: 'Checked Out',   cls: 'bg-green-100 text-green-700' },
        payment_failed: { label: 'Payment Failed',cls: 'bg-red-100 text-red-700' },
    };
    return map[action] ?? { label: action ?? 'Updated', cls: 'bg-gray-100 text-gray-700' };
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

document.getElementById('result-modal').addEventListener('click', function (e) {
    if (e.target === this) closeModal();
});

// jsQR fallback — only loads on browsers without BarcodeDetector (Firefox, old Safari)
if (!('BarcodeDetector' in window)) {
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js';
    document.head.appendChild(s);
}
</script>
@endpush