u7php require_once __DIR__.'/includes/header.php'; ?>
<section class="py-5">
  <div class="container">
    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
      <img src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png" alt="Instagram" width="56" height="56" class="rounded-circle border">
      <div>
        <div class="h4 mb-1">@uhomeug</div>
        <div class="text-muted">Official Instagram of U Home Supermarkets • Central Region, Uganda</div>
        <div class="small">
          <a class="btn btn-sm btn-accent mt-2" target="_blank" href="https://www.instagram.com/uhomeug/?hl=en">Open on Instagram</a>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <?php
        $imgs = [
          'https://images.unsplash.com/photo-1516684732162-798a006b327d?q=80&w=1000&auto=format&fit=crop',
          'https://images.unsplash.com/photo-1514517220038-88e5b53896c5?q=80&w=1000&auto=format&fit=crop',
          'https://images.unsplash.com/photo-1493529940640-4b82cd4d3a0b?q=80&w=1000&auto=format&fit=crop',
          'https://images.unsplash.com/photo-1542831371-d531d36971e6?q=80&w=1000&auto=format&fit=crop',
          'https://images.unsplash.com/photo-1541976076758-347942db1970?q=80&w=1000&auto=format&fit=crop',
          'https://images.unsplash.com/photo-1600585154526-990dced4db0d?q=80&w=1000&auto=format&fit=crop',
          'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=1000&auto=format&fit=crop',
          'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?q=80&w=1000&auto=format&fit=crop',
          'https://images.unsplash.com/photo-1615486364135-496c6e3f92cd?q=80&w=1000&auto=format&fit=crop',
          'https://images.unsplash.com/photo-1617195737495-7a2ad5421d1a?q=80&w=1000&auto=format&fit=crop',
          'https://images.unsplash.com/photo-1586202690831-2fda2a5dc021?q=80&w=1000&auto=format&fit=crop',
          'https://images.unsplash.com/photo-1488477181946-6428a0291777?q=80&w=1000&auto=format&fit=crop',
        ];
        foreach($imgs as $src): ?>
      <div class="col-4 col-md-3 col-lg-2">
        <a href="https://www.instagram.com/uhomeug/?hl=en" target="_blank" class="d-block ratio ratio-1x1 rounded overflow-hidden shadow-sm">
          <img src="<?php echo $src; ?>" alt="Instagram post" class="w-100 h-100" style="object-fit:cover;">
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require_once __DIR__.'/includes/footer.php'; ?>
