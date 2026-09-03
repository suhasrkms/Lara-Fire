<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

/*
| Stateless JSON API. Authenticate with a Firebase ID token:
|   Authorization: Bearer <idToken from firebase.auth().currentUser.getIdToken()>
*/

Route::prefix('v1')->middleware('throttle:api')->name('api.')->group(function () {
    Route::middleware('firebase.token')->group(function () {
        Route::get('me', [ApiController::class, 'me'])->name('me');

        Route::get('notes', [ApiController::class, 'notes'])->name('notes.index');
        Route::post('notes', [ApiController::class, 'storeNote'])->name('notes.store');
        Route::get('notes/{id}', [ApiController::class, 'showNote'])->name('notes.show');
        Route::match(['put', 'patch'], 'notes/{id}', [ApiController::class, 'updateNote'])->name('notes.update');
        Route::delete('notes/{id}', [ApiController::class, 'destroyNote'])->name('notes.destroy');
    });
});
