<?php
namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Affiche la liste de tous les candidats (actifs ou non) avec leur nombre de votes
    public function dashboard()
    {
        $candidates = Candidate::withCount('votes')->orderByDesc('votes_count')->get();
        return view('admin.dashboard', compact('candidates'));
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
