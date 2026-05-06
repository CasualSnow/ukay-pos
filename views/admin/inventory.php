<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div x-data="inventoryApp()" x-init="init()" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 ml-20 md:ml-64 bg-background min-h-screen p-8 transition-all duration-300">
        <header class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-2xl font-extrabold text-primary tracking-tight">Inventory Management</h1>
            </div>
            <div class="flex gap-3">
                <button @click="showBulkModal = true" 
                    class="bg-surface text-primary border border-border px-5 py-2.5 rounded-lg font-bold text-xs flex items-center gap-2 hover:bg-background transition-all shadow-sm">
                    <i class="fa-solid fa-layer-group"></i>
                    Bulk Upload
                </button>
                <button @click="editMode = false; currentItem = { gender: 'Unisex', stock: 1 }; showModal = true" 
                    class="bg-accent text-white px-5 py-2.5 rounded-lg font-bold text-xs flex items-center gap-2 hover:bg-accent-hover transition-all shadow-sm">
                    <i class="fa-solid fa-plus"></i>
                    Add New Item
                </button>
            </div>
        </header>

        <!-- Items Table -->
        <div class="bg-surface rounded-xl shadow-sm border border-border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-background border-b border-border">
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Item</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Category</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Gender/Size</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Price</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Stock</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php foreach ($items as $item): ?>
                        <tr class="hover:bg-background/50 transition-all group">
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-primary"><?php echo $item['name']; ?></span>
                            </td>
                            <td class="px-6 py-4 text-xs text-secondary font-medium"><?php echo $item['category']; ?></td>
                            <td class="px-6 py-4 text-xs text-secondary font-medium">
                                <?php echo $item['gender']; ?> / <?php echo $item['size'] ?: 'N/A'; ?>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-primary">₱<?php echo number_format($item['price'], 2); ?></td>
                            <td class="px-6 py-4 text-xs font-bold text-primary"><?php echo $item['stock']; ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider <?php 
                                    echo $item['status'] == 'available' ? 'bg-green-50 text-green-600 border border-green-100' : ($item['status'] == 'sold' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-yellow-50 text-yellow-600 border border-yellow-100'); 
                                ?>"><?php echo $item['status']; ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                    <button @click="editMode = true; currentItem = <?php echo htmlspecialchars(json_encode($item)); ?>; showModal = true" 
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-background text-secondary hover:text-accent border border-border hover:border-accent/30 transition-all">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <form action="<?php echo $base_url; ?>/inventory/delete" method="POST" onsubmit="return confirm('Are you sure?')">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-background text-secondary hover:text-red-600 border border-border hover:border-red-100 transition-all">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
            <div @click.away="showModal = false" class="bg-surface w-full max-w-lg rounded-xl overflow-hidden shadow-xl scale-in border border-border">
                <div class="p-6 border-b border-border flex items-center justify-between">
                    <h3 class="text-lg font-bold text-primary" x-text="editMode ? 'Edit Item' : 'Add New Item'"></h3>
                    <button @click="showModal = false" class="text-secondary hover:text-primary transition-colors"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form :action="editMode ? '<?php echo $base_url; ?>/inventory/update' : '<?php echo $base_url; ?>/inventory/add'" method="POST" class="p-8 space-y-6">
                    <input type="hidden" name="id" :value="currentItem.id">
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Item Name</label>
                            <input type="text" name="name" :value="currentItem.name" required
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Category</label>
                            <select name="category" required
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all appearance-none text-sm font-medium">
                                <option value="" disabled :selected="!currentItem.category">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" :selected="currentItem.category === '<?php echo $cat; ?>'"><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Gender</label>
                            <select name="gender" required
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all appearance-none text-sm font-medium">
                                <option value="Men" :selected="currentItem.gender === 'Men'">Men</option>
                                <option value="Women" :selected="currentItem.gender === 'Women'">Women</option>
                                <option value="Unisex" :selected="currentItem.gender === 'Unisex'">Unisex</option>
                                <option value="Kids" :selected="currentItem.gender === 'Kids'">Kids</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Size</label>
                            <input type="text" name="size" :value="currentItem.size" placeholder="e.g. S, M, L, 32"
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Price (₱)</label>
                            <input type="number" step="0.01" name="price" :value="currentItem.price" required
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Stock</label>
                            <input type="number" name="stock" :value="currentItem.stock || 1" required
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm font-medium">
                        </div>
                        <div x-show="editMode">
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Status</label>
                            <select name="status"
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all appearance-none text-sm font-medium">
                                <option value="available" :selected="currentItem.status === 'available'">Available</option>
                                <option value="sold" :selected="currentItem.status === 'sold'">Sold</option>
                                <option value="reserved" :selected="currentItem.status === 'reserved'">Reserved</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-accent text-white py-3.5 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all shadow-sm">
                        <span x-text="editMode ? 'Save Changes' : 'Add Item'"></span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Bulk Upload Modal -->
        <div x-show="showBulkModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
            <div @click.away="showBulkModal = false" class="bg-surface w-full max-w-4xl rounded-xl overflow-hidden shadow-xl scale-in border border-border">
                <div class="p-6 border-b border-border flex items-center justify-between">
                    <h3 class="text-lg font-bold text-primary">Bulk Product Upload</h3>
                    <button @click="showBulkModal = false" class="text-secondary hover:text-primary transition-colors"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="p-8 space-y-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] font-bold text-secondary uppercase tracking-widest">
                                    <th class="pb-4">Name</th>
                                    <th class="pb-4">Category</th>
                                    <th class="pb-4">Gender</th>
                                    <th class="pb-4">Size</th>
                                    <th class="pb-4">Price</th>
                                    <th class="pb-4">Stock</th>
                                    <th class="pb-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in bulkItems" :key="index">
                                    <tr>
                                        <td class="pr-2 pb-2">
                                            <input type="text" x-model="item.name" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm outline-none">
                                        </td>
                                        <td class="pr-2 pb-2">
                                            <select x-model="item.category" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm outline-none">
                                                <option value="">Select</option>
                                                <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="pr-2 pb-2">
                                            <select x-model="item.gender" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm outline-none">
                                                <option value="Unisex">Unisex</option>
                                                <option value="Men">Men</option>
                                                <option value="Women">Women</option>
                                                <option value="Kids">Kids</option>
                                            </select>
                                        </td>
                                        <td class="pr-2 pb-2">
                                            <input type="text" x-model="item.size" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm outline-none">
                                        </td>
                                        <td class="pr-2 pb-2">
                                            <input type="number" x-model="item.price" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm outline-none">
                                        </td>
                                        <td class="pr-2 pb-2">
                                            <input type="number" x-model="item.stock" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm outline-none">
                                        </td>
                                        <td class="pb-2 text-right">
                                            <button @click="removeBulkItem(index)" class="text-red-500 hover:text-red-700"><i class="fa-solid fa-trash-can"></i></button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-between items-center">
                        <button @click="addBulkItem()" class="text-accent font-bold text-sm flex items-center gap-2 hover:underline">
                            <i class="fa-solid fa-plus"></i> Add Another Row
                        </button>
                        <button @click="saveBulkItems()" :disabled="bulkItems.length === 0 || loading"
                            class="bg-accent text-white px-8 py-2.5 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all shadow-sm disabled:opacity-50">
                            <span x-text="loading ? 'Saving...' : 'Save All Products'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
    @keyframes scale-in {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .scale-in { animation: scale-in 0.2s ease-out forwards; }
</style>

<script>
function inventoryApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true' || '<?php echo $_SESSION['theme'] ?? 'light'; ?>' === 'dark',
        showModal: false,
        showBulkModal: false,
        editMode: false,
        currentItem: {},
        bulkItems: [
            { name: '', category: '', gender: 'Unisex', size: '', price: '', stock: 1 }
        ],
        loading: false,

        init() {
            this.$watch('darkMode', val => localStorage.setItem('darkMode', val));
            window.addEventListener('darkModeChanged', (e) => {
                this.darkMode = e.detail;
            });
        },

        addBulkItem() {
            this.bulkItems.push({ name: '', category: '', gender: 'Unisex', size: '', price: '', stock: 1 });
        },

        removeBulkItem(index) {
            this.bulkItems.splice(index, 1);
            if (this.bulkItems.length === 0) this.addBulkItem();
        },

        async saveBulkItems() {
            this.loading = true;
            try {
                const formData = new URLSearchParams();
                this.bulkItems.forEach((item, index) => {
                    formData.append(`items[${index}][name]`, item.name);
                    formData.append(`items[${index}][category]`, item.category);
                    formData.append(`items[${index}][gender]`, item.gender);
                    formData.append(`items[${index}][size]`, item.size);
                    formData.append(`items[${index}][price]`, item.price);
                    formData.append(`items[${index}][stock]`, item.stock);
                });

                const response = await fetch('<?php echo $base_url; ?>/inventory/bulk-add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error saving bulk items:', error);
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
