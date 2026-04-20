@extends('layouts.app')

@section('title', 'Login Admin — Saltos Sorteios')

@section('content')

    <div class="min-h-[70vh] flex items-center justify-center">

        <div class="w-full bg-[#0a0a0a] p-6 rounded-2xl neon-border"
             style="box-shadow: 0 0 30px rgba(57,255,20,0.15);">

            <!-- LOGO -->
            <div class="flex justify-center mb-4">
                <img src="/logo.png" alt="Logo" class="w-24 h-24 rounded-full"
                     style="border: 3px solid #39FF14; box-shadow: 0 0 20px #39FF14, 0 0 40px rgba(57,255,20,0.3);">
            </div>

            <!-- TITULO -->
            <h1 class="text-2xl font-extrabold text-center mb-1 neon-text">
                Painel Admin
            </h1>

            <p class="text-center text-[#39FF14]/60 text-sm mb-5">
                Saltos Sorteios — Acesso seguro
            </p>

            <!-- ERROR -->
            @if(session('error'))
                <div class="bg-red-500/90 text-white p-3 rounded-xl mb-4 text-center text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- FORM -->
            <form method="POST" action="/admin/login" class="space-y-4">
                @csrf

                <input type="password" name="password" placeholder="🔒 Senha"
                    class="w-full p-4 rounded-xl text-white text-lg bg-black outline-none focus:ring-2 focus:ring-[#39FF14] transition"
                    style="border: 1px solid #39FF14; box-shadow: 0 0 6px rgba(57,255,20,0.2);">

                <button class="w-full btn-neon py-4 text-lg rounded-xl font-black shadow-lg neon-glow">
                    🚀 Entrar
                </button>
            </form>

            <!-- FOOTER -->
            <p class="text-center text-xs text-gray-500 mt-5">
                Sistema seguro • Saltos Sorteios
            </p>

        </div>

    </div>

    <style>
        input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 1000px #000 inset !important;
            -webkit-text-fill-color: white !important;
        }
    </style>

@endsection
