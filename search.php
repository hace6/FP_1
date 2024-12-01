<?php
include 'db.php';
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

if (isset($_GET['assorty_id'])) {
    $assorty_id = $_GET['assorty_id'];
    $search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%";

    // Если search пуст, запрос вернет все товары из ассортимента
    $query = $db->prepare("SELECT * FROM tovar WHERE name LIKE ? AND assorty_id = ?");
    $query->execute([$search, $assorty_id]);
    echo json_encode($query->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

$query = $db->query("SELECT * FROM assorty");
$assorty = $query->fetchAll(PDO::FETCH_ASSOC);
?>
