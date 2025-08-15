<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\AdminController;

Route::get('/', [VoteController::class, 'index'])->name('vote.index');
Route::get('/vote/{id}', [VoteController::class, 'show'])->name('vote.show');
Route::post('/vote/{id}', [VoteController::class, 'vote'])->name('vote.submit');

Route::get('/candidater', [CandidateController::class, 'create'])->name('candidate.create');
Route::post('/candidater', [CandidateController::class, 'store'])->name('candidate.store');

Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
