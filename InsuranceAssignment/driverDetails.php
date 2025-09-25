<?php
header("Content-Type: application/json");

// Database configuration
$host = '127.0.0.1';
$db = 'DBInsurance';
$user = 'root';
$pass = 'AppDev@2021';
$charset = 'utf8mb4';

// PDO Database Connection
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit; // Stop further execution if connection fails
}

// Helper function to check if a parameter is set and not empty
function isParamSet($param) {
    return isset($_GET[$param]) && strlen($_GET[$param]) > 0;
}

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Start building the query dynamically based on provided fields
        $query = "SELECT * FROM driverDetails WHERE 1 = 1";
        $params = [];
        $fields = ['ID', 'KIDSDRIV', 'Age', 'INCOME', 'MSTATUS', 'GENDER', 'EDUCATION', 'OCCUPATION'];

        foreach ($fields as $field) {
            if (isParamSet($field)) {
                $query .= " AND $field = ?";
                $params[] = $_GET[$field];
            }
        }

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $drivers = $stmt->fetchAll();
            echo json_encode($drivers);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $required_fields = ['ID', 'KIDSDRIV', 'Age', 'INCOME', 'GENDER', 'EDUCATION', 'OCCUPATION'];
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
            $stmt = $pdo->prepare("INSERT INTO driverDetails (ID, KIDSDRIV, Age, INCOME, MSTATUS, GENDER, EDUCATION, OCCUPATION) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$input['ID'], $input['KIDSDRIV'], $input['Age'], $input['INCOME'], $input['MSTATUS'], $input['GENDER'], $input['EDUCATION'], $input['OCCUPATION']]);
            echo json_encode(['status' => 'Driver Details Added']);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $ID = $input['ID'] ?? null;

        if (!$ID) {
            echo json_encode(['error' => 'No ID provided for update']);
            exit;
        }

        $fields = [];
        $params = [];
        foreach ($input as $key => $value) {
            if (!empty($value) && $key != 'ID') {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            echo json_encode(['error' => 'No fields to update provided']);
            exit;
        }

        $params[] = $ID;
        $sql = "UPDATE driverDetails SET " . join(', ', $fields) . " WHERE ID = ?";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'Driver Details Updated']);
            } else {
                echo json_encode(['error' => 'No update performed, check your data']);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['ID'])) {
                echo json_encode(['error' => 'No ID provided for deletion']);
                exit;
            }
        
            try {
                // Start transaction
                $pdo->beginTransaction();
        
                // Disable foreign key constraint checks
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');

                // Delete from claimDetails
                $stmt = $pdo->prepare("DELETE FROM claimDetails WHERE ID = ?");
                $stmt->execute([$input['ID']]);
        
                // Delete from carDetails
                $stmt = $pdo->prepare("DELETE FROM carDetails WHERE ID = ?");
                $stmt->execute([$input['ID']]);
        
        
                // Delete from driverDetails
                $stmt = $pdo->prepare("DELETE FROM driverDetails WHERE ID = ?");
                $stmt->execute([$input['ID']]);
        
                // Re-enable foreign key constraint checks
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
        
                // Commit transaction
                $pdo->commit();
        
                echo json_encode(['status' => 'All associated details deleted successfully with constraints managed']);
            } catch (PDOException $e) {
                // Rollback transaction on error, and re-enable foreign key checks
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
                $pdo->rollback();
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
        

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}
?>
