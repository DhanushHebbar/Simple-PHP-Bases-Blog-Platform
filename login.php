<?php
// CRITICAL FIX: Use require_once to load functions/session start
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? sanitize_input($_POST['action']) : '';
    
    // --- Login Logic ---
    if ($action === 'login') {
        $username = sanitize_input($_POST['login_username']);
        $password = $_POST['login_password'];

        // Retrieve user from MySQL
        $user = get_user_by_username($username);

        // Verify user exists and password hash matches
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            // Successful login redirects immediately
            header("Location: index.php");
            exit;
        } else {
            // Failure sets the error message
            $error = "Invalid username or password.";
        }

    // --- Registration Logic ---
    } elseif ($action === 'register') {
        $username = sanitize_input($_POST['register_username']);
        $password = $_POST['register_password'];
        $confirm_password = $_POST['register_confirm_password'];

        if (empty($username) || empty($password) || empty($confirm_password)) {
            $error = "All fields are required for registration.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif (get_user_by_username($username)) {
            $error = "Username already exists.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            if (register_user($username, $password_hash)) {
                $success = "Registration successful! You can now log in.";
                unset($username); 
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<?php require_once 'includes/header.php'; ?>

<style>
    /* Styling specific to this page to balance the forms */
    .form-container {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        max-width: 1000px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    @media (min-width: 768px) {
        .form-container {
            flex-direction: row;
        }
        .form-card {
            flex: 1;
        }
    }
</style>

<div class="form-container">
    
    <div class="form-card bg-white shadow-xl rounded-xl p-8 border-t-4 border-indigo-600">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-3">Sign In</h2>
        <?php if ($error && ($action === 'login' || !isset($action))): ?>
             <p class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php" onsubmit="return validateLoginForm(event)">
            <input type="hidden" name="action" value="login">
            <div class="space-y-4">
                <div>
                    <label for="login_username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" id="login_username" name="login_username" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-red-500 mt-1 hidden" id="login_username_error">Username is required.</p>
                </div>
                <div>
                    <label for="login_password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="login_password" name="login_password" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-red-500 mt-1 hidden" id="login_password_error">Password is required.</p>
                </div>
                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                        Log In
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="form-card bg-white shadow-xl rounded-xl p-8 border-t-4 border-emerald-600">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-3">Create Account</h2>
        <?php if ($success): ?>
            <p class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded mb-4" role="alert"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if ($error && $action === 'register'): ?>
            <p class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php" onsubmit="return validateRegistrationForm(event)">
            <input type="hidden" name="action" value="register">
            <div class="space-y-4">
                <div>
                    <label for="register_username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" id="register_username" name="register_username" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500">
                    <p class="text-xs text-red-500 mt-1 hidden" id="register_username_error">Username is required.</p>
                </div>
                <div>
                    <label for="register_password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="register_password" name="register_password" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500">
                    <p class="text-xs text-red-500 mt-1 hidden" id="register_password_error">Password must be at least 6 characters.</p>
                </div>
                <div>
                    <label for="register_confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" id="register_confirm_password" name="register_confirm_password" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500">
                    <p class="text-xs text-red-500 mt-1 hidden" id="register_confirm_password_error">Passwords do not match.</p>
                </div>
                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition duration-150">
                        Register
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

<script>
    function displayError(id, message, isVisible) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = message;
            element.classList.toggle('hidden', !isVisible);
        }
    }

    // Client-side validation for the Login Form
    function validateLoginForm(event) {
        let isValid = true;
        const username = document.getElementById('login_username').value.trim();
        const password = document.getElementById('login_password').value.trim();

        if (username === '') {
            displayError('login_username_error', 'Username is required.', true);
            isValid = false;
        } else {
            displayError('login_username_error', '', false);
        }

        if (password === '') {
            displayError('login_password_error', 'Password is required.', true);
            isValid = false;
        } else {
            displayError('login_password_error', '', false);
        }

        return isValid;
    }

    // Client-side validation for the Registration Form
    function validateRegistrationForm(event) {
        let isValid = true;
        const username = document.getElementById('register_username').value.trim();
        const password = document.getElementById('register_password').value.trim();
        const confirm_password = document.getElementById('register_confirm_password').value.trim();
        
        // Reset errors
        displayError('register_username_error', '', false);
        displayError('register_password_error', '', false);
        displayError('register_confirm_password_error', '', false);

        if (username === '') {
            displayError('register_username_error', 'Username is required.', true);
            isValid = false;
        }

        if (password.length < 6) {
            displayError('register_password_error', 'Password must be at least 6 characters.', true);
            isValid = false;
        }

        if (password !== confirm_password) {
            displayError('register_confirm_password_error', 'Passwords do not match.', true);
            isValid = false;
        }

        return isValid;
    }
</script>

<?php require_once 'includes/footer.php'; ?>