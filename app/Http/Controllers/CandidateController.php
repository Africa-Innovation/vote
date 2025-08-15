<?php
namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CandidateController extends Controller
{
    // Affiche le formulaire de candidature
    public function create()
    {
        $count = Candidate::count();
        if ($count >= 75) {
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
            'payment_phone' => 'required',
            'photo' => 'nullable|image',
            'operator' => 'required|in:orange,moov',
            'amount' => 'required|numeric|min:1',
        ]);

        if (Candidate::count() >= 75) {
            return back()->withErrors(['limit' => 'Le nombre maximum de candidatures est atteint.']);
        }

        // Vérifier le paiement via l'API externe
        $isPaid = $this->verifierPaiement(
            $request->payment_phone,
            $request->amount,
            $request->operator === 'orange'
        );

        if (!$isPaid) {
            return back()->withErrors(['payment' => 'Paiement non validé.']);
        }

        DB::transaction(function () use ($request) {
            $photoPath = $request->file('photo') ? $request->file('photo')->store('candidates', 'public') : null;
            Candidate::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'photo' => $photoPath,
            ]);
            Payment::create([
                'phone' => $request->payment_phone,
                'amount' => $request->amount,
                'type' => 'candidature',
                'operator' => $request->operator,
                'status' => 'success',
            ]);
        });

        return redirect()->route('vote.index')->with('success', 'Candidature enregistrée avec succès !');
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
