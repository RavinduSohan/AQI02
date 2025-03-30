<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\AirQualityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Models\AdditionalAirQualityReading;

// Public routes
Route::get('/', function () {
    return view('air-quality');
});

Route::get('/test-apis', function () {
    $googleApiKey = "AIzaSyD95OAdYDDrJjBBqpUqm7vVHchMQiGppBQ"; 
    $waqiApiKey = "628b190bed36ade4d7cc3c591ce28dd19a441d3c"; 

    $locations = [
        "Colombo, Sri Lanka",
        "Nugegoda, Sri Lanka",
        "Maharagama, Sri Lanka",
        "Padukka, Sri Lanka"
    ];

    $results = [];

    foreach ($locations as $location) {
        // Get coordinates from Google Geocoding API
        $googleResponse = Http::withoutVerifying()->get("https://maps.googleapis.com/maps/api/geocode/json", [
            'address' => $location,
            'key' => $googleApiKey,
        ]);

        if (!$googleResponse->successful()) {
            continue;
        }

        $geoData = $googleResponse->json();

        if (empty($geoData['results'])) {
            continue;
        }

        $lat = $geoData['results'][0]['geometry']['location']['lat'];
        $lng = $geoData['results'][0]['geometry']['location']['lng'];

        // Get AQI data from WAQI API
        $waqiResponse = Http::withoutVerifying()->get("https://api.waqi.info/feed/geo:$lat;$lng/", [
            'token' => $waqiApiKey,
        ]);

        if (!$waqiResponse->successful()) {
            continue;
        }

        $waqiData = $waqiResponse->json();

        $results[] = [
            "location" => $location,
            "latitude" => $lat,
            "longitude" => $lng,
            "air_quality_data" => $waqiData,
        ];
    }

    return response()->json($results);
});

// Data storage routes
Route::get('/store-reading', [AirQualityController::class, 'storeReading']);
Route::get('/get-readings', [AirQualityController::class, 'getReadings']);
Route::get('/trigger-store-reading', [AirQualityController::class, 'triggerStoreReading'])->name('trigger.store.reading');

Route::get('/charts', function () {
    return view('charts-page');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Notification Routes
Route::middleware('auth')->group(function () {
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::get('/users', [NotificationController::class, 'getUsers']);
});
Route::get('/notifications', [NotificationController::class, 'getNotifications']);

// API Routes
Route::get('/api/air-quality-readings', [AirQualityController::class, 'getReadings']);
Route::get('/api/additional-air-quality-readings', function () {
    return AdditionalAirQualityReading::with('airQualityReading')
        ->orderBy('reading_time', 'desc')
        ->take(30)
        ->get();
});
