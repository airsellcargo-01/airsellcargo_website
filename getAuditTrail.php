<?php
header('Content-Type: application/json');

// Database connection
$pdo = new PDO("mysql:host=localhost;dbname=airsellcargo", "db_user", "db_pass");

// Inputs from request
$objectId = $_GET['object_id'];
$userRole = $_GET['role']; // 'admin' or 'partner'
$accessGrantedDate = $_GET['access_date']; // e.g. '2026-04-25'

// Base query
$query = "SELECT * FROM audit_trail WHERE object_id = :object_id";

// Apply permission filter
if ($userRole !== 'admin') {
    $query .= " AND timestamp >= :access_date";
}

// Prepare and execute
$stmt = $pdo->prepare($query);
$stmt->bindParam(':object_id', $objectId);
if ($userRole !== 'admin') {
    $stmt->bindParam(':access_date', $accessGrantedDate);
}
$stmt->execute();

// Return JSON
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
