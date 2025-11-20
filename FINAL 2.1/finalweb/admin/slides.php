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
      case 'add_slide':
        // allow either direct URL or uploaded file
        $imageUrl = post('image_url');
        if(!empty($_FILES['image_file']['name'])){
          $dir = __DIR__.'/../assets/uploads';
          if(!is_dir($dir)) { @mkdir($dir, 0777, true); }
          $fname = $_FILES['image_file']['name'];
          $tmp = $_FILES['image_file']['tmp_name'];
          $size = (int)$_FILES['image_file']['size'];
          $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
          $allowed = ['jpg','jpeg','png','gif','webp'];
          if(in_array($ext,$allowed) && $size>0 && $size<=5*1024*1024){
            $new = 'slide_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
            if(move_uploaded_file($tmp, $dir.'/'.$new)){
              $imageUrl = BASE_URL.'/assets/uploads/'.$new;
            }
          }
        }
        $st=$pdo->prepare('INSERT INTO slides(title,subtitle,image_url) VALUES(?,?,?)');
        $st->execute([post('title'),post('subtitle'),$imageUrl]);
        header('Location: slides.php?msg='.urlencode('Slide added').'&autorun_update=1');
        exit;
      case 'delete_slide':
        $pdo->prepare('DELETE FROM slides WHERE id=?')->execute([post('id')]);
        header('Location: slides.php?msg='.urlencode('Slide deleted').'&autorun_update=1');
        exit;
    }
  }
}catch(Exception $e){ $msg='Error: '.$e->getMessage(); }

if(!$msg && isset($_GET['msg'])){ $msg=trim($_GET['msg']); }

$slides=$pdo->query('SELECT * FROM slides ORDER BY id DESC LIMIT 50')->fetchAll();

require_once __DIR__.'/admin_header.php';
?>
<?php if($msg): ?><div class="alert alert-info py-2"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<div id="updateStatus" class="alert d-none" role="alert"></div>

<?php require_once __DIR__.'/admin_sidebar.php'; ?>
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card shadow-sm"><div class="card-body">
        <h6 class="card-title">Add Slide</h6>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="action" value="add_slide">
          <div class="mb-2"><label class="form-label">Title</label><input name="title" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Subtitle</label><input name="subtitle" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Image URL</label><input name="image_url" class="form-control" placeholder="https://..."></div>
          <div class="mb-2"><label class="form-label">Or Upload Image</label><input type="file" name="image_file" accept="image/*" class="form-control"></div>
          <button class="btn btn-navy btn-sm">Save</button>
        </form>
      </div></div>
    </div>
    <div class="col-lg-8">
      <div class="card shadow-sm"><div class="card-body">
        <h6 class="card-title">Slides</h6>
        <div class="table-responsive">
          <table class="table table-sm table-striped align-middle">
            <thead><tr><th>ID</th><th>Title</th><th>Image</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach($slides as $s): ?>
              <tr>
                <td><?php echo $s['id']; ?></td>
                <td><?php echo htmlspecialchars($s['title']); ?></td>
                <td><img src="<?php echo htmlspecialchars($s['image_url']); ?>" style="height:40px"></td>
                <td class="d-flex gap-1">
                  <a class="btn btn-outline-primary btn-sm" href="edit_slide.php?id=<?php echo $s['id']; ?>">Edit</a>
                  <form method="post" onsubmit="return confirm('Delete slide?');" class="d-inline">
                    <input type="hidden" name="action" value="delete_slide">
                    <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                    <button class="btn btn-outline-danger btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div></div>
    </div>

<?php require_once __DIR__.'/admin_sidebar_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const btn = document.getElementById('update-site-btn');
  const statusBox = document.getElementById('updateStatus');
  if (btn) {
    btn.addEventListener('click', async () => {
      btn.disabled = true; btn.textContent = 'Updating...';
      statusBox.className = 'alert alert-info';
      statusBox.textContent = 'Updating website content...';
      statusBox.classList.remove('d-none');
      try {
        const res = await fetch('seed.php', { cache: 'no-store' });
        const text = await res.text();
        statusBox.className = res.ok ? 'alert alert-success' : 'alert alert-warning';
        statusBox.textContent = text || (res.ok ? 'Update complete.' : 'Update finished with warnings.');
        if (res.ok) setTimeout(()=>window.location.reload(), 800);
      } catch (e) {
        statusBox.className = 'alert alert-danger';
        statusBox.textContent = 'Update failed. Please try again.';
      } finally {
        btn.disabled = false; btn.textContent = 'Update Website';
      }
    });
  }
  // Auto-run update if requested via URL
  try {
    const p = new URLSearchParams(location.search);
    if (p.get('autorun_update') === '1' && btn) btn.click();
  } catch {}
</script>
</body>
</html>
