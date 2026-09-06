@extends('layouts.app')

@section('title', 'Créer un personnage - Compétences')

@section('content')
<div class="card">
    <div class="text-center mb-3">
        <span class="text-gold">Créer un personnage</span>
    </div>

    <div class="progress-bar">
        <div class="progress-line"></div>
        <div class="progress-fill" style="width: 66.6%;"></div>
        <div class="progress-step completed">1</div>
        <div class="progress-step completed">2</div>
        <div class="progress-step completed">3</div>
        <div class="progress-step completed">4</div>
        <div class="progress-step active">5</div>
        <div class="progress-step">6</div>
    </div>

    <h2 class="title-section text-center">Compétences</h2>
    <p class="text-muted text-center mb-3">Choisissez {{ $nombreCompetences }} compétences</p>

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <form action="{{ route('creation.competences.save') }}" method="POST">
        @csrf
        <div class="grid-cards mb-4">
            @foreach($competencesDisponibles as $comp)
                @php
                    $isSelected = in_array($comp, $competencesSelectionnees);
                @endphp
                <div class="card-choice {{ $isSelected ? 'selected' : '' }}"
                     onclick="toggleCompetence('{{ $comp }}', this)">
                    <input type="checkbox" name="competences[]" value="{{ $comp }}"
                           {{ $isSelected ? 'checked' : '' }}
                           style="display: none;">
                    <div class="card-title">{{ $comp }}</div>
                </div>
            @endforeach
        </div>

        <div class="text-center mb-3">
            <span class="text-gold" id="countDisplay">{{ count($competencesSelectionnees) }} / {{ $nombreCompetences }} sélectionnées</span>
        </div>

        <div class="btn-group-row">
            <a href="{{ route('creation.caracteristiques') }}" class="btn btn-secondary">Retour</a>
            <button type="submit" class="btn btn-primary" id="btnSuivant">Suivant</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const maxCompetences = {{ $nombreCompetences }};
    const selected = @json($competencesSelectionnees);

    function toggleCompetence(comp, element) {
        const checkbox = element.querySelector('input[type="checkbox"]');

        if (checkbox.checked) {
            const idx = selected.indexOf(comp);
            if (idx > -1) selected.splice(idx, 1);
        } else {
            if (selected.length >= maxCompetences) return;
            selected.push(comp);
        }

        checkbox.checked = !checkbox.checked;
        element.classList.toggle('selected');
        updateCount();
    }

    function updateCount() {
        document.getElementById('countDisplay').textContent = selected.length + ' / ' + maxCompetences + ' sélectionnées';
        document.getElementById('btnSuivant').disabled = selected.length < maxCompetences;
    }

    updateCount();
</script>
@endpush
@endsection
