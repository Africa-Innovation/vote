<?php
namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Vote;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoteController extends Controller
{
    // Affiche la page de vérification du paiement du vote
    public function paymentForm()
    {
        $candidateId = session('pending_vote_candidate_id');
        $amount = session('pending_vote_amount');
        $operator = session('pending_vote_operator');
        $candidate = Candidate::find($candidateId);
        if (!$candidate) {
            return redirect()->route('vote.index')->withErrors(['expired' => 'Session expirée, veuillez recommencer.']);
        }
        return view('vote.payment', compact('candidate', 'amount', 'operator'));
    }

    // Vérifie le paiement et enregistre le vote
    public function paymentVerify(Request $request)
    {
        $request->validate([
            'payment_phone' => 'required',
        ]);
        $candidateId = session('pending_vote_candidate_id');
        $amount = session('pending_vote_amount');
        $operator = session('pending_vote_operator');
        $candidate = Candidate::find($candidateId);
        if (!$candidate) {
            return redirect()->route('vote.index')->withErrors(['expired' => 'Session expirée, veuillez recommencer.']);
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
            Vote::create([
                'candidate_id' => $candidate->id,
                'amount' => $amount,
                'operator' => $operator,
                'payment_status' => 'success',
                'voter_phone' => $request->payment_phone,
            ]);
            Payment::create([
                'phone' => $request->payment_phone,
                'amount' => $amount,
                'type' => 'vote',
                'operator' => $operator,
                'status' => 'success',
            ]);
        });
        session()->forget(['pending_vote_candidate_id', 'pending_vote_amount', 'pending_vote_operator', 'pending_vote_payment_phone']);
        return redirect()->route('vote.index')->with('success', 'Vote enregistré avec succès !');
    }
    // Affiche la liste des candidats pour voter
    public function index(Request $request)
    {
        $query = Candidate::where('status', 'active')->withCount('votes');
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%$search%");
        }
        $candidates = $query->get();
        return view('vote.index', compact('candidates'));
    }

    // Affiche le formulaire de vote pour un candidat
    public function show($id)
    {
        $candidate = Candidate::findOrFail($id);
        return view('vote.show', compact('candidate'));
    }

    // Traite la demande de vote (paiement + enregistrement)
    public function vote(Request $request, $id)
    {
        $request->validate([
            'payment_phone' => 'required',
            'operator' => 'required|in:orange,moov',
        ]);

        $candidate = Candidate::findOrFail($id);
        $amount = \App\Models\Setting::getValue('vote_amount', 100); // 100 par défaut

        // Stocker les infos du vote en session pour la vérification paiement
        session([
            'pending_vote_candidate_id' => $candidate->id,
            'pending_vote_amount' => $amount,
            'pending_vote_operator' => $request->operator,
            'pending_vote_payment_phone' => $request->payment_phone,
        ]);

        return redirect()->route('vote.payment');
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
    Log::info('API paiement vote', [
            'body' => $body,
            'response_status' => $response->status(),
            'response_json' => $response->json(),
        ]);
        return $response->ok() && $response->json('success') === true;
    }
}
