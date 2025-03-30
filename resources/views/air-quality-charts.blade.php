<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air Quality Charts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-8">Air Quality History</h1>
        
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Chart Container -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                <canvas id="aqiChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        let chart;

        function getAQIColor(aqi) {
            if (aqi <= 50) return '#4CAF50';
            if (aqi <= 100) return '#FDD835';
            if (aqi <= 150) return '#FF9800';
            if (aqi <= 200) return '#F44336';
            if (aqi <= 300) return '#8B0000';
            return '#800080';
        }

        function createChart(data) {
            const ctx = document.getElementById('aqiChart').getContext('2d');
            
            // Process data for chart
            const datasets = [];
            const timeLabels = [];
            
            // Get unique timestamps for X-axis
            Object.values(data)[0].forEach(reading => {
                const time = new Date(reading.reading_time).toLocaleTimeString();
                if (!timeLabels.includes(time)) {
                    timeLabels.push(time);
                }
            });

            // Create dataset for each location
            Object.entries(data).forEach(([location, readings]) => {
                const locationData = readings.map(r => r.aqi);
                const color = getAQIColor(locationData[locationData.length - 1]);
                
                datasets.push({
                    label: location,
                    data: locationData,
                    borderColor: color,
                    backgroundColor: color + '20',
                    tension: 0.4,
                    fill: true
                });
            });

            // Create chart
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: timeLabels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Air Quality Index (AQI)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Time'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: '24-Hour AQI Trends'
                        },
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        }

        function updateData() {
            $.get('/get-readings', function(data) {
                if (chart) {
                    chart.destroy();
                }
                createChart(data);
            });
        }

        // Initial load
        $(document).ready(function() {
            updateData();
            // Update every 5 minutes
            setInterval(updateData, 5 * 60 * 1000);
        });
    </script>
</body>
</html> 