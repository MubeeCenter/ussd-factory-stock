<?php
/* ============================================================
   MUBEETECH — USSD Callback Handler
   Africa's Talking USSD API — "Factory Stock Check"
   Database: mubeetec_portfolio_db
   ============================================================ */

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================
// 1. DATABASE CONNECTION — Using environment variables
// ============================================================
require_once __DIR__ . '/config.php';

// ============================================================
// 2. USSD SESSION HANDLING
// ============================================================

// Africa's Talking sends these parameters via POST
$sessionId   = $_POST['sessionId'] ?? null;
$serviceCode = $_POST['serviceCode'] ?? null;
$phoneNumber = $_POST['phoneNumber'] ?? null;
$text        = $_POST['text'] ?? null;

// Array to hold session state
$sessionData = [];

// Retrieve existing session from database
if ($sessionId) {
    $stmt = mysqli_prepare($conn, "SELECT response FROM ussd_sessions WHERE session_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $sessionId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        $sessionData = json_decode($row['response'], true) ?? [];
    }
    mysqli_stmt_close($stmt);
}

// ============================================================
// 3. USSD MENU LOGIC
// ============================================================

$response = "";

if ($text == "") {
    // First time user — show main menu
    $response = "CON Welcome to Factory Stock Check.\n";
    $response .= "1. Check Stock\n";
    $response .= "2. Request Restock\n";
    $response .= "0. Exit";
    $sessionData['state'] = 'main_menu';
    
} else {
    $userInput = explode("*", $text);
    $lastInput = end($userInput);
    $state = $sessionData['state'] ?? 'main_menu';
    
    switch ($state) {
        case 'main_menu':
            if ($lastInput == "1") {
                $response = "CON Enter Material Code:";
                $sessionData['state'] = 'check_stock';
            } elseif ($lastInput == "2") {
                $response = "CON Enter Material Code for restock:";
                $sessionData['state'] = 'request_restock';
            } elseif ($lastInput == "0") {
                $response = "END Thank you. Goodbye.";
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
            $materialCode = strtoupper(trim($lastInput));
            
            $stmt = mysqli_prepare($conn, "SELECT name, quantity, last_updated FROM materials WHERE code = ?");
            mysqli_stmt_bind_param($stmt, "s", $materialCode);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $material = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
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
            break;
            
        case 'request_restock':
            $materialCode = strtoupper(trim($lastInput));
            
            $stmt = mysqli_prepare($conn, "SELECT name FROM materials WHERE code = ?");
            mysqli_stmt_bind_param($stmt, "s", $materialCode);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $material = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            if ($material) {
                $stmt = mysqli_prepare($conn, "INSERT INTO restock_requests (material_code, requested_by, status) VALUES (?, ?, 'pending')");
                mysqli_stmt_bind_param($stmt, "ss", $materialCode, $phoneNumber);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                
                $response = "END Restock request for " . $material['name'] . " sent to supervisor.\n";
                $response .= "You will receive confirmation shortly.";
                $sessionData = [];
            } else {
                $response = "CON Invalid material code. Please try again.\n";
                $response .= "Enter Material Code for restock:";
                $sessionData['state'] = 'request_restock';
            }
            break;
            
        case 'after_stock_check':
            if ($lastInput == "1") {
                $materialCode = $sessionData['last_material_code'] ?? '';
                if ($materialCode) {
                    $stmt = mysqli_prepare($conn, "SELECT name FROM materials WHERE code = ?");
                    mysqli_stmt_bind_param($stmt, "s", $materialCode);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $material = mysqli_fetch_assoc($result);
                    mysqli_stmt_close($stmt);
                    
                    if ($material) {
                        $stmt = mysqli_prepare($conn, "INSERT INTO restock_requests (material_code, requested_by, status) VALUES (?, ?, 'pending')");
                        mysqli_stmt_bind_param($stmt, "ss", $materialCode, $phoneNumber);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                        
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
// 4. SAVE SESSION STATE
// ============================================================
if ($sessionId && !empty($sessionData)) {
    $jsonData = json_encode($sessionData);
    $stmt = mysqli_prepare($conn, "INSERT INTO ussd_sessions (session_id, response, phone_number, updated_at) 
                                   VALUES (?, ?, ?, NOW()) 
                                   ON DUPLICATE KEY UPDATE response = ?, updated_at = NOW()");
    mysqli_stmt_bind_param($stmt, "ssss", $sessionId, $jsonData, $phoneNumber, $jsonData);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} elseif ($sessionId && empty($sessionData)) {
    $stmt = mysqli_prepare($conn, "DELETE FROM ussd_sessions WHERE session_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $sessionId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ============================================================
// 5. SEND RESPONSE BACK TO AFRICA'S TALKING
// ============================================================
header('Content-Type: text/plain');
echo $response;
?>