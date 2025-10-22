<?php
// PHP Error reporting for development (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- 1. CONFIGURATION AND INITIALIZATION ---
session_start();
$upload_dir = 'uploads/';

// !! CREDENTIALS CORRECTED BASED ON USER'S INFINITYFREE IMAGE !!
define('DB_HOST', 'sql211.infinityfree.com'); 
define('DB_USER', 'if0_40096659'); 
define('DB_PASS', 'I1h4DmZ3FRj7'); 
define('DB_NAME', 'if0_40096659_blogproj'); 
// -------------------------------------------------------------------

/**
 * Establishes and returns a database connection.
 * @return mysqli|false The connection object or false on failure.
 */
function db_connect() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: Please verify your DB credentials (Host, User, Pass, Name) are correct."); 
    }
    return $conn;
}

// --- 2. AUTHENTICATION AND SESSION UTILITIES (No change) ---

function is_logged_in($admin_required = false) {
    if (!isset($_SESSION['user_id'])) return false;
    if ($admin_required && $_SESSION['role'] !== 'Admin') return false;
    return true;
}

function require_auth($admin_required = false, $location = 'login.php') {
    if (!is_logged_in($admin_required)) {
        header("Location: $location");
        exit;
    }
}

function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// --- Category Retrieval Function ---
function get_unique_categories() {
    $conn = db_connect();
    $sql = "SELECT DISTINCT category FROM articles WHERE category IS NOT NULL AND category != '' ORDER BY category ASC";
    $result = $conn->query($sql);
    
    $categories = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = htmlspecialchars($row['category']);
        }
    }
    $conn->close();
    return $categories;
}

// --- 3. DATABASE CRUD OPERATIONS ---

function get_all_articles() {
    $conn = db_connect();
    $sql = "SELECT id, author_id, title, content, category, image_path, created_at FROM articles ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $articles = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();
    return $articles;
}

function get_article_by_id($id) {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT id, author_id, title, content, category, image_path, created_at FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $article = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    $conn->close();
    return $article;
}

function add_article($author_id, $title, $content, $category, $image_url) {
    $conn = db_connect();
    $sql = "INSERT INTO articles (author_id, title, content, category, image_path) VALUES (?, ?, ?, ?, ?)"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $author_id, $title, $content, $category, $image_url);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function delete_article($id) {
    $conn = db_connect();
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    $stmt_comments = $conn->prepare("DELETE FROM comments WHERE article_id = ?");
    $stmt_comments->bind_param("i", $id);
    $stmt_comments->execute();
    $stmt_comments->close();
    $conn->close();
    return $success;
}

// --- User/Authentication Functions (No change) ---

function get_user_by_username($username) {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    $conn->close();
    return $user;
}

function register_user($username, $password_hash, $role = 'Author') {
    $conn = db_connect();
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password_hash, $role);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

// --- Comment Functions (CRITICAL FIX APPLIED HERE) ---

function add_comment($article_id, $author_name, $comment_text) {
    $conn = db_connect();
    // FIX: Changed is_approved from 0 (FALSE/Pending) to 1 (TRUE/Approved)
    $sql = "INSERT INTO comments (article_id, author_name, comment_text, is_approved) VALUES (?, ?, ?, 1)"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $article_id, $author_name, $comment_text);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function get_comments($article_id = null, $only_pending = false) {
    $conn = db_connect();
    $sql = "SELECT id, article_id, author_name, comment_text, is_approved, created_at FROM comments WHERE 1=1";
    $types = '';
    $params = [];
    if ($article_id !== null) { $sql .= " AND article_id = ?"; $types .= 'i'; $params[] = $article_id; }
    // NOTE: $only_pending logic is now irrelevant as nothing is pending, but function remains for completeness.
    if ($only_pending) { $sql .= " AND is_approved = 0"; } else { $sql .= " AND is_approved = 1"; }
    $sql .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    $conn->close();
    return $comments;
}

function moderate_comment($comment_id, $approve) {
    $conn = db_connect();
    if ($approve) {
        $stmt = $conn->prepare("UPDATE comments SET is_approved = 1 WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
    }
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

// --- File Handling (No change) ---

function handle_document_upload($file_array) {
    // File upload functionality removed/deprecated per previous user request.
    return false;
}
?>