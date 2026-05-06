<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" class="flex min-h-screen bg-background" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 ml-20 md:ml-64 bg-background min-h-screen p-8 transition-soft">
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
            <div>
                <h1 class="text-2xl font-extrabold text-primary tracking-tight">Earnings Report</h1>
                <p class="text-xs text-secondary mt-1">Filter and view your shop's performance</p>
            </div>

            <form action="" method="GET" class="flex items-center gap-3 bg-surface p-2 rounded-xl border border-border">
                <div class="flex items-center gap-2 px-3">
                    <i class="fa-solid fa-calendar text-accent text-xs"></i>
                    <input type="date" name="start_date" value="<?php echo $startDate; ?>" 
                        class="bg-transparent text-xs font-bold text-primary outline-none">
                </div>
                <div class="w-px h-4 bg-border"></div>
                <div class="flex items-center gap-2 px-3">
                    <input type="date" name="end_date" value="<?php echo $endDate; ?>" 
                        class="bg-transparent text-xs font-bold text-primary outline-none">
                </div>
                <button type="submit" class="bg-accent text-white px-4 py-2 rounded-lg text-[10px] font-bold uppercase tracking-wider hover:bg-accent-hover transition-all shadow-sm">
                    Filter
                </button>
            </form>
        </header>

        <div class="grid grid-cols-1 gap-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php 
                    $totalEarnings = array_sum(array_column($dailySales, 'total'));
                    $totalTransactions = array_sum(array_column($dailySales, 'count'));
                    $avgPerDay = count($dailySales) > 0 ? $totalEarnings / count($dailySales) : 0;
                ?>
                <div class="bg-surface rounded-2xl p-6 border border-border shadow-sm">
                    <p class="text-[10px] font-bold text-secondary uppercase tracking-widest mb-2">Total Earnings</p>
                    <h2 class="text-3xl font-black text-primary">₱<?php echo number_format($totalEarnings, 2); ?></h2>
                </div>
                <div class="bg-surface rounded-2xl p-6 border border-border shadow-sm">
                    <p class="text-[10px] font-bold text-secondary uppercase tracking-widest mb-2">Total Transactions</p>
                    <h2 class="text-3xl font-black text-primary"><?php echo number_format($totalTransactions); ?></h2>
                </div>
                <div class="bg-surface rounded-2xl p-6 border border-border shadow-sm">
                    <p class="text-[10px] font-bold text-secondary uppercase tracking-widest mb-2">Daily Average</p>
                    <h2 class="text-3xl font-black text-primary">₱<?php echo number_format($avgPerDay, 2); ?></h2>
                </div>
            </div>

            <!-- Sales Trends -->
            <div class="bg-surface rounded-2xl p-8 shadow-sm border border-border">
                <h3 class="text-lg font-bold text-primary mb-8 flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-accent"></i>
                    Daily Breakdown
                </h3>
                <div class="space-y-4">
                    <?php if (empty($dailySales)): ?>
                        <div class="text-center py-20 text-secondary/20">
                            <i class="fa-solid fa-calendar-xmark text-5xl mb-4"></i>
                            <p class="text-sm font-medium">No data for the selected range</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($dailySales as $day): ?>
                        <div class="flex items-center justify-between p-5 bg-background rounded-2xl border border-border hover:border-accent/30 transition-soft group">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-surface rounded-xl flex items-center justify-center border border-border group-hover:bg-accent/5 group-hover:border-accent/20 transition-all">
                                    <i class="fa-solid fa-calendar-day text-secondary group-hover:text-accent transition-colors"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-primary"><?php echo date('l, F d, Y', strtotime($day['date'])); ?></p>
                                    <p class="text-[10px] text-secondary font-medium uppercase tracking-widest"><?php echo $day['count']; ?> transactions</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-black text-primary">₱<?php echo number_format($day['total'], 2); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
