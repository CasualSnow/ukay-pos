<?php
/** @var array $reservations */
/** @var string $base_url */
require_once __DIR__ . '/../layouts/header.php';

$reservations = $reservations ?? [];
$base_url = $base_url ?? '';
?>

<div x-data="reservationsApp()" x-init="init()" class="flex min-h-screen bg-background" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 ml-20 md:ml-64 bg-background min-h-screen p-4 md:p-8 transition-all duration-300">
        <header class="mb-8">
            <h1 class="text-2xl font-extrabold text-primary tracking-tight">Active Reservations</h1>
        </header>

        <div class="bg-surface rounded-2xl shadow-sm border border-border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-background border-b border-border">
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Customer</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Item</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Location</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php foreach ($reservations as $res): ?>
                        <tr @click="toggleRow(<?php echo $res['id']; ?>)" 
                            class="hover:bg-background/50 transition-all cursor-pointer group">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-primary"><?php echo $res['customer_name']; ?></span>
                                    <span class="text-[10px] text-secondary"><?php echo $res['contact_number']; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-primary"><?php echo $res['item_name']; ?></span>
                                    <span class="text-[10px] text-accent font-bold">₱<?php echo number_format($res['price'], 2); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-location-dot text-accent text-xs"></i>
                                    <span class="text-xs font-medium text-primary"><?php echo $res['location_indicator'] ?: 'Not set'; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider <?php 
                                    echo $res['status'] == 'reserved' || $res['status'] == 'pending' ? 'bg-yellow-50 text-yellow-600 border border-yellow-100' : 
                                        ($res['status'] == 'paid' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 
                                        ($res['status'] == 'completed' ? 'bg-green-50 text-green-600 border border-green-100' : 
                                        'bg-red-50 text-red-600 border border-red-100')); 
                                ?>"><?php echo $res['status']; ?></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <i class="fa-solid fa-chevron-down text-secondary/30 transition-transform duration-300" 
                                   :class="expandedRows.includes(<?php echo $res['id']; ?>) ? 'rotate-180' : ''"></i>
                            </td>
                        </tr>
                        <!-- Expanded Content -->
                        <tr x-show="expandedRows.includes(<?php echo $res['id']; ?>)" x-cloak x-transition class="bg-background/30">
                            <td colspan="5" class="px-6 py-8">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                    <div class="space-y-4">
                                        <h4 class="text-[10px] font-bold text-secondary uppercase tracking-widest">Proof of Reservation</h4>
                                        <div class="aspect-[4/3] bg-surface rounded-xl border border-border overflow-hidden">
                                            <?php if ($res['proof_of_reservation']): ?>
                                                <img src="<?php echo $res['proof_of_reservation']; ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex flex-col items-center justify-center text-secondary/20">
                                                    <i class="fa-solid fa-camera text-3xl mb-2"></i>
                                                    <p class="text-[10px] font-bold">No photo available</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <h4 class="text-[10px] font-bold text-secondary uppercase tracking-widest">Reservation Details</h4>
                                        <div class="space-y-3">
                                            <div class="flex justify-between text-xs">
                                                <span class="text-secondary">Reserved On:</span>
                                                <span class="font-bold text-primary"><?php echo date('M d, Y', strtotime($res['created_at'])); ?></span>
                                            </div>
                                            <div class="flex justify-between text-xs">
                                                <span class="text-secondary">Duration:</span>
                                                <span class="font-bold text-primary"><?php echo $res['duration_days']; ?> days</span>
                                            </div>
                                            <div class="flex justify-between text-xs">
                                                <span class="text-secondary">Expires:</span>
                                                <span class="font-bold text-red-500"><?php echo date('M d, Y', strtotime($res['expiration_date'])); ?></span>
                                            </div>
                                            <?php if ($res['notes']): ?>
                                            <div class="pt-2">
                                                <span class="text-secondary text-[10px] font-bold uppercase tracking-widest block mb-1">Notes:</span>
                                                <p class="text-xs text-primary italic"><?php echo $res['notes']; ?></p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex flex-col justify-end gap-3">
                                        <?php if ($res['status'] == 'reserved' || $res['status'] == 'pending'): ?>
                                        <button @click="openPaymentModal(<?php echo htmlspecialchars(json_encode($res)); ?>)"
                                            class="w-full bg-accent text-white py-3 rounded-xl font-bold text-xs hover:bg-accent-hover transition-all shadow-sm">
                                            Process Payment
                                        </button>
                                        <?php elseif ($res['status'] == 'paid'): ?>
                                        <form action="<?php echo $base_url; ?>/reservations/complete" method="POST">
                                            <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                            <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-xl font-bold text-xs hover:bg-green-700 transition-all shadow-sm">
                                                Finalize Sale
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        
                                        <?php if (!in_array($res['status'], ['completed', 'cancelled', 'expired'])): ?>
                                        <form action="<?php echo $base_url; ?>/reservations/cancel" method="POST" onsubmit="return confirm('Cancel this reservation?')">
                                            <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                            <button type="submit" class="w-full bg-surface border border-red-100 text-red-600 py-3 rounded-xl font-bold text-xs hover:bg-red-50 transition-all">
                                                Cancel Reservation
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($reservations)): ?>
            <div class="flex flex-col items-center justify-center py-20 text-secondary/20">
                <i class="fa-solid fa-calendar-xmark text-5xl mb-4"></i>
                <p class="text-sm font-medium">No active reservations</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Payment Modal -->
        <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
            <div @click.away="showPaymentModal = false" class="bg-surface w-full max-w-md rounded-2xl overflow-hidden shadow-xl scale-in border border-border">
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
                        <div class="flex justify-between items-center py-2">
                            <span class="text-secondary text-xs font-medium">Total to Pay</span>
                            <span class="text-2xl font-bold text-primary">₱<span x-text="selectedReservation ? parseFloat(selectedReservation.price).toFixed(2) : '0.00'"></span></span>
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

                    <button type="submit" class="w-full bg-accent text-white py-4 rounded-xl font-bold text-sm hover:bg-accent-hover transition-all shadow-sm">
                        Complete Payment
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
        expandedRows: [],
        
        init() {
            this.$watch('darkMode', val => localStorage.setItem('darkMode', val));
            window.addEventListener('darkModeChanged', (e) => this.darkMode = e.detail);
        },

        toggleRow(id) {
            if (this.expandedRows.includes(id)) {
                this.expandedRows = this.expandedRows.filter(rowId => rowId !== id);
            } else {
                this.expandedRows.push(id);
            }
        },

        openPaymentModal(res) {
            this.selectedReservation = res;
            this.showPaymentModal = true;
        }
    }
}
</script>

<style>
    [x-cloak] { display: none !important; }
    @keyframes scale-in { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .scale-in { animation: scale-in 0.2s ease-out forwards; }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
