@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Voter pour {{ $candidate->name }}</h1>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('vote.submit', $candidate->id) }}">
        @csrf
        <div class="mb-3">
            <label for="payment_phone" class="form-label">Numéro utilisé pour le paiement</label>
            <input type="text" class="form-control" id="payment_phone" name="payment_phone" required>
            <div class="form-text">Saisis ici le numéro avec lequel tu as effectué le paiement USSD.</div>
        </div>
        <div class="mb-3">
            <label for="operator" class="form-label">Opérateur</label>
            <select class="form-control" id="operator" name="operator" required>
                <option value="orange">Orange</option>
                <option value="moov">Moov</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Montant du vote</label>
            <input type="number" class="form-control" id="amount" name="amount" min="1" required>
        </div>
        <div class="mb-3">
            <strong>Code USSD à composer :</strong>
            <div id="ussd-code"></div>
            <a id="ussd-link" class="btn btn-warning mt-2" href="#">Lancer le paiement USSD</a>
            <div class="form-text">Clique sur le bouton pour ouvrir le code USSD sur ton téléphone, effectue le paiement, puis reviens saisir le numéro utilisé pour valider.</div>
        </div>
        <button type="submit" class="btn btn-success">Valider mon vote</button>
</form>
    <div class="mt-4">
        <a href="{{ route('vote.payment') }}" class="btn btn-link">Déjà payé ? Vérifier le paiement de mon vote</a>
    </div>
</div>
<script>
    const operatorSelect = document.getElementById('operator');
    const amountInput = document.getElementById('amount');
    const ussdDiv = document.getElementById('ussd-code');
    const phoneInput = document.getElementById('voter_phone');
    function updateUSSD() {
        const op = operatorSelect.value;
        const amount = amountInput.value || 'MONTANT';
        let ussd = '';
        if(op === 'orange') {
            ussd = `*144*10*05690560*${amount}#`;
        } else {
            ussd = `*555*4*1*03301404*${amount}#`;
        }
        ussdDiv.textContent = ussd;
        // Génère le lien USSD pour mobile
        const ussdLink = document.getElementById('ussd-link');
        ussdLink.href = `tel:${encodeURIComponent(ussd)}`;
    }
    operatorSelect.addEventListener('change', updateUSSD);
    amountInput.addEventListener('input', updateUSSD);
    phoneInput.addEventListener('input', updateUSSD);
    updateUSSD();
</script>
@endsection
