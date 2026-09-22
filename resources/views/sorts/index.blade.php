@extends('layouts.app')

@section('title', 'Sorts')

@section('content')
    <div class="card spells-page">
        <div class="card-header">
            <h2>Sorts</h2>
        </div>

        <div class="card-body">
            <div class="spell-controls">
                <div class="spell-presets">
                    <button class="btn btn-outline">Ajouter sort...</button>
                    <button class="btn btn-outline">Ajouter sort...</button>
                    <button class="btn btn-outline">Ajouter sort...</button>
                </div>

                <div class="spell-limit">Limite tour de magie : <strong>xx</strong></div>
            </div>

            <div class="spell-rounds card">
                <h3>Tour magie</h3>
                <div class="spell-grid">
                    <label><input type="checkbox"> Sort</label>
                    <label><input type="checkbox"> Sort</label>
                    <label><input type="checkbox"> Sort</label>
                    <label><input type="checkbox"> Sort</label>
                    <label><input type="checkbox"> Sort</label>
                    <label><input type="checkbox"> Sort</label>
                </div>
            </div>

            <div class="spell-levels">
                <div class="levels-row">
                    <div class="level-card card">
                        <h4>Niveau 1</h4>
                        <div class="slots">Emplacement : <span>..</span></div>
                        <ul class="spell-list">
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                        </ul>
                    </div>

                    <div class="level-card card">
                        <h4>Niveau 1</h4>
                        <div class="slots">Emplacement : <span>..</span></div>
                        <ul class="spell-list">
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                        </ul>
                    </div>

                    <div class="level-card card">
                        <h4>Niveau 1</h4>
                        <div class="slots">Emplacement : <span>..</span></div>
                        <ul class="spell-list">
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="actions mt-3">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Retour</a>
                <button class="btn btn-primary">Ajouter</button>
            </div>
        </div>
    </div>
@endsection
