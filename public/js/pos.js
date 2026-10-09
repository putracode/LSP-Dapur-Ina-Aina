/**
 * POS (Point of Sale) Script - Dapur Ina Aina
 */

let cart = {};
let selectedMetode = 'Tunai';

function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

function getCheckoutUrl() {
  const posApp = document.getElementById('posContainer');
  return posApp?.dataset?.checkoutUrl || '/kasir/checkout';
}

function formatRupiah(amount) {
  return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function addToCart(id, name, price, stock) {
  if (stock <= 0) return;

  if (cart[id]) {
    if (cart[id].qty >= stock) {
      showAlert('Stok tidak mencukupi!', 'warning');
      return;
    }
    cart[id].qty++;
  } else {
    cart[id] = { id, name, price, stock, qty: 1 };
  }

  renderCart();
}

function removeFromCart(id) {
  delete cart[id];
  renderCart();
}

function updateQty(id, delta) {
  if (!cart[id]) return;

  cart[id].qty += delta;

  if (cart[id].qty <= 0) {
    delete cart[id];
  } else if (cart[id].qty > cart[id].stock) {
    cart[id].qty = cart[id].stock;
    showAlert('Stok maksimal: ' + cart[id].stock, 'warning');
  }

  renderCart();
}

function renderCart() {
  const items = Object.values(cart);
  const cartEmpty = document.getElementById('cartEmpty');
  const cartItems = document.getElementById('cartItems');
  const btnBayar = document.getElementById('btnBayar');
  const btnClear = document.getElementById('btnClear');

  if (items.length === 0) {
    if (cartEmpty) cartEmpty.style.display = '';
    if (cartItems) cartItems.innerHTML = '';
    const cartTotalEl = document.getElementById('cartTotal');
    if (cartTotalEl) cartTotalEl.textContent = 'Rp 0';
    if (btnBayar) btnBayar.disabled = true;
    if (btnClear) btnClear.disabled = true;
    return;
  }

  if (cartEmpty) cartEmpty.style.display = 'none';
  if (btnBayar) btnBayar.disabled = false;
  if (btnClear) btnClear.disabled = false;

  let total = 0;
  let html = '';

  items.forEach(item => {
    const subtotal = item.price * item.qty;
    total += subtotal;

    html += `
      <div class="cart-item">
        <div class="flex-grow-1 me-2">
          <div class="fw-semibold" style="font-size: 0.85rem;">${item.name}</div>
          <small class="text-muted">${formatRupiah(item.price)} × ${item.qty} = ${formatRupiah(subtotal)}</small>
        </div>
        <div class="qty-controls">
          <button class="btn btn-sm btn-outline-danger" onclick="updateQty(${item.id}, -1)">
            <i class="bi bi-dash"></i>
          </button>
          <span>${item.qty}</span>
          <button class="btn btn-sm btn-outline-primary" onclick="updateQty(${item.id}, 1)">
            <i class="bi bi-plus"></i>
          </button>
          <button class="btn btn-sm btn-outline-danger ms-1" onclick="removeFromCart(${item.id})" title="Hapus">
            <i class="bi bi-x"></i>
          </button>
        </div>
      </div>`;
  });

  if (cartItems) cartItems.innerHTML = html;
  const cartTotalEl = document.getElementById('cartTotal');
  if (cartTotalEl) cartTotalEl.textContent = formatRupiah(total);
}

function clearCart() {
  if (confirm('Yakin ingin mengosongkan keranjang?')) {
    cart = {};
    renderCart();
  }
}

function getCartTotal() {
  return Object.values(cart).reduce((sum, item) => sum + (item.price * item.qty), 0);
}

function showPaymentModal() {
  const total = getCartTotal();
  const modalTotal = document.getElementById('modalTotal');
  if (modalTotal) modalTotal.textContent = formatRupiah(total);
  const bayarInput = document.getElementById('jumlahBayar');
  if (bayarInput) bayarInput.value = '';
  const kembalianDisplay = document.getElementById('kembalianDisplay');
  if (kembalianDisplay) kembalianDisplay.textContent = 'Rp 0';
  setMetode('Tunai');
  const modalEl = document.getElementById('paymentModal');
  if (modalEl) {
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }
}

function setMetode(metode) {
  selectedMetode = metode;
  document.querySelectorAll('.metode-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.metode === metode);
    btn.classList.toggle('btn-outline-success', btn.dataset.metode === 'Tunai' && metode !== 'Tunai');
    btn.classList.toggle('btn-success', btn.dataset.metode === 'Tunai' && metode === 'Tunai');
    btn.classList.toggle('btn-outline-info', btn.dataset.metode === 'Non-Tunai' && metode !== 'Non-Tunai');
    btn.classList.toggle('btn-info', btn.dataset.metode === 'Non-Tunai' && metode === 'Non-Tunai');
  });
  const tunaiSection = document.getElementById('tunaiSection');
  if (tunaiSection) {
    tunaiSection.style.display = (metode === 'Tunai') ? '' : 'none';
  }
}

function calculateKembalian() {
  const total = getCartTotal();
  const bayar = parseInt(document.getElementById('jumlahBayar')?.value, 10) || 0;
  const kembalian = bayar - total;
  const display = document.getElementById('kembalianDisplay');
  if (display) {
    display.textContent = formatRupiah(Math.max(0, kembalian));
    display.className = kembalian >= 0 ? 'text-success' : 'text-danger';
  }
}

function setUangPas(amount) {
  const bayarInput = document.getElementById('jumlahBayar');
  if (bayarInput) {
    bayarInput.value = amount;
    calculateKembalian();
  }
}

function setUangPasExact() {
  const bayarInput = document.getElementById('jumlahBayar');
  if (bayarInput) {
    bayarInput.value = getCartTotal();
    calculateKembalian();
  }
}

async function processPayment() {
  const total = getCartTotal();
  const jumlahBayar = parseInt(document.getElementById('jumlahBayar')?.value, 10) || 0;

  if (selectedMetode === 'Tunai' && jumlahBayar < total) {
    showAlert('Jumlah bayar kurang dari total tagihan!', 'danger');
    return;
  }

  const items = Object.values(cart).map(item => ({
    id_menu: item.id,
    kuantitas: item.qty,
  }));

  const btnConfirm = document.getElementById('btnConfirmPay');
  if (btnConfirm) {
    btnConfirm.disabled = true;
    btnConfirm.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
  }

  try {
    const response = await fetch(getCheckoutUrl(), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        items,
        metode_pembayaran: selectedMetode,
        jumlah_bayar: selectedMetode === 'Tunai' ? jumlahBayar : total,
      }),
    });

    const data = await response.json();

    if (data.success) {
      const modalEl = document.getElementById('paymentModal');
      if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
      }
      showReceipt(data.data);
      updateStockDisplay(items);
    } else {
      showAlert(data.message, 'danger');
    }
  } catch (error) {
    showAlert('Terjadi kesalahan saat memproses pembayaran.', 'danger');
    console.error(error);
  } finally {
    if (btnConfirm) {
      btnConfirm.disabled = false;
      btnConfirm.innerHTML = '<i class="bi bi-check-circle me-1"></i> Konfirmasi Bayar';
    }
  }
}

function updateStockDisplay(items) {
  items.forEach(item => {
    const cards = document.querySelectorAll(`[data-menu-id="${item.id_menu}"]`);
    cards.forEach(card => {
      let currentStock = parseInt(card.dataset.stock, 10) - item.kuantitas;
      card.dataset.stock = currentStock;
      const badge = card.querySelector('.stock-badge');
      if (badge) {
        badge.textContent = 'Stok: ' + currentStock;
        if (currentStock <= 0) {
          card.classList.add('disabled');
          badge.className = 'stock-badge badge text-bg-danger';
        } else if (currentStock <= 5) {
          badge.className = 'stock-badge badge text-bg-warning';
        }
      }
    });
  });
}

function showReceipt(data) {
  const receiptHtml = `
    <div id="printableReceipt" style="font-family: monospace; font-size: 12px; max-width: 300px; margin: 0 auto;">
      <div style="text-align: center; margin-bottom: 10px;">
        <h4 style="margin: 0; font-size: 16px;">DAPUR INA AINA</h4>
        <p style="margin: 2px 0; font-size: 11px;">Sistem POS & Restoran</p>
        <hr style="border: 1px dashed #000;">
      </div>
      <div style="margin-bottom: 8px;">
        <table style="width: 100%; font-size: 11px;">
          <tr><td>No. Nota</td><td style="text-align:right">${data.no_nota}</td></tr>
          <tr><td>Tanggal</td><td style="text-align:right">${data.tanggal}</td></tr>
          <tr><td>Kasir</td><td style="text-align:right">${data.kasir}</td></tr>
        </table>
      </div>
      <hr style="border: 1px dashed #000;">
      <table style="width: 100%; font-size: 11px;">
        <thead>
          <tr><th style="text-align:left">Item</th><th style="text-align:center">Qty</th><th style="text-align:right">Subtotal</th></tr>
        </thead>
        <tbody>
          ${data.items.map(item => `
            <tr>
              <td>${item.nama_menu}</td>
              <td style="text-align:center">${item.kuantitas}</td>
              <td style="text-align:right">${formatRupiah(item.subtotal)}</td>
            </tr>
            <tr><td colspan="3" style="font-size:10px; color:#666;">@ ${formatRupiah(item.harga)}</td></tr>
          `).join('')}
        </tbody>
      </table>
      <hr style="border: 1px dashed #000;">
      <table style="width: 100%; font-size: 12px;">
        <tr><td><strong>TOTAL</strong></td><td style="text-align:right"><strong>${formatRupiah(data.total_tagihan)}</strong></td></tr>
        <tr><td>Metode</td><td style="text-align:right">${data.metode_pembayaran}</td></tr>
        <tr><td>Bayar</td><td style="text-align:right">${formatRupiah(data.jumlah_bayar)}</td></tr>
        <tr><td>Kembalian</td><td style="text-align:right">${formatRupiah(data.kembalian)}</td></tr>
      </table>
      <hr style="border: 1px dashed #000;">
      <div style="text-align: center; margin-top: 10px; font-size: 11px;">
        <p style="margin: 2px 0;">Terima Kasih</p>
        <p style="margin: 2px 0;">Selamat Menikmati!</p>
      </div>
    </div>`;

  const contentEl = document.getElementById('receiptContent');
  if (contentEl) contentEl.innerHTML = receiptHtml;
  const modalEl = document.getElementById('receiptModal');
  if (modalEl) {
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }
}

function printReceipt() {
  const content = document.getElementById('printableReceipt')?.innerHTML || '';
  const printWindow = window.open('', '_blank', 'width=350,height=600');
  if (!printWindow) {
    alert('Pop-up terblokir. Izinkan pop-up untuk mencetak struk.');
    return;
  }
  printWindow.document.write(
    '<!DOCTYPE html><html><head><title>Struk - Dapur Ina Aina</title>' +
    '<style>' +
    'body { font-family: monospace; font-size: 12px; padding: 10px; margin: 0; }' +
    'table { border-collapse: collapse; }' +
    '@media print { @page { size: 80mm auto; margin: 5mm; } }' +
    '</style></head><body>' +
    content +
    '</body></html>'
  );
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 250);
}

function resetAfterReceipt() {
  cart = {};
  renderCart();
}

function showAlert(message, type) {
  const wrapper = document.createElement('div');
  wrapper.innerHTML = `
    <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 9999;">
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
  document.body.appendChild(wrapper);
  setTimeout(() => wrapper.remove(), 3000);
}
