<!DOCTYPE html>
<html>
<head>
    <title>Debug Error 500</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .error { background: #ffebee; border-left: 4px solid #f44336; padding: 15px; margin: 10px 0; }
        .success { background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 10px 0; }
        .info { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Debug Error 500</h1>
    
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<h2>1. Test Database Connection</h2>";
    try {
        require_once 'config/database.php';
        echo "<div class='success'>✅ Database connected successfully</div>";
        echo "<div class='info'>Database: " . mysqli_get_host_info($conn) . "</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Database Error: " . $e->getMessage() . "</div>";
    }
    
    echo "<h2>2. Test Functions</h2>";
    try {
        require_once 'config/functions.php';
        echo "<div class='success'>✅ Functions loaded successfully</div>";
        
        // Test clean_input function
        $test = clean_input("test<script>");
        echo "<div class='info'>clean_input() works: " . htmlspecialchars($test) . "</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Functions Error: " . $e->getMessage() . "</div>";
    }
    
    echo "<h2>3. Test Query Ruangan</h2>";
    try {
        $query = "SELECT COUNT(*) as total FROM ruangan WHERE status = 'tersedia'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            echo "<div class='success'>✅ Query executed: " . $row['total'] . " ruangan tersedia</div>";
        } else {
            echo "<div class='error'>❌ Query failed: " . mysqli_error($conn) . "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Query Error: " . $e->getMessage() . "</div>";
    }
    
    echo "<h2>4. Test Filter Kategori</h2>";
    try {
        $where = array();
        $where[] = "r.status = 'tersedia'";
        $where[] = "r.nama_ruangan LIKE '%Lab%'";
        
        $where_clause = implode(' AND ', $where);
        $query = "SELECT DISTINCT r.* 
                  FROM ruangan r 
                  LEFT JOIN jadwal_ruangan j ON r.id = j.ruangan_id 
                  WHERE $where_clause 
                  ORDER BY r.nama_ruangan ASC";
        
        $result = mysqli_query($conn, $query);
        if ($result) {
            $total = mysqli_num_rows($result);
            echo "<div class='success'>✅ Filter kategori works: " . $total . " lab rooms found</div>";
            echo "<div class='info'><strong>Ruangan Lab:</strong><br>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "- " . $row['nama_ruangan'] . "<br>";
            }
            echo "</div>";
        } else {
            echo "<div class='error'>❌ Filter query failed: " . mysqli_error($conn) . "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Filter Error: " . $e->getMessage() . "</div>";
    }
    
    echo "<h2>5. Test AJAX File</h2>";
    try {
        $ajax_file = 'ajax_ruangan_load.php';
        if (file_exists($ajax_file)) {
            echo "<div class='success'>✅ " . $ajax_file . " exists</div>";
            
            // Check file permissions
            if (is_readable($ajax_file)) {
                echo "<div class='success'>✅ File is readable</div>";
            } else {
                echo "<div class='error'>❌ File is not readable</div>";
            }
        } else {
            echo "<div class='error'>❌ " . $ajax_file . " not found</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ File Error: " . $e->getMessage() . "</div>";
    }
    
    echo "<h2>6. PHP Info</h2>";
    echo "<div class='info'>";
    echo "PHP Version: " . phpversion() . "<br>";
    echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
    echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
    echo "</div>";
    
    echo "<h2>7. Test AJAX Request</h2>";
    echo "<button onclick='testAjax()'>Test AJAX Request</button>";
    echo "<div id='ajaxResult'></div>";
    ?>
    
    <script>
    function testAjax() {
        const resultDiv = document.getElementById('ajaxResult');
        resultDiv.innerHTML = '<div class="info">⏳ Loading...</div>';
        
        fetch('ajax_ruangan_load.php?kategori=lab')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = '<div class="success">✅ AJAX works! Found ' + data.total + ' rooms</div>';
                } else {
                    resultDiv.innerHTML = '<div class="error">❌ AJAX Error: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="error">❌ Fetch Error: ' + error + '</div>';
            });
    }
    </script>
</body>
</html>
