<?php
header("Content-Type: application/json");

// Database configuration
$host = '127.0.0.1';
$db   = 'DBInsurance';
$user = 'root';
$pass = 'AppDev@2021';
$charset = 'utf8mb4';

// PDO Database Connection
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit; // Stop further execution if connection fails
}

function isParamSet($param) {
    return isset($_GET[$param]) && strlen($_GET[$param]) > 0;
}

// Function to check if carID exists in carDetails
function checkCarExists($pdo, $carID) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM carDetails WHERE carID = ?");
    $stmt->execute([$carID]);
    return $stmt->fetchColumn() > 0;
}

// Function to check if driver ID exists in driverDetails
function checkDriverExists($pdo, $driverID) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM driverDetails WHERE ID = ?");
    $stmt->execute([$driverID]);
    return $stmt->fetchColumn() > 0;
}

// Function to check if claimID already exists in claims
function checkClaimIDExists($pdo, $claimID) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM claimDetails WHERE claimID = ?");
    $stmt->execute([$claimID]);
    return $stmt->fetchColumn() > 0;
}

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Start building the query dynamically based on filled out fields
        $query = "SELECT * FROM claimDetails WHERE 1=1";
        $params = [];

        // Dynamically append conditions for each field
        $fields = ['claimID', 'carID', 'ID', 'CLAIM_FLAG', 'CLM_AMT', 'CLM_FREQ', 'OLDCLAIM'];
        foreach ($fields as $field) {
            if (isParamSet($field)) {
                $query .= " AND $field = ?";
                $params[] = $_GET[$field];
            }
        }

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $claims = $stmt->fetchAll();
            echo json_encode($claims);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

        case 'POST':
            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true);
                $required_fields = ['claimID', 'carID', 'ID', 'CLAIM_FLAG', 'CLM_AMT', 'CLM_FREQ', 'OLDCLAIM'];
                $missing_fields = [];
        
                foreach ($required_fields as $field) {
                    if (empty($input[$field])) {
                        $missing_fields[] = $field;
                    }
                }
        
                if (!empty($missing_fields)) {
                    echo json_encode(['error' => 'Missing required fields: ' . implode(', ', $missing_fields)]);
                    exit;
                }
        
                // Check if claimID already exists
                if (checkClaimIDExists($pdo, $input['claimID'])) {
                    echo json_encode(['error' => 'claimID already exists']);
                    exit;
                }
        
                // Check if carID and driver ID exist
                if (!checkCarExists($pdo, $input['carID'])) {
                    echo json_encode(['error' => 'carID does not exist']);
                    exit;
                }
        
                if (!checkDriverExists($pdo, $input['ID'])) {
                    echo json_encode(['error' => 'Driver ID does not exist']);
                    exit;
                }
        
                try {
                    $stmt = $pdo->prepare("INSERT INTO claimDetails (claimID, carID, ID, CLAIM_FLAG, CLM_AMT, CLM_FREQ, OLDCLAIM) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$input['claimID'], $input['carID'], $input['ID'], $input['CLAIM_FLAG'], $input['CLM_AMT'], $input['CLM_FREQ'], $input['OLDCLAIM']]);
                    echo json_encode(['status' => 'Claim Details Added']);
                } catch (PDOException $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
                break;
    
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $claimID = $input['claimID'] ?? null;  // Ensure claimID is provided to identify the record.
    
        if (!$claimID) {
            echo json_encode(['error' => 'No claimID provided for update']);
            exit;
        }
    
        $fields = [];
        $params = [];
        foreach ($input as $key => $value) {
            if (!empty($value) && $key != 'claimID') {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
    
        if (empty($fields)) {
            echo json_encode(['error' => 'No fields to update provided']);
            exit;
        }
    
        // Append the claimID at the end of the params array for the WHERE clause
        $params[] = $claimID;
        $sql = "UPDATE claimDetails SET " . join(', ', $fields) . " WHERE claimID = ?";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'Claim Details Updated']);
            } else {
                echo json_encode(['error' => 'No update performed, check your data']);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['claimID'])) {
                echo json_encode(['error' => 'No claimID provided for deletion']);
                exit;
            }
            
            if (!checkClaimIDExists($pdo, $input['claimID'])) {
                echo json_encode(['error' => 'claimID does not exist']);
                exit;
            }

            try {
                // Disable foreign key checks
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
                
                $stmt = $pdo->prepare("DELETE FROM claimDetails WHERE claimID = ?");
                $stmt->execute([$input['claimID']]);
                
                // Re-enable foreign key checks
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['status' => 'Claim Details Deleted']);
                } else {
                    echo json_encode(['status' => 'No claim found with that ID, nothing deleted']);
                }
            } catch (PDOException $e) {
                // Ensure foreign key checks are re-enabled if an error occurs
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
}
?>
