<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air Quality Information</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Air Quality in {{ $location }}</h1>

        @if(isset($air_quality_data['status']) && $air_quality_data['status'] === 'ok')
            <div class="mt-4">
                <h2>Air Quality Index (AQI): {{ $air_quality_data['data']['aqi'] }}</h2>

                <h3>Pollutants:</h3>
                <ul>
                    <li><strong>CO:</strong> {{ $air_quality_data['data']['iaqi']['co']['v'] }} µg/m³</li>
                    <li><strong>NO2:</strong> {{ $air_quality_data['data']['iaqi']['no2']['v'] }} µg/m³</li>
                    <li><strong>PM10:</strong> {{ $air_quality_data['data']['iaqi']['pm10']['v'] }} µg/m³</li>
                    <li><strong>SO2:</strong> {{ $air_quality_data['data']['iaqi']['so2']['v'] }} µg/m³</li>
                    <li><strong>O3:</strong> {{ $air_quality_data['data']['iaqi']['o3']['v'] }} µg/m³</li>
                </ul>

                <h3>Forecast:</h3>
                <div>
                    <ul>
                        @foreach ($air_quality_data['data']['forecast']['daily'] as $forecast)
                            <li>{{ $forecast['day'] }}: 
                                PM10 Avg: {{ $forecast['pm10']['avg'] }} µg/m³, 
                                O3 Avg: {{ $forecast['o3']['avg'] }} µg/m³
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @else
            <p>Sorry, air quality data could not be retrieved at this time.</p>
        @endif
    </div>
</body>
</html>
