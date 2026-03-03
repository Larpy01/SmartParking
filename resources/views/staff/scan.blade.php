@extends('layouts.app')

@section('title', 'Scan QR Code')

@section('content')
<div class="p-6">
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
            {{-- Scanner Section --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-4">Scan QR Code</h3>
                <div id="qr-reader" style="width: 100%"></div>
                <div id="qr-reader-results" class="mt-4 text-center text-sm text-gray-600"></div>

                {{-- Re-scan button shown after a successful scan --}}
                <button id="rescan-btn"
                        onclick="restartScanner()"
                        class="hidden mt-3 w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    Scan Another
                </button>
            </div>

            {{-- Manual Entry Section --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-4">Manual Entry</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Reservation ID
                    </label>
                    <input type="text"
                           id="reservation_id"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter reservation ID">
                </div>
                <button onclick="handleManualEntry()"
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Process Reservation
                </button>
            </div>
        </div>

        {{-- Results Modal --}}
        <div id="result-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div id="modal-content"></div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button onclick="closeModal()"
                                class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-md">
                            Close
                        </button>
                        <button onclick="printReceipt()"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Print Receipt
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js"
        onload="initQrScanner()"></script>

<script>
let html5QrcodeScanner = null;
let isProcessing = false; // prevent duplicate scans firing simultaneously

// ─── Initialise scanner ───────────────────────────────────────────────────────

function initQrScanner() {
    console.log('QR library loaded ✅');
    startScanner();
}

function startScanner() {
    if (typeof Html5QrcodeScanner === 'undefined') {
        console.error('Html5QrcodeScanner not loaded');
        return;
    }

    html5QrcodeScanner = new Html5QrcodeScanner(
        'qr-reader',
        {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            rememberLastUsedCamera: true,
            showTorchButtonIfSupported: true,
        },
        false
    );

    html5QrcodeScanner.render(onScanSuccess, onScanError);
}

function restartScanner() {
    document.getElementById('rescan-btn').classList.add('hidden');
    document.getElementById('qr-reader-results').textContent = '';
    isProcessing = false;

    // Clear and re-render the scanner widget
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear().then(() => startScanner()).catch(console.error);
    } else {
        startScanner();
    }
}

// ─── Scan handlers ────────────────────────────────────────────────────────────

function onScanSuccess(decodedText) {
    if (isProcessing) return; // ignore extra rapid-fire scans
    isProcessing = true;

    // Stop the scanner so it doesn't fire again while we process
    if (html5QrcodeScanner) {
        html5QrcodeScanner.pause(true);
    }

    document.getElementById('qr-reader-results').textContent = 'Processing…';
    document.getElementById('rescan-btn').classList.remove('hidden');

    submitToBackend(decodedText);
}

function onScanError(error) {
    // Suppress the constant "no QR code" stream — only log real errors
    if (!error?.includes('No MultiFormat Readers')) {
        console.warn('Scan error:', error);
    }
}

// ─── Manual entry ─────────────────────────────────────────────────────────────

function handleManualEntry() {
    const id = document.getElementById('reservation_id').value.trim();
    if (!id) {
        alert('Please enter a Reservation ID.');
        return;
    }
    // Wrap the plain ID in the same JSON shape the backend expects
    const payload = JSON.stringify({ reservation_id: id, token: '' });
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
                'Accept': 'application/json',
            },
            body: JSON.stringify({ qr_data: qrData }),
        });

        const result = await response.json();
        showModal(result);

        document.getElementById('qr-reader-results').textContent =
            result.success ? '✅ ' + result.message : '❌ ' + result.message;

    } catch (err) {
        console.error('Backend error:', err);
        showModal({ success: false, message: 'Network error. Please try again.' });
        document.getElementById('qr-reader-results').textContent = '❌ Network error.';
        isProcessing = false; // allow retry
    }
}

// ─── Modal ────────────────────────────────────────────────────────────────────

function showModal(result) {
    const modal   = document.getElementById('result-modal');
    const content = document.getElementById('modal-content');

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
                ${row('Check-in',      r.start_time  ?? '—')}
                ${row('Check-out',     r.end_time    ?? '—')}
                ${row('Free hours',    r.free_hours  != null ? r.free_hours  + ' hr(s)' : '—')}
                ${row('Paid hours',    r.paid_hours  != null ? r.paid_hours  + ' hr(s)' : '—')}
                ${row('Total',         r.total_amount != null ? '₱' + r.total_amount : '—')}
                ${row('Payment',       r.payment_method ? r.payment_method + ' · ' + r.payment_status : '—')}
            </div>`;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('result-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function printReceipt() {
    window.print();
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function row(label, value) {
    return `
        <div class="bg-gray-50 rounded p-2">
            <span class="block text-xs text-gray-500">${label}</span>
            <span class="font-medium text-gray-900">${escHtml(String(value ?? '—'))}</span>
        </div>`;
}

function actionBadge(action) {
    const map = {
        checked_in:     { label: 'Checked In',      cls: 'bg-blue-100 text-blue-700' },
        checked_out:    { label: 'Checked Out',      cls: 'bg-green-100 text-green-700' },
        payment_failed: { label: 'Payment Failed',   cls: 'bg-red-100 text-red-700' },
    };
    return map[action] ?? { label: action ?? 'Updated', cls: 'bg-gray-100 text-gray-700' };
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// Close modal when clicking the backdrop
document.getElementById('result-modal').addEventListener('click', function (e) {
    if (e.target === this) closeModal();
});
</script>
@endpush