<?php
namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CandidateController extends Controller
{
    // Formulaire pour reprendre la vérification via téléphone
    public function resumeForm()
    {
        return view('candidate.resume');
    }

    // Traite la reprise de vérification
    public function resume(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);
        $candidate = Candidate::where('phone', $request->phone)->where('status', 'pending')->first();
        if (!$candidate) {
            return back()->withErrors(['phone' => 'Aucune candidature en attente trouvée pour ce numéro.']);
        }
        // On suppose que le montant et l'opérateur sont stockés dans le dernier paiement associé
        $payment = $candidate->payments()->where('type', 'candidature')->latest()->first();
        if (!$payment) {
            return back()->withErrors(['phone' => 'Aucun paiement associé trouvé pour ce numéro.']);
        }
        session(['pending_candidate_id' => $candidate->id, 'pending_amount' => $payment->amount, 'pending_operator' => $payment->operator]);
        return redirect()->route('candidate.payment');
    }
    // Affiche le formulaire de candidature
    public function create()
    {
        $maxCandidates = \App\Models\Setting::getValue('max_candidates', 75);
        $count = Candidate::count();
        if ($count >= $maxCandidates) {
            return redirect()->route('vote.index')->withErrors(['limit' => 'Le nombre maximum de candidatures est atteint.']);
        }
        return view('candidate.create');
    }

    // Traite la soumission de candidature
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:candidates,phone',
            'photo' => 'nullable|image',
            'operator' => 'required|in:orange,moov',
        ]);

        $maxCandidates = \App\Models\Setting::getValue('max_candidates', 75);
        if (Candidate::count() >= $maxCandidates) {
            return back()->withErrors(['limit' => 'Le nombre maximum de candidatures est atteint.']);
        }

        $photoPath = $request->file('photo') ? $request->file('photo')->store('candidates', 'public') : null;
        $candidate = Candidate::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'photo' => $photoPath,
            'status' => 'pending',
        ]);

        $amount = \App\Models\Setting::getValue('candidature_amount', 1000); // 1000 par défaut

        // Stocker les infos nécessaires en session pour la vérification paiement
        session(['pending_candidate_id' => $candidate->id, 'pending_amount' => $amount, 'pending_operator' => $request->operator]);

        return redirect()->route('candidate.payment');
    }

    // Affiche la page de vérification du paiement
    public function paymentForm()
    {
        $candidateId = session('pending_candidate_id');
        $amount = session('pending_amount');
        $operator = session('pending_operator');
        $candidate = Candidate::find($candidateId);
        if (!$candidate) {
            return redirect()->route('candidate.create')->withErrors(['expired' => 'Session expirée, veuillez recommencer.']);
        }
        return view('candidate.payment', compact('candidate', 'amount', 'operator'));
    }

    // Vérifie le paiement et active la candidature
    public function paymentVerify(Request $request)
    {
        $request->validate([
            'payment_phone' => 'required',
        ]);
        $candidateId = session('pending_candidate_id');
        $amount = session('pending_amount');
        $operator = session('pending_operator');
        $candidate = Candidate::find($candidateId);
        if (!$candidate) {
            return redirect()->route('candidate.create')->withErrors(['expired' => 'Session expirée, veuillez recommencer.']);
        }

        $isPaid = $this->verifierPaiement(
            $request->payment_phone,
            $amount,
            $operator === 'orange'
        );

        if (!$isPaid) {
            return back()->withErrors(['payment' => 'Paiement non validé.']);
        }

        DB::transaction(function () use ($candidate, $request, $amount, $operator) {
            $candidate->status = 'active';
            $candidate->save();
            Payment::create([
                'phone' => $request->payment_phone,
                'amount' => $amount,
                'type' => 'candidature',
                'operator' => $operator,
                'status' => 'success',
                'candidate_id' => $candidate->id,
            ]);
        });
        session()->forget(['pending_candidate_id', 'pending_amount', 'pending_operator']);
        return redirect()->route('vote.index')->with('success', 'Candidature validée avec succès !');
    }

    // Vérifie le paiement via l'API externe
    private function verifierPaiement($numero, $montant, $isOrange)
    {
        $url = "https://shark-app-xeyhn.ondigitalocean.app/pay/control/phone_number";
        $apiKey = "XGw-djtJyl3Vi-v8sQUGf_tLySvC2kd5";
        $appId = $isOrange
            ? "8914aecc-c838-4b15-9245-0684f413a02d"
            : "6eddada1-b5a1-4a59-80fc-f0217ab030cf";

        $body = [
            "api_key" => $apiKey,
            "app_id" => $appId,
            "amount" => $montant,
            "phonenumber" => $numero,
            "orange" => $isOrange,
        ];

        $response = Http::post($url, $body);
        return $response->ok() && $response->json('success') === true;
    }
}
