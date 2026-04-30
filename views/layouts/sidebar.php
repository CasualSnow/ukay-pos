<aside class="fixed left-0 top-0 h-screen w-20 md:w-64 bg-surface border-r border-border z-50 transition-soft">
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="p-6">
            <div class="flex items-center gap-3 justify-center md:justify-start">
                <div class="w-10 h-10 bg-accent rounded-lg flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-store text-white text-xl"></i>
                </div>
                <h1 class="hidden md:block text-xl font-bold text-primary tracking-tight">ThriftPOS</h1>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <?php 
            $current_page = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $menu_items = [
                ['path' => '/dashboard', 'icon' => 'fa-chart-pie', 'label' => 'Dashboard', 'role' => 'admin'],
                ['path' => '/pos', 'icon' => 'fa-cash-register', 'label' => 'POS', 'role' => 'any'],
                ['path' => '/reservations', 'icon' => 'fa-calendar-check', 'label' => 'Reservations', 'role' => 'any'],
                ['path' => '/returns', 'icon' => 'fa-rotate-left', 'label' => 'Returns', 'role' => 'any'],
                ['path' => '/inventory', 'icon' => 'fa-boxes-stacked', 'label' => 'Inventory', 'role' => 'admin'],
                ['path' => '/reports', 'icon' => 'fa-chart-line', 'label' => 'Reports', 'role' => 'admin'],
            ];

            foreach ($menu_items as $item): 
                if ($item['role'] !== 'any' && $_SESSION['role'] !== $item['role']) continue;
                
                $is_active = strpos($current_page, $item['path']) !== false;
            ?>
            <a href="<?php echo $base_url . $item['path']; ?>" 
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-soft group <?php echo $is_active ? 'bg-accent/10 text-accent font-bold' : 'text-secondary hover:bg-background hover:text-primary'; ?>">
                <div class="w-5 flex justify-center">
                    <i class="fa-solid <?php echo $item['icon']; ?> text-lg <?php echo $is_active ? 'text-accent' : 'text-secondary group-hover:text-primary'; ?>"></i>
                </div>
                <span class="hidden md:block text-sm"><?php echo $item['label']; ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- Footer Actions -->
        <div class="p-4 border-t border-border space-y-1">
            <a href="<?php echo $base_url; ?>/logout" 
               class="flex items-center gap-3 px-4 py-3 rounded-lg text-secondary hover:bg-red-50 hover:text-red-600 transition-soft group">
                <div class="w-5 flex justify-center">
                    <i class="fa-solid fa-right-from-bracket text-lg"></i>
                </div>
                <span class="hidden md:block text-sm">Logout</span>
            </a>
        </div>
    </div>
</aside>
