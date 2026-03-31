<?php
include "../base/chech.php"; 
include "../base/main.php";
session_start();
include "db.php";


if (!isset($_GET['user'])) {
    echo "No user specified.";
    exit();
}

$currentUser = $_SESSION['username'];
$user = $_GET['user'];

if ($currentUser == $user) {
    header("Location: index.php");
    exit();
}
$stmt = $conn->prepare("SELECT username, name, description, icon FROM profiles WHERE username = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

if (!$profile) {
    echo "User not found.";
    exit();
}

$stmt = $conn->prepare("SELECT username FROM profiles WHERE username = ?");
$stmt->bind_param("s", $currentUser);
$stmt->execute();
$currentProfileResult = $stmt->get_result();
$currentProfile = $currentProfileResult->fetch_assoc();

if (!$currentProfile) {
    echo "Current user not found.";
    exit();
}

$stmt = $conn->prepare("SELECT 1 FROM followers WHERE follower_username = ? AND username = ?");
$stmt->bind_param("ss", $currentUser, $user);
$stmt->execute();
$isFollowing = $stmt->get_result()->num_rows > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['follow'])) {
    if ($isFollowing) {
        $stmt = $conn->prepare("DELETE FROM followers WHERE follower_username = ? AND username = ?");
        $stmt->bind_param("ss", $currentUser, $user);
    } else {
        $stmt = $conn->prepare("INSERT INTO followers (follower_username, username) VALUES (?, ?)");
        $stmt->bind_param("ss", $currentUser, $user);
    }
    $stmt->execute();
    header("Location: profile_view.php?user=" . urlencode($user));
    exit();
}

$stmt = $conn->prepare("SELECT u.username, u.name 
                        FROM followers f 
                        JOIN profiles u ON f.follower_username = u.username 
                        WHERE f.username = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$followersResult = $stmt->get_result();

$stmt = $conn->prepare("SELECT u.username, u.name 
                        FROM followers f 
                        JOIN profiles u ON f.username = u.username 
                        WHERE f.follower_username = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$followingResult = $stmt->get_result();
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
        <title><?php echo htmlspecialchars($profile['name']); ?>'s Profile</title>
    </head>
    <body>
        <canvas class="back" id="canvas"></canvas>
        <?php include '../base/sidebar.php'; ?>
        <div class='con'>
            <button class="circle-btn" onclick="openNav()">☰</button>  
            <form method="GET" action="search.php">
                <input type="text" name="query" placeholder="Search users by name or username..." required>
                <button type="submit">Search</button>
            </form>
            <h1><?php echo htmlspecialchars($profile['name']); ?>'s Profile</h1>
            <img src ="<?php echo htmlspecialchars($profile['icon'])?>"/>
            <div class="view">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($profile['name']); ?></p>
                <p><strong>Username:</strong> @<?php echo htmlspecialchars($user); ?></p>
                <br>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($profile['description']); ?></p>
                <p><strong>Followers</strong> (<?php echo $followersResult->num_rows; ?>):</p>
                <ul>
                <?php while ($follower = $followersResult->fetch_assoc()): ?>
                    <li><a href="profile_view.php?user=<?php echo urlencode($follower['username']); ?>"><?php echo htmlspecialchars($follower['name']); ?></a></li>
                <?php endwhile; ?>
                </ul>
                
                <p><strong>Following</strong> (<?php echo $followingResult->num_rows; ?>):</p>
                <ul>
                <?php while ($following = $followingResult->fetch_assoc()): ?>
                    <li><a href="profile_view.php?user=<?php echo urlencode($following['username']); ?>"><?php echo htmlspecialchars($following['name']); ?></a></li>
                <?php endwhile; ?>
                </ul>

            </div>
            <form method="POST">
                <input type="hidden" name="follow" value="1">
                <?php if ($isFollowing): ?>
                    <button type="submit">Unfollow</button>
                <?php else: ?>
                    <button type="submit">Follow</button>
                <?php endif; ?>
            </form>
            <br>
        </div>
    </body>
    <script src="https://theme.house-778.theorangecow.org/background.js"></script>
    <script src="https://house-778.theorangecow.org/base/main.js"></script>
    <script src="https://house-778.theorangecow.org/base/sidebar.js"></script>
</html>
