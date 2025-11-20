<?php require_once __DIR__.'/includes/header.php'; ?>
<section class="py-5">
  <div class="container">
    <h3 class="mb-4">Your Cart</h3>
    <div class="table-responsive">
      <table class="table align-middle" id="cart-table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Price</th>
            <th style="width:120px">Qty</th>
            <th>Subtotal</th>
            <th></th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">Total</th>
            <th id="cart-total">UGX 0</th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
    <div class="d-flex justify-content-between">
      <button class="btn btn-outline-danger" id="cart-clear">Clear Cart</button>
      <a href="#" id="checkout-btn" class="btn btn-accent disabled" aria-disabled="true">Checkout</a>
    </div>
  </div>
</section>
<?php require_once __DIR__.'/includes/footer.php'; ?>
