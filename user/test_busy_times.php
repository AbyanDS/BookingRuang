<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Test Busy Times Check</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            font-size: 14px;
        }
        button {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #5568d3;
        }
        #result {
            margin-top: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
            white-space: pre-wrap;
            font-family: monospace;
        }
        .warning-box {
            margin-top: 15px;
            padding: 15px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 5px;
        }
        .busy-item {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Test Busy Times Check</h1>
    
    <div class="form-group">
        <label for="ruangan_id">ID Ruangan:</label>
        <input type="number" id="ruangan_id" value="1" placeholder="Masukkan ID ruangan">
    </div>
    
    <div class="form-group">
        <label for="tanggal">Tanggal:</label>
        <input type="date" id="tanggal" value="<?php echo date('Y-m-d'); ?>">
    </div>
    
    <button onclick="checkBusyTimes()">Check Busy Times</button>
    
    <div id="result"></div>
    <div id="warnings"></div>
    
    <script>
        function checkBusyTimes() {
            const ruanganId = document.getElementById('ruangan_id').value;
            const tanggal = document.getElementById('tanggal').value;
            const resultDiv = document.getElementById('result');
            const warningsDiv = document.getElementById('warnings');
            
            resultDiv.textContent = 'Loading...';
            warningsDiv.innerHTML = '';
            
            console.log('Checking busy times for room:', ruanganId, 'date:', tanggal);
            
            fetch('ajax_check_busy_times.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ruangan_id=' + ruanganId + '&tanggal=' + tanggal
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed data:', data);
                    
                    resultDiv.textContent = JSON.stringify(data, null, 2);
                    
                    if (data.success && data.warnings.length > 0) {
                        let html = '<div class="warning-box">';
                        html += '<h3>Warnings (' + data.warnings.length + '):</h3>';
                        html += '<p>Total ' + data.busy_times.length + ' slot waktu terblokir</p>';
                        
                        data.warnings.forEach(warning => {
                            html += '<div class="busy-item">';
                            html += '<strong>' + warning.start + ' - ' + warning.end + '</strong><br>';
                            html += warning.info + '<br>';
                            if (warning.user) html += '<small>User: ' + warning.user + '</small>';
                            if (warning.pic) html += '<small>PIC: ' + warning.pic + '</small>';
                            html += '</div>';
                        });
                        
                        html += '<h4>Busy Times Array:</h4>';
                        html += '<p>' + data.busy_times.join(', ') + '</p>';
                        html += '</div>';
                        
                        warningsDiv.innerHTML = html;
                    } else if (data.success) {
                        warningsDiv.innerHTML = '<div class="warning-box" style="background: #d4edda; border-left-color: #28a745;"><strong>✓ Tidak ada waktu yang terpakai</strong></div>';
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    resultDiv.textContent = 'Error parsing JSON: ' + e.message + '\n\nRaw response:\n' + text;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                resultDiv.textContent = 'Error: ' + error.message;
            });
        }
        
        // Auto-load on page load
        window.addEventListener('load', function() {
            checkBusyTimes();
        });
    </script>
</body>
</html>
