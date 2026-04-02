<?php
/**
 * File: public/login/index.php
 * Purpose: Secure login with email persistence on error.
 */
ob_start();
session_start();

// 1. Load Database Connection
include('../../config/config.php'); 

$base_url = "/php-bugtracking-system/";
$error = "";
$email_val = ""; // Variable to hold the email if login fails

// If user is already logged in, send them straight to their dashboard
if (isset($_SESSION['role'])) {
    header("Location: ../" . $_SESSION['role'] . "/index.php");
    exit();
}

if (isset($_POST['login'])) {
    $email_val = trim($_POST['email']);
    $password = $_POST['password']; 

    // 2. Search for the user safely
    $stmt = $connection->prepare("SELECT id, name, password, role FROM Users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email_val);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // 3. Check the password
        if (password_verify($password, $user['password'])) {
            
            // Success: Secure the session
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = strtolower($user['role']); 
            $_SESSION['name'] = $user['name'];

            // 4. Redirect to the correct folder (admin, developer, or user)
            ob_clean();
            header("Location: ../" . $_SESSION['role'] . "/index.php");
            exit();

        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "No account found with that email.";
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
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(16px); }
        .input-card { transition: all 0.3s ease; }
        .input-card:focus-within { transform: translateY(-2px); }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased min-h-screen flex flex-col">

<nav class="fixed top-0 w-full z-[100] border-b border-slate-200/60 glass">
  <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
    <a href="<?php echo $base_url; ?>" class="flex items-center gap-2.5 group">
      <div class="bg-blue-600 p-2 rounded-xl group-hover:rotate-12 transition-all">
        <i data-lucide="zap" class="text-white w-5 h-5 fill-current"></i>
      </div>
      <span class="text-xl font-black tracking-tighter uppercase">Zappr<span class="text-blue-600">.</span></span>
    </a>
    <a href="../register/index.php" class="bg-slate-900 text-white px-6 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 transition shadow-xl shadow-slate-200">
        Create Account
    </a>
  </div>
</nav>

<main class="flex-grow flex items-center justify-center px-6 pt-32 pb-12 relative overflow-hidden">
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-100 rounded-full blur-[120px] opacity-50 -z-10"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-100 rounded-full blur-[120px] opacity-50 -z-10"></div>

    <div class="w-full max-w-[480px]">
        <div class="bg-white rounded-[3rem] border border-slate-200 shadow-2xl p-10 md:p-14">
            
            <header class="mb-10 text-center md:text-left">
                <h2 class="text-4xl font-black tracking-tighter mb-3 leading-none">Welcome Back</h2>
                <p class="text-slate-500 font-medium leading-relaxed">Sign in to manage your error reports and projects.</p>
            </header>

            <form action="" method="POST" class="space-y-6">
                <div class="space-y-2 input-card">
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Email Address</label>
                    <div class="relative group">
                        <i data-lucide="mail" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email_val); ?>" placeholder="name@example.com" required 
                            class="w-full pl-14 pr-6 py-5 bg-slate-50 border border-slate-100 rounded-[2rem] focus:ring-4 focus:ring-blue-50 focus:border-blue-600 outline-none text-sm font-bold transition-all">
                    </div>
                </div>

                <div class="space-y-2 input-card">
                    <div class="flex justify-between items-center ml-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Password</label>
                        <a href="#" class="text-[10px] font-black uppercase text-blue-600 hover:underline">Forgot?</a>
                    </div>
                    <div class="relative group">
                        <i data-lucide="lock" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        <input type="password" name="password" placeholder="••••••••" required 
                            class="w-full pl-14 pr-6 py-5 bg-slate-50 border border-slate-100 rounded-[2rem] focus:ring-4 focus:ring-blue-50 focus:border-blue-600 outline-none text-sm font-bold transition-all">
                    </div>
                </div>

                <button type="submit" name="login" class="w-full bg-slate-900 text-white py-5 rounded-[2rem] font-black text-[11px] uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl shadow-blue-100 flex items-center justify-center gap-3 mt-6 active:scale-95 group">
                    Sign In
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>

            <footer class="mt-12 pt-8 border-t border-slate-100 text-center">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                    New to Zappr? <a href="../register/index.php" class="text-blue-600 hover:underline">Join the platform</a>
                </p>
            </footer>
        </div>
    </div>
</main>

<script>
    lucide.createIcons();

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