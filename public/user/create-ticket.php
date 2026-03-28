<?php
session_start();
// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit();
}

include('../../config/config.php');
$base_url = "/php-bugtracking-system/";
$message = '';
$message_type = '';

// Fetch projects for the dropdown
$projectsResult = mysqli_query($connection, "SELECT id, name FROM Projects");

if (isset($_POST['create_ticket'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $project_id = $_POST['project_id'];
    $status = 'open'; // Default to open for new user tickets
    $created_by = $_SESSION['user_id'];

    if (empty($title) || empty($description) || empty($project_id)) {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    } else {
        // SECURE PREPARED STATEMENT
        $stmt = $connection->prepare("INSERT INTO Tickets (title, description, status, created_by, project_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $title, $description, $status, $created_by, $project_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Ticket # " . $stmt->insert_id . " created successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: index.php");
            exit();
        } else {
            $message = "Database error: " . $connection->error;
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
    <title>Report an Issue | Zappr</title>
    <link href="<?php echo $base_url; ?>dist/output.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@0.344.0/dist/umd/lucide.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex min-h-screen">

    <?php include('header.php'); ?>

    <main class="flex-grow p-6 lg:p-10 flex flex-col items-center">
        
        <div class="w-full max-w-3xl">
            <a href="index.php" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-blue-600 transition mb-6 group">
                <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                Back to Dashboard
            </a>

            <div class="mb-10">
                <h1 class="text-4xl font-extrabold tracking-tight">Report a New Bug</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg">Provide as much detail as possible to help our devs zap it faster.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none p-8 md:p-12">
                
                <form action="create-ticket.php" method="POST" class="space-y-8">
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 ml-1">Select Project</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="layers" class="w-5 h-5"></i>
                            </div>
                            <select name="project_id" required 
                                    class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-50 dark:focus:ring-blue-900/20 focus:border-blue-600 transition-all appearance-none cursor-pointer">
                                <option value="">Which project is this for?</option>
                                <?php while ($project = mysqli_fetch_assoc($projectsResult)): ?>
                                    <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 ml-1">Issue Title</label>
                        <input type="text" name="title" placeholder="e.g. Login button unresponsive on mobile" required 
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-50 dark:focus:ring-blue-900/20 focus:border-blue-600 transition-all font-medium text-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 ml-1">Detailed Description</label>
                        <textarea name="description" rows="6" placeholder="Steps to reproduce, expected behavior, and actual results..." required 
                                  class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-50 dark:focus:ring-blue-900/20 focus:border-blue-600 transition-all font-medium resize-none"></textarea>
                    </div>

                    <div class="pt-4">
                        <button type="submit" name="create_ticket" 
                                class="w-full bg-blue-600 text-white py-5 rounded-[1.5rem] font-bold text-xl hover:bg-blue-700 hover:scale-[1.01] active:scale-[0.99] transition-all shadow-xl shadow-blue-200 dark:shadow-none flex items-center justify-center gap-3">
                            Submit Bug Report
                            <i data-lucide="send" class="w-6 h-6"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-8 p-6 bg-amber-50 dark:bg-amber-900/10 rounded-2xl border border-amber-100 dark:border-amber-900/30 flex gap-4">
                <i data-lucide="info" class="w-6 h-6 text-amber-600 shrink-0"></i>
                <p class="text-sm text-amber-800 dark:text-amber-200 leading-relaxed">
                    <strong>Note:</strong> Your ticket will be set to <span class="font-bold">"Open"</span> by default. An admin or developer will review it shortly and update the status accordingly.
                </p>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();

        // 🍞 SweetAlert if error occurs
        <?php if ($message_type === 'error'): ?>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?php echo $message; ?>',
            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e293b'
        });
        <?php endif; ?>
    </script>
</body>
</html>