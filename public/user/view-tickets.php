<?php
session_start();
// Security & Errors
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit();
}
include('../../config/config.php');

$base_url = "/php-bugtracking-system/";
$user_id = $_SESSION['user_id'];

// --- Logic: Search, Filter, Sort ---
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($connection, $_GET['status']) : '';
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'id';

$valid_sort_columns = ['id', 'title', 'status', 'created_at'];
if (!in_array($sort_column, $valid_sort_columns)) { $sort_column = 'id'; }

// API Logic: Fetch Comments via AJAX
if (isset($_GET['fetch_comments'])) {
    $tid = mysqli_real_escape_string($connection, $_GET['fetch_comments']);
    $c_query = "SELECT C.comment, U.name, C.created_at FROM Comments C 
                LEFT JOIN Users U ON C.user_id = U.id WHERE C.ticket_id = '$tid' ORDER BY C.created_at DESC";
    $c_res = mysqli_query($connection, $c_query);
    $comments = [];
    while($row = mysqli_fetch_assoc($c_res)) { $comments[] = $row; }
    header('Content-Type: application/json');
    echo json_encode($comments);
    exit();
}

// Main Ticket Query
$query = "SELECT T.*, U.name AS creator, B.name AS developer, P.name AS project_name
          FROM Tickets T
          LEFT JOIN Users U ON T.created_by = U.id
          LEFT JOIN Users B ON T.assigned_to = B.id
          LEFT JOIN Projects P ON T.project_id = P.id
          WHERE T.created_by = '$user_id' OR T.assigned_to = '$user_id'";

if ($status_filter) { $query .= " AND T.status = '$status_filter'"; }
if ($search_query) { $query .= " AND (T.title LIKE '%$search_query%' OR T.description LIKE '%$search_query%')"; }

$query .= " ORDER BY T.$sort_column DESC";
$result = mysqli_query($connection, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets | Zappr</title>
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

    <main class="flex-grow p-6 lg:p-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">My Tickets</h1>
                <p class="text-slate-500 dark:text-slate-400">Manage and track your reported issues.</p>
            </div>
            
            <form method="GET" class="flex flex-wrap gap-3">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-3.5 text-slate-400"></i>
                    <input type="text" name="search" value="<?php echo $search_query; ?>" placeholder="Search bugs..." 
                           class="pl-10 pr-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm w-64">
                </div>
                <select name="status" onchange="this.form.submit()" 
                        class="px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-sm outline-none">
                    <option value="">All Status</option>
                    <option value="open" <?php if($status_filter=='open') echo 'selected'; ?>>Open</option>
                    <option value="resolved" <?php if($status_filter=='resolved') echo 'selected'; ?>>Resolved</option>
                </select>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Ticket</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Assigned To</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Project</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): 
                                $status_color = [
                                    'open' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'resolved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'closed' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
                                    'in-progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                                ][strtolower($row['status'])] ?? 'bg-slate-100 text-slate-700';
                            ?>
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-5">
                                        <div class="font-bold text-slate-900 dark:text-white mb-1"><?php echo htmlspecialchars($row['title']); ?></div>
                                        <div class="text-xs text-slate-500 truncate max-w-xs"><?php echo htmlspecialchars($row['description']); ?></div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-tight <?php echo $status_color; ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold">
                                                <?php echo substr($row['developer'] ?? 'NA', 0, 2); ?>
                                            </div>
                                            <span class="text-sm font-medium"><?php echo $row['developer'] ?: 'Unassigned'; ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-sm font-medium text-slate-600 dark:text-slate-400">
                                        <?php echo $row['project_name'] ?: 'General'; ?>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <button onclick="openComments(<?php echo $row['id']; ?>)" 
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 dark:bg-white dark:text-slate-900 text-white rounded-xl text-xs font-bold hover:bg-blue-600 dark:hover:bg-blue-500 transition-colors">
                                            <i data-lucide="message-square" class="w-3.5 h-3.5"></i> Comments
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <i data-lucide="inbox" class="w-12 h-12 text-slate-300 mb-4"></i>
                                        <p class="text-slate-500 font-medium">No tickets found matching your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="commentModal" class="fixed inset-0 z-[150] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white dark:bg-slate-900 shadow-2xl flex flex-col animate-slide-in">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="text-lg font-bold">Ticket Discussions</h3>
                <button onclick="closeModal()" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div id="commentsList" class="flex-grow overflow-y-auto p-6 space-y-4">
                </div>

            <div class="p-6 border-t border-slate-100 dark:border-slate-800">
                <textarea id="newComment" placeholder="Type your message..." 
                          class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none h-24 mb-3"></textarea>
                <input type="hidden" id="activeTicketId">
                <button onclick="submitComment()" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                    Post Comment
                </button>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openComments(tid) {
            document.getElementById('activeTicketId').value = tid;
            document.getElementById('commentModal').classList.remove('hidden');
            fetchComments(tid);
        }

        function closeModal() {
            document.getElementById('commentModal').classList.add('hidden');
        }

        function fetchComments(tid) {
            const list = document.getElementById('commentsList');
            list.innerHTML = '<p class="text-sm text-slate-500 italic">Loading conversation...</p>';
            
            fetch(`view-tickets.php?fetch_comments=${tid}`)
                .then(r => r.json())
                .then(data => {
                    list.innerHTML = data.length ? '' : '<p class="text-sm text-slate-500">No comments yet. Be the first!</p>';
                    data.forEach(c => {
                        const div = document.createElement('div');
                        div.className = "p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800";
                        div.innerHTML = `<div class="flex justify-between mb-1">
                                            <span class="font-bold text-xs text-blue-600">${c.name}</span>
                                            <span class="text-[10px] text-slate-400">${c.created_at}</span>
                                         </div>
                                         <p class="text-sm text-slate-700 dark:text-slate-300">${c.comment}</p>`;
                        list.appendChild(div);
                    });
                });
        }

        function submitComment() {
            const tid = document.getElementById('activeTicketId').value;
            const msg = document.getElementById('newComment').value;
            if(!msg) return;

            const fd = new FormData();
            fd.append('ticket_id', tid);
            fd.append('comment', msg);

            fetch('add-comment.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if(res.success) {
                        document.getElementById('newComment').value = '';
                        fetchComments(tid);
                    }
                });
        }
    </script>
</body>
</html>