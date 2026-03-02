@extends('layouts.staff')

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

{{-- Include Html5Qrcode library --}}
<script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>

<script>
let html5QrcodeScanner = null;
let lastScannedData = null;

function startScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear();
    }
    
    html5QrcodeScanner = new Html5QrcodeScanner(
        "qr-reader", 
        { 
            fps: 10, 
            qrbox: { width: 250, height: 250 },
            rememberLastUsedCamera: true,
            showTorchButtonIfSupported: true
        },
        /* verbose= */ false
    );
    
    html5QrcodeScanner.render(onScanSuccess, onScanError);
}

function onScanSuccess(decodedText, decodedResult) {
    // Prevent duplicate scans
    if (lastScannedData === decodedText) {
        return;
    }
    
    lastScannedData = decodedText;
    
    // Handle the scanned QR code
    handleScannedData(decodedText);
    
    // Pause scanner briefly to prevent multiple scans
    if (html5QrcodeScanner) {
        html5QrcodeScanner.pause();
    }
}

function onScanError(errorMessage) {
    // Handle scan error (optional)
    console.log('Scan error:', errorMessage);
}

function handleScannedData(data) {
    // Show loading state
    showModal(`
        <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
            <p class="mt-4 text-gray-600">Processing...</p>
        </div>
    `);
    
    // Send to server using the correct route name
    fetch('{{ route("staff.scan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ qr_data: data })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        displayResult(data);
        setTimeout(() => {
            lastScannedData = null;
        }, 3000);
    })
    .catch(error => {
        console.error('Error:', error);
        showModal(`
            <div class="text-center py-8">
                <div class="text-red-600 text-5xl mb-4">❌</div>
                <h3 class="text-xl font-bold text-red-600 mb-2">Error</h3>
                <p class="text-gray-600">${error.message || 'Failed to process QR code'}</p>
            </div>
        `);
        lastScannedData = null;
    });
}

function handleManualEntry(event) {
    event.preventDefault();
    const reservationId = document.getElementById('reservation_id').value;
    
    if (!reservationId) {
        alert('Please enter a reservation ID');
        return;
    }
    
    handleScannedData(reservationId);
}

function displayResult(data) {
    let html = '';
    
    if (data.success) {
        if (data.action === 'checked_in') {
            html = `
                <div class="text-center">
                    <div class="text-green-600 text-5xl mb-4">✅</div>
                    <h3 class="text-2xl font-bold text-green-600 mb-2">Check-in Successful!</h3>
                    <p class="text-gray-600 mb-6">${data.message}</p>
                    
                    <div class="bg-gray-50 rounded-lg p-6 text-left">
                        <h4 class="font-semibold text-gray-900 mb-4">Reservation Details</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Reservation #</p>
                                <p class="font-medium">${data.reservation.id}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Customer</p>
                                <p class="font-medium">${data.reservation.user || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Vehicle</p>
                                <p class="font-medium">${data.reservation.vehicle || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Slot</p>
                                <p class="font-medium">${data.reservation.slot || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Location</p>
                                <p class="font-medium">${data.reservation.location || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Start Time</p>
                                <p class="font-medium">${data.reservation.start_time || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else if (data.action === 'checked_out') {
            html = `
                <div class="text-center">
                    <div class="text-green-600 text-5xl mb-4">✅</div>
                    <h3 class="text-2xl font-bold text-green-600 mb-2">Check-out Successful!</h3>
                    <p class="text-gray-600 mb-6">${data.message}</p>
                    
                    <div class="bg-gray-50 rounded-lg p-6 text-left">
                        <h4 class="font-semibold text-gray-900 mb-4">Payment Summary</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Free Hours Used:</span>
                                <span class="font-medium">${data.reservation.free_hours || 0} hrs</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Paid Hours:</span>
                                <span class="font-medium">${data.reservation.paid_hours || 0} hrs</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t pt-3">
                                <span>Total Amount:</span>
                                <span class="text-green-600">₱${data.reservation.total_amount || 0}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Method:</span>
                                <span class="font-medium capitalize">${data.reservation.payment_method || 'N/A'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Status:</span>
                                <span class="font-medium capitalize ${data.reservation.payment_status === 'paid' ? 'text-green-600' : 'text-yellow-600'}">${data.reservation.payment_status || 'N/A'}</span>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-sm text-gray-500">
                            <p>End Time: ${data.reservation.end_time || 'N/A'}</p>
                        </div>
                    </div>
                </div>
            `;
        } else if (data.action === 'payment_failed') {
            html = `
                <div class="text-center">
                    <div class="text-yellow-600 text-5xl mb-4">⚠️</div>
                    <h3 class="text-2xl font-bold text-yellow-600 mb-2">Payment Failed</h3>
                    <p class="text-gray-600 mb-6">${data.message}</p>
                    
                    <div class="bg-gray-50 rounded-lg p-6 text-left">
                        <h4 class="font-semibold text-gray-900 mb-4">Amount to Collect</h4>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-red-600">₱${data.reservation.total_amount || 0}</p>
                            <p class="text-sm text-gray-500 mt-2">Please collect cash payment</p>
                        </div>
                    </div>
                </div>
            `;
        }
    } else {
        html = `
            <div class="text-center py-8">
                <div class="text-red-600 text-5xl mb-4">❌</div>
                <h3 class="text-xl font-bold text-red-600 mb-2">Error</h3>
                <p class="text-gray-600">${data.message || 'An error occurred'}</p>
            </div>
        `;
    }
    
    showModal(html);
}

function showModal(content) {
    const modal = document.getElementById('result-modal');
    const modalContent = document.getElementById('modal-content');
    modalContent.innerHTML = content;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('result-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
    
    // Resume scanner
    if (html5QrcodeScanner) {
        html5QrcodeScanner.resume();
    }
    
    // Clear manual entry
    document.getElementById('reservation_id').value = '';
}

function printReceipt() {
    const content = document.getElementById('modal-content').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Parking Receipt</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .receipt { max-width: 300px; margin: 0 auto; }
                    @media print {
                        body { margin: 0; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="receipt">
                    ${content}
                </div>
                <div class="no-print text-center mt-4">
                    <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md">Print</button>
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
}

// Initialize scanner when page loads
document.addEventListener('DOMContentLoaded', function() {
    startScanner();
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear();
    }
});

// Handle escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endsection