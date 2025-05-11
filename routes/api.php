<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

Route::apiResource('users', UserController::class);

Route::fallback(static function () {
    return response()->json([
        'message' => 'Not Found',
    ], Response::HTTP_NOT_FOUND);
});
