@section('hide_navbar', true)
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Vérification du paiement</h1>
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
        <strong>Nom :</strong> {{ $candidate->name }}<br>
        <strong>Montant à payer :</strong> {{ $amount }}<br>
        <strong>Opérateur :</strong> {{ ucfirst($operator) }}<br>
        <strong>Code USSD à composer :</strong>
        <span id="ussd-code"></span>
    </div>
    <form method="POST" action="{{ route('candidate.payment.verify') }}">
        @csrf
        <div class="mb-3">
            <label for="payment_phone" class="form-label">Numéro utilisé pour le paiement</label>
            <input type="text" class="form-control" id="payment_phone" name="payment_phone" required>
        </div>
        <button type="submit" class="btn btn-primary">Vérifier le paiement</button>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @php
            $ussd = $operator === 'orange'
                ? '*144*10*05690560*' . $amount . '#'
                : '*555*4*1*03301404*' . $amount . '#';
        @endphp
        const ussd = @json($ussd);
        const ussdDiv = document.getElementById('ussd-code');
        if (ussdDiv) {
            ussdDiv.textContent = ussd;
        }
    });
</script>
