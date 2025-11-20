<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/config.php';
$pdo->exec("CREATE TABLE IF NOT EXISTS collections(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,description TEXT)");
$msg='';
if(!$msg && isset($_GET['msg'])){ $msg=trim($_GET['msg']); }
$cats=$pdo->query('SELECT * FROM collections ORDER BY id DESC')->fetchAll();

require_once __DIR__.'/admin_header.php';
?>
<?php if($msg): ?><div class="alert alert-info py-2"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<div id="updateStatus" class="alert d-none" role="alert"></div>

<?php require_once __DIR__.'/admin_sidebar.php'; ?>
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h6 class="card-title">Add Category</h6>
        <form method="post" action="actions/categories.php">
          <input type="hidden" name="action" value="add_category">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
          <button class="btn btn-navy btn-sm">Save</button>
        </form>
      </div>
    </div>
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Categories Management</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-striped align-middle">
            <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach($cats as $c): ?>
              <tr>
                <td><?php echo $c['id']; ?></td>
                <td><?php echo htmlspecialchars($c['name']); ?></td>
                <td><?php echo htmlspecialchars($c['description']); ?></td>
                <td class="d-flex gap-1">
                  <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleEdit(<?php echo $c['id']; ?>)">Edit</button>
                  <form method="post" action="actions/categories.php" onsubmit="return confirm('Delete category?');" class="d-inline">
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                    <button class="btn btn-outline-danger btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
              <tr id="edit-row-<?php echo $c['id']; ?>" style="display:none">
                <td colspan="4">
                  <form method="post" action="actions/categories.php" class="p-3 border rounded bg-light">
                    <input type="hidden" name="action" value="update_category">
                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                    <div class="row g-2">
                      <div class="col-md-4">
                        <label class="form-label">Name</label>
                        <input name="name" class="form-control" value="<?php echo htmlspecialchars($c['name']); ?>" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input name="description" class="form-control" value="<?php echo htmlspecialchars($c['description']); ?>">
                      </div>
                      <div class="col-md-2 d-flex align-items-end gap-2">
                        <button class="btn btn-navy btn-sm w-100">Save</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="toggleEdit(<?php echo $c['id']; ?>)">Cancel</button>
                      </div>
                    </div>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>


<?php require_once __DIR__.'/admin_sidebar_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const btn=document.getElementById('update-site-btn');const box=document.getElementById('updateStatus');
if(btn){btn.addEventListener('click',async()=>{btn.disabled=true;btn.textContent='Updating...';box.className='alert alert-info';box.textContent='Updating website content...';box.classList.remove('d-none');try{const res=await fetch('seed.php',{cache:'no-store'});const text=await res.text();box.className=res.ok?'alert alert-success':'alert alert-warning';box.textContent=text|| (res.ok?'Update complete.':'Update finished with warnings.');if(res.ok)setTimeout(()=>window.location.reload(),800);}catch(e){box.className='alert alert-danger';box.textContent='Update failed. Please try again.'}finally{btn.disabled=false;btn.textContent='Update Website'}})}

// Inline edit row toggler
function toggleEdit(id){
  const row=document.getElementById('edit-row-'+id);
  if(!row) return;
  const isOpen=row.style.display!=="none";
  document.querySelectorAll('[id^="edit-row-"]').forEach(el=>el.style.display='none');
  row.style.display=isOpen?'none':'';
  try{row.scrollIntoView({behavior:'smooth',block:'center'});}catch(e){}
}
</script>
</body></html>
