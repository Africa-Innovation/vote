@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tableau de bord admin</h1>

    <div class="mb-4">
        <form method="POST" action="{{ route('admin.add.votes') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-auto">
                <label for="candidate_id" class="form-label">Candidat</label>
                <select class="form-select" id="candidate_id" name="candidate_id" required>
                    <option value="">Sélectionner...</option>
                    @foreach($candidates as $candidate)
                        <option value="{{ $candidate->id }}">{{ $candidate->name }} ({{ $candidate->phone }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label for="votes" class="form-label">Nombre de votes à ajouter</label>
                <input type="number" class="form-control" id="votes" name="votes" min="1" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-success">Ajouter les votes</button>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-4">
        <form method="POST" action="{{ route('admin.amounts.update') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-auto">
                <label for="vote_amount" class="form-label">Montant du vote</label>
                <input type="number" class="form-control" id="vote_amount" name="vote_amount" value="{{ $voteAmount }}" min="1" required>
            </div>
            <div class="col-auto">
                <label for="candidature_amount" class="form-label">Montant de la candidature</label>
                <input type="number" class="form-control" id="candidature_amount" name="candidature_amount" value="{{ $candidatureAmount }}" min="1" required>
            </div>
            <div class="col-auto">
                <label for="max_candidates" class="form-label">Limite de candidatures</label>
                <input type="number" class="form-control" id="max_candidates" name="max_candidates" value="{{ $maxCandidates }}" min="1" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
    <form method="GET" action="{{ route('admin.dashboard') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Rechercher un candidat par nom..." value="{{ request('search') }}">
            <button class="btn btn-outline-secondary" type="submit">Rechercher</button>
        </div>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>Photo</th>
                <th>Nombre de votes</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($candidates as $candidate)
                <tr>
                    <td>{{ $candidate->name }}</td>
                    <td>{{ $candidate->phone }}</td>
                    <td>
                        @if($candidate->photo)
                            <img src="{{ asset('storage/' . $candidate->photo) }}" width="60"/>
                        @endif
                    </td>
                    <td>{{ $candidate->votes_count }}</td>
                    <td>
                        @if($candidate->status === 'active')
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-secondary">En attente</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.candidate.toggle', $candidate->id) }}" style="display:inline-block;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary mb-1">
                                {{ $candidate->status === 'active' ? 'Mettre en attente' : 'Activer' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Aucun candidat trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
