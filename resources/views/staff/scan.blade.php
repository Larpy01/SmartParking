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
            </div>

            {{-- Manual Entry Section --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-4">Manual Entry</h3>
                <form id="manual-entry-form" onsubmit="handleManualEntry(event)">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Reservation ID
                        </label>
                        <input type="text" 
                               id="reservation_id" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter reservation ID"
                               required>
                    </div>
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Process Reservation
                    </button>
                </form>
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
let lastScannedData = null;

function initQrScanner() {
    console.log("QR library loaded ✅");
    startScanner();
}

function startScanner() {
    if (typeof Html5QrcodeScanner === "undefined") {
        console.error("Html5QrcodeScanner STILL not loaded!");
        return;
    }

    html5QrcodeScanner = new Html5QrcodeScanner(
        "qr-reader",
        {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            rememberLastUsedCamera: true,
            showTorchButtonIfSupported: true
        },
        false
    );

    html5QrcodeScanner.render(onScanSuccess, onScanError);
}

function onScanSuccess(decodedText, decodedResult) {
    console.log("Scanned:", decodedText);
}

function onScanError(error) {
    console.log("Scan error:", error);
}
</script>

@endpush