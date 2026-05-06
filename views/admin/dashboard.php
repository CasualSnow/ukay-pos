<?php
/** @var array $stats */
/** @var array $recentSales */
/** @var array $categoryStats */
/** @var array $dailyEarnings */
/** @var string $base_url */
require_once __DIR__ . '/../layouts/header.php';

$stats = $stats ?? [
    'today' => 0,
    'available' => 0,
    'reserved' => 0,
    'items_sold' => 0,
];
$recentSales = $recentSales ?? [];
$categoryStats = $categoryStats ?? [];
$dailyEarnings = $dailyEarnings ?? [];
$base_url = $base_url ?? '';
?>

<div x-data="dashboardApp()" x-init="init()" class="flex min-h-screen bg-background transition-all duration-300" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 ml-20 md:ml-64 bg-background min-h-screen p-4 md:p-8 transition-soft">
        <header class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-primary tracking-tight">Business Overview</h1>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-surface rounded-xl border border-border flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-calendar text-accent"></i>
                    <span class="text-xs font-semibold text-primary"><?php echo date('F d, Y'); ?></span>
                </div>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-10">
            <div class="bg-surface p-6 rounded-2xl shadow-sm border border-border transition-soft hover:border-accent/30">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-coins text-lg"></i>
                    </div>
                    <span class="text-[9px] font-bold text-secondary tracking-widest uppercase">Revenue Today</span>
                </div>
                <h2 class="text-2xl font-black text-primary tracking-tight">₱<?php echo number_format($stats['today'], 2); ?></h2>
            </div>

            <div class="bg-surface p-6 rounded-2xl shadow-sm border border-border transition-soft hover:border-accent/30">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-box-open text-lg"></i>
                    </div>
                    <span class="text-[9px] font-bold text-secondary tracking-widest uppercase">Available</span>
                </div>
                <h2 class="text-2xl font-black text-primary tracking-tight"><?php echo $stats['available']; ?></h2>
            </div>

            <div class="bg-surface p-6 rounded-2xl shadow-sm border border-border transition-soft hover:border-accent/30">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-clock text-lg"></i>
                    </div>
                    <span class="text-[9px] font-bold text-secondary tracking-widest uppercase">Reserved</span>
                </div>
                <h2 class="text-2xl font-black text-primary tracking-tight"><?php echo $stats['reserved']; ?></h2>
            </div>

            <div class="bg-surface p-6 rounded-2xl shadow-sm border border-border transition-soft hover:border-accent/30">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-tags text-lg"></i>
                    </div>
                    <span class="text-[9px] font-bold text-secondary tracking-widest uppercase">Sold Items</span>
                </div>
                <h2 class="text-2xl font-black text-primary tracking-tight"><?php echo $stats['items_sold']; ?></h2>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Calendar Widget -->
            <div class="xl:col-span-2 bg-surface rounded-2xl p-8 shadow-sm border border-border">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-accent"></i>
                        Earnings Calendar
                    </h3>
                    <div class="flex items-center gap-2">
                        <button @click="prevMonth()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-background transition-colors">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </button>
                        <span class="text-sm font-bold text-primary min-w-[120px] text-center" x-text="monthName + ' ' + year"></span>
                        <button @click="nextMonth()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-background transition-colors">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-px bg-border border border-border rounded-xl overflow-hidden">
                    <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']">
                        <div class="bg-background py-3 text-center text-[10px] font-bold text-secondary uppercase tracking-widest" x-text="day"></div>
                    </template>
                    <template x-for="blank in blankDays">
                        <div class="bg-surface h-24"></div>
                    </template>
                    <template x-for="day in daysInMonth">
                        <div @click="showDayEarnings(day)" 
                            class="bg-surface h-24 p-2 transition-all hover:bg-accent/5 cursor-pointer relative group"
                            :class="{ 'ring-1 ring-inset ring-accent': isToday(day) }">
                            <span class="text-xs font-bold text-secondary group-hover:text-accent transition-colors" x-text="day"></span>
                            <template x-if="getEarnings(day) > 0">
                                <div class="mt-2">
                                    <div class="text-[9px] font-black text-accent">₱<span x-text="formatNumber(getEarnings(day))"></span></div>
                                    <div class="w-full h-1 bg-accent/20 rounded-full mt-1">
                                        <div class="h-full bg-accent rounded-full" :style="`width: ${Math.min(100, (getEarnings(day) / 5000) * 100)}%`"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Recent Sales & Category Stats -->
            <div class="space-y-8">
                <div class="bg-surface rounded-2xl p-6 shadow-sm border border-border">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-primary">Recent Sales</h3>
                        <a href="<?php echo $base_url; ?>/reports" class="text-xs font-bold text-accent hover:underline">View All</a>
                    </div>
                    <div class="space-y-3">
                        <?php foreach ($recentSales as $sale): ?>
                        <div class="flex items-center justify-between p-4 bg-background rounded-xl border border-border hover:border-accent/30 transition-soft">
                            <div>
                                <p class="text-sm font-bold text-primary">Order #<?php echo $sale['id']; ?></p>
                                <p class="text-[10px] text-secondary font-medium"><?php echo date('M d, h:i A', strtotime($sale['created_at'])); ?></p>
                            </div>
                            <p class="text-sm font-bold text-primary">₱<?php echo number_format($sale['total_amount'], 2); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-surface rounded-2xl p-6 shadow-sm border border-border">
                    <h3 class="text-lg font-bold text-primary mb-6">Popular Categories</h3>
                    <div class="space-y-4">
                        <?php foreach ($categoryStats as $cat): ?>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold text-primary uppercase tracking-widest"><?php echo $cat['category']; ?></span>
                                <span class="text-xs font-bold text-secondary"><?php echo $cat['count']; ?> sold</span>
                            </div>
                            <div class="h-1.5 bg-background rounded-full overflow-hidden border border-border">
                                <div class="h-full bg-accent rounded-full transition-all duration-1000" style="width: <?php echo ($cat['count'] / max(array_column($categoryStats, 'count'))) * 100; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Day Detail Modal -->
    <div x-show="showDayModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
        <div @click.away="showDayModal = false" class="bg-surface w-full max-w-sm rounded-2xl overflow-hidden shadow-2xl border border-border scale-in">
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-accent/10 text-accent rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-coins text-2xl"></i>
                </div>
                <p class="text-[10px] font-bold text-secondary uppercase tracking-widest mb-1" x-text="selectedDateFormatted"></p>
                <h3 class="text-3xl font-black text-primary mb-6">₱<span x-text="formatNumber(selectedDayEarnings)"></span></h3>
                <button @click="showDayModal = false" class="w-full bg-primary text-white py-3 rounded-xl font-bold text-sm hover:opacity-90 transition-all">
                    Close Details
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function dashboardApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true' || '<?php echo $_SESSION['theme'] ?? 'light'; ?>' === 'dark',
        month: new Date().getMonth(),
        year: new Date().getFullYear(),
        daysInMonth: [],
        blankDays: [],
        monthName: '',
        dailyEarnings: <?php echo json_encode($dailyEarnings); ?>,
        showDayModal: false,
        selectedDayEarnings: 0,
        selectedDateFormatted: '',

        init() {
            this.generateCalendar();
            this.$watch('darkMode', val => localStorage.setItem('darkMode', val));
            window.addEventListener('darkModeChanged', (e) => this.darkMode = e.detail);
        },

        generateCalendar() {
            const firstDay = new Date(this.year, this.month, 1).getDay();
            const daysCount = new Date(this.year, this.month + 1, 0).getDate();
            
            this.blankDays = Array.from({ length: firstDay });
            this.daysInMonth = Array.from({ length: daysCount }, (_, i) => i + 1);
            this.monthName = new Date(this.year, this.month).toLocaleString('default', { month: 'long' });
        },

        prevMonth() {
            if (this.month === 0) {
                this.month = 11;
                this.year--;
            } else {
                this.month--;
            }
            this.generateCalendar();
        },

        nextMonth() {
            if (this.month === 11) {
                this.month = 0;
                this.year++;
            } else {
                this.month++;
            }
            this.generateCalendar();
        },

        isToday(day) {
            const today = new Date();
            return today.getDate() === day && today.getMonth() === this.month && today.getFullYear() === this.year;
        },

        getEarnings(day) {
            const dateStr = `${this.year}-${String(this.month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const entry = this.dailyEarnings.find(e => e.date === dateStr);
            return entry ? parseFloat(entry.total) : 0;
        },

        showDayEarnings(day) {
            this.selectedDayEarnings = this.getEarnings(day);
            this.selectedDateFormatted = new Date(this.year, this.month, day).toLocaleDateString('default', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            this.showDayModal = true;
        },

        formatNumber(num) {
            return parseFloat(num).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };
}
</script>

<style>
    @keyframes scale-in { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .scale-in { animation: scale-in 0.2s ease-out forwards; }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
