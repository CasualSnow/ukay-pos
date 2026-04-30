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
                ['path' => '/staff', 'icon' => 'fa-people-line', 'label' => 'Manage Staff', 'role' => 'admin'],
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

        <!-- Bottom Actions -->
        <div class="p-4 border-t border-border space-y-2">
            <button x-data="{
                darkMode: localStorage.getItem('darkMode') === 'true' || '<?php echo $_SESSION['theme'] ?? 'light'; ?>' === 'dark',
                init() {
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                },
                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode);
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                    window.dispatchEvent(new CustomEvent('darkModeChanged', { detail: this.darkMode }));
                    this.saveToDB();
                },
                saveToDB() {
                    fetch('<?php echo $base_url; ?>/user/update-theme', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                        body: 'theme=' + (this.darkMode ? 'dark' : 'light')
                    });
                }
            }" x-init="init()" @click="toggleTheme()" class="w-full flex items-center space-x-3 p-3 rounded-xl text-secondary hover:bg-background hover:text-primary transition-soft">
                <i :class="darkMode ? 'fa-solid fa-sun' : 'fa-solid fa-moon'" class="w-6 text-center"></i>
                <span class="hidden md:block font-medium" x-text="darkMode ? 'Light Mode' : 'Dark Mode'"></span>
            </button>
            <a href="<?php echo $base_url; ?>/logout" class="w-full flex items-center space-x-3 p-3 rounded-xl text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                <i class="fa-solid fa-right-from-bracket w-6 text-center"></i>
                <span class="hidden md:block font-medium">Logout</span>
            </a>
        </div>
    </div>
</aside>
