<?php
/* ============================================================
   MUBEETECH — USSD Callback Handler
   Africa's Talking USSD API — "Factory Stock Check"
   ============================================================ */

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================
// 1. DATABASE CONNECTION (using your existing config)
// ============================================================
// Adjust path to your global config if needed
require_once __DIR__ . '/../includes/config.php';

// Database connection (fallback if config.php not found)
if (!function_exists('getDBConnection')) {
    function getDBConnection() {
        $host = 'localhost';
        $dbname = 'ussd_factory';
        $username = 'your_db_user';
        $password = 'your_db_password';
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            // Log error and return simple response
            error_log("Database connection failed: " . $e->getMessage());
            return null;
        }
    }
}

// ============================================================
// 2. USSD SESSION HANDLING
// ============================================================
$pdo = getDBConnection();

// Africa's Talking sends these parameters via POST
$sessionId   = $_POST['sessionId'] ?? null;
$serviceCode = $_POST['serviceCode'] ?? null;
$phoneNumber = $_POST['phoneNumber'] ?? null;
$text        = $_POST['text'] ?? null;

// For demo/sandbox, we use a simple array to store session state
// In production, use database or Redis for session persistence
$sessionData = [];

// If session exists in database, retrieve it
if ($pdo && $sessionId) {
    $stmt = $pdo->prepare("SELECT response, user_input FROM ussd_sessions WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $sessionData = json_decode($row['response'], true) ?? [];
    }
}

// ============================================================
// 3. USSD MENU LOGIC
// ============================================================

// Default response (empty -> main menu)
$response = "";

if ($text == "") {
    // First time user — show main menu
    $response = "CON Welcome to Factory Stock Check.\n";
    $response .= "1. Check Stock\n";
    $response .= "2. Request Restock\n";
    $response .= "0. Exit";
    
    // Save session state
    $sessionData['state'] = 'main_menu';
    
} else {
    // Split user input by asterisk to handle multi-level USSD
    $userInput = explode("*", $text);
    $lastInput = end($userInput);
    
    // Determine current state
    $state = $sessionData['state'] ?? 'main_menu';
    
    switch ($state) {
        case 'main_menu':
            if ($lastInput == "1") {
                // User selected "Check Stock"
                $response = "CON Enter Material Code:";
                $sessionData['state'] = 'check_stock';
            } elseif ($lastInput == "2") {
                // User selected "Request Restock"
                $response = "CON Enter Material Code for restock:";
                $sessionData['state'] = 'request_restock';
            } elseif ($lastInput == "0") {
                $response = "END Thank you. Goodbye.";
                // Clear session
                $sessionData = [];
            } else {
                $response = "CON Invalid option. Please try again.\n";
                $response .= "1. Check Stock\n";
                $response .= "2. Request Restock\n";
                $response .= "0. Exit";
                $sessionData['state'] = 'main_menu';
            }
            break;
            
        case 'check_stock':
            // User entered material code
            $materialCode = strtoupper(trim($lastInput));
            
            // Validate and check stock
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT name, quantity, last_updated FROM materials WHERE code = ?");
                $stmt->execute([$materialCode]);
                $material = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($material) {
                    $response = "CON Material: " . $material['name'] . "\n";
                    $response .= "Stock: " . number_format($material['quantity']) . " units\n";
                    $response .= "Last Updated: " . date('d M Y', strtotime($material['last_updated'])) . "\n";
                    $response .= "1. Request Restock\n";
                    $response .= "0. Exit";
                    $sessionData['state'] = 'after_stock_check';
                    $sessionData['last_material_code'] = $materialCode;
                } else {
                    $response = "CON Invalid code. Please try again.\n";
                    $response .= "Enter Material Code:";
                    $sessionData['state'] = 'check_stock';
                }
            } else {
                // Fallback if no database
                $response = "CON Demo: Stock for " . $materialCode . " is 450 units.\n";
                $response .= "1. Request Restock\n";
                $response .= "0. Exit";
                $sessionData['state'] = 'after_stock_check';
                $sessionData['last_material_code'] = $materialCode;
            }
            break;
            
        case 'request_restock':
            $materialCode = strtoupper(trim($lastInput));
            
            // Validate material exists
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT name FROM materials WHERE code = ?");
                $stmt->execute([$materialCode]);
                $material = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($material) {
                    // Insert restock request
                    $stmt = $pdo->prepare("INSERT INTO restock_requests (material_code, requested_by, status) VALUES (?, ?, 'pending')");
                    $stmt->execute([$materialCode, $phoneNumber]);
                    
                    $response = "END Restock request for " . $material['name'] . " sent to supervisor.\n";
                    $response .= "You will receive confirmation shortly.";
                    $sessionData = [];
                } else {
                    $response = "CON Invalid material code. Please try again.\n";
                    $response .= "Enter Material Code for restock:";
                    $sessionData['state'] = 'request_restock';
                }
            } else {
                // Fallback demo response
                $response = "END Restock request for " . $materialCode . " sent successfully.";
                $sessionData = [];
            }
            break;
            
        case 'after_stock_check':
            if ($lastInput == "1") {
                // User wants to request restock from stock check result
                $materialCode = $sessionData['last_material_code'] ?? '';
                if ($pdo && $materialCode) {
                    $stmt = $pdo->prepare("SELECT name FROM materials WHERE code = ?");
                    $stmt->execute([$materialCode]);
                    $material = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($material) {
                        $stmt = $pdo->prepare("INSERT INTO restock_requests (material_code, requested_by, status) VALUES (?, ?, 'pending')");
                        $stmt->execute([$materialCode, $phoneNumber]);
                        
                        $response = "END Restock request for " . $material['name'] . " sent to supervisor.\n";
                        $response .= "You will receive confirmation shortly.";
                        $sessionData = [];
                    } else {
                        $response = "END Error: Material not found. Please try again.";
                        $sessionData = [];
                    }
                } else {
                    $response = "END Restock request sent successfully.";
                    $sessionData = [];
                }
            } elseif ($lastInput == "0") {
                $response = "END Thank you. Goodbye.";
                $sessionData = [];
            } else {
                $response = "CON Invalid option. Please try again.\n";
                $response .= "1. Request Restock\n";
                $response .= "0. Exit";
                $sessionData['state'] = 'after_stock_check';
            }
            break;
            
        default:
            $response = "CON Welcome to Factory Stock Check.\n";
            $response .= "1. Check Stock\n";
            $response .= "2. Request Restock\n";
            $response .= "0. Exit";
            $sessionData['state'] = 'main_menu';
            break;
    }
}

// ============================================================
// 4. SAVE SESSION STATE (for production persistence)
// ============================================================
if ($pdo && $sessionId && !empty($sessionData)) {
    // Update or insert session
    $jsonData = json_encode($sessionData);
    $stmt = $pdo->prepare("INSERT INTO ussd_sessions (session_id, response, phone_number, updated_at) 
                           VALUES (?, ?, ?, NOW()) 
                           ON DUPLICATE KEY UPDATE response = ?, updated_at = NOW()");
    $stmt->execute([$sessionId, $jsonData, $phoneNumber, $jsonData]);
} elseif ($pdo && $sessionId && empty($sessionData)) {
    // Delete session if data is empty (user exited)
    $stmt = $pdo->prepare("DELETE FROM ussd_sessions WHERE session_id = ?");
    $stmt->execute([$sessionId]);
}

// ============================================================
// 5. SEND RESPONSE BACK TO AFRICA'S TALKING
// ============================================================
header('Content-Type: text/plain');
echo $response;