<aside class="fixed left-0 top-0 h-screen w-20 md:w-64 bg-white dark:bg-gray-800 border-r border-gray-100 dark:border-gray-700 z-40 transition-all duration-300">
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 justify-center md:justify-start">
                <div class="w-10 h-10 bg-black dark:bg-white rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-store text-white dark:text-black text-xl"></i>
                </div>
                <h1 class="hidden md:block text-2xl font-black text-gray-900 dark:text-white tracking-tight">ThriftPOS</h1>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-2">
            <?php if ($_SESSION['role'] == 'admin'): ?>
            <a href="<?php echo $base_url; ?>/dashboard" class="flex items-center space-x-3 p-3 rounded-xl transition-all <?php echo strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'bg-black text-white' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'; ?>">
                <i class="fa-solid fa-chart-line w-6 text-center"></i>
                <span class="hidden md:block font-medium">Dashboard</span>
            </a>
            <?php endif; ?>

            <a href="<?php echo $base_url; ?>/pos" class="flex items-center space-x-3 p-3 rounded-xl transition-all <?php echo strpos($_SERVER['REQUEST_URI'], '/pos') !== false ? 'bg-black text-white' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'; ?>">
                <i class="fa-solid fa-cart-shopping w-6 text-center"></i>
                <span class="hidden md:block font-medium">POS</span>
            </a>

            <a href="<?php echo $base_url; ?>/reservations" class="flex items-center space-x-3 p-3 rounded-xl transition-all <?php echo strpos($_SERVER['REQUEST_URI'], '/reservations') !== false ? 'bg-black text-white' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'; ?>">
                <i class="fa-solid fa-calendar-check w-6 text-center"></i>
                <span class="hidden md:block font-medium">Reservations</span>
            </a>

            <a href="<?php echo $base_url; ?>/returns" class="flex items-center space-x-3 p-3 rounded-xl transition-all <?php echo strpos($_SERVER['REQUEST_URI'], '/returns') !== false ? 'bg-black text-white' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'; ?>">
                <i class="fa-solid fa-rotate-left w-6 text-center"></i>
                <span class="hidden md:block font-medium">Returns</span>
            </a>
            
            <?php if ($_SESSION['role'] == 'admin'): ?>
            <a href="<?php echo $base_url; ?>/inventory" class="flex items-center space-x-3 p-3 rounded-xl transition-all <?php echo strpos($_SERVER['REQUEST_URI'], '/inventory') !== false ? 'bg-black text-white' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'; ?>">
                <i class="fa-solid fa-box w-6 text-center"></i>
                <span class="hidden md:block font-medium">Inventory</span>
            </a>
            <a href="<?php echo $base_url; ?>/reports" class="flex items-center space-x-3 p-3 rounded-xl transition-all <?php echo strpos($_SERVER['REQUEST_URI'], '/reports') !== false ? 'bg-black text-white' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'; ?>">
                <i class="fa-solid fa-file-invoice w-6 text-center"></i>
                <span class="hidden md:block font-medium">Reports</span>
            </a>
            <?php endif; ?>
        </nav>

        <!-- Bottom Actions -->
        <div class="p-4 border-t border-gray-100 dark:border-gray-700 space-y-2">
            <button @click="darkMode = !darkMode" class="w-full flex items-center space-x-3 p-3 rounded-xl text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
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
