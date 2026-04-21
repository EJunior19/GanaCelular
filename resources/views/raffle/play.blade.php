@extends('layouts.app')

@section('title', $raffle->name . ' — Saltos Sorteios')

@section('content')

<div class="px-3 pb-6 max-w-2xl mx-auto">

    <!-- IMAGEN -->
    <div class="mb-5">
        <img src="{{ asset('storage/' . $raffle->image) }}"
             class="w-full h-56 object-cover rounded-2xl shadow-xl"
             style="border: 1px solid rgba(57,255,20,0.3); box-shadow: 0 0 15px rgba(57,255,20,0.15);">
    </div>

    <!-- INFO -->
    <div class="text-center mb-5">
        <h1 class="text-2xl font-black gold-text leading-tight">
            {{ $raffle->name }}
        </h1>

        <p class="neon-text text-xl font-semibold mt-2">
            💰 R$ {{ number_format($raffle->price, 2, ',', '.') }}
        </p>
    </div>

    @php
        $total = $raffle->total_numbers;
        $sold = $raffle->numbers->where('status','sold')->count();
        $reserved = $raffle->numbers->where('status','reserved')->count();
        $free = $raffle->numbers->where('status','free')->count();
        $percent = $total > 0 ? (($sold + $reserved) / $total) * 100 : 0;
    @endphp

    <!-- METRICAS -->
    <div class="grid grid-cols-4 gap-2 mb-5">

        <div class="p-3 rounded-xl text-center card-dark shadow flex flex-col justify-center items-center">
            <p class="text-2xl font-black text-white leading-none">{{ $total }}</p>
            <p class="text-[10px] text-gray-400 mt-1 leading-tight">Total</p>
        </div>

        <div onclick="window.location.href='/sorteo/{{ $raffle->id }}/numeros'"
             class="p-3 rounded-xl text-center shadow cursor-pointer hover:scale-105 active:scale-95 transition flex flex-col justify-center items-center"
             style="background: rgba(57,255,20,0.07); border:1px solid rgba(57,255,20,0.3);">
            <p class="text-2xl font-black neon-text leading-none">{{ $free }}</p>
            <p class="text-[10px] text-gray-300 mt-1 leading-tight">Disponíveis</p>
        </div>

        <div class="p-3 rounded-xl text-center shadow flex flex-col justify-center items-center"
             style="background: rgba(255,215,0,0.07); border:1px solid rgba(255,215,0,0.3);">
            <p class="text-2xl font-black gold-text leading-none">{{ $reserved }}</p>
            <p class="text-[10px] text-gray-400 mt-1 leading-tight">Reservados</p>
        </div>

        <div class="p-3 rounded-xl text-center shadow flex flex-col justify-center items-center"
             style="background: rgba(239,68,68,0.07); border:1px solid rgba(239,68,68,0.25);">
            <p class="text-2xl font-black text-red-400 leading-none">{{ $sold }}</p>
            <p class="text-[10px] text-gray-400 mt-1 leading-tight">Vendidos</p>
        </div>

    </div>

    <!-- PROGRESO -->
    <div class="p-4 rounded-2xl card-dark shadow mb-5">

        <div class="flex justify-between items-center text-sm mb-2">
            <span class="text-gray-400 font-medium">Progresso</span>
            <span class="font-black neon-text text-base">{{ round($percent) }}%</span>
        </div>

        <div class="w-full bg-black rounded-full h-4 overflow-hidden"
             style="border: 1px solid rgba(57,255,20,0.2);">
            <div class="h-4 rounded-full transition-all duration-500"
                 style="width: {{ $percent }}%; background: linear-gradient(to right, #39FF14, #00cc0a); box-shadow: 0 0 8px #39FF14;">
            </div>
        </div>

        <p class="text-xs text-gray-500 mt-2 text-center">
            {{ $sold + $reserved }} de {{ $total }} ocupados
        </p>

    </div>

    <!-- BOTÓN PARTICIPAR -->
    <div onclick="window.location.href='/sorteo/{{ $raffle->id }}/numeros'"
         class="w-full btn-neon text-center py-4 rounded-xl font-black shadow-lg text-base cursor-pointer neon-glow">
        🚀 ESCOLHER NÚMEROS
    </div>

</div>

@endsection