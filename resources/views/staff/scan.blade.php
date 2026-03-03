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

                {{-- Spinner shown while library loads --}}
                <div id="qr-loading" class="flex flex-col items-center justify-center py-10 text-gray-400 text-sm gap-2">
                    <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    Loading camera…
                </div>

                <div id="qr-reader" style="width: 100%"></div>
                <div id="qr-reader-results" class="mt-4 text-center text-sm text-gray-600"></div>

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
