@extends('layouts.app')

@section('title', 'Elegir números — Gana tu Celular Py')

@section('content')

@php
    $numbers = $raffle->numbers->keyBy('number');
@endphp

<div class="px-3 pb-6">

    <h2 class="neon-text text-lg font-black text-center mb-4">
        🎯 Elegí tu número
    </h2>

    <!-- GRID -->
    <div class="grid grid-cols-5 gap-2 mb-4">
        @for($i = 1; $i <= $raffle->total_numbers; $i++)
            @php
                $key = str_pad($i, 2, '0', STR_PAD_LEFT);
                $n = $numbers[$key] ?? null;
                $status = $n->status ?? 'free';
            @endphp

            <div onclick="toggleNumber('{{ $i }}','{{ $status }}')"
                 id="num-{{ $i }}"
                 class="p-3 text-center font-black rounded-xl cursor-pointer transition"
                 @if($status == 'free')
                     style="background:#39FF14; color:#000; box-shadow: 0 0 6px #39FF14;"
                 @elseif($status == 'reserved')
                     style="background:#FFD700; color:#000; cursor:not-allowed; box-shadow: 0 0 4px #FFD700;"
                 @else
                     style="background:#7f1d1d; color:#fca5a5; cursor:not-allowed; opacity:0.7;"
                 @endif>
                {{ $i }}
            </div>
        @endfor
    </div>

    <!-- LEYENDA -->
    <div class="flex gap-4 justify-center text-xs text-gray-400 mb-4">
        <span class="flex items-center gap-1">
            <span class="w-3 h-3 rounded inline-block" style="background:#39FF14;"></span> Disponible
        </span>
        <span class="flex items-center gap-1">
            <span class="w-3 h-3 rounded inline-block" style="background:#FFD700;"></span> Reservado
        </span>
        <span class="flex items-center gap-1">
            <span class="w-3 h-3 rounded inline-block" style="background:#7f1d1d;"></span> Vendido
        </span>
    </div>

    <!-- FORM -->
    <input id="nombre" type="text" placeholder="Tu nombre"
        class="w-full p-3 rounded-xl bg-black text-white mb-3 outline-none focus:ring-2 focus:ring-[#39FF14] transition"
        style="border: 1px solid #39FF14; box-shadow: 0 0 6px rgba(57,255,20,0.15);">

    <div id="seleccionadosBox" class="neon-text text-sm text-center mb-3 font-semibold">
        Ninguno seleccionado
    </div>

    <button type="button" onclick="reservarNumeros()"
        class="w-full btn-neon py-3 rounded-xl font-black neon-glow">
        Reservar
    </button>

</div>

<script>
let seleccionados = [];

function toggleNumber(num, status) {
    if (status !== 'free') return;

    const el = document.getElementById('num-' + num);

    if (seleccionados.includes(num)) {
        seleccionados = seleccionados.filter(n => n !== num);
        el.style.outline = '';
        el.style.transform = '';
    } else {
        seleccionados.push(num);
        el.style.outline = '3px solid #FFD700';
        el.style.outlineOffset = '2px';
        el.style.transform = 'scale(1.1)';
    }

    document.getElementById('seleccionadosBox').innerText =
        seleccionados.length
            ? 'Seleccionados: ' + seleccionados.join(', ')
            : 'Ninguno seleccionado';
}

async function reservarNumeros() {
    const nombre = document.getElementById('nombre').value.trim();

    if (!nombre) {
        alert('Completá tu nombre');
        return;
    }

    if (seleccionados.length === 0) {
        alert('Seleccioná al menos un número');
        return;
    }

    try {
        const res = await fetch('/reservar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                raffle_id: {{ $raffle->id }},
                name: nombre,
                numbers: seleccionados
            })
        });

        const data = await res.json();

        if (!res.ok || data.error) {
            alert(data.error || 'Error al reservar');
            return;
        }

        const msg = `Hola quiero reservar:%0A%0A📱 {{ $raffle->name }}%0A🔢 ${seleccionados.join(', ')}%0A👤 ${nombre}`;
        window.open(`https://wa.me/595986770148?text=${msg}`, '_blank');

        window.location.reload();

    } catch (error) {
        console.error(error);
        alert('Ocurrió un error al reservar');
    }
}
</script>

@endsection
