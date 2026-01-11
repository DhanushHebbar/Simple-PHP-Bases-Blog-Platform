<?php
require_once 'includes/functions.php';
require_auth(false); 

$error = '';
$success = '';
$is_admin = is_logged_in(true); 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = sanitize_input($_POST['action']);
    
    if ($action === 'create_article') {
        $title = sanitize_input($_POST['title']);
        $content = $_POST['content'];
        $category_select = sanitize_input($_POST['category_select']);
        $category_custom = sanitize_input($_POST['category_custom']);
        $image_url = sanitize_input($_POST['cover_image_url']);

        $author_id = $_SESSION['user_id'];
        
        if (!empty($category_custom)) {
            $final_category = $category_custom;
        } elseif (!empty($category_select)) {
            $final_category = $category_select;
        } else {
            $final_category = 'Uncategorized';
        }
        
        // Image URL is saved directly
        $final_path = !empty($image_url) ? $image_url : null;

        if (add_article($author_id, $title, $content, $final_category, $final_path)) {
            $success = "Article '$title' published successfully under '$final_category'!";
        } else {
            $error = "Failed to publish article. Check database connection.";
        }
    } 
    
    // Management Actions (Only allowed if $is_admin is true)
    elseif ($is_admin && $action === 'delete_article' && isset($_POST['article_id'])) {
        $article_id = (int)$_POST['article_id'];
        if (delete_article($article_id)) {
            $success = "Article deleted successfully.";
        } else {
            $error = "Failed to delete article.";
        }
    }
    elseif ($is_admin && $action === 'approve_comment' && isset($_POST['comment_id'])) {
        $comment_id = (int)$_POST['comment_id'];
        if (moderate_comment($comment_id, true)) {
            $success = "Comment approved.";
        } else {
            $error = "Failed to approve comment.";
        }
    }
}

$pending_comments = $is_admin ? get_comments(null, true) : [];
$all_articles = get_all_articles();

// Categories setup
$default_categories = ["Web Dev", "PHP", "MySQL", "Security", "Frontend", "Backend", "Algorithms", "Data Structures", "Cloud Computing", "Project Management"];
$existing_categories = get_unique_categories();
$categories_to_display = array_unique(array_merge($default_categories, $existing_categories));

?>
<?php require_once 'includes/header.php'; ?>

<style>
    .article-textarea {
        min-height: 250px;
        font-family: ui-sans-serif, system-ui;
        font-size: 1rem;
    }
    /* FIX: Ensure the two category input fields stack correctly on mobile */
    .category-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
    }
    @media (min-width: 768px) {
        .category-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<div class="max-w-7xl mx-auto p-4 md:p-8">
    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-8">
        <?= $is_admin ? 'Admin Panel & Posting' : 'Publish Your Article' ?>
    </h1>

    <?php if ($success): ?>
        <p class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded mb-4" role="alert"><?php echo $success; ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($is_admin): ?>
        <div class="bg-white shadow-xl rounded-xl p-6 mb-10 border-t-4 border-yellow-600">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Pending Comments (<?= count($pending_comments) ?>)</h2>
            <?php if (empty($pending_comments)): ?>
                <p class="text-gray-500">All comments are up-to-date and approved!</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pending_comments as $comment): ?>
                        <div class="border border-gray-200 p-4 rounded-lg flex flex-col sm:flex-row justify-between items-start sm:items-center bg-gray-50">
                            <div class="mb-2 sm:mb-0">
                                <p class="font-semibold text-gray-800 text-sm sm:text-base"><?= htmlspecialchars($comment['author_name']) ?> (on Article #<?= $comment['article_id'] ?>)</p>
                                <p class="text-gray-600 text-xs sm:text-sm italic mt-1"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></p>
                            </div>
                            <form method="POST" action="admin.php" class="flex space-x-2 mt-2 sm:mt-0">
                                <input type="hidden" name="action" value="approve_comment">
                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded text-sm transition duration-150">
                                    Approve
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>


    <div class="bg-white shadow-xl rounded-xl p-6 mb-10 border-t-4 border-indigo-600">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Publish New Article</h2>
        <form method="POST" action="admin.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create_article">
            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="title" name="title" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div class="category-grid">
                    <div>
                        <label for="category_select" class="block text-sm font-bold text-gray-700"> Select Existing Category</label>
                        <select id="category_select" name="category_select" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Choose Category --</option>
                            <?php 
                            foreach ($categories_to_display as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="category_custom" class="block text-sm font-bold text-gray-700"> Add Custom Category </label>
                        <input type="text" id="category_custom" name="category_custom" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="e.g., Data Structures">
                    </div>
                </div>
                <p class="text-xs text-indigo-500">Only fill out ONE of the category fields above. Custom input overrides selection.</p>
                
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700">Content (Supports HTML/Markdown)</label>
                    <textarea id="content" name="content" required class="article-textarea mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                
                <div>
                    <label for="cover_image_url" class="block text-sm font-medium text-gray-700">Cover Image URL (Optional)</label>
                    <input type="url" id="cover_image_url" name="cover_image_url" placeholder="e.g., https://unsplash.com/photos/xyz"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-indigo-500 mt-1">Paste a link to an image. This avoids file system permissions entirely.</p>
                </div>

                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                    Publish Article
                </button>
            </div>
        </form>
    </div>

    <?php if ($is_admin): ?>
        <div class="bg-white shadow-xl rounded-xl p-6 border-t-4 border-red-600">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Manage Existing Articles</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($all_articles)): ?>
                            <tr><td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">No articles found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($all_articles as $article): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <a href="article.php?id=<?= $article['id'] ?>" class="hover:underline text-indigo-600">
                                            <?= htmlspecialchars($article['title']) ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($article['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <form method="POST" action="admin.php" onsubmit="return confirm('Are you sure you want to delete this article and all its comments?')" class="inline">
                                            <input type="hidden" name="action" value="delete_article">
                                            <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900 transition duration-150">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
