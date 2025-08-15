@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Reprendre la vérification de paiement</h1>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('candidate.resume') }}">
        @csrf
        <div class="mb-3">
            <label for="phone" class="form-label">Numéro de téléphone utilisé lors de la candidature</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
        </div>
        <button type="submit" class="btn btn-primary">Reprendre la vérification</button>
    </form>
</div>
@endsection
