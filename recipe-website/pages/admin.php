<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Include configuration and functions
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

// Ensure the user is an admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect(URL_ROOT);
}

// Fetch all users, recipes, and categories
$users = mysqli_query($conn, "SELECT * FROM users");
$recipes = mysqli_query($conn, "SELECT * FROM recipes");
$categories = mysqli_query($conn, "SELECT * FROM recipe_categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <section class="p-6 mt-16">
        <h1 class="text-2xl font-bold">Admin Panel</h1>

        <h2 class="text-xl font-bold mt-6">Users</h2>
        <table class="w-full mt-4">
            <thead>
                <tr>
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Name</th>
                    <th class="border p-2">Email</th>
                    <th class="border p-2">Role</th>
                    <th class="border p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td class="border p-2"><?php echo $user['user_id']; ?></td>
                        <td class="border p-2"><?php echo htmlspecialchars($user['name']); ?></td>
                        <td class="border p-2"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="border p-2"><?php echo htmlspecialchars($user['role']); ?></td>
                        <td class="border p-2">
                            <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="text-blue-500">Edit</a>
                            <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" class="text-red-500">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2 class="text-xl font-bold mt-6">Recipes</h2>
        <table class="w-full mt-4">
            <thead>
                <tr>
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Title</th>
                    <th class="border p-2">Author</th>
                    <th class="border p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($recipe = mysqli_fetch_assoc($recipes)): ?>
                    <tr>
                        <td class="border p-2"><?php echo $recipe['recipe_id']; ?></td>
                        <td class="border p-2"><?php echo htmlspecialchars($recipe['title']); ?></td>
                        <td class="border p-2"><?php echo htmlspecialchars($recipe['user_id']); ?></td>
                        <td class="border p-2">
                            <a href="edit_recipe.php?id=<?php echo $recipe['recipe_id']; ?>" class="text-blue-500">Edit</a>
                            <a href="delete_recipe.php?id=<?php echo $recipe['recipe_id']; ?>" class="text-red-500">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2 class="text-xl font-bold mt-6">Categories</h2>
        <table class="w-full mt-4">
            <thead>
                <tr>
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Name</th>
                    <th class="border p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                    <tr>
                        <td class="border p-2"><?php echo $category['category_id']; ?></td>
                        <td class="border p-2"><?php echo htmlspecialchars($category['category_name']); ?></td>
                        <td class="border p-2">
                            <a href="edit_category.php?id=<?php echo $category['category_id']; ?>" class="text-blue-500">Edit</a>
                            <a href="delete_category.php?id=<?php echo $category['category_id']; ?>" class="text-red-500">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>