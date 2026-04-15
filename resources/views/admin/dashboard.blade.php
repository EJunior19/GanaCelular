@extends('layouts.app')

@section('title', 'Admin — Gana tu Celular Py')

@section('content')

@if(!$raffle)

    <div class="text-center neon-text mt-10 text-lg font-bold">
        No hay sorteos creados aún
    </div>

    <div class="text-center mt-4">
        <a href="/admin/create" class="btn-neon px-6 py-3 rounded-xl font-black inline-block neon-glow">
            + Crear primer sorteo
        </a>
    </div>

@else

    <!-- HEADER -->
    <div class="flex justify-between mb-4 items-center gap-2">
        <a href="/admin/create"
           class="btn-neon px-3 py-2 rounded-lg text-sm font-black neon-glow">
            + Crear
        </a>
        <button
            id="btn-whatsapp"
            onclick="enviarWhatsapp({{ $raffle->id ?? 0 }})"
            @if(!isset($raffle) || !$raffle) disabled @endif
            class="bg-green-500 hover:bg-green-400 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
            📲 WhatsApp
        </button>
        <a href="/admin/logout"
           class="bg-red-600 hover:bg-red-500 px-3 py-2 rounded-lg text-sm font-bold text-white transition">
            Salir
        </a>
    </div>

    <!-- INFO SORTEO -->
    <div class="bg-[#0a0a0a] p-4 rounded-xl mb-4 text-center card-dark">

        <h2 class="gold-text font-black text-lg">{{ $raffle->name }}</h2>

        <p class="text-[#39FF14] text-sm mt-1 font-semibold">
            💰 Gs. {{ number_format($raffle->price, 0, ',', '.') }}
        </p>

        @if($raffle->prizes->isNotEmpty())
            <p class="text-gray-400 text-xs mt-1">
                📱 {{ $raffle->prizes->count() }} premios — celulares
            </p>
        @endif

        <!-- PROGRESO -->
        <div class="mt-3">
            <div class="w-full bg-black rounded-full h-3" style="border: 1px solid rgba(57,255,20,0.2);">
                <div class="h-3 rounded-full transition-all"
                     style="width: {{ $progress }}%; background: #39FF14; box-shadow: 0 0 8px #39FF14;"></div>
            </div>
            <p class="text-xs text-[#39FF14]/70 mt-1">{{ $progress }}% vendido</p>
        </div>

    </div>

    <!-- GANADORES (multi-premio) -->
    @if($raffle->prizes->isNotEmpty() && $raffle->prizes->whereNotNull('winner_number')->isNotEmpty())

        <div class="bg-[#0a0a0a] rounded-xl p-4 mb-4 space-y-2 card-dark">
            <h3 class="gold-text font-black text-sm mb-3">🏆 Resultados del sorteo</h3>

            @foreach($raffle->prizes->sortByDesc('order') as $prize)
                @if($prize->winner_number)
                    <div class="flex justify-between items-center bg-[#39FF14]/10 rounded-lg px-3 py-2"
                         style="border: 1px solid rgba(57,255,20,0.3);">
                        <div>
                            <div class="neon-text font-bold text-sm">{{ $prize->name }}</div>
                            @if($prize->description)
                                <div class="text-gray-400 text-xs">{{ $prize->description }}</div>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-white font-black">Nº {{ $prize->winner_number }}</div>
                            <div class="gold-text text-xs">{{ $prize->winner_name }}</div>
                        </div>
                    </div>
                @else
                    <div class="flex justify-between items-center bg-gray-900/40 rounded-lg px-3 py-2 opacity-60"
                         style="border: 1px solid rgba(255,255,255,0.1);">
                        <div class="text-gray-400 text-sm">{{ $prize->name }}</div>
                        <div class="text-gray-500 text-xs">Pendiente</div>
                    </div>
                @endif
            @endforeach
        </div>

    <!-- GANADOR LEGACY (1 solo premio) -->
    @elseif($raffle->winner_number)

        <div class="p-4 rounded-xl text-center font-bold mb-4 text-lg neon-border neon-glow"
             style="background: rgba(57,255,20,0.1);">
            🏆 GANADOR: <span class="neon-text">{{ $raffle->winner_number }}</span>
            @if($raffle->winner_name)
                <div class="text-sm font-normal mt-1 text-white">{{ $raffle->winner_name }}</div>
            @endif
        </div>

    @endif

    <!-- BOTON SORTEO -->
    @if($free == 0 && $raffle->status == 'active')
        <a href="{{ route('admin.roulette', $raffle->id) }}"
           class="block w-full btn-neon py-3 rounded-xl font-black shadow-lg text-center mb-4 neon-glow text-black">
            🎯 REALIZAR SORTEO
        </a>
    @endif

    <!-- GRID NUMEROS -->
    <div class="grid grid-cols-5 gap-2">

        @foreach($raffle->numbers ?? [] as $n)

            <div class="text-center text-xs">

                <div class="p-3 rounded-lg font-bold transition-all
                    @if($n->status == 'free') bg-[#39FF14] text-black
                    @elseif($n->status == 'reserved') bg-[#FFD700] text-black
                    @else bg-red-600 text-white
                    @endif"
                    @if($n->status == 'free') style="box-shadow: 0 0 6px #39FF14;"
                    @elseif($n->status == 'reserved') style="box-shadow: 0 0 6px #FFD700;"
                    @endif>
                    {{ $n->number }}
                </div>

                <div class="text-[10px] mt-1 truncate text-gray-300">
                    {{ $n->customer_name ?? '-' }}
                </div>

                @if($n->status == 'reserved')
                    <form method="POST" action="/admin/confirmar/{{ $n->id }}">
                        @csrf
                        <button class="text-xs mt-1 px-2 py-1 rounded w-full text-black font-bold"
                                style="background: #39FF14; box-shadow: 0 0 4px #39FF14;">
                            ✔ Confirmar
                        </button>
                    </form>
                @endif

            </div>

        @endforeach

    </div>

@endif

<script>
async function enviarWhatsapp(raffleId) {
    if (!raffleId) return;
    const btn = document.getElementById('btn-whatsapp');
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ Generando...';
    try {
        const response = await fetch(`/admin/whatsapp/${raffleId}`);
        if (!response.ok) throw new Error('Error');
        const data = await response.json();
        await navigator.clipboard.writeText(data.mensaje);
        btn.innerHTML = '✅ ¡Copiado!';
        btn.classList.remove('bg-green-500','hover:bg-green-400');
        btn.classList.add('bg-green-700');
        setTimeout(() => {
            window.open('https://chat.whatsapp.com/IW4f2FC2Nwj6bbcWuAlLeD', '_blank');
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
            btn.classList.remove('bg-green-700');
            btn.classList.add('bg-green-500','hover:bg-green-400');
        }, 1000);
    } catch (error) {
        btn.innerHTML = '❌ Error';
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        }, 2000);
    }
}
</script>

@endsection
