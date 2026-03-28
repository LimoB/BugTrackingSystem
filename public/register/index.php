<?php
// register.php - Registration Page
session_start();
include('../../config/config.php');

$base_url = "/php-bugtracking-system/";
$message = "";
$message_type = ""; // 'success' or 'error'

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    if ($password !== $confirmPassword) {
        $message = "Passwords do not match!";
        $message_type = "error";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Secure Prepared Statement
        $stmt = $connection->prepare("INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
        
        if ($stmt->execute()) {
            $message = "Account created! You can now login.";
            $message_type = "success";
        } else {
            $message = "Error: Email might already be in use.";
            $message_type = "error";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Zappr | Create Account</title>
    
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

<nav class="fixed top-0 w-full z-[100] border-b border-slate-200/60 glass text-black">
  <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
    <a href="<?php echo $base_url; ?>" class="flex items-center gap-2.5 group">
      <div class="bg-blue-600 p-2 rounded-xl group-hover:rotate-12 transition-transform">
        <i data-lucide="zap" class="text-white w-5 h-5 fill-current"></i>
      </div>
      <span class="text-xl font-bold tracking-tight text-slate-900">Zappr.</span>
    </a>

    <div class="flex items-center gap-3">
      <span class="text-sm text-slate-500 hidden sm:block font-medium">Already a member?</span>
      <a href="../login/index.php" class="bg-slate-100 text-slate-900 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-200 transition">
        Log In
      </a>
    </div>
  </div>
</nav>

<main class="flex-grow flex items-center justify-center px-6 pt-32 pb-12">
    <div class="w-full max-w-[550px]">
        
        <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-2xl shadow-slate-100 p-8 md:p-12 relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-3xl font-extrabold text-slate-900 mb-2 text-black">Create Account</h2>
                <p class="text-slate-500 mb-8">Join 2,000+ developers shipping better code.</p>

                <form action="index.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2 ml-1 text-black">Full Name</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                <i data-lucide="user" class="w-5 h-5"></i>
                            </div>
                            <input type="text" name="name" placeholder="John Doe" required 
                                class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-600 transition-all text-slate-900">
                        </div>
                    </div>

                    <div class="md:col-span-2 text-black">
                        <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Work Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                <i data-lucide="mail" class="w-5 h-5 text-black"></i>
                            </div>
                            <input type="email" name="email" placeholder="john@company.com" required 
                                class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-600 transition-all text-slate-900">
                        </div>
                    </div>

                    <div class="text-black">
                        <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Password</label>
                        <input type="password" name="password" placeholder="••••••••" required 
                            class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-600 transition-all text-slate-900">
                    </div>

                    <div class="text-black">
                        <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Confirm</label>
                        <input type="password" name="confirm_password" placeholder="••••••••" required 
                            class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-600 transition-all text-slate-900">
                    </div>

                    <div class="md:col-span-2 text-black">
                        <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Select Your Role</label>
                        <select name="role" class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-600 transition-all text-slate-900 appearance-none cursor-pointer">
                            <option value="user">User / Reporter</option>
                            <option value="developer">Developer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 mt-2">
                        <button type="submit" name="register" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold text-lg hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-200 transition-all flex items-center justify-center gap-2">
                            Create Account
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-center mt-8 text-sm text-slate-400">
            By clicking "Create Account", you agree to our <a href="#" class="underline">Terms of Service</a>.
        </p>
    </div>
</main>

<script>
    lucide.createIcons();

    // 🍞 SweetAlert2 Toast Implementation
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });

    <?php if (!empty($message)) : ?>
        Toast.fire({
            icon: '<?php echo $message_type; ?>',
            title: '<?php echo addslashes($message); ?>'
        });
    <?php endif; ?>
</script>

</body>
</html>