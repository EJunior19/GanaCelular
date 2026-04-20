@extends('layouts.app')

@section('title', 'Ganadores — Saltos Sorteios')

@section('content')

<div class="max-w-4xl mx-auto px-4 py-10 text-white">

    <h1 class="text-3xl font-black neon-text mb-6 text-center">
        🏆 Últimos Ganhadores
    </h1>

    <div class="space-y-4">

        @forelse($winners as $raffle)

            <div class="rounded-xl shadow-lg overflow-hidden card-dark">

                <!-- ENCABEZADO DEL SORTEO -->
                <div class="flex justify-between items-center px-5 py-3"
                     style="border-bottom: 1px solid rgba(57,255,20,0.15);">
                    <div>
                        <div class="text-lg font-black gold-text">
                            📱 {{ $raffle->name ?? 'Sorteio #' . $raffle->id }}
                        </div>
                        <div class="text-gray-400 text-xs">
                            {{ $raffle->updated_at->format('d/m/Y') }}
                        </div>
                    </div>

                    @if($raffle->prizes->isEmpty())
                        <span class="text-xs bg-gray-800 text-gray-400 px-2 py-1 rounded-full">
                            1 prêmio
                        </span>
                    @else
                        <span class="text-xs px-2 py-1 rounded-full font-bold"
                              style="background: rgba(57,255,20,0.1); border:1px solid rgba(57,255,20,0.4); color:#39FF14;">
                            {{ $raffle->prizes->count() }} prêmios
                        </span>
                    @endif
                </div>

                <!-- PREMIOS MÚLTIPLES -->
                @if($raffle->prizes->isNotEmpty())

                    <div>
                        @foreach($raffle->prizes as $prize)
                            <div class="flex justify-between items-center px-5 py-3"
                                 style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <div>
                                    <div class="text-sm font-bold gold-text">
                                        {{ $prize->name }}
                                    </div>
                                    @if($prize->description)
                                        <div class="text-gray-400 text-xs">
                                            {{ $prize->description }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="neon-text font-black">
                                        Nº {{ str_pad($prize->winner_number, 2, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="text-gray-300 text-xs">
                                        {{ $prize->winner_name ?? 'Ganhador' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                <!-- GANADOR LEGACY (1 solo premio) -->
                @elseif($raffle->winner_number)

                    <div class="flex justify-between items-center px-5 py-4">
                        <div class="text-gray-400 text-sm">Ganhador único</div>
                        <div class="text-right">
                            <div class="neon-text text-xl font-black">
                                Nº {{ str_pad($raffle->winner_number, 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <div class="gold-text text-sm">
                                {{ $raffle->winner_name ?? 'Ganhador' }}
                            </div>
                        </div>
                    </div>

                @endif

            </div>

        @empty

            <div class="text-center text-gray-400 py-10">
                Nenhum ganhador ainda
            </div>

        @endforelse

    </div>

</div>

@endsection
