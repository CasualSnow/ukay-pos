<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="min-h-screen flex items-center justify-center px-4 bg-background">
    <div class="max-w-md w-full bg-surface rounded-xl shadow-sm overflow-hidden p-10 border border-border">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-primary mb-2 tracking-tight">ThriftPOS</h1>
            <p class="text-sm text-secondary font-medium">Sign in to your account</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-r-lg">
                <p class="text-red-700 text-xs font-bold"><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <form action="<?php echo $base_url; ?>/login" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Username</label>
                <input type="text" id="username" name="username" required
                    class="w-full px-4 py-3 rounded-lg border border-border bg-background text-primary focus:ring-1 focus:ring-accent focus:border-accent outline-none transition-all text-sm font-medium">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 rounded-lg border border-border bg-background text-primary focus:ring-1 focus:ring-accent focus:border-accent outline-none transition-all text-sm font-medium">
            </div>

            <button type="submit"
                class="w-full bg-accent text-white py-3 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all shadow-sm active:scale-[0.98]">
                Login
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
