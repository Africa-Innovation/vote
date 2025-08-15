@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Devenir candidat</h1>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('candidate.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Nom</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Téléphone du candidat</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
        </div>
        <div class="mb-3">
            <label for="photo" class="form-label">Photo (optionnel)</label>
            <input type="file" class="form-control" id="photo" name="photo">
        </div>
        <div class="mb-3">
            <label for="operator" class="form-label">Opérateur</label>
            <select class="form-control" id="operator" name="operator" required>
                <option value="orange">Orange</option>
                <option value="moov">Moov</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="payment_phone" class="form-label">Numéro utilisé pour le paiement</label>
            <input type="text" class="form-control" id="payment_phone" name="payment_phone" required>
            <div class="form-text" id="payment-phone-help">Saisis ici le numéro avec lequel tu as effectué le paiement USSD.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Montant de la candidature</label>
            <div class="form-control bg-light" readonly>
                {{ \App\Models\Setting::getValue('candidature_amount', 1000) }} FCFA
            </div>
        </div>
        <div class="mb-3">
            <strong>Code USSD à composer :</strong>
            <div id="ussd-code"></div>
            <a id="ussd-link" class="btn btn-warning mt-2" href="#">Lancer le paiement USSD</a>
            <div class="form-text">Clique sur le bouton pour ouvrir le code USSD sur ton téléphone, effectue le paiement, puis reviens saisir le numéro utilisé pour valider.</div>
        </div>
        <button type="submit" class="btn btn-primary">Valider ma candidature</button>
    </form>
    <div class="mt-4">
        <a href="{{ route('candidate.resume.form') }}" class="btn btn-link">Déjà candidat ? Reprendre la vérification de paiement</a>
    </div>
</div>
<script>
    const operatorSelect = document.getElementById('operator');
    const phoneInput = document.getElementById('phone');
    const ussdDiv = document.getElementById('ussd-code');
    const candidatureAmount = {{ \App\Models\Setting::getValue('candidature_amount', 1000) }};
    function updateUSSD() {
        const op = operatorSelect.value;
        let ussd = '';
        if(op === 'orange') {
            ussd = `*144*10*05690560*${candidatureAmount}#`;
        } else {
            ussd = `*555*4*1*03301404*${candidatureAmount}#`;
        }
        ussdDiv.textContent = ussd;
        // Génère le lien USSD pour mobile
        const ussdLink = document.getElementById('ussd-link');
        ussdLink.href = `tel:${encodeURIComponent(ussd)}`;
    }
    operatorSelect.addEventListener('change', updateUSSD);
    phoneInput.addEventListener('input', updateUSSD);
    updateUSSD();
</script>
@endsection
