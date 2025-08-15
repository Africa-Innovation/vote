@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tableau de bord admin</h1>
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
            @foreach($candidates as $candidate)
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
                        <form method="POST" action="{{ route('admin.candidate.toggle', $candidate->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                {{ $candidate->status === 'active' ? 'Mettre en attente' : 'Activer' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
