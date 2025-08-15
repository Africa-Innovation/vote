<?php
namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;

class AdminController extends Controller
{
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
