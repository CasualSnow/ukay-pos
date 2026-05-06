<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" class="flex min-h-screen bg-background" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 ml-20 md:ml-64 bg-background min-h-screen p-8 transition-soft">
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
            <div>
                <h1 class="text-2xl font-extrabold text-primary tracking-tight">Sales History</h1>
                <p class="text-xs text-secondary mt-1">Review all items sold by date</p>
            </div>

            <form action="" method="GET" class="flex items-center gap-3 bg-surface p-2 rounded-xl border border-border">
                <div class="flex items-center gap-2 px-3">
                    <i class="fa-solid fa-calendar text-accent text-xs"></i>
                    <input type="date" name="date" value="<?php echo $selectedDate; ?>" 
                        onchange="this.form.submit()"
                        class="bg-transparent text-xs font-bold text-primary outline-none">
                </div>
            </form>
        </header>

        <div class="bg-surface rounded-2xl shadow-sm border border-border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-background border-b border-border">
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Time</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Item</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Staff</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Method</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest text-right">Price</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest text-right">Proof</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php foreach ($sales as $sale): ?>
                        <tr class="hover:bg-background/50 transition-all group">
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-secondary"><?php echo date('h:i A', strtotime($sale['sale_date'])); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-primary"><?php echo $sale['item_name']; ?></span>
                                    <span class="text-[10px] text-secondary uppercase tracking-widest"><?php echo $sale['category']; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-primary capitalize"><?php echo $sale['staff_name']; ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider <?php 
                                    echo $sale['payment_method'] == 'cash' ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-blue-50 text-blue-600 border border-blue-100'; 
                                ?>"><?php echo $sale['payment_method']; ?></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-black text-primary">₱<?php echo number_format($sale['final_price'], 2); ?></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($sale['proof_of_purchase']): ?>
                                    <button @click="$dispatch('open-proof', { url: '<?php echo $sale['proof_of_purchase']; ?>' })" 
                                        class="text-accent hover:text-accent-hover transition-colors">
                                        <i class="fa-solid fa-image"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-secondary/20"><i class="fa-solid fa-image"></i></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (empty($sales)): ?>
            <div class="flex flex-col items-center justify-center py-20 text-secondary/20">
                <i class="fa-solid fa-receipt text-5xl mb-4"></i>
                <p class="text-sm font-medium">No items sold on this date</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Proof Modal -->
    <div x-data="{ open: false, url: '' }" 
         x-show="open" 
         @open-proof.window="open = true; url = $event.detail.url"
         x-cloak 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-primary/60 backdrop-blur-sm">
        <div @click.away="open = false" class="bg-surface max-w-lg w-full rounded-2xl overflow-hidden shadow-2xl border border-border">
            <div class="p-4 border-b border-border flex justify-between items-center">
                <h3 class="text-sm font-bold text-primary">Proof of Purchase</h3>
                <button @click="open = false" class="text-secondary hover:text-primary"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-4">
                <img :src="url" class="w-full h-auto rounded-xl">
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
