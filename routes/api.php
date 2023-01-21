<?php

use App\Http\Controllers\campaignController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => "ads"], function () {
    Route::apiResource('campaigns', campaignController::class);
});

Route::post('/ads/updateCampaign/{id}', [campaignController::class, "updateCampaign"]);;
