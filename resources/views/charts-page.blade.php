<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AQI Charts - Detailed View</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center">
                        <span class="text-xl font-semibold text-gray-800">AQI Monitor</span>
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <a href="/charts" class="text-blue-600 font-semibold">Charts</a>
                    @guest
                        <a href="/login" class="text-gray-600 hover:text-gray-900">Login</a>
                        <a href="/register" class="text-gray-600 hover:text-gray-900">Register</a>
                    @else
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-600">{{ Auth::user()->name }}</span>
                            @if(Auth::user()->is_admin)
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Admin</span>
                            @endif
                            <form method="POST" action="/logout" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-600 hover:text-gray-900">Logout</button>
                            </form>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Data Source Toggle -->
            <div class="flex justify-end mb-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="dataToggle" class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Use Mock Data</span>
                </label>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Line Chart -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                    <h2 class="text-xl font-semibold mb-4">24-Hour AQI Trends</h2>
                    <div class="chart-container">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>

                <!-- Bar Chart -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                    <h2 class="text-xl font-semibold mb-4">Location Comparison</h2>
                    <div class="chart-container">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                    <h2 class="text-xl font-semibold mb-4">AQI Distribution</h2>
                    <div class="chart-container">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>

                <!-- Area Chart -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                    <h2 class="text-xl font-semibold mb-4">AQI Levels Over Time</h2>
                    <div class="chart-container">
                        <canvas id="areaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let charts = {};
        let useMockData = false;

        function getAQIColor(aqi) {
            if (aqi <= 50) return '#4CAF50';
            if (aqi <= 100) return '#FDD835';
            if (aqi <= 150) return '#FF9800';
            if (aqi <= 200) return '#F44336';
            if (aqi <= 300) return '#8B0000';
            return '#800080';
        }

        function generateMockData() {
            const locationCoords = {
                'Colombo': { lat: 6.9271, lng: 79.8612 },
                'Nugegoda': { lat: 6.8649, lng: 79.8997 },
                'Maharagama': { lat: 6.8483, lng: 79.9247 },
                'Padukka': { lat: 6.8588, lng: 80.0917 }
            };
            const mockData = [];
            const now = new Date();
            
            for (let i = 0; i < 24; i++) {
                Object.entries(locationCoords).forEach(([location, coords]) => {
                    const time = new Date(now - i * 3600000);
                    mockData.push({
                        location: location,
                        aqi: Math.floor(Math.random() * 150) + 50,
                        reading_time: time.toISOString(),
                        latitude: coords.lat,
                        longitude: coords.lng
                    });
                });
            }

            return mockData;
        }

        function createLineChart(data) {
            const ctx = document.getElementById('lineChart').getContext('2d');
            const locations = [...new Set(data.map(item => item.location))];
            const timeLabels = [...new Set(data.map(item => 
                new Date(item.reading_time).toLocaleTimeString()
            ))].sort();

            const datasets = locations.map(location => {
                const locationData = data.filter(d => d.location === location);
                return {
                    label: location,
                    data: locationData.map(d => d.aqi),
                    borderColor: getAQIColor(locationData[0].aqi),
                    fill: false
                };
            });

            if (charts.lineChart) {
                charts.lineChart.destroy();
            }

            charts.lineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: timeLabels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'AQI Value'
                            }
                        }
                    }
                }
            });
        }

        function createBarChart(data) {
            const ctx = document.getElementById('barChart').getContext('2d');
            const locations = [...new Set(data.map(item => item.location))];
            const averageAQI = locations.map(location => {
                const locationData = data.filter(d => d.location === location);
                const avg = locationData.reduce((sum, curr) => sum + curr.aqi, 0) / locationData.length;
                return {
                    location: location,
                    aqi: Math.round(avg)
                };
            });

            if (charts.barChart) {
                charts.barChart.destroy();
            }

            charts.barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: averageAQI.map(d => d.location),
                    datasets: [{
                        label: 'Average AQI',
                        data: averageAQI.map(d => d.aqi),
                        backgroundColor: averageAQI.map(d => getAQIColor(d.aqi))
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function createPieChart(data) {
            const ctx = document.getElementById('pieChart').getContext('2d');
            const aqiLevels = {
                'Good (0-50)': 0,
                'Moderate (51-100)': 0,
                'Unhealthy for Sensitive Groups (101-150)': 0,
                'Unhealthy (151-200)': 0,
                'Very Unhealthy (201-300)': 0,
                'Hazardous (301+)': 0
            };

            data.forEach(reading => {
                if (reading.aqi <= 50) aqiLevels['Good (0-50)']++;
                else if (reading.aqi <= 100) aqiLevels['Moderate (51-100)']++;
                else if (reading.aqi <= 150) aqiLevels['Unhealthy for Sensitive Groups (101-150)']++;
                else if (reading.aqi <= 200) aqiLevels['Unhealthy (151-200)']++;
                else if (reading.aqi <= 300) aqiLevels['Very Unhealthy (201-300)']++;
                else aqiLevels['Hazardous (301+)']++;
            });

            if (charts.pieChart) {
                charts.pieChart.destroy();
            }

            charts.pieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: Object.keys(aqiLevels),
                    datasets: [{
                        data: Object.values(aqiLevels),
                        backgroundColor: ['#4CAF50', '#FDD835', '#FF9800', '#F44336', '#8B0000', '#800080']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function createAreaChart(data) {
            const ctx = document.getElementById('areaChart').getContext('2d');
            const timeLabels = [...new Set(data.map(item => 
                new Date(item.reading_time).toLocaleTimeString()
            ))].sort();

            if (charts.areaChart) {
                charts.areaChart.destroy();
            }

            charts.areaChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: timeLabels,
                    datasets: [{
                        label: 'Maximum AQI',
                        data: timeLabels.map(time => {
                            const timeData = data.filter(d => 
                                new Date(d.reading_time).toLocaleTimeString() === time
                            );
                            return Math.max(...timeData.map(d => d.aqi));
                        }),
                        fill: true,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function updateCharts(data) {
            createLineChart(data);
            createBarChart(data);
            createPieChart(data);
            createAreaChart(data);
        }

        function updateData() {
            if (useMockData) {
                const mockData = generateMockData();
                updateCharts(mockData);
            } else {
                $.get('/get-readings', function(data) {
                    updateCharts(Object.values(data).flat());
                });
            }
        }

        $(document).ready(function() {
            updateData();

            $('#dataToggle').change(function() {
                useMockData = $(this).is(':checked');
                updateData();
            });

            setInterval(updateData, 60 * 1000);
        });
    </script>
</body>
</html> 