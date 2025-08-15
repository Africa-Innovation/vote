@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Vérification du paiement pour {{ $candidate->name }}</h1>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="mb-3">
        <strong>Montant à payer :</strong> {{ $data['amount'] }}<br>
        <strong>Opérateur :</strong> {{ ucfirst($data['operator']) }}<br>
        <strong>Code USSD à composer :</strong>
        <span id="ussd-code"></span>
    </div>
    <form method="POST" action="{{ route('vote.payment.verify') }}">
        @csrf
        <div class="mb-3">
            <label for="payment_phone" class="form-label">Numéro utilisé pour le paiement</label>
            <input type="text" class="form-control" id="payment_phone" name="payment_phone" required>
        </div>
        <button type="submit" class="btn btn-primary">Vérifier le paiement</button>
    </form>
</div>
<script>
    const ussd = @json($data['operator'] === 'orange' ? '*144*10*05690560*' + $data['amount'] + '#' : '*555*4*1*03301404*' + $data['amount'] + '#');
    document.getElementById('ussd-code').textContent = ussd;
</script>
@endsection
