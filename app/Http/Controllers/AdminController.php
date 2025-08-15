<?php
namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Affiche la liste des candidats avec leur nombre de votes
    public function dashboard()
    {
        $candidates = Candidate::withCount('votes')->orderByDesc('votes_count')->get();
        return view('admin.dashboard', compact('candidates'));
    }
}
