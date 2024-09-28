<?php 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With,  Origin, Content-Type,");
header("Access-Control-Max-Age: 86400");
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set("Asia/Manila");
set_time_limit(1000);

$path = $_SERVER["DOCUMENT_ROOT"] ."/APIact/Connection.php";
require_once $path;

if (!class_exists('Database')) {
  die('Database class not found.');
}

$dbo = new Database();

// Fetch all users
function getUsers($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM Users");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch a user by ID
function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Insert a new user
function insertUser($pdo, $firstname, $lastname, $is_admin) {
    $stmt = $pdo->prepare("INSERT INTO Users (firstname, lastname, is_admin) VALUES (:firstname, :lastname, :is_admin)");
    $stmt->bindParam(':firstname', $firstname);
    $stmt->bindParam(':lastname', $lastname);
    $stmt->bindParam(':is_admin', $is_admin, PDO::PARAM_BOOL);
    return $stmt->execute();
}

// Update an existing user
function updateUser($pdo, $id, $firstname, $lastname, $is_admin) {
    $stmt = $pdo->prepare("UPDATE Users SET firstname = :firstname, lastname = :lastname, is_admin = :is_admin WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':firstname', $firstname);
    $stmt->bindParam(':lastname', $lastname);
    $stmt->bindParam(':is_admin', $is_admin, PDO::PARAM_BOOL);
    return $stmt->execute();
}

// Delete a user
function deleteUser($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM Users WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

// Handle incoming API requests
$request_method = $_SERVER['REQUEST_METHOD'];
switch ($request_method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $user = getUserById($dbo->pdo, $id); 
            echo json_encode($user);
        } else {
            $users = getUsers($dbo->pdo); 
            echo json_encode($users);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        $is_admin = $data['is_admin'];
        $result = insertUser($dbo->pdo, $firstname, $lastname, $is_admin); 
        echo json_encode(['success' => $result]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        $is_admin = $data['is_admin'];
        $result = updateUser($dbo->pdo, $id, $firstname, $lastname, $is_admin); 
        echo json_encode(['success' => $result]);
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $result = deleteUser($dbo->pdo, $id); 
            echo json_encode(['success' => $result]);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid Request']);
        break;
}
?>
