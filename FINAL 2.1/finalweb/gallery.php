<?php require_once __DIR__.'/includes/header.php'; ?>
<?php
// Load all site data from the JSON file
$data_file = __DIR__.'/includes/site_data.json';
$data = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];

$videos = array_filter($data['slides'] ?? [], function($s) {
    return !empty($s['video_url']);
});
$videos = array_slice($videos, 0, 2);

$images = array_slice($data['posts'] ?? [], 0, 8);
?>
<section class="py-5">
  <div class="container">
    <h3 class="mb-4">Gallery</h3>
    <?php if (!empty($videos)): ?>
    <div class="row g-4 mb-4">
      <?php foreach($videos as $v): ?>
      <div class="col-md-6">
        <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
          <iframe src="<?php echo htmlspecialchars($v['video_url']); ?>" title="<?php echo htmlspecialchars($v['title']); ?>" allowfullscreen></iframe>
        </div>
        <div class="small text-muted mt-2"><?php echo htmlspecialchars($v['subtitle']); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($images)): ?>
    <div class="row g-4">
      <?php foreach($images as $img): ?>
      <div class="col-6 col-md-3">
        <img src="<?php echo htmlspecialchars($img['image_url']); ?>" class="img-fluid rounded shadow-sm" alt="<?php echo htmlspecialchars($img['title']); ?>">
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__.'/includes/footer.php'; ?>
