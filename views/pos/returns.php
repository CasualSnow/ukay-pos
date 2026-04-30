<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div x-data="returnApp()" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 ml-20 md:ml-64 bg-gray-50 dark:bg-gray-900 min-h-screen p-8">
        <header class="mb-10">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">Process Returns</h1>
        
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Sales List -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-gray-900 dark:text-white">Recent Sales</h3>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-gray-700">
                    <?php foreach ($sales as $sale): ?>
                    <button @click="selectSale(<?php echo htmlspecialchars(json_encode($sale)); ?>)" 
                        class="w-full p-6 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-all text-left">
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white">Order #<?php echo $sale['id']; ?> • ₱<?php echo number_format($sale['total_amount'], 2); ?></p>
                            <p class="text-xs text-gray-500"><?php echo date('M d, h:i A', strtotime($sale['created_at'])); ?> • By <?php echo $sale['username']; ?></p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-300"></i>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sale Items (to return) -->
            <div x-show="selectedSale" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="font-bold text-gray-900 dark:text-white">Items in Order #<span x-text="selectedSale.id"></span></h3>
                    <button @click="selectedSale = null" class="text-gray-400 hover:text-gray-600">Close</button>
                </div>

                <div x-show="loadingItems" class="space-y-4">
                    <template x-for="i in 3">
                        <div class="h-20 bg-gray-50 dark:bg-gray-900 rounded-2xl animate-pulse"></div>
                    </template>
                </div>

                <div x-show="!loadingItems" class="space-y-4">
                    <template x-for="item in saleItems" :key="item.id">
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl">
                            <div class="flex items-center gap-4">
                                <img :src="item.image_url" class="w-12 h-12 rounded-xl object-cover">
                                <div>
                                    <p class="font-bold text-gray-900 dark:text-white" x-text="item.name"></p>
                                    <p class="text-xs text-gray-500">₱<span x-text="item.final_price"></span></p>
                                </div>
                            </div>
                            <form action="<?php echo $base_url; ?>/returns/process" method="POST" onsubmit="return confirm('Restore this item to inventory?')">
                                <input type="hidden" name="sale_item_id" :value="item.id">
                                <input type="hidden" name="item_id" :value="item.item_id">
                                <button type="submit" class="px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 text-xs font-bold rounded-xl hover:bg-red-600 hover:text-white transition-all">
                                    Return
                                </button>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
            
            <div x-show="!selectedSale" class="flex flex-col items-center justify-center p-20 text-gray-300 border-2 border-dashed border-gray-100 dark:border-gray-700 rounded-3xl">
                <i class="fa-solid fa-receipt text-6xl mb-4"></i>
                <p class="font-medium text-center">Select a sale from the left to process a return</p>
            </div>
        </div>
    </main>
</div>

<script>
function returnApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true',
        selectedSale: null,
        saleItems: [],
        loadingItems: false,

        selectSale(sale) {
            this.selectedSale = sale;
            this.loadingItems = true;
            this.saleItems = [];
            
            fetch(`<?php echo $base_url; ?>/api/sale-items/${sale.id}`)
                .then(res => {
                    if (!res.ok) throw new Error('Failed to fetch items');
                    return res.json();
                })
                .then(data => {
                    this.saleItems = data;
                })
                .catch(err => {
                    console.error(err);
                    alert('Error loading items. Please try again.');
                })
                .finally(() => {
                    this.loadingItems = false;
                });
        }
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
