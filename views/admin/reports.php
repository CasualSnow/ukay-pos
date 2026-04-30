<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 ml-20 md:ml-64 bg-background min-h-screen p-8 transition-soft">
        <header class="mb-10">
            <h1 class="text-2xl font-bold text-primary tracking-tight">Analytics & Reports</h1>
            <p class="text-sm text-secondary font-medium">Detailed insights into your store operations</p>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Sales Trends -->
            <div class="bg-surface rounded-xl p-6 shadow-sm border border-border">
                <h3 class="text-lg font-bold text-primary mb-6">Recent Sales Trends (Daily)</h3>
                <div class="space-y-3">
                    <?php foreach ($dailySales as $day): ?>
                    <div class="flex items-center justify-between p-4 bg-background rounded-lg border border-border hover:border-accent/30 transition-soft">
                        <div>
                            <p class="text-sm font-bold text-primary"><?php echo date('l, M d', strtotime($day['date'])); ?></p>
                            <p class="text-[10px] text-secondary font-medium"><?php echo $day['count']; ?> transactions</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-primary">₱<?php echo number_format($day['total'], 2); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Staff Performance -->
            <div class="bg-surface rounded-xl p-6 shadow-sm border border-border">
                <h3 class="text-lg font-bold text-primary mb-6">Staff Performance</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-bold text-secondary uppercase tracking-widest border-b border-border">
                                <th class="pb-4">Staff Member</th>
                                <th class="pb-4">Transactions</th>
                                <th class="pb-4 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <?php foreach ($staffPerformance as $staff): ?>
                            <tr>
                                <td class="py-4 text-sm font-bold text-primary capitalize"><?php echo $staff['username']; ?></td>
                                <td class="py-4 text-xs text-secondary font-medium"><?php echo $staff['sales_count']; ?></td>
                                <td class="py-4 text-right text-sm font-bold text-accent">₱<?php echo number_format($staff['total_revenue'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Inventory Breakdown -->
            <div class="bg-surface rounded-xl p-6 shadow-sm border border-border">
                <h3 class="text-lg font-bold text-primary mb-6">Inventory Status Breakdown</h3>
                <div class="grid grid-cols-3 gap-4">
                    <?php foreach ($inventoryStatus as $status): ?>
                    <div class="text-center p-5 bg-background rounded-xl border border-border">
                        <p class="text-[10px] font-bold text-secondary uppercase tracking-widest mb-2"><?php echo $status['status']; ?></p>
                        <h4 class="text-2xl font-bold text-primary"><?php echo $status['count']; ?></h4>
                        <p class="text-[10px] text-secondary/40 font-bold uppercase mt-1">Items</p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
