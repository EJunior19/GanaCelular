@extends('layouts.app')

@section('title', 'Admin — Saltos Sorteios')

@section('content')

@if(!$raffle)

    <div class="text-center neon-text mt-10 text-lg font-bold">
        Nenhum sorteio criado ainda
    </div>

    <div class="text-center mt-4">
        <a href="/admin/create" class="btn-neon px-6 py-3 rounded-xl font-black inline-block neon-glow">
            + Criar primeiro sorteio
        </a>
    </div>

@else

    <!-- HEADER -->
    <div class="flex justify-between mb-4 items-center gap-2">
        <a href="/admin/create"
           class="btn-neon px-3 py-2 rounded-lg text-sm font-black neon-glow">
            + Criar
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
            Sair
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
                🏅 {{ $raffle->prizes->count() }} prêmios
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
            <h3 class="gold-text font-black text-sm mb-3">🏆 Resultados do sorteio</h3>

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
                        <div class="text-gray-500 text-xs">Pendente</div>
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
            🎯 REALIZAR SORTEIO
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

<!-- Modal mensajes WhatsApp -->
<div id="modal-whatsapp" class="fixed inset-0 z-50 hidden items-center justify-center px-3" style="background-color: rgba(0,0,0,0.95);">
    <div class="bg-gradient-to-b from-[#0a0a0a] to-[#000000] rounded-2xl w-full max-w-md shadow-2xl max-h-[90vh] flex flex-col"
         style="border: 2px solid rgba(57,255,20,0.3); box-shadow: 0 0 20px rgba(57,255,20,0.2);">

        <!-- Header fijo -->
        <div class="flex justify-between items-center p-4 border-b shrink-0"
             style="border-color: rgba(57,255,20,0.2); background: #111;">
            <h2 class="neon-text font-black text-lg">📲 Mensagens WhatsApp</h2>
            <button onclick="cerrarModal()" class="text-gray-400 hover:text-white text-2xl leading-none p-1 rounded">×</button>
        </div>

        <!-- Loading -->
        <div id="modal-loading" class="p-8 text-center text-gray-400 shrink-0">
            <div class="text-4xl mb-3">⏳</div>
            <p>Gerando mensagens...</p>
        </div>

        <!-- Lista scrolleable -->
        <div id="modal-contenido" class="hidden flex-1 overflow-y-auto p-4 space-y-2 pb-6"></div>

    </div>
</div>

<script>
const RAFFLE_STATUS = '{{ $raffle->status ?? "active" }}';
const RAFFLE_IMAGE  = '{{ $raffle->image ?? "" }}';

const ETIQUETAS = {
    mensaje_completo:   { titulo: '📋 Completo',           color: 'neon'   },
    urgencia:           { titulo: '🔥 Urgência',           color: 'red'    },
    invitacion:         { titulo: '🎉 Convite',            color: 'purple' },
    flash:              { titulo: '⚡ Flash',              color: 'green'  },
    ultima_oportunidad: { titulo: '🚨 Última Chance',      color: 'red'    },
    anuncio_ganadores:  { titulo: '🏆 Ganhadores',         color: 'gold'   },
    agradecimiento:     { titulo: '🙏 Agradecimento',      color: 'pink'   },
    compartir_grupo:    { titulo: '🔗 Compartilhar Grupo', color: 'teal'   },
};

const COLOR_MAP = {
    neon:   'border-[#39FF14]/40 bg-[#39FF14]/10',
    red:    'border-red-500/40 bg-red-900/20',
    purple: 'border-purple-500/40 bg-purple-900/20',
    green:  'border-green-500/40 bg-green-900/20',
    gold:   'border-[#FFD700]/40 bg-yellow-900/20',
    pink:   'border-pink-500/40 bg-pink-900/20',
    teal:   'border-teal-500/40 bg-teal-900/20',
};

const BTN_MAP = {
    neon:   'bg-[#39FF14] hover:bg-[#32DD12] text-black',
    red:    'bg-red-600 hover:bg-red-500 text-white',
    purple: 'bg-purple-600 hover:bg-purple-500 text-white',
    green:  'bg-green-600 hover:bg-green-500 text-white',
    gold:   'bg-[#FFD700] hover:bg-yellow-400 text-black',
    pink:   'bg-pink-600 hover:bg-pink-500 text-white',
    teal:   'bg-teal-600 hover:bg-teal-500 text-white',
};

function cerrarModal() {
    document.getElementById('modal-whatsapp').classList.add('hidden');
    document.getElementById('modal-whatsapp').classList.remove('flex');
}

async function copiarImagenSorteo(imagenPath) {
    if (!imagenPath) { alert('O sorteio não tem imagem'); return; }
    const btn = document.getElementById('btn-copiar-imagen-modal');
    const original = btn.innerHTML;
    btn.innerHTML = '⏳ Copiando...';
    btn.disabled = true;
    try {
        const response = await fetch(`/storage/${imagenPath}`);
        const blob = await response.blob();
        const img = new Image();
        img.onload = async function() {
            const canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;
            canvas.getContext('2d').drawImage(img, 0, 0);
            canvas.toBlob(async (pngBlob) => {
                try {
                    await navigator.clipboard.write([new ClipboardItem({ 'image/png': pngBlob })]);
                    btn.innerHTML = '✅ Imagem copiada!';
                    setTimeout(() => {
                        btn.innerHTML = original;
                        btn.disabled = false;
                    }, 1500);
                } catch (e) {
                    alert('Erro: ' + e.message);
                    btn.innerHTML = original;
                    btn.disabled = false;
                }
            }, 'image/png');
        };
        img.onerror = () => {
            alert('Não foi possível carregar a imagem');
            btn.innerHTML = original;
            btn.disabled = false;
        };
        img.src = URL.createObjectURL(blob);
    } catch (e) {
        alert('Erro: ' + e.message);
        btn.innerHTML = original;
        btn.disabled = false;
    }
}

async function enviarWhatsapp(raffleId) {
    if (!raffleId) return;
    const btn = document.getElementById('btn-whatsapp');
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ Carregando...';

    const modal = document.getElementById('modal-whatsapp');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('modal-loading').classList.remove('hidden');
    document.getElementById('modal-contenido').classList.add('hidden');
    document.getElementById('modal-contenido').innerHTML = '';

    try {
        const response = await fetch(`/admin/whatsapp/${raffleId}`);
        const data = await response.json();
        if (!response.ok || data.error) throw new Error(data.error || 'Erro do servidor');

        const mensajes = generarMensajes(data);
        const contenido = document.getElementById('modal-contenido');

        const ordenActive = [
            'mensaje_completo',
            'urgencia',
            'invitacion',
            'flash',
            'ultima_oportunidad',
            'compartir_grupo',
        ];

        const ordenFinished = [
            'anuncio_ganadores',
            'agradecimiento',
            'compartir_grupo',
        ];

        const orden = RAFFLE_STATUS === 'finished' ? ordenFinished : ordenActive;

        for (const key of orden) {
            if (!mensajes[key]) continue;
            const meta = ETIQUETAS[key];
            contenido.innerHTML += `
                <div class="flex justify-between items-center p-3 rounded-lg border ${COLOR_MAP[meta.color]}">
                    <span class="text-white text-sm font-bold">${meta.titulo}</span>
                    <button onclick="copiarMensaje('${key}', this)"
                        class="${BTN_MAP[meta.color]} text-xs font-bold px-4 py-2 rounded-lg whitespace-nowrap">
                        Copiar
                    </button>
                    <div id="msg-${key}" style="display:none;">${escapeHtml(mensajes[key])}</div>
                </div>`;
        }

        // Botón copiar imagen AL FINAL
        if (RAFFLE_IMAGE) {
            contenido.innerHTML += `
                <div class="pt-2 border-t mt-2" style="border-color: rgba(57,255,20,0.2);">
                    <button
                        id="btn-copiar-imagen-modal"
                        onclick="copiarImagenSorteo('${RAFFLE_IMAGE}')"
                        class="w-full bg-blue-500 hover:bg-blue-400 text-white font-bold py-3 px-4 rounded-lg transition flex items-center justify-center gap-2">
                        🖼️ Copiar Imagem do Sorteio
                    </button>
                </div>`;
        }

        document.getElementById('modal-loading').classList.add('hidden');
        document.getElementById('modal-contenido').classList.remove('hidden');

    } catch (error) {
        document.getElementById('modal-loading').innerHTML = `
            <div class="text-4xl mb-3">❌</div>
            <p class="text-red-400">Erro: ${error.message}</p>
            <button onclick="cerrarModal()" class="mt-4 bg-red-500 text-white px-4 py-2 rounded">Fechar</button>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    }
}

function generarMensajes(data) {
    const {
        raffle_name = 'Sorteio',
        price = 0,
        prizes = [],
        numbers = []
    } = data;

    const precioFormato = new Intl.NumberFormat('es-PY', {
        useGrouping: true, minimumFractionDigits: 0
    }).format(price).replace(/\s/g, '.');

    const totalNumeros      = numbers.length;
    const numerosAsignados  = numbers.filter(n => n.customer_name && n.customer_name.trim() !== '').length;
    const numerosLibres     = totalNumeros - numerosAsignados;
    const porcentajeVendido = totalNumeros > 0 ? Math.round((numerosAsignados / totalNumeros) * 100) : 0;

    const medallas = ['🥇','🥈','🥉','🎁','🎀','🌟','💫','✨','🎯','🎪'];

    let listaPremios = '🏆 *PRÊMIOS:*\n';
    if (prizes.length > 0) {
        prizes.forEach((p, i) => {
            listaPremios += `${medallas[i] || '🎁'} *${i+1}° Prêmio:* ${p.name || 'Prêmio'}`;
            if (p.description) listaPremios += ` ${p.description}`;
            listaPremios += '\n';
        });
    } else {
        listaPremios += '🎁 *Prêmios surpresa!*\n';
    }

    let listaNumeros = '';
    numbers.forEach(num => {
        const nombre = num.customer_name || '';
        if (num.status === 'sold' || num.paid) {
            listaNumeros += `${num.number} - ${nombre} 💵\n`;
        } else if (nombre) {
            listaNumeros += `${num.number} - ${nombre}\n`;
        } else {
            listaNumeros += `${num.number}\n`;
        }
    });

    const disponibles = numbers.filter(n => n.status === 'free');
    let numerosDisponiblesLista = '';
    disponibles.forEach((num, idx) => {
        numerosDisponiblesLista += num.number;
        if ((idx + 1) % 10 === 0 || idx === disponibles.length - 1) {
            numerosDisponiblesLista += '\n';
        } else {
            numerosDisponiblesLista += ', ';
        }
    });

    let listaGanadores = '';
    if (prizes.length > 0) {
        prizes.forEach((p, i) => {
            const ganador = p.winner_name || 'A sortear';
            const numero  = p.winner_number ? `Nº ${p.winner_number}` : '---';
            listaGanadores += `${medallas[i] || '🎁'} *${i+1}° Prêmio:* ${p.name || p.description || 'Prêmio'}\n`;
            listaGanadores += `   👤 ${ganador} — ${numero}\n`;
        });
    } else {
        listaGanadores = '🎁 Sem prêmios registrados\n';
    }

    let mensajeUrgencia = '';
    if (numerosLibres === 0) {
        mensajeUrgencia =
`🔥🔥🔥
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🚨 *ESGOTADO!* 🚨
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
*${raffle_name}* já não tem números disponíveis

Obrigado a todos que participaram! 🙏
🍀 Até o próximo sorteio!`;

    } else if (numerosLibres <= 5) {
        mensajeUrgencia =
`🔥 *ÚLTIMOS ${numerosLibres} NÚMEROS!* 🔥
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎫 *NÚMEROS DISPONÍVEIS:*
${numerosDisponiblesLista}
💰 Gs. ${precioFormato} por número
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ Reserva já o teu número!
🍀 Não perca esta oportunidade!`;

    } else if (porcentajeVendido >= 70) {
        mensajeUrgencia =
`⚠️ *SE APROXIMA O FINAL!* ⚠️
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎫 *NÚMEROS DISPONÍVEIS:*
${numerosDisponiblesLista}
💰 Gs. ${precioFormato} por número
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ Ainda há tempo, mas pouco!
🍀 Boa sorte!`;

    } else if (porcentajeVendido >= 40) {
        mensajeUrgencia =
`⚡ *VENDE RÁPIDO!* ⚡
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎫 *NÚMEROS DISPONÍVEIS:*
${numerosDisponiblesLista}
💰 Gs. ${precioFormato} por número
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ Reserva antes que se esgotem!
🍀 Boa sorte!`;

    } else {
        mensajeUrgencia =
`🎉 *AINDA HÁ NÚMEROS!* 🎉
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎫 *NÚMEROS DISPONÍVEIS:*
${numerosDisponiblesLista}
💰 Gs. ${precioFormato} por número
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🍀 Escolhe o teu número favorito!`;
    }

    return {
        mensaje_completo:
`🎰✨ *SORTEIO EM CURSO!* ✨🎰
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎟️ *${raffle_name}*
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
${listaPremios}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
💰 *Preço:* Gs. ${precioFormato}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 *Estado:* ${porcentajeVendido}% vendido (${numerosAsignados}/${totalNumeros})
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎫 *LISTA DE NÚMEROS:*
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
${listaNumeros}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
💵 = Pago confirmado
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🚀 *QUER PARTICIPAR?*
👉 Escolhe o teu número
💵 Paga em dinheiro
✅ Pronto!
🍀 *Boa sorte a todos!* 🍀`,

        urgencia: mensajeUrgencia,

        invitacion:
`🎉 *CONVIDAMOS-TE A PARTICIPAR!* 🎉
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎟️ *${raffle_name}*

${listaPremios}
💰 Apenas Gs. ${precioFormato} por número

📲 *Como participar?*
1️⃣ Escolhe o teu número favorito
2️⃣ Paga em dinheiro
3️⃣ Confirma a tua participação

🏆 Todos têm chance de ganhar!
🍀 *Boa sorte!* 🍀`,

        flash:
`⚡ *FLASH* ⚡
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎰 *${raffle_name}*

📊 ${porcentajeVendido}% vendido
💰 Gs. ${precioFormato} por número
🎁 Prêmios incríveis!
🚀 Participa agora!

Compartilha com quem quiseres 📢`,

        ultima_oportunidad:
`🚨 *ÚLTIMA CHANCE!* 🚨
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎟️ *${raffle_name}*

📍 *Restam: ${numerosLibres} números*
⏰ *O tempo está a acabar!*

Quem não reservar agora
fica sem participar ❌

💰 Gs. ${precioFormato}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
👇 *RESERVA AGORA!* 👇`,

        anuncio_ganadores:
`🏆✨ *TEMOS GANHADORES!* ✨🏆
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎟️ *${raffle_name}*
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎉 *RESULTADOS OFICIAIS:*

${listaGanadores}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎊 *Parabéns aos ganhadores!*
📞 Serão contactados brevemente

🙏 Obrigado a todos os participantes
🍀 *Até o próximo sorteio!*`,

        agradecimiento:
`🙏 *OBRIGADO A TODOS!* 🙏
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎟️ *${raffle_name}*
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Queremos agradecer a cada um de vocês
pela confiança e participação ❤️

🌟 Sem vocês isto não seria possível
🤝 Cada número é uma demonstração
   do vosso apoio incondicional

💪 Continuamos a trabalhar para trazer
   mais e melhores sorteios

🔔 *Fiquem atentos ao próximo sorteio!*
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🍀 *Até breve e boa sorte!* 🍀`,

        compartir_grupo:
`🔗 *JUNTA-TE AO NOSSO GRUPO!* 🔗
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎰 *${raffle_name}*

Queres participar nos nossos sorteios?
Junta-te ao grupo e fica a saber de tudo! 👇

📲 *GRUPO OFICIAL:*
https://chat.whatsapp.com/IW4f2FC2Nwj6bbcWuAlLeD

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ Sorteios ao vivo
✅ Resultados instantâneos
✅ Prêmios incríveis
✅ 100% confiável!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Partilha com os teus amigos 📢
🍀 *Boa sorte a todos!* 🍀`
    };
}

async function copiarMensaje(key, btnEl) {
    const texto = document.getElementById('msg-' + key)?.innerText || '';
    if (!texto) { alert('Não há texto para copiar'); return; }
    try {
        await navigator.clipboard.writeText(texto);
        const original = btnEl.innerHTML;
        btnEl.innerHTML = '✅';
        setTimeout(() => { btnEl.innerHTML = original; }, 800);
    } catch (e) {
        alert('Erro ao copiar: ' + e.message);
    }
}

function escapeHtml(str) {
    return str.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}
</script>

@endsection