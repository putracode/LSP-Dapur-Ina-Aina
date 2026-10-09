<div class="pos-container" x-data="{
    cartOpen: @entangle('isCartOpen'),
    toasts: [],
    addToast(msg, type = 'info') {
      const id = Date.now();
      this.toasts.push({ id, msg, type });
      setTimeout(() => {
        this.toasts = this.toasts.filter(t => t.id !== id);
      }, 3000);
    }
}" x-on:pos-toast.window="addToast($event.detail.message, $event.detail.type)">

  {{-- In-App Floating Toasts --}}
  <div class="pos-toast-container">
    <template x-for="t in toasts" :key="t.id">
      <div :class="'pos-toast pos-toast-' + t.type">
        <i :class="{
          'bi bi-check-circle-fill text-success': t.type === 'success',
          'bi bi-exclamation-triangle-fill text-warning': t.type === 'warning',
          'bi bi-x-circle-fill text-danger': t.type === 'danger',
          'bi bi-info-circle-fill text-primary': t.type === 'info'
        }"></i>
        <span x-text="t.msg"></span>
      </div>
    </template>
  </div>

  {{-- TOP BAR: Search & Category Chips & Cart Trigger --}}
  <div class="pos-topbar card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
      <div class="row g-3 align-items-center">
        
        {{-- Search Bar --}}
        <div class="col-12 col-md-5 col-lg-4">
          <div class="pos-search-box">
            <i class="bi bi-search pos-search-icon"></i>
            <input
              type="text"
              class="form-control pos-search-input"
              placeholder="Cari makanan atau minuman..."
              wire:model.live.debounce.250ms="search"
            >
            @if(!empty($search))
              <button class="btn btn-sm pos-search-clear" wire:click="$set('search', '')" type="button">
                <i class="bi bi-x-lg"></i>
              </button>
            @endif
          </div>
        </div>

        {{-- Category Pills --}}
        <div class="col-12 col-md-7 col-lg-5">
          <div class="pos-category-scroll">
            <button
              type="button"
              class="pos-cat-pill {{ $selectedKategoriId === 0 ? 'active' : '' }}"
              wire:click="selectCategory(0)"
            >
              <i class="bi bi-grid-fill me-1"></i> Semua
            </button>
            @foreach($kategoris as $kategori)
              <button
                type="button"
                class="pos-cat-pill {{ $selectedKategoriId === $kategori->id_kategori ? 'active' : '' }}"
                wire:click="selectCategory({{ $kategori->id_kategori }})"
              >
                {{ $kategori->nama_kategori }}
                <span class="pos-cat-count">{{ $kategori->menu_count }}</span>
              </button>
            @endforeach
          </div>
        </div>

        {{-- Cart Icon Button Header --}}
        <div class="col-12 col-lg-3 text-lg-end">
          <button
            type="button"
            class="pos-cart-toggle-btn {{ $cartCount > 0 ? 'has-items' : '' }}"
            wire:click="toggleCart"
          >
            <div class="pos-cart-icon-wrap">
              <i class="bi bi-bag-check-fill"></i>
              @if($cartCount > 0)
                <span class="pos-cart-badge">{{ $cartCount }}</span>
              @endif
            </div>
            <div class="pos-cart-toggle-info">
              <div class="pos-cart-toggle-label">Keranjang</div>
              <div class="pos-cart-toggle-total">Rp {{ number_format($cartTotal, 0, ',', '.') }}</div>
            </div>
            <i class="bi bi-chevron-right pos-cart-toggle-arrow"></i>
          </button>
        </div>

      </div>
    </div>
  </div>

  {{-- MAIN CONTENT AREA --}}
  <div class="row g-4">

    {{-- MENU GRID AREA (FULL WIDTH) --}}
    <div class="col-12 pos-grid-column">
      
      {{-- Active filter indicators --}}
      <div class="d-flex align-items-center justify-content-between mb-3 px-1">
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted small">Menampilkan:</span>
          <span class="badge text-black py-1">
            {{ $menus->count() }} Menu Tersedia
          </span>
          @if(!empty($search))
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">
              "{{ $search }}"
            </span>
          @endif
        </div>
      </div>

      {{-- Grid Cards --}}
      <div class="row g-3">
        @forelse($menus as $menu)
          @php
            $key = (string) $menu->id_menu;
            $qtyInCart = $cart[$key]['qty'] ?? 0;
            $isOutOfStock = $menu->stok <= 0;
          @endphp

          <div class="col-6 col-md-4 col-lg-3">
            <div
              class="pos-menu-card card h-100 {{ $isOutOfStock ? 'is-disabled' : '' }} {{ $qtyInCart > 0 ? 'is-in-cart' : '' }}"
              wire:click="{{ !$isOutOfStock ? 'addToCart(' . $menu->id_menu . ')' : '' }}"
            >
              {{-- Card Media --}}
              <div class="pos-menu-media">
                @if($menu->foto)
                  <img src="{{ asset('storage/' . $menu->foto) }}" alt="{{ $menu->nama_menu }}" class="pos-menu-img" loading="lazy">
                @else
                  <div class="pos-menu-placeholder">
                    <i class="bi bi-cup-hot-fill"></i>
                  </div>
                @endif

                {{-- Stock Badge --}}
                <span class="pos-stock-pill {{ $isOutOfStock ? 'stock-empty' : ($menu->stok <= 5 ? 'stock-low' : 'stock-ok') }}">
                  @if($isOutOfStock)
                    Habis
                  @else
                    Stok {{ $menu->stok }}
                  @endif
                </span>

                {{-- In-cart Badge overlay --}}
                @if($qtyInCart > 0)
                  <span class="pos-card-cart-qty">
                    <i class="bi bi-check-lg"></i> {{ $qtyInCart }}x
                  </span>
                @endif
              </div>

              {{-- Card Info --}}
              <div class="card-body p-2 d-flex flex-column justify-content-between">
                <div>
                  <span class="pos-menu-category">{{ $menu->kategori->nama_kategori ?? 'Umum' }}</span>
                  <h6 class="pos-menu-title mb-1" title="{{ $menu->nama_menu }}">{{ $menu->nama_menu }}</h6>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-light-subtle">
                  <div class="pos-menu-price text-black">
                    Rp {{ number_format($menu->harga, 0, ',', '.') }}
                  </div>
                  @if(!$isOutOfStock)
                    <button
                      type="button"
                      class="pos-add-btn"
                      wire:click.stop="addToCart({{ $menu->id_menu }})"
                      title="Tambah ke Keranjang"
                    >
                      <i class="bi bi-plus"></i>
                    </button>
                  @endif
                </div>
              </div>

            </div>
          </div>
        @empty
          <div class="col-12 py-5 text-center">
            <div class="pos-empty-grid">
              <i class="bi bi-search display-3 text-muted mb-3 d-block"></i>
              <h5 class="fw-bold">Tidak ada menu yang cocok</h5>
              <p class="text-muted small">Coba cari dengan kata kunci lain atau ubah filter kategori.</p>
              @if(!empty($search) || $selectedKategoriId > 0)
                <button
                  type="button"
                  class="btn btn-sm btn-outline-primary rounded-pill px-3 mt-2"
                  wire:click="$set('search', ''); $set('selectedKategoriId', 0);"
                >
                  <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Pencarian
                </button>
              @endif
            </div>
          </div>
        @endforelse
      </div>

  </div>

  {{-- CART MODAL (teleported to body to escape stacking context) --}}
  @if($isCartOpen)
    <template x-teleport="body">
      <div>
        <div class="pos-modal-backdrop" wire:click="closeCart"></div>
        <div class="pos-modal-dialog pos-cart-modal-dialog">
          <div class="pos-modal-content card border-0 shadow-lg">
            
            {{-- Cart Modal Header --}}
            <div class="pos-modal-header p-3 border-bottom d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <div class="pos-modal-icon-badge bg-primary text-white">
                  <i class="bi bi-bag-heart-fill"></i>
                </div>
                <div>
                  <h5 class="mb-0 fw-bold">Keranjang Pesanan</h5>
                  <small class="text-muted">{{ $cartCount }} item dipilih</small>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                @if(count($cart) > 0)
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1"
                    wire:click="clearCart"
                    wire:confirm="Yakin ingin mengosongkan keranjang?"
                    title="Kosongkan Keranjang"
                  >
                    <i class="bi bi-trash3 me-1"></i> Kosongkan
                  </button>
                @endif
                <button
                  type="button"
                  class="btn btn-sm btn-light rounded-circle"
                  wire:click="closeCart"
                  title="Tutup Modal"
                >
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
            </div>

            {{-- Cart Modal Body --}}
            <div class="pos-modal-body p-3 pos-cart-modal-body">
              @if(empty($cart))
                <div class="pos-cart-empty text-center py-5">
                  <div class="pos-cart-empty-circle mb-3">
                    <i class="bi bi-cart-x"></i>
                  </div>
                  <h6 class="fw-bold mb-1">Keranjang Masih Kosong</h6>
                  <p class="text-muted small mb-3">Pilih menu dari daftar untuk menambahkan pesanan.</p>
                  <button type="button" class="btn btn-sm btn-primary rounded-pill px-4" wire:click="closeCart">
                    <i class="bi bi-grid-fill me-1"></i> Lihat Menu
                  </button>
                </div>
              @else
                <div class="pos-cart-items-list">
                  @foreach($cart as $item)
                    <div class="pos-cart-item card mb-2 border-0 bg-light-subtle shadow-none">
                      <div class="card-body p-2 d-flex align-items-center gap-2">
                        
                        {{-- Thumbnail --}}
                        <div class="pos-item-thumb">
                          @if($item['foto'])
                            <img src="{{ asset('storage/' . $item['foto']) }}" alt="{{ $item['nama'] }}">
                          @else
                            <i class="bi bi-cup-hot"></i>
                          @endif
                        </div>

                        {{-- Item Info --}}
                        <div class="flex-grow-1 min-w-0">
                          <div class="pos-item-title text-truncate fw-semibold" title="{{ $item['nama'] }}">
                            {{ $item['nama'] }}
                          </div>
                          <div class="pos-item-price small text-muted">
                            Rp {{ number_format($item['harga'], 0, ',', '.') }}
                          </div>
                        </div>

                        {{-- Quantity Controls --}}
                        <div class="pos-qty-stepper">
                          <button
                            type="button"
                            class="pos-stepper-btn"
                            wire:click="updateQty({{ $item['id'] }}, -1)"
                          >
                            <i class="bi bi-dash"></i>
                          </button>
                          <span class="pos-stepper-val">{{ $item['qty'] }}</span>
                          <button
                            type="button"
                            class="pos-stepper-btn"
                            wire:click="updateQty({{ $item['id'] }}, 1)"
                            {{ $item['qty'] >= $item['stok'] ? 'disabled' : '' }}
                          >
                            <i class="bi bi-plus"></i>
                          </button>
                        </div>

                        {{-- Subtotal & Remove --}}
                        <div class="text-end ms-1">
                          <div class="pos-item-subtotal fw-bold text-primary">
                            Rp {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}
                          </div>
                          <button
                            type="button"
                            class="btn btn-link btn-sm text-danger p-0 text-decoration-none"
                            wire:click="removeFromCart({{ $item['id'] }})"
                            title="Hapus menu"
                          >
                            <i class="bi bi-x-circle"></i>
                          </button>
                        </div>

                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>

            {{-- Cart Modal Footer --}}
            <div class="pos-modal-footer p-3 border-top bg-white d-block">
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small">Total Kuantitas</span>
                <span class="fw-semibold small">{{ $cartCount }} item</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold fs-6">Total Tagihan</span>
                <span class="fw-bold fs-4 text-primary">
                  Rp {{ number_format($cartTotal, 0, ',', '.') }}
                </span>
              </div>

              <div class="d-flex gap-2">
                <button
                  type="button"
                  class="btn btn-outline-secondary rounded-pill px-4"
                  wire:click="closeCart"
                >
                  Tutup
                </button>
                <button
                  type="button"
                  class="btn btn-primary flex-grow-1 pos-checkout-btn py-2 rounded-pill fw-bold shadow-sm"
                  wire:click="openPaymentModal"
                  {{ empty($cart) ? 'disabled' : '' }}
                >
                  <i class="bi bi-credit-card-2-front-fill me-2"></i> Lanjut ke Pembayaran
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </template>
  @endif



  {{-- PAYMENT MODAL (teleported to body) --}}
  @if($showPaymentModal)
    <template x-teleport="body">
      <div>
        <div class="pos-modal-backdrop" wire:click="closePaymentModal"></div>
        <div class="pos-modal-dialog">
          <div class="pos-modal-content card border-0 shadow-lg">

            {{-- Modal Header --}}
            <div class="pos-modal-header p-3 border-bottom d-flex align-items-center justify-content-between flex-shrink-0">
              <div class="d-flex align-items-center gap-2">
                <div class="pos-modal-icon-badge bg-primary text-white">
                  <i class="bi bi-wallet2"></i>
                </div>
                <div>
                  <h5 class="mb-0 fw-bold">Konfirmasi Pembayaran</h5>
                  <small class="text-muted">Pilih metode bayar dan masukkan nominal</small>
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-light rounded-circle" wire:click="closePaymentModal">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>

            {{-- Modal Body (scrollable) --}}
            <div class="pos-modal-body p-4" style="overflow-y: auto; max-height: 70vh;">

              {{-- Total Hero Banner --}}
              <div class="pos-total-hero text-center p-3 mb-4 rounded-4">
                <span class="text-uppercase small text-muted fw-bold">Total Tagihan</span>
                <h2 class="display-6 fw-bold text-primary mb-0">
                  Rp {{ number_format($cartTotal, 0, ',', '.') }}
                </h2>
                <small class="text-muted">{{ $cartCount }} Item Pesanan</small>
              </div>

              {{-- Payment Method Selection --}}
              <label class="form-label fw-bold small text-muted text-uppercase mb-2">Pilih Metode Pembayaran</label>
              <div class="row g-2 mb-4">
                <div class="col-6">
                  <div
                    class="pos-method-card {{ $selectedMetode === 'Tunai' ? 'active' : '' }}"
                    wire:click="setMetode('Tunai')"
                  >
                    <i class="bi bi-cash-stack fs-3 mb-1"></i>
                    <span class="fw-bold">Tunai (Cash)</span>
                    <small class="text-muted">Bayar uang tunai</small>
                  </div>
                </div>
                <div class="col-6">
                  <div
                    class="pos-method-card {{ $selectedMetode === 'Non-Tunai' ? 'active' : '' }}"
                    wire:click="setMetode('Non-Tunai')"
                  >
                    <i class="bi bi-qr-code-scan fs-3 mb-1"></i>
                    <span class="fw-bold">Non-Tunai</span>
                    <small class="text-muted">QRIS / Debit / Transfer</small>
                  </div>
                </div>
              </div>

              {{-- Cash Payment Inputs --}}
              @if($selectedMetode === 'Tunai')
                <div class="mb-4">
                  <label class="form-label fw-bold small text-muted text-uppercase mb-1">Uang Diterima (Rp)</label>
                  <div class="input-group input-group-lg mb-2">
                    <span class="input-group-text bg-white border-end-0 fw-bold text-muted">Rp</span>
                    <input
                      type="number"
                      class="form-control border-start-0 ps-0 fw-bold fs-4 text-end"
                      placeholder="0"
                      wire:model.live="jumlahBayar"
                      min="0"
                      step="1000"
                      autofocus
                    >
                  </div>

                  {{-- Quick cash buttons --}}
                  <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" wire:click="setUangPas({{ $cartTotal }})">
                      <i class="bi bi-check2-circle me-1"></i> Uang Pas
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" wire:click="setUangPas(10000)">10K</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" wire:click="setUangPas(20000)">20K</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" wire:click="setUangPas(50000)">50K</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" wire:click="setUangPas(100000)">100K</button>
                  </div>
                </div>

                {{-- Change display --}}
                <div class="pos-kembalian-box p-3 rounded-3 d-flex justify-content-between align-items-center mb-2 {{ $jumlahBayar >= $cartTotal ? 'bg-success-subtle border border-success-subtle text-success' : 'bg-light text-muted' }}">
                  <div>
                    <span class="fw-semibold d-block">Kembalian:</span>
                    <small class="{{ $jumlahBayar < $cartTotal ? 'text-danger' : 'text-success' }}">
                      {{ $jumlahBayar < $cartTotal ? 'Uang bayar masih kurang' : 'Kembalian yang harus diserahkan' }}
                    </small>
                  </div>
                  <span class="fs-4 fw-bold">
                    Rp {{ number_format($kembalian, 0, ',', '.') }}
                  </span>
                </div>
              @else
                <div class="pos-nontunai-notice p-3 rounded-3 bg-primary-subtle text-primary border border-primary-subtle text-center mb-3">
                  <i class="bi bi-info-circle-fill me-1"></i>
                  Transaksi non-tunai diselesaikan otomatis sesuai total tagihan <strong>Rp {{ number_format($cartTotal, 0, ',', '.') }}</strong>.
                </div>
              @endif

            </div>

            {{-- Modal Footer --}}
            <div class="pos-modal-footer p-3 border-top d-flex gap-2 justify-content-end bg-light-subtle flex-shrink-0">
              <button type="button" class="btn btn-outline-secondary rounded-pill px-4" wire:click="closePaymentModal">
                Batal
              </button>
              <button
                type="button"
                class="btn btn-success rounded-pill px-4 fw-bold shadow-sm"
                wire:click="processPayment"
                wire:loading.attr="disabled"
                {{ ($selectedMetode === 'Tunai' && $jumlahBayar < $cartTotal) ? 'disabled' : '' }}
              >
                <span wire:loading.remove wire:target="processPayment">
                  <i class="bi bi-printer-fill me-1"></i> Simpan & Cetak Struk
                </span>
                <span wire:loading wire:target="processPayment">
                  <span class="spinner-border spinner-border-sm me-1" role="status"></span> Memproses...
                </span>
              </button>
            </div>

          </div>
        </div>
      </div>
    </template>
  @endif

  {{-- RECEIPT MODAL (THERMAL STYLE) --}}
  @if($showReceiptModal && !empty($receiptData))
    <template x-teleport="body">
      <div>
        <div class="pos-modal-backdrop" wire:click="closeReceiptModal"></div>
        <div class="pos-modal-dialog pos-receipt-dialog">
          <div class="pos-modal-content card border-0 shadow-lg">
            
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-bold"><i class="bi bi-receipt me-2 text-success"></i>Struk Transaksi Berhasil</h6>
              <button type="button" class="btn btn-sm btn-light rounded-circle" wire:click="closeReceiptModal">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>

            <div class="pos-receipt-wrapper p-4 bg-light">
              <div class="pos-receipt-paper" id="printableReceipt">
                
                {{-- Receipt Header --}}
                <div class="text-center mb-3">
                  <h5 class="fw-bold mb-0">DAPUR INA AINA</h5>
                  <div class="small text-muted">Sistem Informasi POS & Restoran</div>
                  <div class="small text-muted">Jl. Raya Kuliner No. 12</div>
                  <div class="small text-muted">Telp: 0812-3456-7890</div>
                  <div class="pos-receipt-divider my-2"></div>
                </div>

                {{-- Metadata --}}
                <div class="small mb-2">
                  <div class="d-flex justify-content-between">
                    <span>No. Nota:</span>
                    <span class="fw-bold">{{ $receiptData['no_nota'] ?? '-' }}</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span>Tanggal:</span>
                    <span>{{ $receiptData['tanggal'] ?? '-' }}</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span>Kasir:</span>
                    <span>{{ $receiptData['kasir'] ?? '-' }}</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span>Status:</span>
                    <span class="fw-bold text-success">LUNAS</span>
                  </div>
                  <div class="pos-receipt-divider my-2"></div>
                </div>

                {{-- Items --}}
                <div class="small mb-2">
                  @foreach($receiptData['items'] as $item)
                    <div class="pos-receipt-item mb-2">
                      <div class="fw-bold">{{ $item['nama_menu'] }}</div>
                      <div class="d-flex justify-content-between text-muted">
                        <span>{{ $item['kuantitas'] }}x @ Rp {{ number_format($item['harga'], 0, ',', '.') }}</span>
                        <span class="fw-bold text-dark">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                      </div>
                    </div>
                  @endforeach
                  <div class="pos-receipt-divider my-2"></div>
                </div>

                {{-- Totals --}}
                <div class="small">
                  <div class="d-flex justify-content-between fw-bold mb-1" style="font-size: 13px;">
                    <span>TOTAL TAGIHAN:</span>
                    <span>Rp {{ number_format($receiptData['total_tagihan'], 0, ',', '.') }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-1">
                    <span>Metode Bayar:</span>
                    <span class="fw-bold">{{ $receiptData['metode_pembayaran'] }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-1">
                    <span>Jumlah Bayar:</span>
                    <span>Rp {{ number_format($receiptData['jumlah_bayar'], 0, ',', '.') }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-1">
                    <span>Kembalian:</span>
                    <span class="fw-bold text-success">Rp {{ number_format($receiptData['kembalian'], 0, ',', '.') }}</span>
                  </div>
                  <div class="pos-receipt-divider my-2"></div>
                </div>

                {{-- Receipt Footer --}}
                <div class="text-center small text-muted mt-3">
                  <p class="mb-0 fw-bold">Terima kasih atas kunjungan Anda!</p>
                  <p class="mb-0">Selamat menikmati hidangan kami</p>
                </div>

              </div>
            </div>

            <div class="p-3 border-top d-flex flex-wrap gap-2 justify-content-end bg-white">
              @if(!empty($receiptData['id_transaksi']))
                <a href="{{ route('kasir.receipt', $receiptData['id_transaksi']) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                  <i class="bi bi-box-arrow-up-right me-1"></i> Buka Tab Struk
                </a>
              @endif
              <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" wire:click="closeReceiptModal">
                <i class="bi bi-plus-circle me-1"></i> Transaksi Baru
              </button>
            </div>

          </div>
        </div>
      </div>
    </template>
  @endif

</div>

@push('styles')
<style>
/* POS Global Styles */
.pos-container {
  position: relative;
  min-height: calc(100vh - 180px);
}

/* Toast notifications */
.pos-toast-container {
  position: fixed;
  top: 75px;
  right: 25px;
  z-index: 1060;
  display: flex;
  flex-direction: column;
  gap: 8px;
  pointer-events: none;
}
.pos-toast {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #ffffff;
  padding: 10px 18px;
  border-radius: 50px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
  font-size: 0.9rem;
  font-weight: 500;
  color: #333;
  animation: posSlideIn 0.3s ease;
  pointer-events: auto;
}
@keyframes posSlideIn {
  from { opacity: 0; transform: translateY(-10px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Topbar */
.pos-topbar {
  border-radius: 16px;
  background: #ffffff;
}
.pos-search-box {
  position: relative;
}
.pos-search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 1rem;
}
.pos-search-input {
  border-radius: 50px;
  padding-left: 42px;
  padding-right: 36px;
  height: 44px;
  border: 1px solid #e2e8f0;
  background-color: #f8fafc;
  transition: all 0.2s;
}
.pos-search-input:focus {
  background-color: #ffffff;
  border-color: #0d6efd;
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
}
.pos-search-clear {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  padding: 0;
  background: none;
  border: none;
}

/* Category Pills */
.pos-category-scroll {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 4px;
  scrollbar-width: thin;
}
.pos-cat-pill {
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #475569;
  padding: 8px 18px;
  border-radius: 50px;
  font-size: 0.88rem;
  font-weight: 600;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.pos-cat-pill:hover {
  background: #e2e8f0;
  color: #1e293b;
}
.pos-cat-pill.active {
  background: linear-gradient(135deg, #0d6efd, #0b5ed7);
  border-color: #0d6efd;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}
.pos-cat-count {
  background: rgba(0, 0, 0, 0.08);
  font-size: 0.72rem;
  padding: 2px 7px;
  border-radius: 50px;
}
.pos-cat-pill.active .pos-cat-count {
  background: rgba(255, 255, 255, 0.25);
  color: #ffffff;
}

/* Header Cart Toggle Button */
.pos-cart-toggle-btn {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 50px;
  padding: 6px 16px 6px 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.pos-cart-toggle-btn:hover, .pos-cart-toggle-btn.has-items {
  background: #e0f2fe;
  border-color: #bae6fd;
}
.pos-cart-icon-wrap {
  position: relative;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #0d6efd;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}
.pos-cart-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #ef4444;
  color: #fff;
  font-size: 0.7rem;
  font-weight: 700;
  border-radius: 50px;
  min-width: 18px;
  height: 18px;
  padding: 0 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #fff;
}
.pos-cart-toggle-info {
  text-align: left;
  line-height: 1.2;
}
.pos-cart-toggle-label {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
}
.pos-cart-toggle-total {
  font-size: 0.95rem;
  font-weight: 700;
  color: #0f172a;
}
.pos-cart-toggle-arrow {
  color: #94a3b8;
  font-size: 0.9rem;
}

/* Menu Card */
.pos-menu-card {
  border-radius: 16px;
  border: 1px solid #f1f5f9;
  background: #ffffff;
  overflow: hidden;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  cursor: pointer;
  user-select: none;
}
.pos-menu-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.12);
  border-color: #cbd5e1;
}
.pos-menu-card.is-in-cart {
  border-color: #3b82f6;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.15);
}
.pos-menu-card.is-disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}
.pos-menu-media {
  position: relative;
  width: 100%;
  aspect-ratio: 4 / 3;
  background: #f8fafc;
  overflow: hidden;
}
.pos-menu-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.35s ease;
}
.pos-menu-card:hover .pos-menu-img {
  transform: scale(1.06);
}
.pos-menu-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
  color: #94a3b8;
  font-size: 2.5rem;
}
.pos-stock-pill {
  position: absolute;
  top: 8px;
  left: 8px;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 50px;
  backdrop-filter: blur(4px);
}
.stock-ok {
  background: rgba(16, 185, 129, 0.9);
  color: #ffffff;
}
.stock-low {
  background: rgba(245, 158, 11, 0.9);
  color: #ffffff;
}
.stock-empty {
  background: rgba(239, 68, 68, 0.9);
  color: #ffffff;
}
.pos-card-cart-qty {
  position: absolute;
  top: 8px;
  right: 8px;
  background: #0d6efd;
  color: #ffffff;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 50px;
  box-shadow: 0 4px 10px rgba(13, 110, 253, 0.4);
}
.pos-menu-category {
  font-size: 0.72rem;
  color: #94a3b8;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.5px;
}
.pos-menu-title {
  font-size: 0.92rem;
  font-weight: 600;
  color: #1e293b;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.pos-menu-price {
  font-size: 0.92rem;
  font-weight: 700;
  color: #0d6efd;
}
.pos-add-btn {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #e0f2fe;
  color: #0284c7;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  transition: all 0.2s;
}
.pos-add-btn:hover {
  background: #0284c7;
  color: #ffffff;
  transform: scale(1.1);
}

/* Cart Panel */
.pos-cart-panel {
  border-radius: 20px;
  overflow: hidden;
  position: sticky;
  top: 20px;
  background: #ffffff;
}
.pos-cart-header {
  border-bottom: 1px solid #f1f5f9;
  background: #ffffff;
}
.pos-cart-icon-circle {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  background: #e0f2fe;
  color: #0284c7;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}
.pos-cart-body {
  max-height: 420px;
  overflow-y: auto;
}
.pos-cart-empty-circle {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: #f8fafc;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #cbd5e1;
}
.pos-item-thumb {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  overflow: hidden;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.pos-item-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.pos-qty-stepper {
  display: flex;
  align-items: center;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 50px;
  padding: 2px 4px;
}
.pos-stepper-btn {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: none;
  background: #f1f5f9;
  color: #334155;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  transition: all 0.15s;
}
.pos-stepper-btn:hover:not(:disabled) {
  background: #0d6efd;
  color: #ffffff;
}
.pos-stepper-val {
  min-width: 24px;
  text-align: center;
  font-weight: 700;
  font-size: 0.85rem;
}
.pos-checkout-btn {
  background: linear-gradient(135deg, #0d6efd, #0284c7);
  border: none;
  font-size: 1.05rem;
  transition: all 0.25s;
}
.pos-checkout-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #0b5ed7, #0369a1);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(13, 110, 253, 0.35) !important;
}



/* Modals — rendered as direct children of <body> via x-teleport */
.pos-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  z-index: 99990;
}
.pos-modal-dialog {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 95%;
  max-width: 520px;
  z-index: 99995;
  animation: posModalScale 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.pos-receipt-dialog {
  max-width: 420px;
}
.pos-cart-modal-dialog {
  max-width: 560px;
}
.pos-cart-modal-body {
  max-height: 55vh;
  overflow-y: auto;
}
@keyframes posModalScale {
  from { opacity: 0; transform: translate(-50%, -46%) scale(0.95); }
  to   { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}
.pos-modal-content {
  border-radius: 20px;
  overflow: hidden;
}
.pos-modal-icon-badge {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}
.pos-total-hero {
  background: linear-gradient(135deg, #eff6ff, #dbeafe);
  border: 1px solid #bfdbfe;
}
.pos-method-card {
  border: 2px solid #e2e8f0;
  border-radius: 16px;
  padding: 14px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.pos-method-card:hover {
  border-color: #94a3b8;
  background: #f8fafc;
}
.pos-method-card.active {
  border-color: #0d6efd;
  background: #eff6ff;
  color: #0d6efd;
}

/* Thermal Receipt Paper — on screen */
.pos-receipt-wrapper {
  overflow-y: auto;
  max-height: 65vh;
}
.pos-receipt-paper {
  background: #ffffff;
  padding: 24px 20px;
  border-radius: 8px;
  font-family: 'Courier New', Courier, monospace;
  font-size: 13px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.06);
  border: 1px dashed #cbd5e1;
  width: 100%;
  max-width: 340px;
  margin: 0 auto;
}
.pos-receipt-divider {
  border-top: 1px dashed #000;
  margin: 6px 0;
  display: block;
  width: 100%;
  height: 1px;
}
</style>
@endpush
