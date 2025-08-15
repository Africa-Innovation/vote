<?php
namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Permet à l'admin d'ajouter des votes à un candidat donné
    public function addVotes(Request $request)
    {
        $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'votes' => 'required|integer|min:1',
        ]);
        $candidate = \App\Models\Candidate::findOrFail($request->candidate_id);
        $voteAmount = \App\Models\Setting::getValue('vote_amount', 100);
        $votesToAdd = $request->votes;
        $votes = [];
        for ($i = 0; $i < $votesToAdd; $i++) {
            $votes[] = [
                'candidate_id' => $candidate->id,
                'amount' => $voteAmount,
                'operator' => 'admin', // opérateur spécial pour votes ajoutés par admin
                'payment_status' => 'admin', // statut spécial pour votes ajoutés par admin
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        \App\Models\Vote::insert($votes);
        return redirect()->route('admin.dashboard')->with('success', 'Votes ajoutés avec succès.');
    }
    // Affiche la liste de tous les candidats (actifs ou non) avec leur nombre de votes
    public function dashboard(Request $request)
    {
        $query = Candidate::withCount('votes')->orderByDesc('votes_count');
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%$search%");
        }
        $candidates = $query->get();
    $voteAmount = \App\Models\Setting::getValue('vote_amount', 100);
    $candidatureAmount = \App\Models\Setting::getValue('candidature_amount', 1000);
    $maxCandidates = \App\Models\Setting::getValue('max_candidates', 75);
    return view('admin.dashboard', compact('candidates', 'voteAmount', 'candidatureAmount', 'maxCandidates'));
    }

    // Met à jour les montants de vote et de candidature
    public function updateAmounts(Request $request)
    {
        $request->validate([
            'vote_amount' => 'required|numeric|min:1',
            'candidature_amount' => 'required|numeric|min:1',
            'max_candidates' => 'required|integer|min:1',
        ]);
        \App\Models\Setting::setValue('vote_amount', $request->vote_amount);
        \App\Models\Setting::setValue('candidature_amount', $request->candidature_amount);
        \App\Models\Setting::setValue('max_candidates', $request->max_candidates);
        return redirect()->route('admin.dashboard')->with('success', 'Paramètres mis à jour.');
    }

    // Change le statut d'un candidat (actif <-> attente)
    public function toggleStatus($id)
    {
        $candidate = Candidate::findOrFail($id);
        $candidate->status = $candidate->status === 'active' ? 'pending' : 'active';
        $candidate->save();
        return redirect()->route('admin.dashboard')->with('success', 'Statut du candidat mis à jour.');
    }
}
