<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Air Quality Index</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD95OAdYDDrJjBBqpUqm7vVHchMQiGppBQ"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        #map {
            height: 400px;
            width: 100%;
            border-radius: 0.5rem;
        }
        .custom-marker {
            background-color: #4CAF50;
            border-radius: 50%;
            color: white;
            padding: 8px;
            min-width: 32px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
            cursor: pointer;
        }
        .custom-marker:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.4);
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        .legend-item {
            display: flex;
            align-items: center;
            margin: 4px 0;
            font-size: 12px;
            padding: 4px;
        }
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            margin-right: 8px;
            flex-shrink: 0;
        }
        .marker-tooltip {
            position: absolute;
            background: white;
            padding: 8px 12px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            font-size: 12px;
            z-index: 2;
            display: none;
            white-space: nowrap;
            pointer-events: none;
            transform: translate(-50%, -100%);
            margin-top: -8px;
        }
        .chart-container {
            position: relative;
            height: 300px;
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
                    <a href="#" class="flex items-center">
                        <span class="text-xl font-semibold text-gray-800">AQI Monitor</span>
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-blue-600 font-semibold">Dashboard</a>
                    <a href="/charts" class="text-gray-600 hover:text-gray-900">Charts</a>
                    <a href="#" class="text-gray-600 hover:text-gray-900">Locations</a>
                    <a href="#" class="text-gray-600 hover:text-gray-900">About</a>
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
                <div class="md:hidden flex items-center">
                    <button class="text-gray-600 hover:text-gray-900 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-8">Air Quality Index Checker</h1>
        
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Data Source Toggle -->
            <div class="flex justify-end mb-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="dataToggle" class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Use Mock Data</span>
                </label>
            </div>

            <!-- Data Container -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                <div id="result" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="text-center text-gray-600">
                        Loading air quality data...
                    </div>
                </div>
            </div>

            <!-- Map Container -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                <h2 class="text-xl font-semibold mb-4">Location Map</h2>
                <div class="relative mb-4">
                    <div id="map"></div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm font-semibold mb-2">AQI Legend</div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #4CAF50"></div>
                            <span>Good (0-50)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #FDD835"></div>
                            <span>Moderate (51-100)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #FF9800"></div>
                            <span>Unhealthy for Sensitive Groups (101-150)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #F44336"></div>
                            <span>Unhealthy (151-200)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #8B0000"></div>
                            <span>Very Unhealthy (201-300)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #800080"></div>
                            <span>Hazardous (301+)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Notifications Panel -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Notifications</h2>
                        @auth
                        <button id="createNotification" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                            Create Alert
                        </button>
                        @endauth
                    </div>
                    <div id="notificationsPanel" class="space-y-4 max-h-[300px] overflow-y-auto">
                        <div class="text-gray-600">Loading notifications...</div>
                    </div>
                </div>

                @auth
                <!-- Users Panel (Only visible to logged-in users) -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                    <h2 class="text-xl font-semibold mb-4">Registered Users</h2>
                    <div id="usersPanel" class="space-y-4 max-h-[300px] overflow-y-auto">
                        <div class="text-gray-600">Loading users...</div>
                    </div>
                </div>
                @endauth

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

    <!-- Notification Modal -->
    <div id="notificationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-xl font-semibold mb-4">Create AQI Alert</h3>
            <form id="notificationForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="notificationTitle" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea id="notificationMessage" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Location</label>
                    <select id="notificationLocation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select a location</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" id="closeModal" class="px-4 py-2 border rounded-md hover:bg-gray-100">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Send Alert</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Add auth check variable at the top
        const isAuthenticated = {!! auth()->check() ? 'true' : 'false' !!};
        
        let map;
        let markers = [];
        let charts = {};
        let useMockData = false;
        let currentData = []; // Add this to store current data globally

        function getAQIColor(aqi) {
            if (aqi <= 50) return '#4CAF50';
            if (aqi <= 100) return '#FDD835';
            if (aqi <= 150) return '#FF9800';
            if (aqi <= 200) return '#F44336';
            if (aqi <= 300) return '#8B0000';
            return '#800080';
        }

        function getAQIStatus(aqi) {
            if (aqi <= 50) return 'Good';
            if (aqi <= 100) return 'Moderate';
            if (aqi <= 150) return 'Unhealthy for Sensitive Groups';
            if (aqi <= 200) return 'Unhealthy';
            if (aqi <= 300) return 'Very Unhealthy';
            return 'Hazardous';
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
            
            // Generate 24 hours of data for each location
            for (let i = 0; i < 24; i++) {
                Object.entries(locationCoords).forEach(([location, coords]) => {
                    const time = new Date(now - i * 3600000); // Subtract hours
                    mockData.push({
                        location: location,
                        aqi: Math.floor(Math.random() * 150) + 50, // Random AQI between 50 and 200
                        reading_time: time.toISOString(),
                        latitude: coords.lat,
                        longitude: coords.lng
                    });
                });
            }

            return mockData;
        }

        function getLatestLocations(data) {
            const locations = [...new Set(data.map(item => item.location))];
            return locations.map(location => {
                const locationData = data.filter(d => d.location === location)
                    .sort((a, b) => new Date(b.reading_time) - new Date(a.reading_time));
                return locationData[0]; // Return only the latest reading
            });
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

            const maxAQI = Math.max(...data.map(d => d.aqi));

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

        function createCustomMarker(location) {
            const div = document.createElement('div');
            div.className = 'custom-marker';
            div.style.backgroundColor = getAQIColor(location.aqi);
            div.textContent = Math.round(location.aqi); // Round AQI value for cleaner display

            const tooltip = document.createElement('div');
            tooltip.className = 'marker-tooltip';
            tooltip.innerHTML = `
                <div class="font-semibold">${location.location}</div>
                <div>AQI: ${location.aqi}</div>
                <div>Status: ${getAQIStatus(location.aqi)}</div>
                <div class="text-xs text-gray-600">Last Updated: ${new Date().toLocaleTimeString()}</div>
            `;
            div.appendChild(tooltip);

            div.addEventListener('mouseenter', () => {
                tooltip.style.display = 'block';
            });

            div.addEventListener('mouseleave', () => {
                tooltip.style.display = 'none';
            });

            return div;
        }

        function initMap(locations) {
            const center = { lat: 6.9271, lng: 79.8612 };
            
            if (!map) {
                map = new google.maps.Map(document.getElementById("map"), {
                    zoom: 11,
                    center: center,
                    mapTypeControl: true,
                    streetViewControl: false,
                    styles: [
                        {
                            featureType: "poi",
                            elementType: "labels",
                            stylers: [{ visibility: "off" }]
                        }
                    ]
                });
            }

            // Clear existing markers
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            locations.forEach(location => {
                const position = { 
                    lat: parseFloat(location.latitude), 
                    lng: parseFloat(location.longitude) 
                };

                const overlay = new google.maps.OverlayView();
                
                overlay.onAdd = function() {
                    const div = createCustomMarker(location);
                    this.div = div;
                    const panes = this.getPanes();
                    panes.overlayMouseTarget.appendChild(div);
                };

                overlay.draw = function() {
                    const projection = this.getProjection();
                    const point = projection.fromLatLngToDivPixel(position);
                    if (point) {
                        this.div.style.position = 'absolute';
                        this.div.style.left = (point.x - 16) + 'px';
                        this.div.style.top = (point.y - 16) + 'px';
                    }
                };

                overlay.onRemove = function() {
                    if (this.div) {
                        this.div.parentNode.removeChild(this.div);
                        delete this.div;
                    }
                };

                overlay.setMap(map);
                markers.push(overlay);
            });
        }

        function updateData() {
            if (useMockData) {
                const mockData = generateMockData();
                currentData = mockData;
                updateCharts(mockData);
                updateMapAndCards(getLatestLocations(mockData));
                updateLocationDropdown(mockData);
            } else {
                $.get('/test-apis', function(data) {
                    currentData = data;
                    updateMapAndCards(data);
                    updateLocationDropdown(data);
                });
                
                $.get('/get-readings', function(data) {
                    updateCharts(Object.values(data).flat());
                });
            }
        }

        function updateMapAndCards(data) {
            let html = '';
            
            const processedData = data.map(location => {
                // Normalize the data structure between mock and live data
                return {
                    location: location.location,
                    latitude: location.latitude,
                    longitude: location.longitude,
                    aqi: useMockData ? location.aqi : location.air_quality_data.data.aqi,
                    station: useMockData ? 'Simulated' : location.air_quality_data.data.city.name,
                    reading_time: new Date().toISOString()
                };
            });

            processedData.forEach(location => {
                html += `
                    <div class="border rounded-lg p-4">
                        <h3 class="text-lg font-semibold">${location.location}</h3>
                        <p class="text-sm text-gray-600">Lat: ${location.latitude}, Lng: ${location.longitude}</p>
                        <div class="mt-2">
                            <div class="flex items-center gap-2">
                                <div class="custom-marker" style="background-color: ${getAQIColor(location.aqi)}">${location.aqi}</div>
                                <p class="text-lg">AQI: ${location.aqi}</p>
                            </div>
                            <p class="text-sm text-gray-600">Station: ${location.station}</p>
                            <p class="text-sm text-gray-600">Status: ${getAQIStatus(location.aqi)}</p>
                        </div>
                    </div>
                `;
            });
            
            $('#result').html(html);

            // Initialize map with normalized data
            initMap(processedData);
        }

        function updateNotifications() {
            $.get('/notifications', function(notifications) {
                let html = '';
                notifications.forEach(notification => {
                    const date = new Date(notification.created_at).toLocaleString();
                    const aqiColor = getAQIColor(notification.aqi_level);
                    html += `
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold">${notification.title}</h3>
                                    <p class="text-sm text-gray-600">By ${notification.user.name} • ${date}</p>
                                </div>
                                <div class="custom-marker" style="background-color: ${aqiColor}">${notification.aqi_level}</div>
                            </div>
                            <p class="mt-2">${notification.message}</p>
                            <p class="text-sm text-gray-600 mt-1">Location: ${notification.location}</p>
                        </div>
                    `;
                });
                $('#notificationsPanel').html(html || '<p class="text-gray-600">No notifications yet.</p>');
            });
        }

        function updateUsers() {
            $.get('/users', function(users) {
                let html = '';
                users.forEach(user => {
                    html += `
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold">${user.name}</h3>
                                    <p class="text-sm text-gray-600">${user.email}</p>
                                </div>
                                ${user.is_admin ? '<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Admin</span>' : ''}
                            </div>
                        </div>
                    `;
                });
                $('#usersPanel').html(html || '<p class="text-gray-600">No users found.</p>');
            });
        }

        function updateLocationDropdown(data) {
            const locations = data.map(item => item.location || item.air_quality_data?.data?.city?.name).filter(Boolean);
            let html = '<option value="">Select a location</option>';
            [...new Set(locations)].forEach(location => {
                html += `<option value="${location}">${location}</option>`;
            });
            $('#notificationLocation').html(html);
        }

        $(document).ready(function() {
            // Add CSRF token to all AJAX requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            // Initialize data and start updates
            updateData();
            setInterval(updateData, 60 * 1000);

            // Initialize notifications
            updateNotifications();
            if (isAuthenticated) {
                updateUsers();
            }
            setInterval(updateNotifications, 60 * 1000);

            // Set up data toggle
            $('#dataToggle').change(function() {
                useMockData = $(this).is(':checked');
                updateData();
            });

            // Store new readings every 15 minutes
            setInterval(function() {
                if (!useMockData) {
                    $.get('/store-reading');
                }
            }, 15 * 60 * 1000);

            // Modal handling
            $('#createNotification').click(function() {
                $('#notificationModal').removeClass('hidden').addClass('flex');
            });

            $('#closeModal').click(function() {
                $('#notificationModal').removeClass('flex').addClass('hidden');
            });

            $('#notificationForm').submit(function(e) {
                e.preventDefault();
                const location = $('#notificationLocation').val();
                const title = $('#notificationTitle').val();
                const message = $('#notificationMessage').val();

                // Form validation
                if (!location || !title || !message) {
                    alert('Please fill in all fields');
                    return;
                }

                // Find the current AQI for selected location
                let aqi;
                if (useMockData) {
                    const locationData = currentData.find(d => d.location === location);
                    aqi = locationData?.aqi;
                } else {
                    const locationData = currentData.find(d => d.location === location);
                    aqi = locationData?.air_quality_data?.data?.aqi;
                }

                if (!aqi) {
                    alert('Could not determine AQI for selected location');
                    return;
                }

                $.ajax({
                    url: '/notifications',
                    method: 'POST',
                    data: {
                        title: title,
                        message: message,
                        location: location,
                        aqi_level: aqi
                    },
                    success: function(response) {
                        $('#notificationModal').removeClass('flex').addClass('hidden');
                        $('#notificationForm')[0].reset();
                        updateNotifications();
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'An error occurred';
                        alert('Error creating notification: ' + message);
                    }
                });
            });
        });
    </script>
</body>
</html> 