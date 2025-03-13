<?php

declare(strict_types=1);

use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\TeamSettingsController;
use App\Http\Controllers\TeamSwitcherController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::put('/teams/{team}/switch', [TeamSwitcherController::class, 'store'])->name('teams.switch');
    Route::get('/teams/{team}/settings', [TeamSettingsController::class, 'index'])->name('teams.settings');
    Route::put('/teams/{team}', [TeamSettingsController::class, 'update'])->name('teams.update');

    // Team invitations
    Route::post('/teams/{team}/invitations', [TeamInvitationController::class, 'store'])->name('teams.invitations.store');
    Route::delete('/teams/{team}/invitations/{invitation}', [TeamInvitationController::class, 'destroy'])->name('teams.invitations.destroy');

    // Team members
    Route::delete('/teams/{team}/members/{user}', [TeamMemberController::class, 'destroy'])->name('teams.members.destroy');
});
