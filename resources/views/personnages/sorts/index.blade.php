@extends('layouts.app')
@section('title', $personnage->nom . ' — Sorts connus')

@push('styles')
    <style>
        .sorts-page {
            max-width: 880px;
            margin: 0 auto;
            color: #e6b84d;
        }

        .maquette-header {
            background: rgba(25,10,50,0.95);
            border: 1px solid #6b2b8a;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        /* Use same 3-column grid as levels so columns align */
        .spell-slots {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: start;
        }

        .slot-box {
            flex: 1;
            border: 1px dashed rgba(107,43,138,0.6);
            padding: 0.75rem;
            text-align: center;
            border-radius: 6px;
            background: rgba(107,43,138,0.04);
            color: #f1d484;
        }

        .cast-bar {
            background: rgba(25,10,50,0.95);
            border: 1px solid rgba(107,43,138,0.6);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        /* cast-grid uses the same 3-column template to align with slots and levels */
        .cast-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            align-items: center;
        }

        .cast-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: #f1d484;
            font-weight: 600;
        }

        .levels {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .level-card {
            background: rgba(25,10,50,0.95);
            border: 1px solid rgba(107,43,138,0.6);
            padding: 0.75rem;
            border-radius: 8px;
            min-height: 300px;
            color: #e6b84d;
        }

        .level-card .slots {
            margin-bottom: 0.75rem;
            color: #f1d484;
            font-weight: 700;
        }

        .spell-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .spell-list li {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        /* Checkbox styled as yellow square */
        .prepare-checkbox {
            position: relative;
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 6px;
            vertical-align: middle;
        }

        .prepare-checkbox input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 14px;
            height: 14px;
            margin: 0;
            cursor: pointer;
        }

        .prepare-checkbox .box {
            width: 14px;
            height: 14px;
            border-radius: 3px;
            background: rgba(241,212,132,0.15);
            border: 1px solid rgba(241,212,132,0.35);
            display: inline-block;
        }

        .prepare-checkbox input[type="checkbox"]:checked + .box {
            background: #f1d484;
            border-color: #f1d484;
        }

        .prepare-checkbox input[type="checkbox"]:disabled + .box {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .bottom-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            gap: 1rem;
        }

        .btn-yellow {
            background: #f1d484;
            color: #1a0533;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
        }

        .btn-purple {
            background: #6b2b8a;
            color: #f1d484;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
        }
    </style>
@endpush

@section('content')
    <div class="sorts-page">
        <h2 style="text-align:center; margin-bottom: 0.5rem; color: #e6b84d;">Sorts</h2>

        <div class="maquette-header">
            <div class="spell-slots">
                <div class="slot-box">Abilité sort: </div>
                <div class="slot-box">Sauvegarde sort: </div>
                <div class="slot-box">Modificateur sort: </div>
            </div>
            <div style="font-weight:700; color:#f1d484;">
                Limite tour de magie : {{ $limiteTour ?? '—' }}
                <br>
                Limite sort : {{ $limiteSort ?? '—' }}
                @if(!empty($slots))
                    <div style="margin-top:0.5rem; color:#f1d484; font-weight:600;">
                        Emplacements — N1: {{ $slots['Niveau1'] ?? '—' }}, N2: {{ $slots['Niveau2'] ?? '—' }}, N3: {{ $slots['Niveau3'] ?? '—' }}
                    </div>
                @endif
            </div>
        </div>

        <div class="cast-bar">
            <div style="text-align:center; font-weight:700; margin-bottom:0.6rem; color:#e6b84d">Tour magie</div>
            <div class="cast-grid">
                @php $cantrips = $level0 ?? collect(); @endphp
                @if($cantrips->isEmpty())
                    @for($i=0;$i<6;$i++)
                        <div class="cast-item">
                            <div class="checkbox-square"></div>
                            <div>—</div>
                        </div>
                    @endfor
                @else
                    @foreach($cantrips as $c)
                        <div class="cast-item">
                            <label class="prepare-checkbox">
                                @php $p = (int) ($c->prepare ?? 0); @endphp
                                <input type="checkbox"
                                       @if($p === 1) checked @endif
                                       @if($p === 2) checked disabled @endif
                                       data-sort-id="{{ $c->sort->id ?? '' }}">
                                <span class="box"></span>
                            </label>
                            <div style="display:flex; flex-direction:column;">
                                <span>{{ $c->sort->Nom ?? 'Sort' }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="levels">
            @foreach($levels as $niveau)
                <div class="level-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <div style="font-weight:800;">Niveau {{ $niveau }}</div>
                        <div class="slots">Emplacement: •••</div>
                    </div>

                    <ul class="spell-list">
                        @foreach(($available ?? collect())->filter(fn($s) => (int) ($s->niveau ?? 1) === (int) $niveau) as $item)
                            <li>
                                <label class="prepare-checkbox">
                                    @php $p = (int) ($item->prepare ?? 0); @endphp
                                    <input type="checkbox"
                                           @if($p === 1) checked @endif
                                           @if($p === 2) checked disabled @endif
                                           data-sort-id="{{ $item->sort->id ?? '' }}">
                                    <span class="box"></span>
                                </label>

                                <div style="color:#e6b84d; display:flex; gap:0.5rem; align-items:center;">
                                    <span>{{ $item->sort->Nom ?? 'Sort' }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="bottom-actions">
            <a href="{{ route('personnages.show', $personnage) }}" class="btn-yellow">Retour</a>
            <button class="btn-purple">Ajouter</button>
        </div>
    </div>
@endsection
