<?php

namespace App\Http\Controllers;

use App\Models\AirQualityReading;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AirQualityController extends Controller
{
    private $locations = [
        "Colombo, Sri Lanka",
        "Nugegoda, Sri Lanka",
        "Maharagama, Sri Lanka",
        "Padukka, Sri Lanka"
    ];

    public function storeReading()
    {
        $googleApiKey = "AIzaSyD95OAdYDDrJjBBqpUqm7vVHchMQiGppBQ"; 
        $waqiApiKey = "628b190bed36ade4d7cc3c591ce28dd19a441d3c"; 

        $results = [];

        foreach ($this->locations as $location) {
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

            // Get real AQI data first
            $waqiResponse = Http::withoutVerifying()->get("https://api.waqi.info/feed/geo:$lat;$lng/", [
                'token' => $waqiApiKey,
            ]);

            if (!$waqiResponse->successful()) {
                continue;
            }

            $waqiData = $waqiResponse->json();
            $baseAqi = $waqiData['data']['aqi'];

            // Generate simulated data based on real data
            $simulatedAqi = $this->simulateAQI($baseAqi);

            // Store the reading
            $reading = AirQualityReading::create([
                'aqi' => $simulatedAqi,
                'latitude' => $lat,
                'longitude' => $lng,
                'location_name' => $location,
                'station_name' => $waqiData['data']['city']['name'],
                'reading_time' => Carbon::now(),
            ]);

            $results[] = $reading;
        }

        return response()->json([
            "message" => "Readings stored successfully",
            "data" => $results
        ]);
    }

    private function simulateAQI($baseAqi)
    {
        // Add random variation of ±10% to make it look realistic
        $variation = $baseAqi * 0.1;
        return round($baseAqi + rand(-$variation, $variation));
    }

    public function getReadings()
    {
        // Get last 24 hours of readings for each location
        $readings = AirQualityReading::where('reading_time', '>=', Carbon::now()->subHours(24))
            ->orderBy('reading_time', 'asc')
            ->get()
            ->groupBy('location_name');

        return response()->json($readings);
    }

    public function showCharts()
    {
        return view('air-quality-charts');
    }
} 