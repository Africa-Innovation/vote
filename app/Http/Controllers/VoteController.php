<?php
namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Vote;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class VoteController extends Controller
{
    // Affiche la liste des candidats pour voter
    public function index()
    {
        $candidates = Candidate::withCount('votes')->get();
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
            'voter_phone' => 'required',
            'payment_phone' => 'required',
            'operator' => 'required|in:orange,moov',
            'amount' => 'required|numeric|min:1',
        ]);

        $candidate = Candidate::findOrFail($id);

        // Vérifier le paiement via l'API externe
        $isPaid = $this->verifierPaiement(
            $request->payment_phone,
            $request->amount,
            $request->operator === 'orange'
        );

        if (!$isPaid) {
            return back()->withErrors(['payment' => 'Paiement non validé.']);
        }

        // Enregistrer le vote et le paiement
        DB::transaction(function () use ($request, $candidate) {
            Vote::create([
                'candidate_id' => $candidate->id,
                'voter_phone' => $request->voter_phone,
                'amount' => $request->amount,
                'operator' => $request->operator,
                'payment_status' => 'success',
            ]);
            Payment::create([
                'phone' => $request->payment_phone,
                'amount' => $request->amount,
                'type' => 'vote',
                'operator' => $request->operator,
                'status' => 'success',
            ]);
        });

        return redirect()->route('vote.index')->with('success', 'Vote enregistré avec succès !');
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
