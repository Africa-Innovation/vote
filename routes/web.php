<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\AdminController;


// Vérification du paiement du vote
Route::get('/vote/payment', [VoteController::class, 'paymentForm'])->name('vote.payment');
Route::post('/vote/payment', [VoteController::class, 'paymentVerify'])->name('vote.payment.verify');

// Reprendre la vérification de paiement d'une candidature en attente
Route::get('/candidater/reprendre', [CandidateController::class, 'resumeForm'])->name('candidate.resume.form');
Route::post('/candidater/reprendre', [CandidateController::class, 'resume'])->name('candidate.resume');

Route::get('/candidater/payment', [CandidateController::class, 'paymentForm'])->name('candidate.payment');
Route::post('/candidater/payment', [CandidateController::class, 'paymentVerify'])->name('candidate.payment.verify');

Route::get('/', [VoteController::class, 'index'])->name('vote.index');
Route::get('/vote/{id}', [VoteController::class, 'show'])->name('vote.show');
Route::post('/vote/{id}', [VoteController::class, 'vote'])->name('vote.submit');

Route::get('/candidater', [CandidateController::class, 'create'])->name('candidate.create');
Route::post('/candidater', [CandidateController::class, 'store'])->name('candidate.store');

Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::post('/admin/amounts/update', [AdminController::class, 'updateAmounts'])->name('admin.amounts.update');
Route::post('/admin/candidate/{id}/toggle', [AdminController::class, 'toggleStatus'])->name('admin.candidate.toggle');
Route::post('/admin/add-votes', [AdminController::class, 'addVotes'])->name('admin.add.votes');