@extends('layouts.app')

@section('title', 'Reservation details')

@section('content')
    @php
        $slot = $reservation->slot;
        $location = $slot?->location;
    @endphp

    <div class="mb-4 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">
                Reservation #{{ $reservation->id }}
            </h1>
            <p class="text-xs text-gray-500">
                {{ $location?->name ?? 'Unknown location' }}
            </p>
        </div>

        <div class="text-right">
            @php
                $status = $reservation->status;
                $color = [
                    'pending' => 'bg-yellow-50 text-yellow-700',
                    'active' => 'bg-green-50 text-green-700',
                    'completed' => 'bg-gray-100 text-gray-700',
                    'canceled' => 'bg-red-50 text-red-700',
                ][$status] ?? 'bg-gray-100 text-gray-700';
            @endphp
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $color }}">
                {{ ucfirst($status) }}
            </span>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[3fr,2fr] items-start">
        <section class="bg-white rounded-lg border border-gray-200 p-4 space-y-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">
                    Reservation details
                </h2>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs text-gray-700">
                    <div>
                        <dt class="text-gray-500">Location</dt>
                        <dd class="font-medium">
                            {{ $location?->name ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Slot</dt>
                        <dd class="font-medium">
                            @if ($slot)
                                #{{ $slot->slot_number }} ({{ ucfirst($slot->type) }})
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Vehicle</dt>
                        <dd class="font-medium">
                            {{ optional($reservation->vehicle)->plate_num ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Time</dt>
                        <dd class="font-medium">
                            {{ $reservation->start_time }} - {{ $reservation->end_time }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">
                    Payment
                </h2>
                @if ($reservation->payment)
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs text-gray-700">
                        <div>
                            <dt class="text-gray-500">Amount</dt>
                            <dd class="font-medium">
                                ₱{{ number_format($reservation->payment->amount, 2) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Method</dt>
                            <dd class="font-medium">
                                {{ str_replace('_', ' ', ucfirst($reservation->payment->payment_method)) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Status</dt>
                            <dd class="font-medium">
                                {{ ucfirst($reservation->payment->status) }}
                            </dd>
                        </div>
                    </dl>
                @else
                    <p class="text-xs text-gray-500">
                        No payment has been recorded for this reservation yet.
                    </p>
                @endif
            </div>
        </section>

        <section class="space-y-4">
            <div class="bg-white rounded-lg border border-gray-200 p-4 text-xs text-gray-700 space-y-2">
                <h2 class="text-sm font-semibold text-gray-900 mb-1">
                    Actions
                </h2>
                <p class="text-gray-500">
                    Status changes are handled by the existing routes
                    (<code>/reservations/{reservation}/start</code> and <code>/reservations/{reservation}/end</code>).
                </p>
            </div>
        </section>
    </div>
@endsection

<script>
let html5QrcodeScanner = null;
let isProcessing = false;

// ─── Dynamically load the QR library then initialise ─────────────────────────
(function loadQrLibrary() {
    // Already on page (e.g. second visit with cache)
    if (typeof Html5QrcodeScanner !== 'undefined') {
        initQrScanner();
        return;
    }

    const script = document.createElement('script');
    script.src = 'https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js';

    script.addEventListener('load', function () {
        console.log('QR library loaded ✅');
        initQrScanner();
    });

    script.addEventListener('error', function () {
        document.getElementById('qr-loading').innerHTML =
            '<span class="text-red-500">Failed to load QR library. Check your connection.</span>';
    });

    document.head.appendChild(script);
})();

// ─── Init & start ─────────────────────────────────────────────────────────────

function initQrScanner() {
    document.getElementById('qr-loading').classList.add('hidden');
    startScanner();
}

function startScanner() {
    if (typeof Html5QrcodeScanner === 'undefined') {
        console.error('Html5QrcodeScanner not available');
        return;
    }

    // Clear any leftover DOM from a previous render
    document.getElementById('qr-reader').innerHTML = '';

    html5QrcodeScanner = new Html5QrcodeScanner(
        'qr-reader',
        {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            rememberLastUsedCamera: true,
            showTorchButtonIfSupported: true,
        },
        /* verbose= */ false
    );

    html5QrcodeScanner.render(onScanSuccess, onScanError);
}

function restartScanner() {
    document.getElementById('rescan-btn').classList.add('hidden');
    document.getElementById('qr-reader-results').textContent = '';
    isProcessing = false;

    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear()
            .then(() => startScanner())
            .catch(err => { console.error('clear() failed:', err); startScanner(); });
    } else {
        startScanner();
    }
}

// ─── Scan callbacks ───────────────────────────────────────────────────────────

function onScanSuccess(decodedText) {
    if (isProcessing) return;
    isProcessing = true;

    try { html5QrcodeScanner.pause(true); } catch (_) {}

    document.getElementById('qr-reader-results').textContent = 'Processing…';
    document.getElementById('rescan-btn').classList.remove('hidden');

    submitToBackend(decodedText);
}

function onScanError(error) {
    // Silence per-frame noise from the library
    if (typeof error === 'string' &&
        (error.includes('No MultiFormat Readers') || error.includes('NotFoundException'))) return;
    console.warn('Scan error:', error);
}

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
        isProcessing = false;
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
                ${row('Check-in',      r.start_time   ?? '—')}
                ${row('Check-out',     r.end_time     ?? '—')}
                ${row('Free hours',    r.free_hours   != null ? r.free_hours  + ' hr(s)' : '—')}
                ${row('Paid hours',    r.paid_hours   != null ? r.paid_hours  + ' hr(s)' : '—')}
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

function printReceipt() { window.print(); }

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
</script>