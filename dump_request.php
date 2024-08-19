<?php
$ipAddress = $_SERVER['REMOTE_ADDR'];

$uploadDir = 'uploads/' . $ipAddress;
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $imageTmpName = $_FILES['image']['tmp_name'];
    $imageExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $imageName = date('Ymd_His') . '.' . $imageExtension; 
    $imagePath = $uploadDir . '/' . $imageName;

    if (move_uploaded_file($imageTmpName, $imagePath)) {
        echo "Image uploaded successfully!\n";
    } else {
        echo "Failed to upload the image.\n";
    }
} else {
    echo "No image file uploaded or there was an error.\n";
}
$latitude = isset($_POST['latitude']) ? $_POST['latitude'] : 'Not provided';
$longitude = isset($_POST['longitude']) ? $_POST['longitude'] : 'Not provided';
$timestamp = date('Y-m-d H:i:s');

$textFilePath = $uploadDir . '/location.txt';
$textFileContent = "Timestamp: " . $timestamp . "\nLatitude: " . $latitude . "\nLongitude: " . $longitude . "\n";

if (file_put_contents($textFilePath, $textFileContent, FILE_APPEND) !== false) {
    echo "Location information saved successfully!\n";
} else {
    echo "Failed to save location information.\n";
}
?>
