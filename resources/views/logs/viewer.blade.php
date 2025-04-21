<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Log Viewer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .log-container {
            height: calc(100vh - 100px);
            overflow-y: auto;
        }
        .log-line {
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .log-line:hover {
            background-color: #f3f4f6;
            color: #000;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800">Laravel Log Viewer</h1>
                <div class="space-x-2">
                    <button id="refreshBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        Refresh
                    </button>
                    <button id="clearBtn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                        Clear Logs
                    </button>
                </div>
            </div>
            <div id="logContainer" class="log-container bg-gray-900 text-gray-100 p-4 rounded-lg">
                <div id="logContent"></div>
            </div>
        </div>
    </div>

    <script>
        function updateLogs() {
            fetch('/logs/get')
                .then(response => response.json())
                .then(data => {
                    const logContent = document.getElementById('logContent');
                    logContent.innerHTML = data.logs.map(log => 
                        `<div class="log-line py-1">${log}</div>`
                    ).join('');
                    logContent.scrollTop = logContent.scrollHeight;
                })
                .catch(error => console.error('Error fetching logs:', error));
        }

        function clearLogs() {
            if (confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
                fetch('/logs/clear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    updateLogs();
                })
                .catch(error => console.error('Error clearing logs:', error));
            }
        }

        // Update logs every 5 seconds
        setInterval(updateLogs, 5000);

        // Initial load
        updateLogs();

        // Refresh button click handler
        document.getElementById('refreshBtn').addEventListener('click', updateLogs);

        // Clear button click handler
        document.getElementById('clearBtn').addEventListener('click', clearLogs);
    </script>
</body>
</html> 