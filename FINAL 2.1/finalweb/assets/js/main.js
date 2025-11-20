document.addEventListener('DOMContentLoaded',function(){
  var counters=document.querySelectorAll('[data-countto]');
  counters.forEach(function(el){
    var target=parseInt(el.getAttribute('data-countto'),10)||0;
    var cur=0; var step=Math.ceil(target/60);
    var i=setInterval(function(){cur+=step;if(cur>=target){cur=target;clearInterval(i);} el.textContent=cur.toLocaleString();},20);
  });
  // simple cart using localStorage
  const CART_KEY='uhome_cart_v1';
  const getCart=()=>{try{return JSON.parse(localStorage.getItem(CART_KEY)||'[]');}catch(e){return []}};
  const setCart=(c)=>{localStorage.setItem(CART_KEY,JSON.stringify(c));updateBadge()};
  const updateBadge=()=>{
    const badge=document.getElementById('cart-count');
    if(!badge) return; const cart=getCart();
    const count=cart.reduce((a,b)=>a+(b.qty||1),0); badge.textContent=count;
  };
  updateBadge();
  // Enhanced add-to-cart functionality
  document.body.addEventListener('click', function(e) {
    const btn = e.target.closest('.add-to-cart');
    if (!btn) return;
    
    // Prevent double-clicks
    if (btn.disabled) return;
    btn.disabled = true;
    
    try {
      const id = parseInt(btn.dataset.id);
      const name = btn.dataset.name;
      const price = parseInt(btn.dataset.price);
      const image = btn.dataset.image;
      
      // Validate input data
      if (!id || !name || price < 0 || !image) {
        showNotification('Invalid product data. Please refresh the page.', 'danger');
        return;
      }
      
      let cart = getCart();
      
      // Check cart limits
      if (cart.length >= MAX_CART_ITEMS) {
        showNotification(`Cart is full. Maximum ${MAX_CART_ITEMS} different items allowed.`, 'warning');
        return;
      }
      
      const existingIndex = cart.findIndex(item => item.id === id);
      
      if (existingIndex > -1) {
        // Update existing item
        if (cart[existingIndex].qty >= MAX_ITEM_QTY) {
          showNotification(`Maximum quantity (${MAX_ITEM_QTY}) reached for this item.`, 'warning');
          return;
        }
        cart[existingIndex].qty = Math.min(cart[existingIndex].qty + 1, MAX_ITEM_QTY);
      } else {
        // Add new item
        cart.push({ id, name, price, image, qty: 1 });
      }
      
      if (setCart(cart)) {
        // Success feedback
        btn.classList.add('btn-success');
        btn.textContent = 'Added!';
        showNotification(`${name} added to cart`, 'success');
        
        setTimeout(() => {
          btn.classList.remove('btn-success');
          btn.textContent = 'Add';
        }, 1500);
      }
    } catch (error) {
      console.error('Add to cart error:', error);
      showNotification('Failed to add item to cart. Please try again.', 'danger');
    } finally {
      // Re-enable button after a short delay
      setTimeout(() => {
        btn.disabled = false;
      }, 500);
    }
  });
  // render cart page if container exists
  const cartTable=document.getElementById('cart-table');
  if(cartTable){
    const tbody=cartTable.querySelector('tbody');
    const totalEl=document.getElementById('cart-total');
    const checkoutBtn=document.getElementById('checkout-btn');
    const render=()=>{
      const cart=getCart();
      tbody.innerHTML=''; let total=0;
      cart.forEach((item,ix)=>{
        const line=item.price*(item.qty||1); total+=line;
        const tr=document.createElement('tr');
        tr.innerHTML=`<td><img src="${item.image}" alt="" style="width:48px;height:48px;object-fit:cover" class="me-2">${item.name}</td>
        <td>UGX ${item.price.toLocaleString()}</td>
        <td><input type="number" min="1" value="${item.qty||1}" data-ix="${ix}" class="form-control form-control-sm cart-qty" style="width:80px"></td>
        <td>UGX ${line.toLocaleString()}</td>
        <td><button class="btn btn-sm btn-outline-danger cart-remove" data-ix="${ix}">Remove</button></td>`;
        tbody.appendChild(tr);
      });
      totalEl.textContent='UGX '+total.toLocaleString();
      updateBadge();
      if(checkoutBtn){
        if(cart.length>0){checkoutBtn.classList.remove('disabled'); checkoutBtn.removeAttribute('aria-disabled'); checkoutBtn.href='checkout.php';}
        else {checkoutBtn.classList.add('disabled'); checkoutBtn.setAttribute('aria-disabled','true'); checkoutBtn.href='#';}
      }
    };
    tbody.addEventListener('change',function(e){
      const el=e.target.closest('.cart-qty'); if(!el) return; const ix=+el.dataset.ix; let cart=getCart(); cart[ix].qty=Math.max(1, +el.value||1); setCart(cart); render();
    });
    tbody.addEventListener('click',function(e){
      const btn=e.target.closest('.cart-remove'); if(!btn) return; const ix=+btn.dataset.ix; let cart=getCart(); cart.splice(ix,1); setCart(cart); render();
    });
    document.getElementById('cart-clear')?.addEventListener('click',()=>{setCart([]); render();});
    render();
  }
});
