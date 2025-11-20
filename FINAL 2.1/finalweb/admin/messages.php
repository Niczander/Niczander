<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/config.php';
function post($k,$d=''){return isset($_POST[$k])?trim($_POST[$k]):$d;}
$msg='';
try{
  if(isset($_POST['action'])){
    switch($_POST['action']){
      case 'delete_message':
        $pdo->prepare('DELETE FROM contact_messages WHERE id=?')->execute([post('id')]);
        header('Location: messages.php?msg='.urlencode('Message deleted'));
        exit;
    }
  }
}catch(Exception $e){ $msg='Error: '.$e->getMessage(); }
if(!$msg && isset($_GET['msg'])){ $msg=trim($_GET['msg']); }
$messages=$pdo->query('SELECT * FROM contact_messages ORDER BY id DESC LIMIT 100')->fetchAll();

require_once __DIR__.'/admin_header.php';
?>
<?php if($msg): ?><div class="alert alert-info py-2"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<div id="updateStatus" class="alert d-none" role="alert"></div>

<?php require_once __DIR__.'/admin_sidebar.php'; ?>
    <div class="card shadow-sm"><div class="card-body">
  <h6 class="card-title">Latest Contact Messages</h6>
  <div class="table-responsive"><table class="table table-sm table-striped align-middle">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Subject</th><th>Reason</th><th>Message</th><th>Created</th><th></th></tr></thead><tbody>
    <?php foreach($messages as $m): ?>
    <tr>
      <td><?php echo $m['id']; ?></td>
      <td><?php echo htmlspecialchars($m['name']); ?></td>
      <td><?php echo htmlspecialchars($m['email']); ?></td>
      <td><?php echo htmlspecialchars($m['phone']); ?></td>
      <td><?php echo htmlspecialchars($m['subject']); ?></td>
      <td><?php echo htmlspecialchars($m['reason']); ?></td>
      <td style="max-width:250px"><?php echo nl2br(htmlspecialchars($m['message'])); ?></td>
      <td><?php echo htmlspecialchars($m['created_at']); ?></td>
      <td>
        <form method="post" onsubmit="return confirm('Delete message?');" class="d-inline">
          <input type="hidden" name="action" value="delete_message">
          <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
          <button class="btn btn-outline-danger btn-sm">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody></table></div>
</div></div></div>

<?php require_once __DIR__.'/admin_sidebar_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const btn=document.getElementById('update-site-btn');const box=document.getElementById('updateStatus');
if(btn){btn.addEventListener('click',async()=>{btn.disabled=true;btn.textContent='Updating...';box.className='alert alert-info';box.textContent='Updating website content...';box.classList.remove('d-none');try{const res=await fetch('seed.php',{cache:'no-store'});const text=await res.text();box.className=res.ok?'alert alert-success':'alert alert-warning';box.textContent=text||(res.ok?'Update complete.':'Update finished with warnings.');if(res.ok)setTimeout(()=>window.location.reload(),800);}catch(e){box.className='alert alert-danger';box.textContent='Update failed. Please try again.'}finally{btn.disabled=false;btn.textContent='Update Website'}})}
</script>
</body></html>
