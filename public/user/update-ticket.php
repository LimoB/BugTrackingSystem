<?php
session_start();
include('../../config/config.php');

// ✅ Security Check
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'user') {
    die("<div class='p-20 text-center font-black text-rose-500 uppercase tracking-widest'>Unauthorized Protocol Access.</div>");
}

// Support for your new loadPage() router which passes ticket_id via query string
$ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
$user_id = (int)$_SESSION['user_id'];

if ($ticket_id === 0) {
    die("<div class='p-20 text-center font-black text-rose-500 uppercase tracking-widest'>Invalid Ticket ID.</div>");
}

// Fetch ticket - ensure ownership
$stmt = $connection->prepare("SELECT * FROM Tickets WHERE id = ? AND created_by = ?");
$stmt->bind_param("ii", $ticket_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<div class='p-20 text-center flex flex-col items-center gap-4'>
            <div class='text-6xl'>🔒</div>
            <div class='font-black uppercase text-rose-500 tracking-widest text-2xl'>Access Denied</div>
            <p class='text-slate-500 font-bold italic'>This record is either restricted or does not exist.</p>
            <button onclick=\"loadPage('view-tickets')\" class='mt-4 px-8 py-3 bg-slate-900 dark:bg-white dark:text-slate-900 text-white rounded-xl font-black text-[10px] uppercase tracking-widest'>Return to Safety</button>
          </div>";
    exit();
}

$ticket = $result->fetch_assoc();

// Lock logic: Users cannot edit if a Dev has already started or finished the work
$is_locked = in_array(strtolower($ticket['status']), ['in-progress', 'resolved', 'closed']);
?>

<div class="max-w-4xl mx-auto animate-in fade-in slide-in-from-bottom-10 duration-700 pb-20">
    
    <button onclick="loadPage('view-tickets')" class="group inline-flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 hover:text-blue-600 transition-all mb-10">
        <div class="p-2 bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-slate-200 dark:border-slate-800 group-hover:border-blue-500/50">
            <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
        </div>
        Back to Dashboard
    </button>

    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
        <div>
            <div class="flex items-center gap-3 mb-3">
                <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 text-[10px] font-black uppercase tracking-widest rounded-md">
                    Ref #TCK-<?php echo $ticket_id; ?>
                </span>
                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-500 text-[10px] font-black uppercase tracking-widest rounded-md italic">
                    Status: <?php echo $ticket['status']; ?>
                </span>
            </div>
            <h1 class="text-5xl font-black tracking-tighter text-slate-900 dark:text-white uppercase">
                Edit <span class="text-blue-600">Ticket.</span>
            </h1>
        </div>
        
        <?php if ($is_locked): ?>
            <div class="flex items-center gap-3 px-6 py-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/30 rounded-2xl">
                <i data-lucide="lock" class="w-5 h-5 text-amber-500"></i>
                <span class="text-[10px] font-black uppercase tracking-widest text-amber-600">Locked for Processing</span>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-slate-800 shadow-2xl shadow-slate-200/50 dark:shadow-none overflow-hidden transition-all">
        <div class="h-2 w-full <?php echo $is_locked ? 'bg-amber-500' : 'bg-blue-600'; ?>"></div>
        
        <form id="updateTicketForm" class="p-10 md:p-16 space-y-10">
            <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">

            <div class="group">
                <label class="block text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 mb-4 ml-2 group-focus-within:text-blue-600 transition-colors">Subject / Title</label>
                <div class="relative">
                    <i data-lucide="type" class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($ticket['title']); ?>" 
                           <?php echo $is_locked ? 'readonly' : 'required'; ?>
                           class="w-full pl-16 pr-8 py-6 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-blue-600/20 focus:bg-white dark:focus:bg-slate-800 rounded-2xl outline-none transition-all font-bold text-lg text-slate-800 dark:text-white <?php echo $is_locked ? 'cursor-not-allowed opacity-60' : ''; ?>">
                </div>
            </div>

            <div class="group">
                <label class="block text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 mb-4 ml-2 group-focus-within:text-blue-600 transition-colors">Bug Description</label>
                <textarea name="description" rows="8" 
                          <?php echo $is_locked ? 'readonly' : 'required'; ?>
                          class="w-full p-8 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-blue-600/20 focus:bg-white dark:focus:bg-slate-800 rounded-[2.5rem] outline-none transition-all resize-none text-slate-600 dark:text-slate-300 font-medium leading-relaxed <?php echo $is_locked ? 'cursor-not-allowed opacity-60' : ''; ?>"><?php echo htmlspecialchars($ticket['description']); ?></textarea>
            </div>

            <div class="pt-10 flex flex-col sm:flex-row items-center justify-end gap-6 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="loadPage('view-tickets')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-rose-500 transition-colors">
                    Discard Changes
                </button>
                
                <?php if (!$is_locked): ?>
                    <button type="submit" id="submitUpdateBtn" class="group w-full sm:w-auto px-12 py-6 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl font-black uppercase text-[11px] tracking-[0.3em] hover:shadow-2xl hover:shadow-blue-500/20 active:scale-95 transition-all flex items-center justify-center gap-4">
                        <i data-lucide="refresh-cw" class="w-4 h-4 group-hover:rotate-180 transition-transform duration-700"></i>
                        Update Logs
                    </button>
                <?php else: ?>
                    <div class="px-8 py-5 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center gap-3">
                        <i data-lucide="info" class="w-4 h-4 text-slate-400"></i>
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Record is currently Read-Only</span>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
    // Initialize icons for the dynamic content
    if (window.lucide) { lucide.createIcons(); }

    // AJAX Submission Logic
    $('#updateTicketForm').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('#submitUpdateBtn');
        const originalContent = $btn.html();
        
        // Visual Feedback
        $btn.prop('disabled', true).addClass('opacity-50 cursor-wait').html('<span class="animate-spin">⏳</span> PROCESSING...');

        $.ajax({
            url: 'update-ticket-action.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'SYSTEM UPDATED', 
                        text: res.message, 
                        background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                        color: document.documentElement.classList.contains('dark') ? '#fff' : '#000',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        loadPage('view-tickets');
                    });
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'ACCESS DENIED', 
                        text: res.message,
                        background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                        color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
                    });
                    $btn.prop('disabled', false).removeClass('opacity-50 cursor-wait').html(originalContent);
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'NETWORK ERROR', text: 'Connection to terminal lost.' });
                $btn.prop('disabled', false).removeClass('opacity-50 cursor-wait').html(originalContent);
            }
        });
    });
</script>