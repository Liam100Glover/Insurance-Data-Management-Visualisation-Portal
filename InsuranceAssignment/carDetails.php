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

// Function to check if Owner ID exists in driverDetails
function checkOwnerExists($pdo, $ownerId) {
    $sql = "SELECT COUNT(*) FROM driverDetails WHERE ID = :ownerId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':ownerId' => $ownerId]);
    return $stmt->fetchColumn() > 0;
}

// Function to check if carID exists for updates/deletions
function checkCarExists($pdo, $carID) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM carDetails WHERE carID = ?");
    $stmt->execute([$carID]);
    return $stmt->fetchColumn() > 0;
}

function isParamSet($param) {
    return isset($_GET[$param]) && strlen($_GET[$param]) > 0;
}

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Start building the query dynamically based on filled out fields
        $query = "SELECT * FROM carDetails WHERE 1=1";
        $params = [];

        // Dynamically append conditions for each field
        $fields = ['carID', 'ID', 'CAR_TYPE', 'RED_CAR', 'CAR_AGE'];
        foreach ($fields as $field) {
            if (isParamSet($field)) {
                $query .= " AND $field = ?";
                $params[] = $_GET[$field];
            }
        }

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $cars = $stmt->fetchAll();
            echo json_encode($cars);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $required_fields = ['carID', 'ID', 'CAR_TYPE', 'CAR_AGE'];
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
        
            try {
                $stmt = $pdo->prepare("INSERT INTO carDetails (carID, ID, CAR_TYPE, RED_CAR, CAR_AGE) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$input['carID'], $input['ID'], $input['CAR_TYPE'], $input['RED_CAR'], $input['CAR_AGE']]);
                echo json_encode(['status' => 'Car Details Added']);
            } catch (PDOException $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
        

            case 'PUT':
                $input = json_decode(file_get_contents('php://input'), true);
                $carID = $input['carID'] ?? null;  // Ensure carID is provided to identify the record.
            
                if (!$carID) {
                    echo json_encode(['error' => 'No carID provided for update']);
                    exit;
                }
            
                // Check if the car exists first
                if (!checkCarExists($pdo, $carID)) {
                    echo json_encode(['error' => 'Car does not exist, provide a valid carID']);
                    exit;
                }
            
                $fields = [];
                $params = [];
                foreach ($input as $key => $value) {
                    // Only add the field to the update list if it's not empty and not 'carID' or 'ID'
                    if (!empty($value) && $key != 'carID' && $key != 'ID') {
                        $fields[] = "$key = ?";
                        $params[] = $value;
                    }
                }
            
                if (empty($fields)) {
                    echo json_encode(['error' => 'No fields to update provided']);
                    exit;
                }
            
                // Append the carID at the end of the params array for the WHERE clause
                $params[] = $carID;
                $sql = "UPDATE carDetails SET " . join(', ', $fields) . " WHERE carID = ?";
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    if ($stmt->rowCount() > 0) {
                        echo json_encode(['status' => 'Car Details Updated']);
                    } else {
                        echo json_encode(['error' => 'No update performed, check your data']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
                break;
            
            
                                          
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['carID'])) {
                echo json_encode(['error' => 'No carID provided for deletion']);
                exit;
            }
        
            try {
                // Disable foreign key checks
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
                
                $stmt = $pdo->prepare("DELETE FROM carDetails WHERE carID = ?");
                $stmt->execute([$input['carID']]);
                
                // Re-enable foreign key checks
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        
                echo json_encode(['status' => 'Car Details Deleted']);
            } catch (PDOException $e) {
                // Ensure foreign key checks are re-enabled if an error occurs
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
        

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}
?>
