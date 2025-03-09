<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Correct the path to config.php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

// Debug: Check if config.php is loaded
if (!defined('URL_ROOT')) {
    die("Config file not loaded.");
}

// Ensure the user is logged in
if (!isLoggedIn()) {
    redirect(URL_ROOT . '/pages/login.php');
}

// Ensure the recipe ID is provided
if (!isset($_GET['id'])) {
    redirect(URL_ROOT);
}

$recipe_id = sanitizeInput($_GET['id']);

// Fetch the recipe from the database
$stmt = mysqli_prepare($conn, "SELECT * FROM recipes WHERE recipe_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $recipe_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$recipe = mysqli_fetch_assoc($result);

if (!$recipe) {
    die("Recipe not found or you do not have permission to edit it.");
}

// Fetch categories for the dropdown
$categories = mysqli_query($conn, "SELECT * FROM recipe_categories");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $ingredients = sanitizeInput($_POST['ingredients']);
    $steps = sanitizeInput($_POST['steps']);
    $category_id = sanitizeInput($_POST['category_id']);
    $prep_time = sanitizeInput($_POST['prep_time']);
    $cook_time = sanitizeInput($_POST['cook_time']);
    $servings = sanitizeInput($_POST['servings']);

    // Handle image upload
    $image = $recipe['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = __DIR__ . '/../uploads/';
        $target_file = $target_dir . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = basename($_FILES['image']['name']);
        }
    }

    // Handle video upload
    $video = $recipe['video'];
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $allowed_types = ['video/mp4', 'video/avi', 'video/quicktime'];
        if (in_array($_FILES['video']['type'], $allowed_types)) {
            $target_video = __DIR__ . '/../uploads/' . basename($_FILES['video']['name']);
            if (move_uploaded_file($_FILES['video']['tmp_name'], $target_video)) {
                $video = basename($_FILES['video']['name']);
            }
        }
    }

    // Update the recipe in the database
    $stmt = mysqli_prepare($conn, "UPDATE recipes SET title = ?, description = ?, ingredients = ?, steps = ?, category_id = ?, image = ?, video = ?, prep_time = ?, cook_time = ?, servings = ? WHERE recipe_id = ?");
    mysqli_stmt_bind_param($stmt, "ssssisssiii", $title, $description, $ingredients, $steps, $category_id, $image, $video, $prep_time, $cook_time, $servings, $recipe_id);
    if (mysqli_stmt_execute($stmt)) {
        redirect(URL_ROOT . '/pages/recipe.php?id=' . $recipe_id);
    } else {
        $error = "Error updating recipe. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <section class="p-6 mt-16">
        <h1 class="text-2xl font-bold">Edit Recipe</h1>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-4" role="alert">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>
        <form action="edit_recipe.php?id=<?php echo $recipe_id; ?>" method="post" enctype="multipart/form-data" class="mt-4">
            <label for="title" class="block">Title:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($recipe['title']); ?>" required class="p-2 border border-gray-300 rounded w-full">

            <label for="description" class="block mt-4">Description:</label>
            <textarea name="description" required class="p-2 border border-gray-300 rounded w-full"><?php echo htmlspecialchars($recipe['description']); ?></textarea>

            <label for="ingredients" class="block mt-4">Ingredients:</label>
            <textarea name="ingredients" required class="p-2 border border-gray-300 rounded w-full"><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>

            <label for="steps" class="block mt-4">Steps:</label>
            <textarea name="steps" required class="p-2 border border-gray-300 rounded w-full"><?php echo htmlspecialchars($recipe['steps']); ?></textarea>

            <label for="category_id" class="block mt-4">Category:</label>
            <select name="category_id" required class="p-2 border border-gray-300 rounded w-full">
                <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                    <option value="<?php echo $category['category_id']; ?>" <?php echo $category['category_id'] == $recipe['category_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="prep_time" class="block mt-4">Prep Time (minutes):</label>
            <input type="number" name="prep_time" value="<?php echo $recipe['prep_time']; ?>" required class="p-2 border border-gray-300 rounded w-full">

            <label for="cook_time" class="block mt-4">Cook Time (minutes):</label>
            <input type="number" name="cook_time" value="<?php echo $recipe['cook_time']; ?>" required class="p-2 border border-gray-300 rounded w-full">

            <label for="servings" class="block mt-4">Servings:</label>
            <input type="number" name="servings" value="<?php echo $recipe['servings']; ?>" required class="p-2 border border-gray-300 rounded w-full">

            <label for="image" class="block mt-4">Recipe Image:</label>
            <input type="file" name="image" class="p-2 border border-gray-300 rounded w-full">
            <?php if ($recipe['image']): ?>
                <img src="../uploads/<?php echo htmlspecialchars($recipe['image']); ?>" alt="Recipe Image" class="w-32 h-32 object-cover rounded mt-4">
            <?php endif; ?>

            <label for="video" class="block mt-4">Recipe Video:</label>
            <input type="file" name="video" accept="video/mp4, video/avi, video/quicktime" class="p-2 border border-gray-300 rounded w-full">
            <?php if ($recipe['video']): ?>
                <video controls class="w-64 mt-4">
                    <source src="../uploads/<?php echo htmlspecialchars($recipe['video']); ?>" type="video/mp4">
                </video>
            <?php endif; ?>

            <button type="submit" class="bg-blue-500 text-white p-2 rounded mt-4">Update Recipe</button>
        </form>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
