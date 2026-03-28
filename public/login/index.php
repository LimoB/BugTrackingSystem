<?php
session_start();
// 1. Database Connection
include('../../config/config.php'); 

$base_url = "/php-bugtracking-system/";
$error = "";
$debug_info = ""; // For console logging

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 2. Prepare Statement to prevent SQL Injection
    $stmt = $connection->prepare("SELECT id, name, password, role FROM Users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // 3. Check Password
        if (password_verify($password, $user['password'])) {
            // SUCCESS!
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            // Redirect based on role
            header("Location: ../" . $user['role'] . "/index.php");
            exit();
        } else {
            $error = "Incorrect password.";
            $debug_info = "User found, but password_verify failed.";
        }
    } else {
        $error = "No account found with that email.";
        $debug_info = "Email not found in database: " . $email;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Zappr</title>
    <link href="<?php echo $base_url; ?>dist/output.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@0.344.0/dist/umd/lucide.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus+Jakarta+Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="bg-[#fcfcfd] text-slate-900 antialiased min-h-screen flex flex-col">

<nav class="fixed top-0 w-full z-[100] border-b border-slate-200/60 glass">
  <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
    <a href="<?php echo $base_url; ?>" class="flex items-center gap-2.5 group">
      <div class="bg-blue-600 p-2 rounded-xl group-hover:scale-110 transition-transform">
        <i data-lucide="zap" class="text-white w-5 h-5 fill-current"></i>
      </div>
      <span class="text-xl font-bold tracking-tight text-slate-900">Zappr.</span>
    </a>
    <a href="../register/index.php" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-600 transition shadow-lg shadow-slate-200">
        Create Account
    </a>
  </div>
</nav>

<main class="flex-grow flex items-center justify-center px-6 pt-32 pb-12">
    <div class="w-full max-w-[450px]">
        <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-2xl shadow-slate-100 p-8 md:p-12 relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-3xl font-extrabold text-slate-900 mb-2">Welcome back</h2>
                <p class="text-slate-500 mb-8">Enter your details to access your dashboard.</p>

                <form action="" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Work Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </div>
                            <input type="email" name="email" placeholder="name@company.com" required 
                                class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-600 transition-all text-slate-900">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2 ml-1">
                            <label class="text-sm font-bold text-slate-700">Password</label>
                            <a href="#" class="text-xs font-bold text-blue-600 hover:text-blue-700">Forgot?</a>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                <i data-lucide="lock" class="w-5 h-5"></i>
                            </div>
                            <input type="password" name="password" placeholder="••••••••" required 
                                class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-600 transition-all text-slate-900">
                        </div>
                    </div>

                    <button type="submit" name="login" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold text-lg hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 flex items-center justify-center gap-2 mt-4">
                        Sign In
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    lucide.createIcons();

    // 🔍 CONSOLE LOGS FOR DEBUGGING
    console.log("Login Page Loaded");
    <?php if (!empty($debug_info)) : ?>
        console.warn("PHP Debug Info: <?php echo $debug_info; ?>");
    <?php endif; ?>

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });

    <?php if (!empty($error)) : ?>
        Toast.fire({
            icon: 'error',
            title: '<?php echo addslashes($error); ?>'
        });
    <?php endif; ?>
</script>

</body>
</html>