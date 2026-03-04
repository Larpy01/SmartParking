@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="p-6 space-y-6">


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

    {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs uppercase tracking-wide font-semibold text-gray-500">Reserved</p>
                        <p class="text-xs text-gray-400 mt-1">Pending + Active</p>
                    </div>
                    <p class="text-3xl font-bold text-black">{{ $totalReserved }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-emerald-200 p-5 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs uppercase tracking-wide font-semibold text-gray-500">Active Now</p>
                        <p class="text-xs text-gray-400 mt-1">Currently parked</p>
                    </div>
                    <p class="text-3xl font-bold text-emerald-600">{{ $totalActive }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-blue-200 p-5 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs uppercase tracking-wide font-semibold text-gray-500">Completed Today</p>
                        <p class="text-xs text-gray-400 mt-1">Checked out today</p>
                    </div>
                    <p class="text-3xl font-bold text-blue-600">{{ $totalCompleted }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs uppercase tracking-wide font-semibold text-gray-500">Cancelled Today</p>
                        <p class="text-xs text-gray-400 mt-1">Cancelled today</p>
                    </div>
                    <p class="text-3xl font-bold text-black">{{ $totalCancelled }}</p>
                </div>
            </div>
        </div>

            <div class="grid grid-cols-2 lg:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Slot Status</h2>
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach([
                            'available' => ['text-emerald-700', 'bg-emerald-50'],
                            'reserved' => ['text-blue-700', 'bg-blue-50'],
                            'occupied' => ['text-black', 'bg-gray-100'],
                            'maintenance' => ['text-gray-600', 'bg-gray-100']
                        ] as $status => [$text, $bg])

                        <div class="flex items-center justify-between p-3 {{ $bg }} rounded-lg">
                            <span class="text-xs font-semibold {{ $text }} capitalize">
                                {{ $status }}
                            </span>
                            <span class="text-lg font-bold {{ $text }}">
                                {{ $slotStats[$status] ?? 0 }}
                            </span>
                        </div>

                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Slot Types</h2>
                    </div>

                <div class="p-4 space-y-3">

                    @forelse($slotTypes as $type => $count)

                        @php
                            $colors = [
                                'compact' => ['text-emerald-700', 'bg-emerald-50'],
                                'large'   => ['text-blue-700', 'bg-blue-50'],
                                'pwd'     => ['text-black', 'bg-gray-100'],
                                'electric'=> ['text-yellow-600', 'bg-yellow-100'],
                            ];

                            [$text, $bg] = $colors[$type] ?? ['text-gray-700', 'bg-gray-50'];
                        @endphp

                        <div class="flex items-center justify-between p-3 {{ $bg }} rounded-lg">
                            <span class="text-xs font-semibold {{ $text }} capitalize">
                                {{ $type }}
                            </span>

                            <span class="text-lg font-bold {{ $text }}">
                                {{ $count }}
                            </span>
                        </div>

                    @empty
                        <p class="text-xs text-gray-400 italic">No slot types found.</p>
                    @endforelse

                </div>
                </div>
            </div>
            
    {{-- Active Reservations Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="grid grid-cols-2 gap-4">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Active & Pending Reservations</h2>
                <p class="text-sm text-gray-500 mt-0.5">All current reservations across all locations.</p>
            </div>
            <div class="justify-end flex items-center px-6 p-4 border-b border-gray-100 text-lg">
                <a href="{{ route('staff.scan.page') }}" class="flex items-center gap-2 text-gray-700 hover:text-blue-800 px-2 py-4 hover:text-xl transition">
                    Scan QR <i class="fa-solid fa-qrcode"></i>
                 </a>
                 
            </div>
        </div>
  
        <div class="p-4">
            @if ($reservations->isEmpty())
                <p class="text-sm text-gray-400 italic text-center py-6">No active reservations at the moment.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="text-left text-gray-400 border-b border-gray-100 uppercase tracking-wider text-[10px]">
                                <th class="py-3 pr-4 font-bold">Customer</th>
                                <th class="py-3 pr-4 font-bold">Vehicle</th>
                                <th class="py-3 pr-4 font-bold">Location</th>
                                <th class="py-3 pr-4 font-bold">Slot</th>
                                <th class="py-3 pr-4 font-bold">Time</th>
                                <th class="py-3 pr-4 font-bold">Amount</th>
                                <th class="py-3 pr-4 font-bold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($reservations as $r)
                                @php
                                    $statusColor = [
                                        'pending' => 'bg-yellow-50 text-yellow-700',
                                        'active'  => 'bg-green-50 text-green-700',
                                    ][$r->status] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-3 pr-4 font-semibold text-gray-900">{{ $r->user?->name ?? '—' }}</td>
                                    <td class="py-3 pr-4 text-gray-600">{{ $r->vehicle?->plate_num ?? '—' }}</td>
                                    <td class="py-3 pr-4 text-gray-600">{{ $r->slot?->location?->name ?? '—' }}</td>
                                    <td class="py-3 pr-4 text-gray-600">
                                        @if($r->slot) #{{ $r->slot->slot_number }} ({{ ucfirst($r->slot->type) }}) @else — @endif
                                    </td>
                                    <td class="py-3 pr-4 text-gray-600">
                                        {{ $r->start_time?->format('h:i A') }} – {{ $r->end_time?->format('h:i A') }}
                                    </td>
                                    <td class="py-3 pr-4 text-right font-semibold text-black">
                                        @if ($r->is_free)
                                            <span class="text-emerald-600">Free</span>
                                        @else
                                            ₱{{ number_format($r->total_amount, 2) }}
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold capitalize {{ $statusColor }}">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.4.0/jsQR.min.js"></script>
<script>
    let stream = null;
    let scanInterval = null;
    let currentReservationId = null;

    function startScanner() {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(s => {
                stream = s;
                const video = document.getElementById('scanner-video');
                video.srcObject = stream;
                video.play();
                document.getElementById('scanner-placeholder').style.display = 'none';
                document.getElementById('start-scanner').classList.add('hidden');
                document.getElementById('stop-scanner').classList.remove('hidden');
                scanInterval = setInterval(scanFrame, 300);
            })
            .catch(() => showError('Camera access denied. Please allow camera permissions.'));
    }

    function stopScanner() {
        if (stream) stream.getTracks().forEach(t => t.stop());
        clearInterval(scanInterval);
        document.getElementById('scanner-placeholder').style.display = 'flex';
        document.getElementById('start-scanner').classList.remove('hidden');
        document.getElementById('stop-scanner').classList.add('hidden');
    }

    function scanFrame() {
        const video = document.getElementById('scanner-video');
        if (video.readyState !== video.HAVE_ENOUGH_DATA) return;

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height);

        if (code) {
            stopScanner();
            processQr(code.data);
        }
    }

    function processQr(data) {
        fetch('{{ route("staff.scan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ qr_data: data })
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success) return showError(res.message);

            const r = res.reservation;
            currentReservationId = r.id;

            document.getElementById('scan-error').classList.add('hidden');
            document.getElementById('scan-result').classList.remove('hidden');

            document.getElementById('result-name').textContent = r.user ?? '—';
            document.getElementById('result-location').textContent = r.location ?? '—';
            document.getElementById('result-vehicle').textContent = r.vehicle ?? '—';
            document.getElementById('result-slot').textContent = r.slot ?? '—';
            document.getElementById('result-start').textContent = r.start_time ?? '—';
            document.getElementById('result-end').textContent = r.end_time ?? '—';
            document.getElementById('result-free-hours').textContent = r.free_hours > 0 ? r.free_hours + ' hrs' : 'None';
            document.getElementById('result-amount').textContent = r.is_free ? 'Free' : '₱' + parseFloat(r.total_amount).toFixed(2);

            const statusEl = document.getElementById('result-status');
            statusEl.textContent = r.status.charAt(0).toUpperCase() + r.status.slice(1);
            statusEl.className = 'text-[11px] font-bold px-2 py-0.5 rounded-full ' +
                (r.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700');

            const actions = document.getElementById('result-actions');
            actions.innerHTML = '';

            if (r.status === 'pending') {
                actions.innerHTML = `
                    <button onclick="updateStatus('active')"
                        class="flex-1 py-2 px-3 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition">
                        Check In
                    </button>`;
            } else if (r.status === 'active') {
                actions.innerHTML = `
                    <button onclick="updateStatus('completed')"
                        class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition">
                        Check Out
                    </button>`;
            }
        })
        .catch(() => showError('Failed to process QR code. Please try again.'));
    }

    function updateStatus(status) {
        fetch(`/staff/reservations/${currentReservationId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const statusEl = document.getElementById('result-status');
                statusEl.textContent = res.status.charAt(0).toUpperCase() + res.status.slice(1);
                statusEl.className = 'text-[11px] font-bold px-2 py-0.5 rounded-full ' +
                    (res.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700');
                document.getElementById('result-actions').innerHTML = '';
                setTimeout(() => location.reload(), 1200);
            }
        });
    }

    function showError(msg) {
        const el = document.getElementById('scan-error');
        el.textContent = msg;
        el.classList.remove('hidden');
        document.getElementById('scan-result').classList.add('hidden');
    }
</script>
@endsection