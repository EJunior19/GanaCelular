@extends('layouts.app')

@section('title', 'Gana tu Celular Py')

@section('content')

@php
    $rafflesCollection = collect($raffles ?? []);
    $activeRaffles = $rafflesCollection->where('status', '!=', 'finished')->values();
    $finishedRaffles = $rafflesCollection->where('status', 'finished')->sortByDesc('created_at')->values();
    $latestFinishedRaffle = $finishedRaffles->first();
@endphp

<div class="px-3 pb-6">

    <!-- HEADER -->
    <div class="text-center mb-6">
        <h1 class="text-2xl font-black neon-text mb-1">
            📱 Gana tu Celular Py
        </h1>
        <p class="text-gray-400 text-sm">
            Participá y ganá un celular nuevo
        </p>
    </div>

    <!-- ACTIVOS -->
    <div class="space-y-4">
        @forelse($activeRaffles as $r)

            @php
                $sold = $r->numbers->where('status','sold')->count();
                $total = $r->total_numbers;
                $percent = $total > 0 ? ($sold / $total) * 100 : 0;
            @endphp

            <div class="rounded-2xl overflow-hidden card-dark">

                <!-- IMAGEN -->
                <div class="relative">
                    @if($r->image)
                        <img src="{{ asset('storage/' . $r->image) }}"
                             class="w-full h-44 object-cover"
                             alt="{{ $r->name }}">
                    @else
                        <div class="w-full h-44 flex items-center justify-center bg-black text-4xl neon-text">
                            📱
                        </div>
                    @endif

                    <div class="absolute top-2 right-2 text-black text-xs px-2 py-1 rounded-lg font-black"
                         style="background:#39FF14; box-shadow: 0 0 8px #39FF14;">
                        ACTIVO
                    </div>
                </div>

                <!-- INFO -->
                <div class="p-4">

                    <h2 class="text-lg font-black gold-text">
                        {{ $r->name }}
                    </h2>

                    <p class="text-[#39FF14] font-semibold mt-1">
                        💰 Gs. {{ number_format($r->price, 0, ',', '.') }}
                    </p>

                    <p class="text-sm text-gray-400">
                        🎟 {{ $r->total_numbers }} números disponibles
                    </p>

                    <!-- PROGRESO -->
                    <div class="mt-3">
                        <div class="w-full bg-black rounded-full h-2"
                             style="border: 1px solid rgba(57,255,20,0.2);">
                            <div class="h-2 rounded-full transition-all"
                                 style="width: {{ $percent }}%; background:#39FF14; box-shadow: 0 0 6px #39FF14;"></div>
                        </div>
                        <p class="text-xs text-[#39FF14]/60 mt-1">
                            {{ round($percent) }}% vendido
                        </p>
                    </div>

                    <!-- BOTÓN -->
                    <div class="mt-4">
                        <div onclick="window.location.href='/sorteo/{{ $r->id }}/play'"
                             class="w-full btn-neon text-center py-3 rounded-xl font-black shadow-lg text-sm cursor-pointer neon-glow">
                            🚀 PARTICIPAR AHORA
                        </div>
                    </div>

                </div>
            </div>

        @empty
            <div class="text-center neon-text mt-10 font-semibold">
                📱 No hay sorteos activos en este momento
            </div>
        @endforelse
    </div>

    <!-- ÚLTIMO RESULTADO -->
    @if($latestFinishedRaffle)
        <div class="mt-8 mb-4">
            <h2 class="text-lg font-black neon-text mb-3 text-center">
                🏆 Último sorteo finalizado
            </h2>

            <div onclick="window.location.href='/sorteo/{{ $latestFinishedRaffle->id }}'"
                 class="rounded-2xl p-4 shadow-lg cursor-pointer hover:scale-105 transition"
                 style="background:rgba(57,255,20,0.05); border:1px solid rgba(57,255,20,0.3); box-shadow: 0 0 10px rgba(57,255,20,0.1);">

                <div class="flex items-center justify-between gap-3">
                    <p class="text-white font-semibold">
                        {{ $latestFinishedRaffle->name }}
                    </p>

                    <span class="text-black text-xs font-black px-3 py-2 rounded-xl"
                          style="background:#39FF14; box-shadow: 0 0 6px #39FF14;">
                        VER RESULTADO
                    </span>
                </div>

            </div>
        </div>
    @endif

    <!-- HISTÓRICO -->
    @if($finishedRaffles->count() > 1)
        <div class="mt-6">
            <h2 class="text-lg font-black text-gray-400 mb-3 text-center">
                📜 Sorteos finalizados
            </h2>

            <div class="space-y-2">
                @foreach($finishedRaffles->skip(1) as $r)
                    <div onclick="window.location.href='/sorteo/{{ $r->id }}'"
                         class="rounded-xl px-4 py-3 cursor-pointer hover:scale-105 transition"
                         style="background:#111; border:1px solid rgba(57,255,20,0.15);">

                        <p class="text-white font-medium">
                            {{ $r->name }}
                        </p>

                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

@endsection
