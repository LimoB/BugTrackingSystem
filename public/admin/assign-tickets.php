<?php
session_start();
include('../../config/config.php');

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("<div class='p-6 text-red-500 font-bold'>Unauthorized Access</div>");
}

$dev_query = "SELECT id, name FROM Users WHERE role = 'developer' ORDER BY name ASC";
$dev_result = mysqli_query($connection, $dev_query);
$developers = [];
while ($dev = mysqli_fetch_assoc($dev_result)) { $developers[] = $dev; }

$sql = "SELECT t.id, t.title, t.priority, p.name AS project_name
        FROM Tickets t
        LEFT JOIN Projects p ON t.project_id = p.id
        WHERE t.assigned_to IS NULL OR t.assigned_to = 0
        ORDER BY CASE t.priority 
            WHEN 'high' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low' THEN 3 ELSE 4 END ASC, t.created_at DESC";
$result = mysqli_query($connection, $sql);
?>

<div class="p-6">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white">Pending Assignments</h1>
            <p class="text-slate-500 text-sm">Distribute tasks to your development team.</p>
        </div>
        <div class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-2xl text-xs font-black uppercase">
            <?php echo mysqli_num_rows($result); ?> Waiting
        </div>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">ID</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Details & Priority</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Assignment Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ticket = mysqli_fetch_assoc($result)): ?>
                        <?php 
                            $prioClass = 'bg-slate-100 text-slate-600';
                            if($ticket['priority'] == 'high') $prioClass = 'bg-rose-100 text-rose-600';
                            if($ticket['priority'] == 'medium') $prioClass = 'bg-amber-100 text-amber-600';
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-all border-b border-slate-50 dark:border-slate-800">
                            <td class="px-6 py-5 text-xs font-mono text-slate-400 text-center">#<?php echo $ticket['id']; ?></td>
                            <td class="px-6 py-5">
                                <div class="font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($ticket['title']); ?></div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] text-emerald-600 font-bold uppercase tracking-tighter"><?php echo htmlspecialchars($ticket['project_name'] ?? 'General'); ?></span>
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase <?php echo $prioClass; ?>">
                                        <?php echo $ticket['priority']; ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <form class="assign-form-submit flex items-center gap-3 justify-end">
                                    <input type="hidden" name="ticketId" value="<?php echo $ticket['id']; ?>">
                                    
                                    <select name="assignedTo" required 
                                            class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold px-3 py-2 outline-none w-full max-w-[180px] cursor-pointer focus:ring-2 focus:ring-emerald-500">
                                        <option value="">Select Developer...</option>
                                        <?php foreach ($developers as $dev): ?>
                                            <option value="<?php echo $dev['id']; ?>"><?php echo htmlspecialchars($dev['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button type="submit" 
                                            class="deploy-btn whitespace-nowrap px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase transition-all shadow-md">
                                        Deploy
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] p-20 text-center">
            <h2 class="text-2xl font-black text-slate-300 uppercase tracking-widest">All Clear!</h2>
        </div>
    <?php endif; ?>
</div>

<script>
    // ✅ Use event delegation so it works even after AJAX page loads
    $(document).off('submit', '.assign-form-submit').on('submit', '.assign-form-submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const btn = form.find('.deploy-btn');
        const devId = form.find('select[name="assignedTo"]').val();

        if(!devId) {
            Swal.fire('Error', 'Please select a developer first.', 'warning');
            return;
        }

        btn.prop('disabled', true).addClass('opacity-50').text('Deploying...');

        $.ajax({
            url: './actions/assign-tickets-process.php', 
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    Swal.fire({ icon: 'success', title: 'Assigned!', text: res.message, timer: 1500, showConfirmButton: false });
                    
                    form.closest('tr').fadeOut(400, function() {
                        $(this).remove();
                        if ($('.assign-form-submit').length === 0) {
                             if(typeof loadPage === 'function') loadPage('assign-tickets');
                             else location.reload();
                        }
                    });
                } else {
                    Swal.fire('Error', res.error, 'error');
                    btn.prop('disabled', false).removeClass('opacity-50').text('Deploy');
                }
            },
            error: function(xhr) {
                console.error("AJAX Error:", xhr.responseText);
                Swal.fire('System Error', 'Check F12 Network tab.', 'error');
                btn.prop('disabled', false).removeClass('opacity-50').text('Deploy');
            }
        });
    });
</script>