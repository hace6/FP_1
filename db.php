<?php
$db = new PDO('sqlite:database.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function createTables($db) {
    $createAssortyTable = "
    CREATE TABLE IF NOT EXISTS assorty (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL
    )";

    $createTovarTable = "
    CREATE TABLE IF NOT EXISTS tovar (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        price REAL NOT NULL,
        assorty_id INTEGER,
        path TEXT,
        FOREIGN KEY (assorty_id) REFERENCES assorty (id)
    )";

    $db->exec($createAssortyTable);
    $db->exec($createTovarTable);
}

// Вызываем функцию создания таблиц
createTables($db);
?>
