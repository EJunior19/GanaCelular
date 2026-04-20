@extends('layouts.app')

@section('title', 'Criar Sorteio — Saltos Sorteios')

@section('content')

<div class="bg-[#0a0a0a] p-6 rounded-2xl card-dark">

    <h1 class="text-2xl neon-text mb-5 text-center font-black">
        📱 Criar Sorteio
    </h1>

    @if ($errors->any())
        <div class="bg-red-600/80 text-white p-3 rounded-xl mb-4"
             style="border: 1px solid rgba(239,68,68,0.5);">
            @foreach ($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <!-- NOMBRE -->
        <input
            name="name"
            required
            placeholder="Nome do sorteio (ex: iPhone 15 Pro)"
            value="{{ old('name') }}"
            class="w-full p-4 rounded-xl bg-black text-white outline-none focus:ring-2 focus:ring-[#39FF14] transition"
            style="border: 1px solid #39FF14; box-shadow: 0 0 6px rgba(57,255,20,0.15);"
        >

        <!-- PRECIO -->
        <input
            id="price"
            name="price"
            required
            type="text"
            placeholder="10.000"
            value="{{ old('price') }}"
            class="w-full p-4 rounded-xl bg-black text-white outline-none focus:ring-2 focus:ring-[#39FF14] transition"
            style="border: 1px solid #39FF14; box-shadow: 0 0 6px rgba(57,255,20,0.15);"
        >

        <!-- CANTIDAD DE NÚMEROS -->
        <input
            name="total_numbers"
            required
            type="number"
            min="1"
            placeholder="Quantidade de números (ex: 50)"
            value="{{ old('total_numbers') }}"
            class="w-full p-4 rounded-xl bg-black text-white outline-none focus:ring-2 focus:ring-[#39FF14] transition"
            style="border: 1px solid #39FF14; box-shadow: 0 0 6px rgba(57,255,20,0.15);"
        >

        <!-- IMAGEN -->
        <input
            type="file"
            name="image"
            accept="image/*"
            required
            class="w-full p-3 rounded-xl bg-black text-white transition"
            style="border: 1px solid #39FF14;"
        >
        <img id="preview" class="mt-3 rounded-xl hidden w-full h-40 object-cover"
             style="border: 1px solid #39FF14; box-shadow: 0 0 10px rgba(57,255,20,0.2);" />

        <!-- SEPARADOR -->
        <div class="pt-4" style="border-top: 1px solid rgba(57,255,20,0.2);">
            <h2 class="neon-text font-black mb-1">📱 Prêmios do sorteio</h2>
            <p class="text-gray-400 text-xs mb-4">
                O sorteio irá do último prêmio ao 1º prêmio para gerar suspense.
            </p>

            <!-- QUANTIDADE DE PRÊMIOS -->
            <div class="flex items-center gap-3 mb-4">
                <label class="text-white text-sm whitespace-nowrap">Quantidade de prêmios:</label>
                <input
                    id="prizesCount"
                    name="prizes_count"
                    type="number"
                    min="1"
                    max="20"
                    value="{{ old('prizes_count', 1) }}"
                    class="w-24 p-3 rounded-xl bg-black text-white text-center font-bold outline-none focus:ring-2 focus:ring-[#39FF14]"
                    style="border: 1px solid #39FF14;"
                >
            </div>

            <!-- PREMIOS DINÁMICOS -->
            <div id="prizesContainer" class="space-y-3"></div>
        </div>

        <button
            type="submit"
            class="w-full btn-neon py-4 rounded-xl font-black mt-2 neon-glow"
        >
            Criar Sorteio
        </button>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Formato precio ─────────────────────────────────────────
    const priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = new Intl.NumberFormat('es-PY').format(value);
        });
    }

    // ── Preview imagen ─────────────────────────────────────────
    const fileInput = document.querySelector('input[name="image"]');
    const preview   = document.getElementById('preview');
    fileInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
        }
    });

    // ── Premios dinámicos ──────────────────────────────────────
    const prizesCountInput = document.getElementById('prizesCount');
    const prizesContainer  = document.getElementById('prizesContainer');

    const oldPrizes = @json(old('prizes', []));

    function ordinalLabel(pos, total) {
        if (pos === 1) return '🥇 1º Prêmio <span style="color:#39FF14;font-size:0.75rem;">(Principal — sorteado por último)</span>';
        if (pos === 2) return '🥈 2º Prêmio';
        if (pos === 3) return '🥉 3º Prêmio';
        return `🏅 ${pos}º Prêmio`;
    }

    function renderPrizes(count) {
        prizesContainer.innerHTML = '';

        for (let i = 0; i < count; i++) {
            const pos       = i + 1;
            const oldName   = oldPrizes[i] ? oldPrizes[i].name        : '';
            const oldDesc   = oldPrizes[i] ? oldPrizes[i].description : '';
            const isLast    = pos === count;
            const extraNote = isLast && count > 1
                ? '<span style="color:#666;font-size:0.75rem;margin-left:0.5rem;">(sorteado primeiro)</span>'
                : '';

            prizesContainer.innerHTML += `
                <div style="background:#0a0a0a; border:1px solid rgba(57,255,20,0.2); border-radius:0.75rem; padding:1rem; margin-bottom:0.5rem;">
                    <div style="font-size:0.875rem; font-weight:900; color:#39FF14; margin-bottom:0.5rem;">
                        ${ordinalLabel(pos, count)}${extraNote}
                    </div>
                    <input
                        name="prizes[${i}][name]"
                        required
                        placeholder="Nome do prêmio (ex: iPhone 15 Pro 128GB)"
                        value="${escHtml(oldName)}"
                        style="width:100%; padding:0.75rem; border-radius:0.5rem; background:#000; color:white; border:1px solid rgba(57,255,20,0.4); outline:none; font-size:0.875rem; margin-bottom:0.5rem; box-sizing:border-box;"
                    >
                    <input
                        name="prizes[${i}][description]"
                        placeholder="Descrição adicional (opcional)"
                        value="${escHtml(oldDesc)}"
                        style="width:100%; padding:0.75rem; border-radius:0.5rem; background:#000; color:#aaa; border:1px solid rgba(57,255,20,0.2); outline:none; font-size:0.875rem; box-sizing:border-box;"
                    >
                </div>
            `;
        }
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    prizesCountInput.addEventListener('input', function () {
        const val = Math.max(1, Math.min(20, parseInt(this.value) || 1));
        this.value = val;
        renderPrizes(val);
    });

    renderPrizes(parseInt(prizesCountInput.value) || 1);
});
</script>

@endsection
