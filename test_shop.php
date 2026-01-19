<?php
session_start();

// Include database connection
require_once 'backend/classes/autoload.php';
$DB = new Database();

// Test query
echo "<h2>Testing Shop Database Connection</h2>";

// Check if shops table exists
try {
    $result = $DB->read("SELECT * FROM shops LIMIT 1");
    echo "<p><strong>✓ Shops table exists</strong></p>";

    if ($result) {
        echo "<p>Sample shop data:</p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    } else {
        echo "<p><strong>⚠ No shops found in database</strong></p>";
    }
} catch (Exception $e) {
    echo "<p><strong>✗ Error:</strong> " . $e->getMessage() . "</p>";
}

// Check shops table structure
try {
    $structure = $DB->read("DESCRIBE shops");
    echo "<p><strong>Shops table structure:</strong></p>";
    echo "<pre>" . print_r($structure, true) . "</pre>";
} catch (Exception $e) {
    echo "<p><strong>✗ Error getting table structure:</strong> " . $e->getMessage() . "</p>";
}

// Test get_my_shop query for user ID 4
echo "<h3>Testing get_my_shop query for user ID 4:</h3>";
try {
    $query = "SELECT s.*, u.id as owner_id, u.name as owner_name, u.email as owner_email 
              FROM shops s 
              LEFT JOIN Users u ON u.id = s.userId 
              WHERE s.userId = :uid 
              LIMIT 1";
    $result = $DB->read($query, ['uid' => 4]);

    if ($result) {
        echo "<p><strong>✓ Query successful</strong></p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    } else {
        echo "<p><strong>⚠ No shop found for user ID 4</strong></p>";
    }
} catch (Exception $e) {
    echo "<p><strong>✗ Error:</strong> " . $e->getMessage() . "</p>";
}
?>