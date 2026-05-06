<?php
/** @var array $categories */
/** @var string $base_url */
require_once __DIR__ . '/../layouts/header.php';

$categories = $categories ?? [];
$base_url = $base_url ?? '';
?>

<div x-data="posApp()" x-init="init()" class="flex min-h-screen bg-background" :class="{ 'dark': darkMode }">
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 ml-20 md:ml-64 flex flex-col md:flex-row h-screen overflow-hidden">
        
        <!-- Left: Item Grid -->
        <div class="flex-1 flex flex-col h-full border-r border-border overflow-hidden">
            <!-- Header/Filters -->
            <div class="p-4 md:p-6 bg-surface border-b border-border">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-primary">Shop Items</h2>
                        <div class="md:hidden">
                            <button @click="showCart = !showCart" class="bg-accent text-white p-2 rounded-lg relative">
                                <i class="fa-solid fa-cart-shopping"></i>
                                <span x-show="cart.length > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center" x-text="cart.length"></span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <div class="relative flex-1">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-secondary/40"></i>
                            <input type="text" x-model="search" @input.debounce.300ms="fetchItems()" 
                                placeholder="Search items..." 
                                class="w-full pl-10 pr-4 py-2.5 bg-background border border-border rounded-xl focus:ring-1 focus:ring-accent focus:border-accent outline-none transition-all text-sm">
                        </div>
                        <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0 scrollbar-hide">
                            <button @click="category = ''; fetchItems()" 
                                :class="category === '' ? 'bg-accent text-white' : 'bg-surface text-secondary border border-border hover:border-accent/30'"
                                class="px-4 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition-all">
                                All
                            </button>
                            <?php foreach ($categories as $cat): ?>
                            <button @click="category = '<?php echo $cat; ?>'; fetchItems()" 
                                :class="category === '<?php echo $cat; ?>' ? 'bg-accent text-white' : 'bg-surface text-secondary border border-border hover:border-accent/30'"
                                class="px-4 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition-all">
                                <?php echo $cat; ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Grid -->
            <div class="flex-1 overflow-y-auto p-4 md:p-6 scrollbar-thin">
                <div x-show="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="i in 6">
                        <div class="bg-surface rounded-2xl p-4 border border-border animate-pulse h-32"></div>
                    </template>
                </div>

                <div x-show="!loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="item in items" :key="item.id">
                        <div class="group relative bg-surface rounded-2xl p-5 transition-all border border-border hover:border-accent/30 hover:shadow-md flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-accent/10 text-accent uppercase tracking-wider" x-text="item.category"></span>
                                    <span class="font-bold text-primary text-lg">₱<span x-text="item.price"></span></span>
                                </div>
                                <h3 class="font-bold text-primary text-base mb-1" x-text="item.name"></h3>
                                <p class="text-xs text-secondary mb-4" x-text="`${item.gender} • ${item.size || 'No Size'}`"></p>
                            </div>

                            <div class="flex flex-col gap-2 mt-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center bg-background border border-border rounded-lg overflow-hidden h-10">
                                        <button @click="item.qty = Math.max(1, (item.qty || 1) - 1)" class="px-3 hover:bg-surface transition-colors border-r border-border text-secondary">
                                            <i class="fa-solid fa-minus text-[10px]"></i>
                                        </button>
                                        <input type="number" x-model.number="item.qty" class="w-10 text-center bg-transparent text-sm font-bold outline-none" min="1" :max="item.stock">
                                        <button @click="item.qty = Math.min(item.stock, (item.qty || 1) + 1)" class="px-3 hover:bg-surface transition-colors border-l border-border text-secondary">
                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                        </button>
                                    </div>
                                    <button @click="addToCart(item)" :disabled="item.status !== 'available' || item.stock <= 0"
                                        class="flex-1 bg-accent text-white h-10 rounded-lg text-xs font-bold hover:bg-accent-hover transition-all shadow-sm disabled:opacity-50">
                                        Add to Cart
                                    </button>
                                </div>
                                <button @click="openReservationModal(item)" :disabled="item.status !== 'available' || item.stock <= 0"
                                    class="w-full bg-surface border border-border text-primary h-9 rounded-lg text-[10px] font-bold hover:bg-background transition-all disabled:opacity-50">
                                    <i class="fa-solid fa-calendar-plus mr-1.5 text-accent"></i>
                                    Reserve Item
                                </button>
                            </div>
                            
                            <div x-show="item.stock <= 5 && item.stock > 0" class="absolute -top-2 -right-2 bg-orange-500 text-white text-[8px] font-bold px-2 py-1 rounded-full shadow-sm">
                                ONLY <span x-text="item.stock"></span> LEFT
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="!loading && items.length === 0" class="flex flex-col items-center justify-center h-full text-secondary/30">
                    <i class="fa-solid fa-box-open text-4xl mb-3"></i>
                    <p class="text-sm font-medium">No items found</p>
                </div>
            </div>
        </div>

        <!-- Right: Cart Panel -->
        <div :class="{ 'translate-x-0': showCart, 'translate-x-full md:translate-x-0': !showCart }" 
            class="fixed inset-0 md:relative md:inset-auto w-full md:w-[380px] bg-surface flex flex-col h-full border-l border-border z-40 transition-transform duration-300">
            
            <div class="p-6 border-b border-border flex items-center justify-between bg-surface">
                <h2 class="text-lg font-bold flex items-center gap-2 text-primary">
                    <i class="fa-solid fa-cart-shopping text-accent"></i>
                    Cart
                </h2>
                <div class="flex items-center gap-3">
                    <button @click="cart = []" class="text-secondary/40 hover:text-red-500 transition-colors">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                    <button @click="showCart = false" class="md:hidden text-secondary hover:text-primary">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 scrollbar-thin">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="flex items-center gap-4 bg-background p-4 rounded-2xl border border-border group transition-all">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-primary text-sm truncate" x-text="item.name"></h4>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-[10px] text-secondary" x-text="`${item.quantity}x ₱${item.price}`"></span>
                                <span class="text-xs font-bold text-primary">₱<span x-text="(item.price * item.quantity).toFixed(2)"></span></span>
                            </div>
                        </div>
                        <button @click="removeFromCart(index)" class="w-8 h-8 flex items-center justify-center rounded-xl text-secondary/40 hover:bg-red-50 hover:text-red-500 transition-all">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                </template>

                <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-full text-secondary/20">
                    <i class="fa-solid fa-shopping-basket text-4xl mb-3"></i>
                    <p class="text-sm font-medium">Your cart is empty</p>
                </div>
            </div>

            <!-- Summary -->
            <div class="p-6 bg-background border-t border-border space-y-4">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm text-secondary">
                        <span>Subtotal</span>
                        <span class="font-bold text-primary">₱<span x-text="cartTotal().subtotal"></span></span>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-bold text-secondary mb-1.5 uppercase tracking-widest">Bargained Price (Optional)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-secondary/40">₱</span>
                            <input type="number" x-model="bargainedPrice" placeholder="Override total price..."
                                class="w-full pl-7 pr-3 py-2 bg-surface border border-border rounded-lg text-sm font-bold outline-none focus:border-accent transition-all">
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-end pt-4 border-t border-border/50">
                    <span class="text-primary font-bold text-sm">Final Total</span>
                    <span class="text-2xl font-bold text-primary">₱<span x-text="bargainedPrice || cartTotal().total"></span></span>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button @click="openPaymentModal('cash')" :disabled="cart.length === 0" 
                        class="bg-surface border border-border py-3.5 rounded-xl font-bold text-xs flex flex-col items-center gap-1.5 hover:border-accent/30 transition-all disabled:opacity-50 shadow-sm">
                        <i class="fa-solid fa-money-bill-wave text-accent"></i>
                        Cash
                    </button>
                    <button @click="openPaymentModal('gcash')" :disabled="cart.length === 0"
                        class="bg-[#007DFE] text-white py-3.5 rounded-xl font-bold text-xs flex flex-col items-center gap-1.5 hover:opacity-90 transition-all disabled:opacity-50 shadow-sm">
                        <i class="fa-solid fa-mobile-screen"></i>
                        GCash
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Camera Integration Modal -->
    <div x-show="showCamera" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
        <div class="bg-surface w-full max-w-md rounded-2xl overflow-hidden shadow-2xl border border-border">
            <div class="p-6 border-b border-border flex items-center justify-between">
                <h3 class="text-lg font-bold text-primary">Proof of Purchase</h3>
                <button @click="stopCamera()" class="text-secondary hover:text-primary"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-6 space-y-6">
                <div class="relative aspect-[4/3] bg-black rounded-xl overflow-hidden">
                    <video x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas x-ref="canvas" class="hidden"></canvas>
                    <template x-if="capturedImage">
                        <img :src="capturedImage" class="absolute inset-0 w-full h-full object-cover z-10">
                    </template>
                </div>
                
                <div class="flex gap-3">
                    <template x-if="!capturedImage">
                        <button @click="takePhoto()" class="flex-1 bg-accent text-white py-3 rounded-xl font-bold text-sm hover:bg-accent-hover transition-all">
                            <i class="fa-solid fa-camera mr-2"></i> Capture Photo
                        </button>
                    </template>
                    <template x-if="capturedImage">
                        <div class="flex-1 flex gap-3">
                            <button @click="capturedImage = null" class="flex-1 bg-surface border border-border py-3 rounded-xl font-bold text-sm">
                                Retake
                            </button>
                            <button @click="confirmCapture()" class="flex-1 bg-green-600 text-white py-3 rounded-xl font-bold text-sm">
                                Use Photo
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal (Cash) -->
    <div x-show="paymentModal === 'cash'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
        <div @click.away="paymentModal = null" class="bg-surface w-full max-w-md rounded-2xl overflow-hidden shadow-xl scale-in border border-border">
            <div class="p-6 border-b border-border flex items-center justify-between">
                <h3 class="text-lg font-bold text-primary">Cash Payment</h3>
                <button @click="paymentModal = null" class="text-secondary hover:text-primary transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-8 space-y-6">
                <div class="text-center">
                    <p class="text-[10px] font-bold text-secondary uppercase tracking-widest mb-1">Total Amount Due</p>
                    <h2 class="text-4xl font-black text-primary">₱<span x-text="bargainedPrice || cartTotal().total"></span></h2>
                </div>
                <div>
                    <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest text-center">Cash Received</label>
                    <div class="relative max-w-[200px] mx-auto">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-secondary/30 text-xl">₱</span>
                        <input type="number" x-model="cashReceived" @input="calculateChange()" autofocus
                            class="w-full pl-10 pr-4 py-4 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-xl text-2xl font-black outline-none transition-all text-center">
                    </div>
                </div>
                <div x-show="cashReceived >= (bargainedPrice || cartTotal().total)" class="p-6 bg-green-50 rounded-2xl flex flex-col items-center border border-green-100">
                    <span class="text-green-700 text-[10px] font-bold uppercase tracking-widest mb-1">Change to Give</span>
                    <span class="text-3xl font-black text-green-700">₱<span x-text="change"></span></span>
                </div>
                <div class="flex flex-col gap-3">
                    <button @click="startCamera()" class="w-full bg-surface border border-border py-3 rounded-xl font-bold text-sm hover:bg-background transition-all">
                        <i class="fa-solid fa-camera mr-2" :class="proofOfPurchase ? 'text-green-500' : 'text-accent'"></i>
                        <span x-text="proofOfPurchase ? 'Change Proof Photo' : 'Take Proof Photo (Optional)'"></span>
                    </button>
                    <button @click="processPayment('cash')" :disabled="parseFloat(cashReceived) < parseFloat(bargainedPrice || cartTotal().total) || loading"
                        class="w-full bg-accent text-white py-4 rounded-xl font-bold text-sm hover:bg-accent-hover transition-all disabled:opacity-50 shadow-lg shadow-accent/20">
                        <span x-show="!loading">Complete Transaction</span>
                        <span x-show="loading"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- GCash Modal -->
    <div x-show="paymentModal === 'gcash'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
        <div @click.away="paymentModal = null" class="bg-surface w-full max-w-md rounded-2xl overflow-hidden shadow-xl scale-in border border-border">
            <div class="bg-[#007DFE] p-10 text-white text-center relative">
                <div class="flex items-center justify-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-mobile-screen text-[#007DFE]"></i>
                    </div>
                    <span class="text-2xl font-black tracking-tight italic">GCash</span>
                </div>
                <p class="text-[10px] opacity-80 uppercase tracking-widest font-bold">Total Amount to Scan</p>
                <h2 class="text-4xl font-black mt-1">₱<span x-text="bargainedPrice || cartTotal().total"></span></h2>
            </div>
            <div class="p-8 space-y-6 text-center">
                <div class="bg-background p-6 rounded-3xl border border-border inline-block shadow-inner">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=THRIFTPOS_PAYMENT" class="w-44 h-44 mix-blend-multiply opacity-80">
                </div>
                <div class="space-y-4">
                    <button @click="startCamera()" class="w-full bg-surface border border-border py-3 rounded-xl font-bold text-sm hover:bg-background transition-all">
                        <i class="fa-solid fa-camera mr-2" :class="proofOfPurchase ? 'text-green-500' : 'text-accent'"></i>
                        <span x-text="proofOfPurchase ? 'Change Proof Photo' : 'Take Proof Photo (Optional)'"></span>
                    </button>
                    <button @click="processPayment('gcash')" :disabled="loading"
                        class="w-full bg-[#007DFE] text-white py-4 rounded-xl font-bold text-sm hover:opacity-90 transition-all shadow-lg shadow-blue-500/20">
                        <span x-show="!loading">Confirm Payment Received</span>
                        <span x-show="loading"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Processing...</span>
                    </button>
                    <button @click="paymentModal = null" class="text-[10px] font-bold text-secondary uppercase tracking-widest hover:text-primary transition-colors">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reservation Modal -->
    <div x-show="reservationModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
        <div @click.away="reservationModal = false" class="bg-surface w-full max-w-md rounded-2xl overflow-hidden shadow-xl scale-in border border-border">
            <div class="p-6 border-b border-border flex items-center justify-between">
                <h3 class="text-lg font-bold text-primary">Reserve Item</h3>
                <button @click="reservationModal = false" class="text-secondary hover:text-primary transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form @submit.prevent="submitReservation" class="p-8 space-y-5">
                <input type="hidden" name="item_id" :value="reservingItem ? reservingItem.id : ''">
                <div class="flex items-center gap-3 p-3 bg-background rounded-xl border border-border">
                    <div>
                        <p class="font-bold text-primary text-sm" x-text="reservingItem ? reservingItem.name : ''"></p>
                        <p class="text-xs font-bold text-accent">₱<span x-text="reservingItem ? reservingItem.price : ''"></span></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-secondary mb-1.5 uppercase tracking-widest">Customer Name *</label>
                            <input type="text" x-model="customerName" required placeholder="Full Name"
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-secondary mb-1.5 uppercase tracking-widest">Contact Number *</label>
                            <input type="text" x-model="contactNumber" inputmode="numeric" maxlength="11" required placeholder="09XXXXXXXXX"
                                @input="contactNumber = contactNumber.replace(/[^0-9]/g, '').slice(0, 11)"
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-secondary mb-1.5 uppercase tracking-widest">Duration (days) *</label>
                            <input type="number" x-model="durationDays" min="1" step="1" required
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-secondary mb-1.5 uppercase tracking-widest">Location in Shop</label>
                            <input type="text" x-model="locationIndicator" placeholder="e.g. Rack A, Shelf 2"
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-secondary mb-1.5 uppercase tracking-widest">Notes</label>
                        <textarea x-model="notes" rows="2" placeholder="Any special instructions..."
                            class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all resize-none"></textarea>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="button" @click="startCameraForReservation()" class="w-full bg-surface border border-border py-3 rounded-xl font-bold text-xs hover:bg-background transition-all">
                        <i class="fa-solid fa-camera mr-2" :class="proofOfReservation ? 'text-green-500' : 'text-accent'"></i>
                        <span x-text="proofOfReservation ? 'Change Proof Photo' : 'Take Proof Photo (Optional)'"></span>
                    </button>
                    <button type="submit" :disabled="!customerName || loading"
                        class="w-full bg-black text-white py-4 rounded-xl font-bold text-sm hover:bg-gray-900 transition-all disabled:opacity-50 shadow-lg active:scale-[0.98]">
                        <span x-show="!loading">Confirm Reservation</span>
                        <span x-show="loading"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Processing...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-[100] flex flex-col items-end gap-3 pointer-events-none"></div>
</div>

<script>
function posApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true' || '<?php echo $_SESSION['theme'] ?? 'light'; ?>' === 'dark',
        loading: false,
        showCart: false,
        showCamera: false,
        items: [],
        cart: [],
        category: '',
        search: '',
        bargainedPrice: null,
        paymentModal: null,
        cashReceived: '',
        change: 0,
        proofOfPurchase: null,
        proofOfReservation: null,
        isReservationCamera: false,
        capturedImage: null,
        stream: null,
        locationIndicator: '',
        reservationModal: false,
        reservingItem: null,
        customerName: '',
        contactNumber: '',
        notes: '',
        durationDays: 1,
        baseUrl: '<?php echo trim($base_url, '/'); ?>',

        init() {
            this.fetchItems();
            this.$watch('darkMode', val => localStorage.setItem('darkMode', val));
            window.addEventListener('darkModeChanged', (e) => this.darkMode = e.detail);
        },

        resolveUrl(path) {
            if (!path.startsWith('/')) path = '/' + path;
            return this.baseUrl ? '/' + this.baseUrl + path : path;
        },

        fetchItems() {
            this.loading = true;
            const params = new URLSearchParams({
                category: this.category,
                search: this.search
            });
            fetch(this.resolveUrl(`/api/items?${params}`))
                .then(res => res.json())
                .then(data => {
                    this.items = data.map(item => ({ ...item, qty: 1 }));
                    this.loading = false;
                })
                .catch(error => {
                    console.error('Fetch items error:', error);
                    this.loading = false;
                });
        },

        addToCart(item) {
            if (item.status !== 'available' || item.stock <= 0) return;
            
            const quantity = item.qty || 1;
            const existing = this.cart.find(i => i.id === item.id);
            
            if (existing) {
                if (existing.quantity + quantity > item.stock) {
                    this.showToast(`Only ${item.stock} in stock`, 'error');
                    return;
                }
                existing.quantity += quantity;
            } else {
                this.cart.push({
                    ...item,
                    quantity: quantity
                });
            }
            this.showToast('Added to cart');
            item.qty = 1; // Reset card qty
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        cartTotal() {
            const subtotal = this.cart.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
            return {
                subtotal: subtotal.toFixed(2),
                total: subtotal.toFixed(2)
            };
        },

        calculateChange() {
            const total = parseFloat(this.bargainedPrice || this.cartTotal().total);
            const received = parseFloat(this.cashReceived) || 0;
            this.change = received >= total ? (received - total).toFixed(2) : 0;
        },

        openPaymentModal(type) {
            this.paymentModal = type;
            this.cashReceived = '';
            this.change = 0;
            this.proofOfPurchase = null;
        },

        openReservationModal(item) {
            this.reservingItem = item;
            this.customerName = '';
            this.contactNumber = '';
            this.notes = '';
            this.durationDays = 1;
            this.locationIndicator = '';
            this.proofOfReservation = null;
            this.reservationModal = true;
        },

        async startCamera() {
            this.isReservationCamera = false;
            this.showCamera = true;
            this.capturedImage = null;
            this._startMedia();
        },

        async startCameraForReservation() {
            this.isReservationCamera = true;
            this.showCamera = true;
            this.capturedImage = null;
            this._startMedia();
        },

        async _startMedia() {
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                this.$refs.video.srcObject = this.stream;
            } catch (err) {
                console.error("Error accessing camera:", err);
                this.showToast("Cannot access camera", "error");
                this.showCamera = false;
            }
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
            }
            this.showCamera = false;
        },

        takePhoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            this.capturedImage = canvas.toDataURL('image/png');
        },

        async confirmCapture() {
            this.loading = true;
            try {
                console.log("Attempting to upload image...");
                const response = await fetch(this.resolveUrl('/api/upload-capture'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ image: this.capturedImage })
                });
                
                const data = await response.json();
                console.log("Upload response:", data);
                
                if (data.success) {
                    if (this.isReservationCamera) {
                        this.proofOfReservation = data.file_url;
                    } else {
                        this.proofOfPurchase = data.file_url;
                    }
                    this.showToast("Photo captured!");
                    this.stopCamera();
                } else {
                    console.error("Upload failed:", data.message);
                    this.showToast("Failed to upload photo: " + data.message, "error");
                }
            } catch (err) {
                console.error("Upload error:", err);
                this.showToast("Upload error. Check console.", "error");
            } finally {
                this.loading = false;
            }
        },

        submitReservation() {
            this.loading = true;
            const payload = {
                item_id: this.reservingItem.id,
                customer_name: this.customerName,
                contact_number: this.contactNumber,
                notes: this.notes,
                duration_days: this.durationDays,
                location_indicator: this.locationIndicator,
                proof_of_reservation: this.proofOfReservation
            };

            fetch(this.resolveUrl('/reservations/add'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.showToast('Reservation successful!', 'success');
                    this.reservationModal = false;
                    this.fetchItems();
                } else {
                    this.showToast(data.message || 'Reservation failed', 'error');
                }
            })
            .catch(error => {
                this.loading = false;
                this.showToast('Network error', 'error');
            });
        },

        processPayment(method) {
            this.loading = true;
            const payload = {
                items: this.cart,
                total: this.cartTotal().total,
                bargained_price: this.bargainedPrice,
                payment_method: method,
                cash_received: method === 'cash' ? parseFloat(this.cashReceived) : null,
                change: method === 'cash' ? parseFloat(this.change) : null,
                proof_of_purchase: this.proofOfPurchase
            };

            fetch(this.resolveUrl('/api/checkout'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.showToast('Transaction completed!', 'success');
                    this.resetPos();
                } else {
                    this.showToast(data.message || 'Payment failed', 'error');
                }
            })
            .catch(error => {
                this.loading = false;
                this.showToast('Network error', 'error');
            });
        },

        resetPos() {
            this.cart = [];
            this.bargainedPrice = null;
            this.paymentModal = null;
            this.proofOfPurchase = null;
            this.fetchItems();
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `px-6 py-3 rounded-2xl shadow-2xl text-white font-bold mb-3 transform transition-all duration-300 translate-y-10 opacity-0 text-sm ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
            toast.innerHTML = `<div class="flex items-center gap-3"><i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>${message}</div>`;
            document.getElementById('toast-container').appendChild(toast);
            setTimeout(() => toast.classList.remove('translate-y-10', 'opacity-0'), 100);
            setTimeout(() => {
                toast.classList.add('translate-y-10', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    };
}
</script>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .scrollbar-thin::-webkit-scrollbar { width: 4px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 10px; }
    .dark .scrollbar-thin::-webkit-scrollbar-thumb { background: #374151; }
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    @keyframes scale-in { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .scale-in { animation: scale-in 0.2s ease-out forwards; }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
