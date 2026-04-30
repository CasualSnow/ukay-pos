<?php
/** @var array $reservations */
/** @var string $base_url */
require_once __DIR__ . '/../layouts/header.php';

$reservations = $reservations ?? [];
$base_url = $base_url ?? '';
?>

<div x-data="reservationsApp()" x-init="init()" class="flex min-h-screen">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 ml-20 md:ml-64 bg-background min-h-screen p-8">
        <header class="mb-10">
            <h1 class="text-2xl font-extrabold text-primary tracking-tight">Customer Reservations</h1>
           
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($reservations as $res): 
                $remainingDays = null;
                $hasExpirationDate = isset($res['expiration_date']) && !empty($res['expiration_date']);
                if (isset($res['duration_days']) && $res['duration_days'] !== null && $res['duration_days'] !== '') {
                    $durationDays = (int) $res['duration_days'];
                } elseif ($hasExpirationDate && isset($res['created_at']) && !empty($res['created_at'])) {
                    $created = new DateTime($res['created_at']);
                    $expiration = new DateTime($res['expiration_date']);
                    $durationDays = $created->diff($expiration)->days;
                } else {
                    $durationDays = null;
                }
                if ($hasExpirationDate && in_array($res['status'], ['reserved', 'pending'])) {
                    $expiration = new DateTime($res['expiration_date']);
                    $now = new DateTime();
                    $interval = $now->diff($expiration);
                    $remainingDays = $interval->invert ? -1 : $interval->days;
                }
            ?>
            <div class="bg-surface rounded-xl p-6 shadow-sm border border-border transition-all hover:border-accent/30 relative group/card">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <img src="<?php echo $res['image_url']; ?>" class="w-14 h-14 rounded-lg object-cover border border-border">
                        <div>
                            <h3 class="font-bold text-primary text-sm"><?php echo $res['item_name']; ?></h3>
                            <p class="text-xs text-accent font-bold">₱<?php echo number_format($res['price'], 2); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-end gap-2">
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider <?php 
                            echo $res['status'] == 'reserved' || $res['status'] == 'pending' ? 'bg-yellow-50 text-yellow-600 border border-yellow-100' : 
                                ($res['status'] == 'paid' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 
                                ($res['status'] == 'completed' ? 'bg-green-50 text-green-600 border border-green-100' : 
                                ($res['status'] == 'expired' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-red-50 text-red-600 border border-red-100'))); 
                        ?>"><?php echo $res['status']; ?></span>
                        
                        <!-- Delete Reservation -->
                        <?php if (in_array($res['status'], ['reserved', 'pending'])): ?>
                        <form action="<?php echo $base_url; ?>/reservations/delete" method="POST" class="opacity-0 group-hover/card:opacity-100 transition-opacity z-10" onsubmit="return confirm('Are you sure you want to delete this reservation?')">
                            <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="bg-background rounded-lg p-4 mb-6 border border-border space-y-3">
                    <div>
                        <div class="flex items-center gap-2 text-secondary/40 mb-1">
                            <i class="fa-solid fa-user text-[10px]"></i>
                            <span class="text-[9px] font-bold uppercase tracking-widest">Customer</span>
                        </div>
                        <p class="font-bold text-primary text-sm"><?php echo $res['customer_name']; ?></p>
                        <?php if (!empty($res['contact_number'])): ?>
                        <p class="text-[10px] text-secondary font-medium mt-0.5"><i class="fa-solid fa-phone text-[8px] mr-1"></i><?php echo $res['contact_number']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="pt-2 border-t border-border/50">
                        <div class="flex items-center gap-2 text-accent mb-1">
                            <i class="fa-solid fa-calendar-check text-xs"></i>
                            <span class="text-[10px] font-bold uppercase tracking-widest">Reservation Details</span>
                        </div>
                        <p class="text-sm font-bold text-primary mb-1">
                            <i class="fa-solid fa-hourglass-half text-accent text-xs mr-1"></i>
                            Duration: <?php echo $durationDays !== null ? $durationDays : 'N/A'; ?><?php echo $durationDays !== null ? ' day' . ($durationDays > 1 ? 's' : '') : ''; ?>
                        </p>
                        <?php if ($hasExpirationDate): ?>
                        <p class="text-xs text-secondary mb-1">
                            <i class="fa-solid fa-calendar-xmark text-secondary/40 mr-1"></i>
                            Expires: <?php echo date('F d, Y', strtotime($res['expiration_date'])); ?>
                        </p>
                        <?php endif; ?>
                        <?php if ($remainingDays !== null && $remainingDays >= 0): ?>
                        <p class="text-xs font-bold <?php echo $remainingDays <= 1 ? 'text-red-600 bg-red-50 px-2 py-1 rounded-full' : 'text-green-600 bg-green-50 px-2 py-1 rounded-full'; ?>">
                            <i class="fa-solid fa-bolt mr-1"></i>
                            <?php echo $remainingDays; ?> day<?php echo $remainingDays > 1 ? 's' : ''; ?> remaining
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($res['notes'])): ?>
                    <div class="pt-2 border-t border-border/50">
                        <div class="flex items-center gap-2 text-secondary/40 mb-1">
                            <i class="fa-solid fa-note-sticky text-[10px]"></i>
                            <span class="text-[9px] font-bold uppercase tracking-widest">Notes</span>
                        </div>
                        <p class="text-[11px] text-secondary leading-relaxed italic"><?php echo $res['notes']; ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="pt-2 border-t border-border/50 flex items-center justify-between">
                        <span class="text-[9px] font-bold text-secondary/40 uppercase tracking-widest">Reserved On</span>
                        <p class="text-[10px] text-secondary/50 font-medium"><?php echo date('M d, Y', strtotime($res['created_at'])); ?></p>
                    </div>
                </div>

                <div class="space-y-2">
                    <?php if ($res['status'] == 'reserved' || $res['status'] == 'pending'): ?>
                    <button @click="selectedReservation = <?php echo htmlspecialchars(json_encode($res)); ?>; showPaymentModal = true; paymentMethod = 'cash'" 
                        class="w-full bg-accent text-white py-2.5 rounded-lg font-bold text-xs hover:bg-accent-hover transition-all shadow-sm">
                        Process Payment
                    </button>
                    <?php elseif ($res['status'] == 'paid'): ?>
                    <button disabled class="w-full bg-green-500 text-white py-2.5 rounded-lg font-bold text-xs cursor-not-allowed opacity-80">
                        Payment Verified
                    </button>
                    <?php elseif ($res['status'] == 'expired'): ?>
                    <button disabled class="w-full bg-red-50 text-red-600 py-2.5 rounded-lg font-bold text-xs cursor-not-allowed border border-red-100">
                        Reservation Expired
                    </button>
                    <?php else: ?>
                    <button disabled class="w-full bg-background text-secondary/40 border border-border py-2.5 rounded-lg font-bold text-xs cursor-not-allowed">
                        <?php echo ucfirst($res['status']); ?>
                    </button>
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <form action="<?php echo $base_url; ?>/reservations/complete" method="POST">
                            <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                            <button type="submit" <?php echo $res['status'] != 'paid' ? 'disabled' : ''; ?>
                                class="w-full py-2.5 rounded-lg font-bold text-[10px] uppercase tracking-wider transition-all <?php 
                                    echo $res['status'] == 'paid' 
                                    ? 'bg-background text-green-600 border border-green-100 hover:bg-green-600 hover:text-white' 
                                    : 'bg-background text-secondary/20 border border-border cursor-not-allowed'; 
                                ?>">
                                Finalize
                            </button>
                        </form>
                        <form action="<?php echo $base_url; ?>/reservations/cancel" method="POST">
                            <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                            <button type="submit" <?php echo ($res['status'] == 'completed' || $res['status'] == 'cancelled' || $res['status'] == 'expired') ? 'disabled' : ''; ?>
                                class="w-full py-2.5 rounded-lg font-bold text-[10px] uppercase tracking-wider transition-all <?php 
                                    echo ($res['status'] == 'completed' || $res['status'] == 'cancelled' || $res['status'] == 'expired')
                                    ? 'bg-background text-secondary/20 border border-border cursor-not-allowed'
                                    : 'bg-background text-red-600 border border-red-100 hover:bg-red-600 hover:text-white';
                                ?>">
                                Cancel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($reservations)): ?>
            <div class="col-span-full flex flex-col items-center justify-center py-20 text-secondary/20">
                <i class="fa-solid fa-calendar-xmark text-5xl mb-4"></i>
                <p class="text-sm font-medium tracking-tight">No active reservations</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Payment Modal -->
        <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
            <div @click.away="showPaymentModal = false" class="bg-surface w-full max-w-md rounded-xl overflow-hidden shadow-xl scale-in border border-border">
                <div class="p-6 border-b border-border flex items-center justify-between">
                    <h3 class="text-lg font-bold text-primary">Confirm Payment</h3>
                    <button @click="showPaymentModal = false" class="text-secondary hover:text-primary transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <form action="<?php echo $base_url; ?>/reservations/pay" method="POST" class="p-8 space-y-6">
                    <input type="hidden" name="reservation_id" :value="selectedReservation ? selectedReservation.id : ''">
                    
                    <div class="space-y-3" x-show="selectedReservation">
                        <div class="flex justify-between items-center py-2 border-b border-border/50">
                            <span class="text-secondary text-xs font-medium">Customer</span>
                            <span class="font-bold text-primary text-sm" x-text="selectedReservation ? selectedReservation.customer_name : ''"></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-border/50">
                            <span class="text-secondary text-xs font-medium">Item</span>
                            <span class="font-bold text-primary text-sm" x-text="selectedReservation ? selectedReservation.item_name : ''"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-secondary text-xs font-medium">Final Amount</span>
                            <span class="text-xl font-bold text-primary">₱<span x-text="selectedReservation ? getDiscountedPrice(parseFloat(selectedReservation.price), selectedReservation.tag_color).toLocaleString(undefined, {minimumFractionDigits: 2}) : '0.00'"></span></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-secondary mb-3 uppercase tracking-widest">Payment Method</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer group">
                                <input type="radio" name="payment_method" value="cash" x-model="paymentMethod" class="hidden peer">
                                <div class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border peer-checked:border-accent peer-checked:bg-accent/5 transition-all">
                                    <i class="fa-solid fa-money-bill-wave text-accent text-lg"></i>
                                    <span class="text-[10px] font-bold text-primary uppercase tracking-widest">Cash</span>
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="payment_method" value="gcash" x-model="paymentMethod" class="hidden peer">
                                <div class="flex flex-col items-center gap-2 p-4 rounded-xl border border-border peer-checked:border-accent peer-checked:bg-accent/5 transition-all">
                                    <i class="fa-solid fa-mobile-screen text-[#007DFE] text-lg"></i>
                                    <span class="text-[10px] font-bold text-primary uppercase tracking-widest">GCash</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- GCash QR Display -->
                    <div x-show="paymentMethod === 'gcash'" x-transition class="flex flex-col items-center justify-center p-6 bg-background rounded-xl border border-dashed border-border">
                        <p class="text-[9px] font-bold text-[#007DFE] uppercase tracking-widest mb-3">Scan to Pay with GCash</p>
                        <div class="w-32 h-32 bg-white p-2 rounded-lg shadow-sm border border-border">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=GCash_Payment_Placeholder" class="w-full h-full opacity-80">
                        </div>
                        <p class="mt-3 text-[9px] text-secondary font-bold uppercase tracking-widest">ThriftPOS Store</p>
                    </div>

                    <button type="submit" class="w-full bg-accent text-white py-4 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all shadow-sm">
                        Confirm Transaction
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
function reservationsApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true' || '<?php echo $_SESSION['theme'] ?? 'light'; ?>' === 'dark',
        showPaymentModal: false,
        selectedReservation: null,
        paymentMethod: 'cash',
        init() {
            this.$watch('darkMode', val => localStorage.setItem('darkMode', val));
            window.addEventListener('darkModeChanged', (e) => {
                this.darkMode = e.detail;
            });
        },
        getDiscountedPrice(price, tagColor) {
            const discounts = {
                'red': 0.50,
                'blue': 0.30,
                'green': 0.20,
                'yellow': 0.00
            };
            const rate = discounts[tagColor] || 0;
            return price - (price * rate);
        }
    }
}
</script>

<style>
    @keyframes scale-in {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .scale-in { animation: scale-in 0.2s ease-out forwards; }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
