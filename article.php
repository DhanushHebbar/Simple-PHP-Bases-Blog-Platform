<?php
require_once 'includes/functions.php';

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = get_article_by_id($article_id); 

if (!$article) {
    // If the article doesn't exist, redirect gracefully
    header("Location: index.php");
    exit;
}

$title = htmlspecialchars($article['title']);

// Fetch author username for display
$conn = db_connect();
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $article['author_id']);
$stmt->execute();
$author_result = $stmt->get_result()->fetch_assoc();
$author_name = $author_result ? htmlspecialchars($author_result['username']) : 'Unknown Author';
$stmt->close();
$conn->close();

// --- Comment Submission Logic ---
$comment_error = '';
$comment_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $author_name_input = sanitize_input($_POST['author_name']);
    $comment_text = sanitize_input($_POST['comment_text']);

    if (empty($author_name_input) || empty($comment_text)) {
        $comment_error = "Name and comment fields are required.";
    } else {
        // Since add_comment now sets is_approved = 1, this makes the comment instantly visible.
        if (add_comment($article_id, $author_name_input, $comment_text)) {
            $comment_success = "Comment submitted successfully!";
            // Reset POST variables to clear the form fields
            $_POST = array(); 
            // Refresh the approved_comments list after a successful submission
            // to show the new comment immediately without requiring a full redirect.
            
            // Note: Since the page reloads after POST (standard practice), the new comment 
            // will appear on the subsequent page load.
            
        } else {
            $comment_error = "Error saving comment data.";
        }
    }
}

// Fetch only approved comments for this article (which now includes all submitted comments)
$approved_comments = get_comments($article_id, false); 
?>

<?php require_once 'includes/header.php'; ?>

<div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-2xl">
    <article>
        <div class="text-center mb-8">
            <span class="inline-block px-4 py-2 text-sm font-medium uppercase rounded-full bg-indigo-100 text-indigo-800 mb-4">
                <?= htmlspecialchars($article['category'] ?? 'General') ?>
            </span>
            <h1 class="text-5xl font-extrabold text-gray-900 mb-4"><?= $title ?></h1>
            <p class="text-lg text-gray-500">By <span class="font-semibold text-indigo-600"><?= $author_name ?></span> on <?= date('F j, Y', strtotime($article['created_at'])) ?></p>
        </div>

        <?php 
        // Display Cover Photo if URL exists. Use a placeholder otherwise.
        $cover_url = $article['image_path'] ?? '';
        $placeholder_url = 'https://placehold.co/800x450/4f46e5/ffffff?text=Project+Log';
        ?>

        <div class="mb-8">
            <img src="<?= htmlspecialchars($cover_url) ?>" 
                 onerror="this.onerror=null; this.src='<?= $placeholder_url ?>';"
                 alt="Cover image" 
                 class="w-full h-auto object-cover rounded-lg shadow-md border border-gray-100">
        </div>
        
        <div class="prose lg:prose-lg max-w-none text-gray-700 leading-relaxed space-y-6">
            <p><?= nl2br(htmlspecialchars($article['content'])) ?></p>
        </div>
    </article>

    <!-- Comments Section: The display logic is verified in this file -->
    <div class="mt-12 pt-8 border-t border-gray-200">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">Comments (<?= count($approved_comments) ?>)</h2>

        <!-- Display Approved Comments -->
        <div class="space-y-6 mb-8">
            <?php if (empty($approved_comments)): ?>
                <p class="text-gray-500">Be the first to comment!</p>
            <?php else: ?>
                <?php foreach ($approved_comments as $comment): ?>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <div class="flex justify-between items-center mb-1">
                            <p class="font-semibold text-gray-900"><?= htmlspecialchars($comment['author_name']) ?></p>
                            <span class="text-xs text-gray-400"><?= date('M d, Y', strtotime($comment['created_at'])) ?></span>
                        </div>
                        <p class="text-gray-700"><?= htmlspecialchars($comment['comment_text']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Comment Submission Form -->
        <h3 class="text-xl font-bold text-gray-800 mb-4">Leave a Comment</h3>
        
        <?php if ($comment_error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4"><?= $comment_error ?></div>
        <?php endif; ?>
        <!-- Display success message when comment is submitted -->
        <?php if ($comment_success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4"><?= $comment_success ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label for="author_name" class="block text-sm font-medium text-gray-700">Your Name</label>
                <input type="text" id="author_name" name="author_name" required 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="comment_text" class="block text-sm font-medium text-gray-700">Comment</label>
                <textarea id="comment_text" name="comment_text" rows="4" required 
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <button type="submit" name="submit_comment"
                    class="w-full sm:w-auto px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition ease-in-out duration-150 transform hover:scale-105">
                Submit Comment
            </button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>



