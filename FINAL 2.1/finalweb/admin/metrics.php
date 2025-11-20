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
      case 'set_metric':
        $st=$pdo->prepare('REPLACE INTO site_metrics(metric_key,metric_value) VALUES(?,?)');
        $st->execute([post('metric_key'),(int)post('metric_value')]);
        header('Location: metrics.php?msg='.urlencode('Metric saved'));
        exit;
      case 'update_metric':
        $st=$pdo->prepare('UPDATE site_metrics SET metric_key=?, metric_value=? WHERE metric_key=?');
        $st->execute([post('metric_key'), (int)post('metric_value'), post('old_key')]);
        header('Location: metrics.php?msg='.urlencode('Metric updated'));
        exit;
      case 'delete_metric':
        $st=$pdo->prepare('DELETE FROM site_metrics WHERE metric_key=?');
        $st->execute([post('metric_key')]);
        header('Location: metrics.php?msg='.urlencode('Metric deleted'));
        exit;
    }
  }
}catch(Exception $e){ $msg='Error: '.$e->getMessage(); }
if(!$msg && isset($_GET['msg'])){ $msg=trim($_GET['msg']); }
$metrics=$pdo->query('SELECT * FROM site_metrics')->fetchAll();

require_once __DIR__.'/admin_header.php';
?>
<?php if($msg): ?><div class="alert alert-info py-2"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<div id="updateStatus" class="alert d-none" role="alert"></div>

<?php require_once __DIR__.'/admin_sidebar.php'; ?>
    <div class="row g-3">
  <div class="col-lg-4"><div class="card shadow-sm"><div class="card-body">
    <h6 class="card-title">Add Metric</h6>
    <form method="post">
      <input type="hidden" name="action" value="set_metric">
      <div class="mb-2"><label class="form-label">Key</label><input name="metric_key" class="form-control" required></div>
      <div class="mb-2"><label class="form-label">Value</label><input type="number" name="metric_value" class="form-control" required></div>
      <button class="btn btn-navy btn-sm">Save</button>
    </form>
  </div></div></div>
  <div class="col-lg-8"><div class="card shadow-sm"><div class="card-body">
    <h6 class="card-title">Edit Metrics</h6>
    <div class="table-responsive"><table class="table table-sm table-striped align-middle">
      <thead><tr><th style="width:35%">Key</th><th style="width:25%">Value</th><th>Actions</th></tr></thead><tbody>
      <?php foreach($metrics as $m): ?>
      <tr>
        <td>
          <form method="post" class="d-flex gap-2 align-items-center">
            <input type="hidden" name="action" value="update_metric">
            <input type="hidden" name="old_key" value="<?php echo htmlspecialchars($m['metric_key']); ?>">
            <input name="metric_key" class="form-control form-control-sm" value="<?php echo htmlspecialchars($m['metric_key']); ?>" required>
        </td>
        <td><input type="number" name="metric_value" class="form-control form-control-sm" value="<?php echo (int)$m['metric_value']; ?>" required></td>
        <td class="d-flex gap-2">
            <button class="btn btn-sm btn-navy">Save</button>
          </form>
          <form method="post" onsubmit="return confirm('Delete metric?');" class="d-inline">
            <input type="hidden" name="action" value="delete_metric">
            <input type="hidden" name="metric_key" value="<?php echo htmlspecialchars($m['metric_key']); ?>">
            <button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($metrics)): ?><tr><td colspan="3" class="text-muted">No metrics yet. Add some on the left.</td></tr><?php endif; ?>
    </tbody></table></div>
  </div></div></div>

<?php require_once __DIR__.'/admin_sidebar_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const btn=document.getElementById('update-site-btn');const box=document.getElementById('updateStatus');
if(btn){btn.addEventListener('click',async()=>{btn.disabled=true;btn.textContent='Updating...';box.className='alert alert-info';box.textContent='Updating website content...';box.classList.remove('d-none');try{const res=await fetch('seed.php',{cache:'no-store'});const text=await res.text();box.className=res.ok?'alert alert-success':'alert alert-warning';box.textContent=text||(res.ok?'Update complete.':'Update finished with warnings.');if(res.ok)setTimeout(()=>window.location.reload(),800);}catch(e){box.className='alert alert-danger';box.textContent='Update failed. Please try again.'}finally{btn.disabled=false;btn.textContent='Update Website'}})}
</script>
</body></html>
