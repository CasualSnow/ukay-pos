<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden p-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">ThriftPOS</h1>
            <p class="text-gray-500 dark:text-gray-400">Sign in to your account</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <p class="text-red-700 text-sm"><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <form action="<?php echo $base_url; ?>/login" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username</label>
                <input type="text" id="username" name="username" required
                    class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-2 focus:ring-black dark:focus:ring-white outline-none transition-all">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-2 focus:ring-black dark:focus:ring-white outline-none transition-all">
            </div>

            <button type="submit"
                class="w-full bg-black text-white dark:bg-white dark:text-black py-3 rounded-lg font-semibold hover:opacity-90 transition-all">
                Login
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
