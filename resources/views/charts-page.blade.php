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
            <div class="flex justify-end mb-4 space-x-8">
                <div class="flex items-center space-x-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="dataToggle" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900 peer-checked:text-gray-900">Sensors Activated</span>
                    </label>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-900">Time Range:</span>
                    <select id="globalTimeRange" class="text-sm border rounded-md px-2 py-1" disabled>
                        <option value="day">24 Hours</option>
                        <option value="month">30 Days</option>
                    </select>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Line Chart -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                    <h2 class="text-xl font-semibold mb-4">AQI Trends</h2>
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

            <!-- Additional Air Quality Parameters Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <!-- Pollutants Line Chart -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Major Pollutants Trends</h2>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="pollutant-toggle sr-only peer" data-pollutant="all" checked>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-900">All</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="pollutant-toggle sr-only peer" data-pollutant="co" checked>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-900">CO</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="pollutant-toggle sr-only peer" data-pollutant="no2" checked>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-900">NO₂</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="pollutant-toggle sr-only peer" data-pollutant="o3" checked>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-900">O₃</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="pollutant-toggle sr-only peer" data-pollutant="so2" checked>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-900">SO₂</span>
                            </label>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="pollutantsChart"></canvas>
                    </div>
                </div>

                <!-- Particulate Matter Bar Chart -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Particulate Matter Levels</h2>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="particulate-toggle sr-only peer" data-particulate="all" checked>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-900">All</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="particulate-toggle sr-only peer" data-particulate="pm10" checked>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-900">PM10</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="particulate-toggle sr-only peer" data-particulate="pm25" checked>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-900">PM2.5</span>
                            </label>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="particulateChart"></canvas>
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

        function generateMockData(timeRange = 'day') {
            const locationCoords = {
                'Colombo': { lat: 6.9271, lng: 79.8612 },
                'Nugegoda': { lat: 6.8649, lng: 79.8997 },
                'Maharagama': { lat: 6.8483, lng: 79.9247 },
                'Padukka': { lat: 6.8588, lng: 80.0917 }
            };
            const mockData = [];
            const now = new Date();
            
            const intervals = timeRange === 'day' ? 24 : 30; // 24 hours or 30 days
            const timeStep = timeRange === 'day' ? 3600000 : 86400000; // 1 hour or 1 day in milliseconds
            
            for (let i = 0; i < intervals; i++) {
                Object.entries(locationCoords).forEach(([location, coords]) => {
                    const time = new Date(now - i * timeStep);
                    // Add some variation to make the monthly data look more realistic
                    const baseAQI = Math.floor(Math.random() * 100) + 50;
                    const variation = timeRange === 'month' ? Math.sin(i / 3) * 20 : 0; // Add sinusoidal variation for monthly data
                    
                    mockData.push({
                        location: location,
                        aqi: Math.max(0, Math.min(500, Math.floor(baseAQI + variation))),
                        reading_time: time.toISOString(),
                        latitude: coords.lat,
                        longitude: coords.lng
                    });
                });
            }

            return mockData;
        }

        function generateMockAdditionalData(timeRange = 'day') {
            const now = new Date();
            const mockData = [];
            const intervals = timeRange === 'day' ? 24 : 30;
            const timeStep = timeRange === 'day' ? 3600000 : 86400000;

            for (let i = 0; i < intervals; i++) {
                const time = new Date(now - i * timeStep);
                // Generate realistic but random values for each pollutant
                mockData.push({
                    reading_time: time.toISOString(),
                    co: (Math.random() * 4 + 1).toFixed(2),  // 1-5 ppm
                    no2: (Math.random() * 0.1 + 0.01).toFixed(2), // 0.01-0.11 ppm
                    o3: (Math.random() * 0.07 + 0.02).toFixed(2), // 0.02-0.09 ppm
                    so2: (Math.random() * 0.1 + 0.01).toFixed(2), // 0.01-0.11 ppm
                    pm10: (Math.random() * 100 + 20).toFixed(2), // 20-120 µg/m³
                    pm25: (Math.random() * 50 + 10).toFixed(2)  // 10-60 µg/m³
                });
            }

            return mockData.sort((a, b) => new Date(a.reading_time) - new Date(b.reading_time));
        }

        function createLineChart(data, timeRange = 'day') {
            const ctx = document.getElementById('lineChart').getContext('2d');
            const locations = [...new Set(data.map(item => item.location))];
            
            // Format time labels based on the time range
            const timeLabels = [...new Set(data.map(item => {
                const date = new Date(item.reading_time);
                return timeRange === 'day' 
                    ? date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    : date.toLocaleDateString([], { month: 'short', day: 'numeric' });
            }))].sort();

            const datasets = locations.map(location => {
                const locationData = data.filter(d => d.location === location)
                    .sort((a, b) => new Date(a.reading_time) - new Date(b.reading_time));
                return {
                    label: location,
                    data: locationData.map(d => d.aqi),
                    borderColor: getAQIColor(locationData[0].aqi),
                    fill: false,
                    tension: timeRange === 'month' ? 0.4 : 0 // Add slight curve for monthly view
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
                        },
                        x: {
                            title: {
                                display: true,
                                text: timeRange === 'day' ? 'Time (24 Hours)' : 'Date (30 Days)'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: timeRange === 'day' ? '24-Hour AQI Trends' : '30-Day AQI Trends'
                        }
                    }
                }
            });
        }

        function createBarChart(data, timeRange = 'day') {
            const ctx = document.getElementById('barChart').getContext('2d');
            const locations = [...new Set(data.map(item => item.location))];
            
            let averageAQI;
            if (timeRange === 'day') {
                // For daily view, show hourly averages
                averageAQI = locations.map(location => {
                    const locationData = data.filter(d => d.location === location);
                    const avg = locationData.reduce((sum, curr) => sum + curr.aqi, 0) / locationData.length;
                    return {
                        location: location,
                        aqi: Math.round(avg)
                    };
                });
            } else {
                // For monthly view, show daily trends
                const daysData = {};
                locations.forEach(location => {
                    daysData[location] = new Array(30).fill(0);
                });

                data.forEach(reading => {
                    const date = new Date(reading.reading_time);
                    const dayIndex = Math.floor((Date.now() - date) / (24 * 60 * 60 * 1000));
                    if (dayIndex < 30) {
                        daysData[reading.location][dayIndex] = reading.aqi;
                    }
                });

                averageAQI = locations.map(location => ({
                    location: location,
                    aqi: Math.round(daysData[location].reduce((a, b) => a + b) / 30)
                }));
            }

            if (charts.barChart) {
                charts.barChart.destroy();
            }

            charts.barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: averageAQI.map(d => d.location),
                    datasets: [{
                        label: timeRange === 'day' ? 'Daily Average AQI' : 'Monthly Average AQI',
                        data: averageAQI.map(d => d.aqi),
                        backgroundColor: averageAQI.map(d => getAQIColor(d.aqi))
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Average AQI Value'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: timeRange === 'day' ? '24-Hour Average AQI by Location' : '30-Day Average AQI by Location'
                        }
                    }
                }
            });
        }

        function createPieChart(data, timeRange = 'day') {
            const ctx = document.getElementById('pieChart').getContext('2d');
            const aqiLevels = {
                'Good (0-50)': 0,
                'Moderate (51-100)': 0,
                'Unhealthy for Sensitive Groups (101-150)': 0,
                'Unhealthy (151-200)': 0,
                'Very Unhealthy (201-300)': 0,
                'Hazardous (301+)': 0
            };

            // Filter data based on time range
            const filteredData = data.filter(reading => {
                const readingDate = new Date(reading.reading_time);
                const now = new Date();
                const timeDiff = now - readingDate;
                return timeRange === 'day' 
                    ? timeDiff <= 24 * 60 * 60 * 1000  // Last 24 hours
                    : timeDiff <= 30 * 24 * 60 * 60 * 1000;  // Last 30 days
            });

            filteredData.forEach(reading => {
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
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: timeRange === 'day' ? '24-Hour AQI Distribution' : '30-Day AQI Distribution'
                        }
                    }
                }
            });
        }

        function createAreaChart(data, timeRange = 'day') {
            const ctx = document.getElementById('areaChart').getContext('2d');
            
            // Format time labels based on the time range
            const timeLabels = [...new Set(data.map(item => {
                const date = new Date(item.reading_time);
                return timeRange === 'day' 
                    ? date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    : date.toLocaleDateString([], { month: 'short', day: 'numeric' });
            }))].sort();

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
                            const timeData = data.filter(d => {
                                const date = new Date(d.reading_time);
                                const formattedTime = timeRange === 'day'
                                    ? date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                                    : date.toLocaleDateString([], { month: 'short', day: 'numeric' });
                                return formattedTime === time;
                            });
                            return Math.max(...timeData.map(d => d.aqi));
                        }),
                        fill: true,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        tension: timeRange === 'month' ? 0.4 : 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Maximum AQI Value'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: timeRange === 'day' ? 'Time (24 Hours)' : 'Date (30 Days)'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: timeRange === 'day' ? '24-Hour Maximum AQI Trends' : '30-Day Maximum AQI Trends'
                        }
                    }
                }
            });
        }

        function createPollutantsChart(data, timeRange = 'day') {
            const ctx = document.getElementById('pollutantsChart').getContext('2d');
            
            const timeLabels = data.map(item => {
                const date = new Date(item.reading_time);
                return timeRange === 'day'
                    ? date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    : date.toLocaleDateString([], { month: 'short', day: 'numeric' });
            });

            const pollutants = {
                co: { label: 'CO (ppm)', color: '#FF6384' },
                no2: { label: 'NO₂ (ppm)', color: '#36A2EB' },
                o3: { label: 'O₃ (ppm)', color: '#FFCE56' },
                so2: { label: 'SO₂ (ppm)', color: '#4BC0C0' }
            };

            // Get current visibility states from checkboxes
            const visibilityStates = {};
            Object.keys(pollutants).forEach(key => {
                const checkbox = $(`.pollutant-toggle[data-pollutant="${key}"]`);
                visibilityStates[key] = checkbox.length ? checkbox.prop('checked') : true;
            });

            const datasets = Object.entries(pollutants).map(([key, info]) => ({
                label: info.label,
                data: data.map(d => d[key]),
                borderColor: info.color,
                backgroundColor: info.color,
                fill: false,
                tension: timeRange === 'month' ? 0.4 : 0,
                hidden: !visibilityStates[key]
            }));

            if (charts.pollutantsChart) {
                charts.pollutantsChart.destroy();
            }

            charts.pollutantsChart = new Chart(ctx, {
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
                                text: 'Concentration'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: timeRange === 'day' ? 'Time (24 Hours)' : 'Date (30 Days)'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: `Major Pollutants Trends (${timeRange === 'day' ? '24 Hours' : '30 Days'})`
                        },
                        legend: {
                            display: true,
                            onClick: null // Disable default legend click behavior
                        }
                    }
                }
            });
        }

        function createParticulateChart(data, timeRange = 'day') {
            const ctx = document.getElementById('particulateChart').getContext('2d');
            
            const timeLabels = data.map(item => {
                const date = new Date(item.reading_time);
                return timeRange === 'day'
                    ? date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    : date.toLocaleDateString([], { month: 'short', day: 'numeric' });
            });

            // Get current visibility states from checkboxes
            const visibilityStates = {
                pm10: $('.particulate-toggle[data-particulate="pm10"]').prop('checked'),
                pm25: $('.particulate-toggle[data-particulate="pm25"]').prop('checked')
            };

            const datasets = [
                {
                    label: 'PM10 (µg/m³)',
                    data: data.map(d => d.pm10),
                    backgroundColor: '#FF9F40',
                    borderColor: '#FF9F40',
                    borderWidth: 1,
                    hidden: !visibilityStates.pm10
                },
                {
                    label: 'PM2.5 (µg/m³)',
                    data: data.map(d => d.pm25),
                    backgroundColor: '#4BC0C0',
                    borderColor: '#4BC0C0',
                    borderWidth: 1,
                    hidden: !visibilityStates.pm25
                }
            ];

            if (charts.particulateChart) {
                charts.particulateChart.destroy();
            }

            charts.particulateChart = new Chart(ctx, {
                type: 'bar',
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
                                text: 'Concentration (µg/m³)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: timeRange === 'day' ? 'Time (24 Hours)' : 'Date (30 Days)'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: `Particulate Matter Levels (${timeRange === 'day' ? '24 Hours' : '30 Days'})`
                        },
                        legend: {
                            display: true,
                            onClick: null // Disable default legend click behavior
                        }
                    }
                }
            });
        }

        async function updateCharts(timeRange = 'day') {
            let aqiData;
            let additionalData;

            if (useMockData) {
                aqiData = generateMockData(timeRange);
                additionalData = generateMockAdditionalData(timeRange);
            } else {
                try {
                    // Fetch AQI data for original charts
                    const aqiResponse = await fetch('/get-readings');
                    const aqiResult = await aqiResponse.json();
                    aqiData = Object.values(aqiResult).flat();
                    
                    // Fetch additional data for new charts
                    const additionalResponse = await fetch('/api/additional-air-quality-readings');
                    additionalData = await additionalResponse.json();
                } catch (error) {
                    console.error('Error fetching data:', error);
                    return;
                }
            }

            // Update original charts with AQI data
            createLineChart(aqiData, timeRange);
            createBarChart(aqiData, timeRange);
            createPieChart(aqiData, timeRange);
            createAreaChart(aqiData, timeRange);

            // Update new charts with additional data
            createPollutantsChart(additionalData, timeRange);
            createParticulateChart(additionalData, timeRange);
        }

        function updateData() {
            if (useMockData) {
                updateCharts();
            } else {
                updateCharts('day');
            }
        }

        $(document).ready(function() {
            // Initialize data
            updateData();

            // Handle global time range changes
            $('#globalTimeRange').change(function() {
                if (!useMockData) {
                    alert('Time range selection is only available with mock data');
                    $(this).val('day');
                    return;
                }

                const timeRange = $(this).val();
                updateCharts(timeRange);
            });

            // Handle mock data toggle
            $('#dataToggle').change(function() {
                useMockData = $(this).is(':checked');
                const timeRange = $('#globalTimeRange').val();
                
                // Enable/disable time range selector
                $('#globalTimeRange').prop('disabled', !useMockData);
                
                // Update label text
                $(this).next().next().text(useMockData ? 'Sensors Deactivated' : 'Sensors Activated');
                
                if (useMockData) {
                    updateCharts(timeRange);
                } else {
                    $('#globalTimeRange').val('day');
                    updateCharts('day');
                }
            });

            // Update every minute
            setInterval(function() {
                const timeRange = $('#globalTimeRange').val();
                updateCharts(timeRange);
            }, 60 * 1000);

            // Handle pollutant toggles
            $('.pollutant-toggle').change(function() {
                const pollutant = $(this).data('pollutant');
                
                if (pollutant === 'all') {
                    // Toggle all checkboxes
                    const isChecked = $(this).prop('checked');
                    $('.pollutant-toggle').prop('checked', isChecked);
                } else {
                    // Update "All" checkbox state
                    const allChecked = $('.pollutant-toggle:not([data-pollutant="all"])').toArray().every(cb => cb.checked);
                    $('.pollutant-toggle[data-pollutant="all"]').prop('checked', allChecked);
                }
                
                // Recreate chart with current visibility states
                const timeRange = $('#globalTimeRange').val();
                if (useMockData) {
                    const mockData = generateMockAdditionalData(timeRange);
                    createPollutantsChart(mockData, timeRange);
                } else {
                    fetch('/api/additional-air-quality-readings')
                        .then(response => response.json())
                        .then(data => createPollutantsChart(data, timeRange))
                        .catch(error => console.error('Error fetching data:', error));
                }
            });

            // Handle particulate matter toggles
            $('.particulate-toggle').change(function() {
                const particulate = $(this).data('particulate');
                
                if (particulate === 'all') {
                    // Toggle all checkboxes
                    const isChecked = $(this).prop('checked');
                    $('.particulate-toggle').prop('checked', isChecked);
                } else {
                    // Update "All" checkbox state
                    const allChecked = $('.particulate-toggle:not([data-particulate="all"])').toArray().every(cb => cb.checked);
                    $('.particulate-toggle[data-particulate="all"]').prop('checked', allChecked);
                }
                
                // Recreate chart with current visibility states
                const timeRange = $('#globalTimeRange').val();
                if (useMockData) {
                    const mockData = generateMockAdditionalData(timeRange);
                    createParticulateChart(mockData, timeRange);
                } else {
                    fetch('/api/additional-air-quality-readings')
                        .then(response => response.json())
                        .then(data => createParticulateChart(data, timeRange))
                        .catch(error => console.error('Error fetching data:', error));
                }
            });
        });
    </script>
</body>
</html> 