<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Include configuration and functions
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

// Ensure the user is logged in
if (!isLoggedIn()) {
    redirect(URL_ROOT . '/pages/login.php');
}

// Fetch the current user's profile
$user_id = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("User not found.");
}

// Fetch the user's recipes
$recipes_query = "SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC";
$recipes_stmt = mysqli_prepare($conn, $recipes_query);
mysqli_stmt_bind_param($recipes_stmt, "i", $user_id);
mysqli_stmt_execute($recipes_stmt);
$recipes_result = mysqli_stmt_get_result($recipes_stmt);

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);

    // Handle profile picture upload
    $profile_pic = $user['profile_pic'];
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = __DIR__ . '/../uploads/';
        $target_file = $target_dir . basename($_FILES['profile_pic']['name']);
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
            $profile_pic = basename($_FILES['profile_pic']['name']);
        }
    }

    // Update the user's profile
    $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, profile_pic = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $profile_pic, $user_id);
    if (mysqli_stmt_execute($stmt)) {
        echo "Profile updated successfully!";
    } else {
        $error = "Error updating profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <section class="p-6 mt-16">
        <h1 class="text-2xl font-bold">Profile</h1>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-4" role="alert">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <!-- Profile Information -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-lg">
            <h2 class="text-xl font-bold">Profile Information</h2>
            <form action="profile.php" method="post" enctype="multipart/form-data" class="mt-4">
                <label for="name" class="block">Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="p-2 border border-gray-300 rounded w-full">

                <label for="email" class="block mt-4">Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="p-2 border border-gray-300 rounded w-full">

                <label for="profile_pic" class="block mt-4">Profile Picture:</label>
                <input type="file" name="profile_pic" class="p-2 border border-gray-300 rounded w-full">

                <?php if ($user['profile_pic']): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" class="w-32 h-32 object-cover rounded mt-4">
                <?php endif; ?>

                <button type="submit" class="bg-blue-500 text-white p-2 rounded mt-4">Update Profile</button>
            </form>
        </div>

        <!-- User's Recipes -->
        <div class="mt-8">
            <h2 class="text-xl font-bold">My Recipes</h2>
            <?php if (mysqli_num_rows($recipes_result) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <?php while ($recipe = mysqli_fetch_assoc($recipes_result)): ?>
                        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow-lg">
                            <img src="../uploads/<?php echo htmlspecialchars($recipe['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($recipe['title']); ?>" 
                                 class="w-full h-48 object-cover rounded">
                            <h3 class="text-lg font-bold mt-2"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                            <p class="text-sm"><?php echo htmlspecialchars($recipe['description']); ?></p>
                            <a href="recipe.php?id=<?php echo $recipe['recipe_id']; ?>" class="text-blue-500 mt-4 inline-block">View Recipe</a>
                            <a href="edit_recipe.php?id=<?php echo $recipe['recipe_id']; ?>" class="text-yellow-500 mt-4 inline-block">Edit Recipe</a>
                            <a href="delete_recipe.php?id=<?php echo $recipe['recipe_id']; ?>" class="text-red-500 mt-4 inline-block">Delete Recipe</a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 mt-4">You haven't uploaded any recipes yet.</p>
            <?php endif; ?>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>