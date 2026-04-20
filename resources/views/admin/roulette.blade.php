@extends('layouts.app')

@section('title', 'Sorteo en vivo — Saltos Sorteios')

@section('content')
@php
    $soldNumbers = $raffle->numbers->where('status', 'sold')->values();

    $items = $soldNumbers->map(function ($n) {
        return [
            'id'     => $n->id,
            'number' => str_pad($n->number, 2, '0', STR_PAD_LEFT),
            'name'   => $n->customer_name ?? 'Participante',
        ];
    })->values();

    // Premios ordenados de menor a mayor (orden de sorteo: último primero)
    $prizes        = $raffle->prizes->sortBy('order')->values();
    $isMultiPrize  = $prizes->isNotEmpty();
@endphp

<div class="max-w-6xl mx-auto px-4 py-8 text-center">

    <h1 class="text-4xl font-extrabold neon-text mb-2">📱 SORTEIO AO VIVO</h1>
    <p class="text-gray-400 text-sm mb-6">{{ $raffle->name }}</p>

    <audio id="spinSound" src="/sounds/spin.mp3"></audio>
    <audio id="winSound"  src="/sounds/win.mp3"></audio>

    <div id="confetti" class="pointer-events-none fixed inset-0 overflow-hidden z-40"></div>

    <!-- Cuenta regresiva -->
    <div id="countdownOverlay"
         class="hidden fixed inset-0 z-50 bg-black/80 flex items-center justify-center">
        <div id="countdownValue"
             class="text-8xl md:text-9xl font-black neon-text animate-pulse">
            3
        </div>
    </div>

    @if($isMultiPrize)
    <!-- BARRA DE PROGRESO DE PREMIOS -->
    <div id="prizesProgress" class="flex flex-wrap justify-center gap-2 mb-6">
        @foreach($prizes->sortByDesc('order') as $prize)
            <div id="prize-pill-{{ $prize->order }}"
                 class="px-3 py-1 rounded-full text-xs font-bold border transition-all
                        {{ $prize->winner_number ? 'border-[#39FF14] text-[#39FF14]' : 'border-gray-600 text-gray-500' }}"
                 style="{{ $prize->winner_number ? 'background:rgba(57,255,20,0.1); box-shadow:0 0 6px rgba(57,255,20,0.3);' : 'background:#111;' }}">
                {{ $prize->name }}
                @if($prize->winner_number)
                    <span class="ml-1">✓</span>
                @endif
            </div>
        @endforeach
    </div>

    <!-- HEADER DEL PREMIO ACTUAL -->
    <div id="currentPrizeHeader" class="mb-6">
        <div id="currentPrizeLabel"
             class="text-2xl font-extrabold neon-text animate-pulse">
            —
        </div>
        <div id="currentPrizeDesc" class="text-gray-400 text-sm mt-1"></div>
    </div>
    @endif

    <!-- Indicador de giro -->
    <div id="spinStatusBox"
         class="hidden max-w-md mx-auto mb-8 rounded-2xl px-6 py-5 neon-border"
         style="background:rgba(57,255,20,0.05); box-shadow: 0 0 20px rgba(57,255,20,0.2);">
        <div class="neon-text text-lg font-black mb-2">🎯 Sorteando...</div>
        <div id="spinTimer" class="text-5xl font-black text-white">6</div>
        <div class="text-gray-300 mt-2 font-semibold">segundos restantes</div>
    </div>

    <!-- RULETA -->
    <div class="relative max-w-4xl mx-auto">
        <div class="absolute left-1/2 -translate-x-1/2 -top-4 z-20">
            <div class="w-0 h-0 border-l-[18px] border-r-[18px] border-b-[28px] border-l-transparent border-r-transparent"
                 style="border-bottom-color: #39FF14; filter: drop-shadow(0 0 6px #39FF14);"></div>
        </div>

        <div class="relative rounded-3xl px-6 py-10 overflow-hidden"
             style="border: 2px solid #39FF14; background: linear-gradient(180deg, #0a0a0a, #000); box-shadow: 0 0 20px rgba(57,255,20,0.3), inset 0 0 30px rgba(0,0,0,0.8);">
            <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-1 z-10"
                 style="background: rgba(57,255,20,0.3);"></div>
            <div class="overflow-hidden">
                <div id="track" class="flex gap-4 items-center will-change-transform"></div>
            </div>
        </div>
    </div>

    <!-- GANADOR -->
    <div id="winnerBox"
         class="hidden mt-10 max-w-2xl mx-auto rounded-2xl p-6 neon-border neon-glow"
         style="background: rgba(57,255,20,0.07);">
        <div class="text-4xl mb-3 animate-bounce">📱</div>
        <div id="winnerPrizeName" class="gold-text font-bold text-lg mb-1 hidden"></div>
        <h2 class="text-3xl font-extrabold neon-text mb-2">GANHADOR</h2>
        <div class="text-5xl font-black text-white mb-3">
            Nº <span id="winnerNumber" class="neon-text"></span>
        </div>
        <div class="text-2xl gold-text font-bold">
            <span id="winnerName"></span>
        </div>
    </div>

    @if($isMultiPrize)
    <!-- RESUMEN FINAL -->
    <div id="finalSummary" class="hidden mt-8 max-w-2xl mx-auto space-y-3">
        <h3 class="gold-text font-extrabold text-xl mb-4">🎉 Todos os prêmios sorteados</h3>
        <div id="finalPrizesList" class="space-y-2"></div>
    </div>
    @endif

    <!-- BOTÓN PRINCIPAL -->
    <button id="spinBtn"
        class="mt-8 btn-neon px-8 py-4 rounded-2xl font-extrabold text-lg shadow-lg transition transform hover:scale-105 neon-glow">
        🎯 INICIAR SORTEIO
    </button>

</div>

<style>
    #track { transition: transform 6s cubic-bezier(0.08, 0.75, 0.12, 1); }

    .draw-card {
        width: 150px; min-width: 150px; height: 150px;
        border-radius: 1rem;
        border: 1px solid rgba(57,255,20,0.3);
        background: linear-gradient(180deg, #111, #0a0a0a);
        color: white;
        display: flex; flex-direction: column;
        justify-content: center; align-items: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.5);
    }
    .draw-card-number { font-size: 2rem; font-weight: 900; line-height: 1; color: #39FF14; text-shadow: 0 0 8px #39FF14; }
    .draw-card-name {
        margin-top: .75rem; font-size: .95rem; font-weight: 700;
        color: #FFD700; max-width: 120px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .winner-card {
        animation: winnerPulse 1s ease-in-out infinite alternate;
        border-color: #39FF14 !important;
        box-shadow: 0 0 20px #39FF14, 0 0 40px rgba(57,255,20,0.4) !important;
    }
    @keyframes winnerPulse {
        from { transform: scale(1); }
        to   { transform: scale(1.06); }
    }
    .confetti-piece {
        position: absolute; width: 10px; height: 18px;
        opacity: .9; animation: confettiFall linear forwards;
    }
    @keyframes confettiFall {
        0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
        100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
    }
</style>

<script>
// ── Datos desde PHP ──────────────────────────────────────────────────────────
const baseItems           = @json($items);
const raffleId            = @json($raffle->id);
const csrfToken           = @json(csrf_token());
const existingWinnerNumber = @json($raffle->winner_number);
const isMultiPrize        = @json($isMultiPrize);

// Premios en orden de sorteo: index 0 = último premio (order=1), último index = 1er premio
let prizes = @json($prizes->values());

// ── Estado ───────────────────────────────────────────────────────────────────
let isRunning         = false;
let countdownInterval = null;
let spinInterval      = null;

// Índice del premio actual (primer sin ganador)
let currentPrizeIdx = isMultiPrize
    ? prizes.findIndex(p => !p.winner_number)
    : -1;

// ── Elementos DOM ────────────────────────────────────────────────────────────
const track              = document.getElementById('track');
const spinBtn            = document.getElementById('spinBtn');
const winnerBox          = document.getElementById('winnerBox');
const winnerPrizeName    = document.getElementById('winnerPrizeName');
const winnerNumberEl     = document.getElementById('winnerNumber');
const winnerNameEl       = document.getElementById('winnerName');
const spinSound          = document.getElementById('spinSound');
const winSound           = document.getElementById('winSound');
const confettiEl         = document.getElementById('confetti');
const countdownOverlay   = document.getElementById('countdownOverlay');
const countdownValue     = document.getElementById('countdownValue');
const spinStatusBox      = document.getElementById('spinStatusBox');
const spinTimer          = document.getElementById('spinTimer');
const currentPrizeLabel  = document.getElementById('currentPrizeLabel');
const currentPrizeDesc   = document.getElementById('currentPrizeDesc');
const finalSummary       = document.getElementById('finalSummary');
const finalPrizesList    = document.getElementById('finalPrizesList');

// ── Track ─────────────────────────────────────────────────────────────────────
function buildTrack() {
    track.style.transition = 'none';
    track.style.transform  = 'translateX(0)';
    track.innerHTML        = '';

    const expanded = [];
    for (let r = 0; r < 14; r++) {
        baseItems.forEach(item => expanded.push({ ...item }));
    }

    expanded.forEach((item, index) => {
        const card = document.createElement('div');
        card.className      = 'draw-card';
        card.dataset.number = item.number;
        card.dataset.name   = item.name;
        card.dataset.index  = index;
        card.innerHTML = `
            <div class="draw-card-number">${item.number}</div>
            <div class="draw-card-name">${item.name}</div>
        `;
        track.appendChild(card);
    });
}

function centerCard(card, animated = true) {
    const container    = track.parentElement;
    const containerWidth = container.offsetWidth;
    const translate    = card.offsetLeft - (containerWidth / 2) + (card.offsetWidth / 2);

    track.style.transition = animated
        ? 'transform 6s cubic-bezier(0.08, 0.75, 0.12, 1)'
        : 'none';

    track.style.transform = `translateX(-${translate}px)`;
}

// ── Confetti ──────────────────────────────────────────────────────────────────
function launchConfetti() {
    confettiEl.innerHTML = '';
    const colors = ['#39FF14','#FFD700','#ffffff','#00ffff','#ff00ff','#39FF14'];

    for (let i = 0; i < 120; i++) {
        const piece = document.createElement('div');
        piece.className = 'confetti-piece';
        piece.style.left            = Math.random() * 100 + 'vw';
        piece.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        piece.style.animationDuration = (2 + Math.random() * 2) + 's';
        piece.style.animationDelay  = (Math.random() * 0.4) + 's';
        confettiEl.appendChild(piece);
    }
    setTimeout(() => { confettiEl.innerHTML = ''; }, 5000);
}

// ── UI helpers ────────────────────────────────────────────────────────────────
function showWinner(number, name, prizeName) {
    winnerNumberEl.textContent = number;
    winnerNameEl.textContent   = name;

    if (prizeName && winnerPrizeName) {
        winnerPrizeName.textContent = prizeName;
        winnerPrizeName.classList.remove('hidden');
    } else if (winnerPrizeName) {
        winnerPrizeName.classList.add('hidden');
    }

    winnerBox.classList.remove('hidden');
}

function updatePrizeHeader() {
    if (!isMultiPrize || currentPrizeIdx < 0) return;

    const prize = prizes[currentPrizeIdx];
    if (!prize) return;

    if (currentPrizeLabel) {
        currentPrizeLabel.textContent = `Sorteando: ${prize.name}`;
    }
    if (currentPrizeDesc) {
        currentPrizeDesc.textContent = prize.description ?? '';
    }
}

function markPillDrawn(order) {
    const pill = document.getElementById(`prize-pill-${order}`);
    if (!pill) return;
    pill.style.background = 'rgba(57,255,20,0.1)';
    pill.style.borderColor = '#39FF14';
    pill.style.color = '#39FF14';
    pill.style.boxShadow = '0 0 6px rgba(57,255,20,0.3)';
    pill.classList.remove('animate-pulse');
    if (!pill.innerHTML.includes('✓')) pill.innerHTML += ' <span>✓</span>';
}

function markPillCurrent(order) {
    const pill = document.getElementById(`prize-pill-${order}`);
    if (!pill) return;
    pill.style.background = 'rgba(255,215,0,0.1)';
    pill.style.borderColor = '#FFD700';
    pill.style.color = '#FFD700';
    pill.classList.add('animate-pulse');
}

function showFinalSummary() {
    if (!finalSummary) return;

    finalPrizesList.innerHTML = '';
    const sorted = [...prizes].sort((a, b) => b.order - a.order);

    sorted.forEach(p => {
        finalPrizesList.innerHTML += `
            <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(57,255,20,0.07); border:1px solid rgba(57,255,20,0.25); border-radius:0.75rem; padding:0.75rem 1rem;">
                <div style="text-align:left;">
                    <div style="color:#39FF14; font-weight:900; font-size:0.875rem;">${p.name}</div>
                    ${p.description ? `<div style="color:#aaa;font-size:0.75rem;">${p.description}</div>` : ''}
                </div>
                <div style="text-align:right;">
                    <div style="color:white; font-weight:900;">Nº ${String(p.winner_number).padStart(2,'0')}</div>
                    <div style="color:#FFD700; font-size:0.75rem;">${p.winner_name ?? ''}</div>
                </div>
            </div>
        `;
    });

    finalSummary.classList.remove('hidden');
}

function resetUI() {
    isRunning = false;
    clearInterval(countdownInterval);
    clearInterval(spinInterval);
    spinBtn.disabled = false;
    spinBtn.classList.remove('opacity-60','cursor-not-allowed');

    try { spinSound.pause(); spinSound.currentTime = 0; } catch(e) {}
}

// ── Inicio de secuencia ───────────────────────────────────────────────────────
function startSequence() {
    if (isRunning || baseItems.length === 0) return;

    isRunning = true;
    winnerBox.classList.add('hidden');
    document.querySelectorAll('.draw-card').forEach(el => el.classList.remove('winner-card'));

    spinBtn.disabled = true;
    spinBtn.classList.add('opacity-60','cursor-not-allowed');
    spinBtn.textContent = '⏳ PREPARANDO...';

    let count = 3;
    countdownOverlay.classList.remove('hidden');
    countdownValue.textContent = count;

    countdownInterval = setInterval(() => {
        count--;
        if (count > 0) {
            countdownValue.textContent = count;
            return;
        }

        clearInterval(countdownInterval);
        countdownValue.textContent = '🎉';

        setTimeout(() => {
            countdownOverlay.classList.add('hidden');
            runDraw();
        }, 500);
    }, 1000);
}

// ── Sorteo principal ──────────────────────────────────────────────────────────
async function runDraw() {
    buildTrack();

    track.getBoundingClientRect();

    spinStatusBox.classList.remove('hidden');
    spinTimer.textContent = '6';

    try {
        spinSound.currentTime = 0;
        spinSound.loop = true;
        await spinSound.play().catch(() => {});
    } catch(e) {}

    const body = new URLSearchParams({ _token: csrfToken });
    if (isMultiPrize && currentPrizeIdx >= 0) {
        body.append('prize_order', prizes[currentPrizeIdx].order);
    }

    let response;
    try {
        response = await fetch(`/admin/sortear/${raffleId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: body.toString(),
        });
    } catch(e) {
        alert('Não foi possível conectar com o servidor.');
        resetUI();
        return;
    }

    if (!response.ok) {
        const err = await response.json().catch(() => ({}));
        alert(err.message ?? 'Ocorreu um erro ao escolher o ganhador.');
        resetUI();
        return;
    }

    const data = await response.json();

    const winNum   = String(data.winner_number).padStart(2, '0');
    const winName  = data.winner_name ?? 'Participante';
    const prizeName = data.prize_name ?? null;

    const allCards = [...document.querySelectorAll('.draw-card')];
    let target = allCards.find((card, idx) =>
        idx > baseItems.length * 7 &&
        card.dataset.number === winNum &&
        card.dataset.name   === winName
    );
    if (!target) {
        target = allCards.find((card, idx) =>
            idx > baseItems.length * 7 &&
            card.dataset.number === winNum
        );
    }
    if (!target) {
        alert('Não foi encontrada a carta ganhadora.');
        resetUI();
        return;
    }

    let secondsLeft = 6;
    spinInterval = setInterval(() => {
        secondsLeft--;
        if (secondsLeft >= 0) spinTimer.textContent = secondsLeft;
    }, 1000);

    centerCard(target, true);

    setTimeout(async () => {
        clearInterval(spinInterval);

        try {
            spinSound.pause(); spinSound.currentTime = 0;
            winSound.currentTime = 0;
            await winSound.play().catch(() => {});
        } catch(e) {}

        spinStatusBox.classList.add('hidden');
        target.classList.add('winner-card');
        showWinner(winNum, winName, prizeName);
        launchConfetti();

        isRunning = false;

        if (isMultiPrize) {
            prizes[currentPrizeIdx].winner_number = winNum;
            prizes[currentPrizeIdx].winner_name   = winName;
            markPillDrawn(prizes[currentPrizeIdx].order);

            if (data.all_drawn) {
                if (currentPrizeLabel) currentPrizeLabel.textContent = '🎉 Sorteio concluído!';
                if (currentPrizeDesc)  currentPrizeDesc.textContent  = '';

                showFinalSummary();

                spinBtn.textContent = '⬅️ VOLTAR AO PAINEL';
                spinBtn.disabled    = false;
                spinBtn.classList.remove('opacity-60','cursor-not-allowed');
                spinBtn.onclick = () => { window.location.href = '/admin'; };
            } else {
                currentPrizeIdx = prizes.findIndex(p => !p.winner_number);

                if (currentPrizeIdx >= 0) {
                    markPillCurrent(prizes[currentPrizeIdx].order);
                    updatePrizeHeader();
                }

                spinBtn.textContent = `▶️ PRÓXIMO PRÊMIO`;
                spinBtn.disabled    = false;
                spinBtn.classList.remove('opacity-60','cursor-not-allowed');
                spinBtn.onclick = () => startSequence();
            }

        } else {
            spinBtn.textContent = '⬅️ VOLTAR AO PAINEL';
            spinBtn.disabled    = false;
            spinBtn.classList.remove('opacity-60','cursor-not-allowed');
            spinBtn.onclick = () => { window.location.href = '/admin'; };
        }

    }, 6000);
}

// ── Inicialización ────────────────────────────────────────────────────────────
buildTrack();

if (isMultiPrize) {

    if (currentPrizeIdx < 0) {
        if (currentPrizeLabel) currentPrizeLabel.textContent = '🎉 Sorteio concluído!';
        if (currentPrizeDesc)  currentPrizeDesc.textContent  = '';

        showFinalSummary();
        spinBtn.textContent = '⬅️ VOLTAR AO PAINEL';
        spinBtn.onclick = () => { window.location.href = '/admin'; };

    } else {
        prizes.forEach(p => {
            if (p.winner_number) markPillDrawn(p.order);
        });
        markPillCurrent(prizes[currentPrizeIdx].order);
        updatePrizeHeader();

        spinBtn.onclick = () => startSequence();
    }

} else {
    spinBtn.onclick = () => startSequence();

    if (existingWinnerNumber) {
        const existing = [...document.querySelectorAll('.draw-card')].find((card, idx) =>
            idx > baseItems.length * 2 &&
            card.dataset.number === String(existingWinnerNumber).padStart(2, '0')
        );

        if (existing) {
            centerCard(existing, false);
            existing.classList.add('winner-card');
            showWinner(String(existingWinnerNumber).padStart(2, '0'), existing.dataset.name || 'Participante', null);

            spinBtn.textContent = '⬅️ VOLTAR AO PAINEL';
            spinBtn.onclick = () => { window.location.href = '/admin'; };
        }
    }
}
</script>

@endsection
