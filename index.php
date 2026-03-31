<?php

include "../base/chech.php"; 
include "../base/main.php";
include "db.php";

session_start(); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
$resolt = "";

if (stripos($_SESSION['username'], "Guest") !== false) {
    $username = "guest";
} else {
    $username = $_SESSION['username'];
}

$currentProfile = [
    'name' => '',
    'description' => '',
    'icon' => '',
    'followers' => [],
    'following' => []
];


$stmt = $conn->prepare("SELECT name, description, icon FROM profiles WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $currentProfile['name'] = $row['name'];
    $currentProfile['description'] = $row['description'];
    $currentProfile['icon'] = $row['icon'];
}
$stmt->close();

$stmt = $conn->prepare("SELECT follower_username FROM followers WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $currentProfile['followers'][] = $row['follower_username'];
}
$stmt->close();

$stmt = $conn->prepare("SELECT username FROM followers WHERE follower_username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $currentProfile['following'][] = $row['username'];
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (stripos($_SESSION['username'], "Guest") === false) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $icon = $_POST['icon'];

        $stmt = $conn->prepare("SELECT username FROM profiles WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $update = $conn->prepare("UPDATE profiles SET name = ?, description = ?, icon = ? WHERE username = ?");
            $update->bind_param("ssss", $name, $description, $icon, $username);
            $update->execute();
            $update->close();
        } else {
            $insert = $conn->prepare("INSERT INTO profiles (username, name, description, icon) VALUES (?, ?, ?, ?)");
            $insert->bind_param("ssss", $username, $name, $description, $icon);
            $insert->execute();
            $insert->close();
        }
        $stmt->close();

        $resolt = "Profile updated successfully!";
        $currentProfile['name'] = $name;
        $currentProfile['description'] = $description;
        $currentProfile['icon'] = $icon;
    } else {
        $resolt = "You have to have an account to edit.";
    }
}

?>



<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Your Profile</title>
        <link rel="stylesheet" href="https://house-778.theorangecow.org/base/style.css">
        <link rel="stylesheet" href="style.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300..700&display=swap" rel="stylesheet">
        <link rel="icon" href="https://house-778.theorangecow.org/base/icon.ico" type="image/x-icon">
    </head>
    <body>
        <canvas class="back" id="canvas"></canvas>
        <?php include '../base/sidebar.php'; ?>
        
        <div class="con">
            <button class="circle-btn" onclick="openNav()">☰</button>  
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <p><?php echo htmlspecialchars($resolt) ?></p>
            <h3>Search for Users:</h3>
            <form method="GET" action="search.php">
                <input type="text" name="query" placeholder="Search users by name or username..." required>
                <button type="submit">Search</button>
            </form>
            <br>
            <img src="<?php echo htmlspecialchars($currentProfile['icon']) ?>"/>
            <div class="view">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($currentProfile['name']); ?></p>
                <p><strong>Username:</strong> @<?php echo htmlspecialchars($username); ?></p>
                <br>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($currentProfile['description']); ?></p>
                <p><strong>Followers</strong> (<?php echo count($currentProfile['followers']); ?>):</p>
                <ul>
                    <?php foreach ($currentProfile['followers'] as $follower): ?>
                        <li><a href="profile_view.php?user=<?php echo urlencode($follower); ?>"><?php echo htmlspecialchars($follower); ?></a></li>
                    <?php endforeach; ?>
                </ul>
        
                <p><strong>Following</strong> (<?php echo count($currentProfile['following']); ?>):</p>
                <ul>
                    <?php foreach ($currentProfile['following'] as $following): ?>
                        <li><a href="profile_view.php?user=<?php echo urlencode($following); ?>"><?php echo htmlspecialchars($profiles[$following]['name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <button onclick="edit()" class ="<?php echo stripos($username, 'guest') !== false ? 'hidden' : ''; ?>">Edit</button>
            <form method="POST" id="edit" class="hidden">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($currentProfile['name']); ?>" required>
    
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($currentProfile['description']); ?></textarea>
    
                <label for="icon">Choose an icon:</label>
                <select id="icon" name="icon">
                    <?php for ($i = 0; $i <= 34; $i++): ?>
                        <option value="images/<?php echo $i; ?>.jpg" <?php echo $currentProfile['icon'] === "images/$i.jpg" ? 'selected' : ''; ?>>
                            Icon <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select><br>
    
                <button type="submit">Update Profile</button>
            </form>
        </div>
    
        <script>
            function edit() {
                let should = <?php echo stripos($username, 'guest') == false ? 'true' : 'false'; ?>;
                if (should){
                    var edit = document.getElementById('edit');
                    edit.classList.toggle('hidden');
                }
            }
        </script>
    </body>
    <script src="https://theme.house-778.theorangecow.org/background.js"></script>
    <script src="https://house-778.theorangecow.org/base/main.js"></script>
    <script src="https://house-778.theorangecow.org/base/sidebar.js"></script>
</html>
