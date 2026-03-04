@extends('layouts.app')

@section('title', 'My Vehicles')

@section('content')
<section class="space-y-6 p-6">

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

    <div class="grid gap-6 lg:grid-cols-[3fr,2fr] items-start">

        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Registered Vehicles</h2>

            @if ($vehicles->isEmpty())
                <p class="text-xs text-gray-500">You have no vehicles registered yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-4">Plate Number</th>
                                <th class="py-2 pr-4">Brand / Model</th>
                                <th class="py-2 pr-4">Color</th>
                                <th class="py-2 pr-0"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($vehicles as $v)
                                <tr>
                                    <td class="py-2 pr-4 font-semibold text-gray-900">
                                        {{ $v->plate_num }}
                                    </td>
                                    <td class="py-2 pr-4 text-gray-700">
                                        {{ $v->brand ?? '—' }} {{ $v->model }}
                                    </td>
                                    <td class="py-2 pr-4 text-gray-700">
                                        {{ $v->color ?? '—' }}
                                    </td>
                                    <td class="py-2 pr-0 text-right">
                                        <form action="{{ route('vehicles.destroy', $v) }}" method="POST"
                                            onsubmit="return confirm('Remove this vehicle?')"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-[11px] text-red-600 hover:text-red-700 font-medium cursor-pointer">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-3">Add a Vehicle</h2>

            <form action="{{ route('vehicles.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1" for="plate_num">
                        Plate Number
                    </label>
                    <input
                        id="plate_num"
                        type="text"
                        name="plate_num"
                        required
                        placeholder="ABC-1234"
                        value="{{ old('plate_num') }}"
                        class="block w-full h-10 px-3 rounded-md border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                    >
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1" for="brand">
                            Brand
                        </label>
                        <input
                            id="brand"
                            type="text"
                            name="brand"
                            placeholder="Toyota"
                            value="{{ old('brand') }}"
                            class="block w-full h-10 px-3 rounded-md border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1" for="model">
                            Model
                        </label>
                        <input
                            id="model"
                            type="text"
                            name="model"
                            placeholder="Vios"
                            value="{{ old('model') }}"
                            class="block w-full h-10 px-3 rounded-md border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1" for="color">
                        Color
                    </label>
                    <input
                        id="color"
                        type="text"
                        name="color"
                        placeholder="White"
                        value="{{ old('color') }}"
                        class="block w-full h-10 px-3 rounded-md border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                    >
                </div>

                <button type="submit"
                    class="w-full inline-flex justify-center items-center px-3 py-2 rounded-md bg-blue-600 text-white text-sm font-semibold shadow-sm hover:bg-blue-700 cursor-pointer">
                    Save Vehicle
                </button>
            </form>
        </div>

    </div>
</section>
@endsection