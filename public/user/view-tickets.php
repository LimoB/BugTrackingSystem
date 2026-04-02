<?php
session_start();
include('../../config/config.php');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'user') {
    die("Unauthorized Access Protocol.");
}

$user_id = (int)$_SESSION['user_id'];

// --- Logic: Search & Filter ---
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($connection, $_GET['status']) : '';
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';

// --- API Logic: Fetch Comments (Internal AJAX) ---
if (isset($_GET['fetch_comments'])) {
    $tid = (int)$_GET['fetch_comments'];
    $c_query = "SELECT C.comment, U.name, C.created_at FROM Comments C 
                LEFT JOIN Users U ON C.user_id = U.id 
                WHERE C.ticket_id = $tid ORDER BY C.created_at ASC";
    $c_res = mysqli_query($connection, $c_query);
    $comments = [];
    while($row = mysqli_fetch_assoc($c_res)) { $comments[] = $row; }
    header('Content-Type: application/json');
    echo json_encode($comments);
    exit();
}

// Main Ticket Query
$query = "SELECT T.*, B.name AS developer, P.name AS project_name
          FROM Tickets T
          LEFT JOIN Users B ON T.assigned_to = B.id
          LEFT JOIN Projects P ON T.project_id = P.id
          WHERE T.created_by = $user_id";

if ($status_filter) { $query .= " AND T.status = '$status_filter'"; }
if ($search_query) { $query .= " AND (T.title LIKE '%$search_query%' OR T.description LIKE '%$search_query%')"; }

$query .= " ORDER BY T.created_at DESC";
$result = mysqli_query($connection, $query);
?>

<div class="animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-6">
        <div>
            <h1 class="text-4xl font-black text-slate-900 dark:text-white uppercase tracking-tighter">
                My <span class="text-blue-600">Tickets.</span>
            </h1>
            <p class="text-slate-500 font-medium italic mt-1 text-sm">Monitor the lifecycle of your reported anomalies.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 bg-white dark:bg-slate-900 p-2 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-4 top-3.5 text-slate-400"></i>
                <input type="text" id="userSearch" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search my reports..." 
                       class="pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-xs font-bold w-64 uppercase tracking-wider">
            </div>
            
            <select id="statusFilter" class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl text-[10px] font-black uppercase tracking-widest outline-none text-slate-500">
                <option value="">All Status</option>
                <option value="open" <?php echo $status_filter == 'open' ? 'selected' : ''; ?>>Open</option>
                <option value="in-progress" <?php echo $status_filter == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                <option value="closed" <?php echo $status_filter == 'closed' ? 'selected' : ''; ?>>Closed</option>
            </select>
            
            <button onclick="applyFilters()" class="p-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200 dark:shadow-none">
                <i data-lucide="filter" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-2xl shadow-slate-200/50 dark:shadow-none overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Identity & Title</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">System Status</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Assigned Dev</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            $status_class = match(strtolower($row['status'])) {
                                'open' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'resolved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                'in-progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-500'
                            };
                            $is_editable = (strtolower($row['status']) === 'open');
                        ?>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1"><?php echo htmlspecialchars($row['project_name'] ?: 'SYSTEM_CORE'); ?></span>
                                        <span class="text-sm font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($row['title']); ?></span>
                                        <span class="text-[10px] text-slate-400 font-medium mt-1 uppercase italic opacity-0 group-hover:opacity-100 transition-opacity">Ref: #TCK-<?php echo $row['id']; ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-[0.1em] <?php echo $status_class; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-[10px] font-black text-slate-500">
                                            <?php echo strtoupper(substr($row['developer'] ?: 'UN', 0, 2)); ?>
                                        </div>
                                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">
                                            <?php echo htmlspecialchars($row['developer'] ?: 'Waiting for Assignee'); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-2">
                                        <?php if ($is_editable): ?>
                                            <button onclick="loadPage('update-ticket&ticket_id=<?php echo $row['id']; ?>')" 
                                                    class="p-2.5 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-xl hover:bg-amber-600 hover:text-white transition-all shadow-sm">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </button>
                                        <?php endif; ?>

                                        <button onclick="openComments(<?php echo $row['id']; ?>, '<?php echo addslashes($row['title']); ?>')" 
                                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 dark:bg-white dark:text-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 dark:hover:bg-blue-500 transition-all active:scale-95 shadow-lg shadow-slate-200 dark:shadow-none">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> 
                                            Discuss
                                        </button>
                                    </div>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-8 py-32 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                        <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                    </div>
                                    <h3 class="text-slate-900 dark:text-white font-black uppercase tracking-tighter">No Reports Found</h3>
                                    <p class="text-slate-500 text-xs font-medium italic mt-1">You haven't submitted any bug reports under these filters.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="commentModal" class="fixed inset-0 z-[200] hidden">
    <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white dark:bg-slate-900 shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/20">
            <div>
                <h3 class="text-[10px] font-black text-blue-600 uppercase tracking-[0.3em] mb-1">Collaboration Hub</h3>
                <h2 id="modalTicketTitle" class="text-xl font-black text-slate-900 dark:text-white truncate pr-4">Ticket Thread</h2>
            </div>
            <button onclick="closeModal()" class="p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:text-rose-500 transition shadow-sm">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        
        <div id="commentsList" class="flex-grow overflow-y-auto p-8 space-y-6 bg-transparent"></div>

        <div class="p-8 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
            <textarea id="newComment" placeholder="Post an update or reply..." 
                      class="w-full p-5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-[1.5rem] text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-none h-32 mb-4 transition-all"></textarea>
            <input type="hidden" id="activeTicketId">
            <button onclick="submitComment()" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-black uppercase text-[10px] tracking-[0.2em] hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-500/30 transition-all flex items-center justify-center gap-3">
                <i data-lucide="send" class="w-4 h-4"></i>
                Submit Message
            </button>
        </div>
    </div>
</div>

<script>
    if (window.lucide) { lucide.createIcons(); }

    function applyFilters() {
        const s = $('#userSearch').val();
        const status = $('#statusFilter').val();
        loadPage(`view-tickets&search=${encodeURIComponent(s)}&status=${encodeURIComponent(status)}`);
    }

    function openComments(tid, title) {
        document.getElementById('activeTicketId').value = tid;
        document.getElementById('modalTicketTitle').innerText = title;
        document.getElementById('commentModal').classList.remove('hidden');
        fetchComments(tid);
    }

    function closeModal() {
        document.getElementById('commentModal').classList.add('hidden');
    }

    function fetchComments(tid) {
        const list = document.getElementById('commentsList');
        list.innerHTML = '<div class="text-center py-10 animate-pulse text-[10px] font-black uppercase tracking-widest text-slate-400">Syncing Thread...</div>';
        
        fetch(`view-tickets.php?fetch_comments=${tid}`)
            .then(r => r.json())
            .then(data => {
                list.innerHTML = '';
                if(data.length === 0) {
                    list.innerHTML = '<div class="text-center py-10"><i data-lucide="message-square-dashed" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">No Activity Yet</p></div>';
                    lucide.createIcons();
                    return;
                }
                data.forEach(c => {
                    const div = document.createElement('div');
                    div.className = "flex flex-col space-y-2 max-w-[90%] " + (c.name === "<?php echo $_SESSION['name']; ?>" ? "ml-auto items-end" : "mr-auto items-start");
                    
                    div.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">${c.name}</span>
                            <span class="text-[8px] font-medium text-slate-300 italic">${c.created_at}</span>
                        </div>
                        <div class="p-4 rounded-2xl text-xs font-medium ${c.name === "<?php echo $_SESSION['name']; ?>" ? "bg-blue-600 text-white rounded-tr-none shadow-lg shadow-blue-500/20" : "bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-tl-none border border-slate-100 dark:border-slate-700"}">
                            ${c.comment}
                        </div>
                    `;
                    list.appendChild(div);
                });
                list.scrollTop = list.scrollHeight;
            });
    }

    function submitComment() {
        const tid = document.getElementById('activeTicketId').value;
        const msg = document.getElementById('newComment').value.trim();
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
                } else {
                    alert('Failed to sync message: ' + res.message);
                }
            });
    }
</script>