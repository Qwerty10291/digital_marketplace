<?php
require 'config.php';

$parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : NULL;
$name = isset($_GET['name']) ? (string)$_GET['name'] : NULL;

if ($parent_id === NULL) {
    $category_sql = "SELECT c.id, c.name, c.parent_id, COUNT(p.id) AS products_count 
                     FROM categories c 
                     LEFT JOIN products p ON c.id = p.category_id 
                     WHERE c.parent_id IS NULL
                     GROUP BY c.id";
} else {
    $category_sql = "SELECT c.id, c.name, c.parent_id, COUNT(p.id) AS products_count 
                     FROM categories c 
                     LEFT JOIN products p ON c.id = p.category_id 
                     WHERE c.parent_id = ?
                     GROUP BY c.id";
}

if ($name !== NULL) {
    $name = "%" . $name ."%";
    $category_sql = "SELECT id, name from categories where name like ? ";
}

$stmt = $conn->prepare($category_sql);

if ($parent_id !== NULL) {
    $stmt->bind_param("i", $parent_id);
}

if ($name != NULL) {
    $stmt->bind_param("s", $name);
}


$stmt->execute();
$result = $stmt->get_result();
$categories = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

$stmt->close();
echo json_encode($categories);

