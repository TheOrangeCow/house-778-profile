<?php
include "../base/chech.php"; 
include "../base/main.php";
include "db.php";
session_start();

$searchQuery = $_GET['query'] ?? '';
$searchResults = [];

if ($searchQuery) {
    if (strpos($searchQuery, '@') === 0) {
        $searchQuery = substr($searchQuery, 1);
        $stmt = $conn->prepare("SELECT username, name FROM profiles WHERE username LIKE ?");
        $searchTerm = "%" . $searchQuery . "%";
        $stmt->bind_param("s", $searchTerm);
    } else {
        $stmt = $conn->prepare("SELECT username, name FROM profiles WHERE name LIKE ?");
        $searchTerm = "%" . $searchQuery . "%";
        $stmt->bind_param("s", $searchTerm);
    }
    $stmt->execute();
    $searchResults = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://house-778.theorangecow.org/base/style.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300..700&display=swap" rel="stylesheet">
        <link rel="icon" href="https://house-778.theorangecow.org/base/icon.ico" type="image/x-icon">
        <title>Search Results</title>
    </head>
    <body>
        <canvas class="back" id="canvas"></canvas>
        <?php include '../base/sidebar.php'; ?>
    
        <div class ="con">
            <button class="circle-btn" onclick="openNav()">☰</button>  
            <h1>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h1>

            <?php if ($searchResults && $searchResults->num_rows > 0): ?>
                <ul>
                    <?php while ($profile = $searchResults->fetch_assoc()): ?>
                        <li>
                            <strong>
                                <a href="profile_view.php?user=<?php echo urlencode($profile['username']); ?>">
                                    <?php echo htmlspecialchars($profile['name']); ?>
                                </a>
                            </strong>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No users found matching your search query. <a href = "index.php">Home</a> </p>
            <?php endif; ?>

        </div>
    </body>
    <script src="https://theme.house-778.theorangecow.org/background.js"></script>
    <script src="https://house-778.theorangecow.org/base/main.js"></script>
    <script src="https://house-778.theorangecow.org/base/sidebar.js"></script>
</html>
