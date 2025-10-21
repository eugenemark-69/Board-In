<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

$db = getDB();

// Check if an ID is passed
if (!isset($_GET['id'])) {
    die("No listing ID provided.");
}

$id = intval($_GET['id']);

// Fetch the listing data
$stmt = $db->prepare("SELECT * FROM listings WHERE id = ?");
$stmt->execute([$id]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    die("Listing not found!");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $amenities = $_POST['amenities'];
    $description = $_POST['description'];
    $image = $listing['image']; // default image

    // If a new image was uploaded
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../assets/images/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        $image = $target_file;
    }

    // Update database
    $stmt = $db->prepare("UPDATE listings SET title=?, price=?, amenities=?, description=?, image=? WHERE id=?");
    $stmt->execute([$title, $price, $amenities, $description, $image, $id]);

    // Redirect back
    header("Location: my-listings.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Listing</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Listing</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <label>Title:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($listing['title']); ?>" required><br>

            <label>Price:</label>
            <input type="number" name="price" value="<?php echo htmlspecialchars($listing['price']); ?>" required><br>

            <label>Amenities:</label>
            <textarea name="amenities" required><?php echo htmlspecialchars($listing['amenities']); ?></textarea><br>

            <label>Description:</label>
            <textarea name="description"><?php echo htmlspecialchars($listing['description']); ?></textarea><br>

            <label>Current Image:</label><br>
            <img src="<?php echo htmlspecialchars($listing['image']); ?>" width="200"><br><br>

            <label>Upload New Image:</label>
            <input type="file" name="image"><br><br>

            <button type="submit">💾 Save Changes</button>
        </form>
    </div>
</body>
</html>
