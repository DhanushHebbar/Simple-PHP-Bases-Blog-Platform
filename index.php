<?php
$title = "Latest Articles";
include_once 'includes/functions.php';
include_once 'includes/header.php';

$articles = get_all_articles(); 
?>

<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-8">Recent Posts</h1>
    
    <?php if (empty($articles)): ?>
        <p class="text-gray-500 text-center py-10">No articles published yet. The admin needs to create one!</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($articles as $article): ?>
                <div class="card bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                    <?php 
                    $image_src = empty($article['image_path']) ? 'https://placehold.co/600x400/312e81/ffffff?text=PHP+Blog' : htmlspecialchars($article['image_path']);
                    ?>
                    <img src="<?= $image_src ?>" 
                         onerror="this.onerror=null; this.src='https://placehold.co/600x400/312e81/ffffff?text=PHP+Blog';"
                         alt="Cover image for <?= htmlspecialchars($article['title']) ?>" 
                         class="w-full h-48 object-cover">
                    
                    <div class="p-6">
                        <span class="inline-block px-3 py-1 text-xs font-semibold uppercase rounded-full bg-indigo-100 text-indigo-800 mb-2">
                            <?= htmlspecialchars($article['category'] ?? 'General') ?>
                        </span>
                        <h2 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">
                            <?= htmlspecialchars($article['title']) ?>
                        </h2>
                        <p class="text-gray-600 mb-4 line-clamp-3">
                            <?= substr(strip_tags($article['content']), 0, 150) ?>...
                        </p>
                        <a href="article.php?id=<?= $article['id'] ?>" class="text-indigo-600 hover:text-indigo-800 font-semibold flex items-center">
                            Read More
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
include_once 'includes/footer.php';
?>