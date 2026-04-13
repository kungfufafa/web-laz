@php($title = 'PPOB Web')

@extends('layouts.web')

@section('content')
    @php($iconTone = [
        'prepaid' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        'postpaid' => 'bg-sky-50 text-sky-600 ring-sky-100',
    ])

    <div class="space-y-6">
        <section class="rounded-[2rem] border border-white/70 bg-white/90 p-7 shadow-[0_30px_80px_-40px_rgba(15,23,42,0.35)]">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-600">PPOB</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950 sm:text-4xl">Beli layanan digital tanpa banyak langkah.</h1>
                </div>
                <div class="rounded-full border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-700">
                    {{ strtoupper($activeGateway) }}
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] border border-zinc-200/80 bg-white/95 p-6 shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]">
            <div class="space-y-6">
                <div>
                    <p class="text-sm font-semibold text-zinc-500">Isi Ulang</p>
                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
                        @foreach ($prepaidJourneyOptions as $journey)
                            <a href="{{ route('ppob.catalog', ['serviceType' => 'prepaid', 'journey' => $journey['key']]) }}" class="group rounded-[1.75rem] border border-zinc-200 bg-white px-4 py-4 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-100/40">
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl ring-1 {{ $iconTone['prepaid'] }}">
                                    @include('ppob.partials.journey-icon', ['journey' => $journey['key'], 'attributes' => new \Illuminate\View\ComponentAttributeBag(['class' => 'h-5 w-5'])])
                                </span>
                                <span class="mt-4 block text-sm font-semibold text-zinc-950">{{ $journey['title'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold text-zinc-500">Tagihan</p>
                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
                        @foreach ($postpaidJourneyOptions as $journey)
                            <a href="{{ route('ppob.catalog', ['serviceType' => 'postpaid', 'journey' => $journey['key']]) }}" class="group rounded-[1.75rem] border border-zinc-200 bg-white px-4 py-4 transition hover:-translate-y-0.5 hover:border-sky-300 hover:shadow-lg hover:shadow-sky-100/50">
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl ring-1 {{ $iconTone['postpaid'] }}">
                                    @include('ppob.partials.journey-icon', ['journey' => $journey['key'], 'attributes' => new \Illuminate\View\ComponentAttributeBag(['class' => 'h-5 w-5'])])
                                </span>
                                <span class="mt-4 block text-sm font-semibold text-zinc-950">{{ $journey['title'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
