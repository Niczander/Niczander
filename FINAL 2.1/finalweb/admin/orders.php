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
      case 'update_order_status':
        $st=$pdo->prepare('UPDATE orders SET status=? WHERE id=?');
        $st->execute([post('status'),post('id')]);
        header('Location: orders.php?msg='.urlencode('Order status updated'));
        exit;
    }
  }
}catch(Exception $e){ $msg='Error: '.$e->getMessage(); }
if(!$msg && isset($_GET['msg'])){ $msg=trim($_GET['msg']); }
$orders=$pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 100')->fetchAll();

require_once __DIR__.'/admin_header.php';
?>
<?php if($msg): ?><div class="alert alert-info py-2"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<div id="updateStatus" class="alert d-none" role="alert"></div>

<?php require_once __DIR__.'/admin_sidebar.php'; ?>
    <div class="card shadow-sm"><div class="card-body">
  <h6 class="card-title">Recent Orders</h6>
  <div class="table-responsive"><table class="table table-sm table-striped align-middle">
    <thead><tr><th>ID</th><th>Code</th><th>Customer</th><th>Phone</th><th>Method</th><th>Status</th><th>Total</th><th>Created</th></tr></thead><tbody>
    <?php foreach($orders as $o): ?>
    <tr>
      <td><?php echo $o['id']; ?></td>
      <td><?php echo htmlspecialchars($o['order_code']); ?></td>
      <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
      <td><?php echo htmlspecialchars($o['customer_phone']); ?></td>
      <td><?php echo htmlspecialchars($o['payment_method']); ?></td>
      <td>
        <form method="post" class="d-flex gap-1 align-items-center">
          <input type="hidden" name="action" value="update_order_status">
          <input type="hidden" name="id" value="<?php echo $o['id']; ?>">
          <select name="status" class="form-select form-select-sm">
            <?php foreach(['pending','awaiting_mobile_money','paid','failed','cancelled'] as $st): ?>
              <option value="<?php echo $st; ?>" <?php echo $o['status']===$st?'selected':''; ?>><?php echo $st; ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-sm btn-navy">Save</button>
        </form>
      </td>
      <td><?php echo number_format($o['total_amount']); ?></td>
      <td><?php echo htmlspecialchars($o['created_at']); ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody></table></div>
</div></div>
</div>

<?php require_once __DIR__.'/admin_sidebar_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const btn=document.getElementById('update-site-btn');const box=document.getElementById('updateStatus');
if(btn){btn.addEventListener('click',async()=>{btn.disabled=true;btn.textContent='Updating...';box.className='alert alert-info';box.textContent='Updating website content...';box.classList.remove('d-none');try{const res=await fetch('seed.php',{cache:'no-store'});const text=await res.text();box.className=res.ok?'alert alert-success':'alert alert-warning';box.textContent=text||(res.ok?'Update complete.':'Update finished with warnings.');if(res.ok)setTimeout(()=>window.location.reload(),800);}catch(e){box.className='alert alert-danger';box.textContent='Update failed. Please try again.'}finally{btn.disabled=false;btn.textContent='Update Website'}})}
</script>
</body></html>
