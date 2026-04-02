<?php
/**
 * File: public/register/index.php
 * Purpose: Secure registration with data persistence (form doesn't clear on error).
 */
session_start();
include('../../config/config.php');

$base_url = "/php-bugtracking-system/";
$message = "";
$message_type = ""; 

// Variables to hold form data so it doesn't clear
$name_val = "";
$email_val = "";
$role_val = "user";

if (isset($_POST['register'])) {
    // Capture data to echo back into the form
    $name_val = trim($_POST['name']);
    $email_val = trim($_POST['email']);
    $role_val = $_POST['role'];
    
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    
    // 1. Validation Checks
    if ($password !== $confirm) {
        $message = "Passwords do not match!";
        $message_type = "error";
    } elseif (strlen($password) < 6) {
        $message = "Password is too short (min 6 chars).";
        $message_type = "error";
    } else {
        // 2. Security: Hash Password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // 3. Check for existing email
        $check = $connection->prepare("SELECT id FROM Users WHERE email = ?");
        $check->bind_param("s", $email_val);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $message = "This email is already in our system.";
            $message_type = "error";
        } else {
            // 4. Insert into Database
            $stmt = $connection->prepare("INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name_val, $email_val, $hashedPassword, $role_val);
            
            if ($stmt->execute()) {
                $message = "Account created! Moving to login...";
                $message_type = "success";
                // Clear fields only on success
                $name_val = $email_val = "";
            } else {
                $message = "Something went wrong. Try again.";
                $message_type = "error";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Zappr | Register</title>
    
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
    <a href="../login/index.php" class="text-xs font-black uppercase tracking-widest text-slate-500 hover:text-blue-600 transition">Log In</a>
  </div>
</nav>

<main class="flex-grow flex items-center justify-center px-6 pt-32 pb-12 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-blue-100/40 rounded-full blur-[100px] -z-10"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-indigo-100/40 rounded-full blur-[100px] -z-10"></div>

    <div class="w-full max-w-[580px]">
        <div class="bg-white rounded-[3rem] border border-slate-200 shadow-2xl p-8 md:p-12">
            
            <header class="mb-10">
                <h2 class="text-4xl font-black tracking-tighter mb-2">Create Account</h2>
                <p class="text-slate-500 font-medium">Join the team and start tracking bugs.</p>
            </header>

            <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                <div class="md:col-span-2 space-y-2 input-card">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Full Name</label>
                    <div class="relative">
                        <i data-lucide="user" class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($name_val); ?>" placeholder="Boaz Limo" required 
                            class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-50 focus:border-blue-600 focus:bg-white outline-none font-bold text-sm transition-all">
                    </div>
                </div>

                <div class="md:col-span-2 space-y-2 input-card">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Email Address</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email_val); ?>" placeholder="boaz@example.com" required 
                            class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-50 focus:border-blue-600 focus:bg-white outline-none font-bold text-sm transition-all">
                    </div>
                </div>

                <div class="space-y-2 input-card">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Password</label>
                    <input type="password" name="password" placeholder="••••••••" required 
                        class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-50 focus:border-blue-600 focus:bg-white outline-none font-bold text-sm transition-all">
                </div>

                <div class="space-y-2 input-card">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Confirm</label>
                    <input type="password" name="confirm_password" placeholder="••••••••" required 
                        class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-50 focus:border-blue-600 focus:bg-white outline-none font-bold text-sm transition-all">
                </div>

                <div class="md:col-span-2 space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Your Role</label>
                    <div class="relative">
                        <select name="role" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-50 focus:border-blue-600 outline-none appearance-none font-bold text-sm text-slate-700 cursor-pointer">
                            <option value="user" <?php echo ($role_val == 'user') ? 'selected' : ''; ?>>User / Reporter</option>
                            <option value="developer" <?php echo ($role_val == 'developer') ? 'selected' : ''; ?>>Developer</option>
                            <option value="admin" <?php echo ($role_val == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="md:col-span-2 pt-4">
                    <button type="submit" name="register" class="w-full bg-blue-600 text-white py-5 rounded-[2rem] font-black text-[11px] uppercase tracking-widest hover:bg-slate-900 transition-all shadow-xl shadow-blue-200 flex items-center justify-center gap-3 active:scale-95 group">
                        Create Account
                        <i data-lucide="sparkles" class="w-4 h-4 group-hover:rotate-12 transition-transform"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    lucide.createIcons();

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    <?php if (!empty($message)) : ?>
        Toast.fire({
            icon: '<?php echo $message_type; ?>',
            title: '<?php echo addslashes($message); ?>'
        }).then(() => {
            <?php if ($message_type === "success") : ?>
                window.location.href = "../login/index.php";
            <?php endif; ?>
        });
    <?php endif; ?>
</script>

</body>
</html>