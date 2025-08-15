@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Candidats</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <a href="{{ route('candidate.create') }}" class="btn btn-primary mb-3">Devenir candidat</a>

    <form method="GET" action="{{ route('vote.index') }}" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Rechercher un candidat par nom..." value="{{ request('search') }}">
            <button class="btn btn-outline-secondary" type="submit">Rechercher</button>
        </div>
    </form>

    <div class="row">
        @forelse($candidates as $candidate)
            <div class="col-md-4 mb-3">
                <div class="card">
                    @if($candidate->photo)
                        <img src="{{ asset('storage/' . $candidate->photo) }}" class="card-img-top" alt="Photo">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $candidate->name }}</h5>
                        <p>Votes : {{ $candidate->votes_count }}</p>
                        <a href="{{ route('vote.show', $candidate->id) }}" class="btn btn-success">Voter</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">Aucun candidat trouvé.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
