<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;



Route::get('/db-test', function() {
    try {
        \DB::connection()->getPdo();
        return "Connected successfully to: " . \DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        return "Connection failed: " . $e->getMessage();
    }
});