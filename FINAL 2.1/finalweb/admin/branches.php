<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/config.php';
function post($k,$d=''){return isset($_POST[$k])?trim($_POST[$k]):$d;}

// Function to update site data
function update_site_data() {
    ob_start();
    include __DIR__ . '/seed.php';
    $output = ob_get_clean();
}
$msg='';
try{
  if(isset($_POST['action'])){
    switch($_POST['action']){
      case 'add_branch':
        $st=$pdo->prepare('INSERT INTO branches(name,district,address,phone,opening_hours) VALUES(?,?,?,?,?)');
        $st->execute([post('name'),post('district'),post('address'),post('phone'),post('opening_hours')]);
        update_site_data();
        header('Location: branches.php?msg='.urlencode('Branch added'));
        exit;
      case 'delete_branch':
        $pdo->prepare('DELETE FROM branches WHERE id=?')->execute([post('id')]);
        update_site_data();
        header('Location: branches.php?msg='.urlencode('Branch deleted'));
        exit;
    }
  }
}catch(Exception $e){ $msg='Error: '.$e->getMessage(); }
if(!$msg && isset($_GET['msg'])){ $msg=trim($_GET['msg']); }
$branches=$pdo->query('SELECT * FROM branches ORDER BY id DESC LIMIT 100')->fetchAll();

require_once __DIR__.'/admin_header.php';
?>
<?php if($msg): ?><div class="alert alert-info py-2"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<div id="updateStatus" class="alert d-none" role="alert"></div>

<?php require_once __DIR__.'/admin_sidebar.php'; ?>
    <div class="row g-4">
  <div class="col-lg-4"><div class="card shadow-sm"><div class="card-body">
    <h6 class="card-title">Add Branch</h6>
    <form method="post">
      <input type="hidden" name="action" value="add_branch">
      <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
      <div class="mb-2"><label class="form-label">District</label><input name="district" class="form-control" required></div>
      <div class="mb-2"><label class="form-label">Address</label><input name="address" class="form-control" required></div>
      <div class="mb-2"><label class="form-label">Phone</label><input name="phone" class="form-control" required></div>
      <div class="mb-2"><label class="form-label">Opening Hours</label><input name="opening_hours" class="form-control" placeholder="8am - 10pm"></div>
      <button class="btn btn-navy btn-sm">Save</button>
    </form>
  </div></div></div>
  <div class="col-lg-8"><div class="card shadow-sm"><div class="card-body">
    <h6 class="card-title">Branches</h6>
    <div class="table-responsive"><table class="table table-sm table-striped align-middle">
      <thead><tr><th>ID</th><th>Name</th><th>District</th><th>Phone</th><th>Hours</th><th></th></tr></thead><tbody>
      <?php foreach($branches as $b): ?>
      <tr>
        <td><?php echo $b['id']; ?></td>
        <td><?php echo htmlspecialchars($b['name']); ?></td>
        <td><?php echo htmlspecialchars($b['district']); ?></td>
        <td><?php echo htmlspecialchars($b['phone']); ?></td>
        <td><?php echo htmlspecialchars($b['opening_hours']); ?></td>
        <td>
          <form method="post" onsubmit="return confirm('Delete branch?');" class="d-inline">
            <input type="hidden" name="action" value="delete_branch">
            <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
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
