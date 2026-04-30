<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', showModal: false, editMode: false, currentUser: {} }" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 ml-20 md:ml-64 bg-gray-50 dark:bg-gray-900 min-h-screen p-8">
        <header class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">User Management</h1>
                
            </div>
            <button @click="editMode = false; currentUser = {}; showModal = true" class="bg-black text-white dark:bg-white dark:text-black px-6 py-3 rounded-2xl font-bold flex items-center gap-2 hover:opacity-90 transition-all">
                <i class="fa-solid fa-user-plus"></i>
                Create New Account
            </button>
        </header>

        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Username</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Role</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Created At</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition-all group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 font-bold">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                    <span class="font-bold text-gray-900 dark:text-white"><?php echo $user['username']; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 capitalize text-gray-500 dark:text-gray-400"><?php echo $user['role']; ?></td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-sm"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                    <button @click="editMode = true; currentUser = <?php echo htmlspecialchars(json_encode($user)); ?>; showModal = true" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-all">
                                        <i class="fa-solid fa-user-pen text-xs"></i>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form action="<?php echo $base_url; ?>/users/delete" method="POST" onsubmit="return confirm('Delete this account?')">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-600 hover:text-white transition-all">
                                            <i class="fa-solid fa-user-minus text-xs"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div @click.away="showModal = false" class="bg-white dark:bg-gray-800 w-full max-w-md rounded-3xl overflow-hidden shadow-2xl scale-in">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="editMode ? 'Edit Account' : 'Create Account'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <form :action="editMode ? '<?php echo $base_url; ?>/users/update' : '<?php echo $base_url; ?>/users/add'" method="POST" class="p-8 space-y-6">
                    <input type="hidden" name="id" :value="currentUser.id">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username</label>
                        <input type="text" name="username" :value="currentUser.username" required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-black dark:focus:border-white rounded-2xl outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password <span x-show="editMode" class="text-xs font-normal text-gray-400">(Leave blank to keep current)</span></label>
                        <input type="password" name="password" :required="!editMode"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-black dark:focus:border-white rounded-2xl outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                        <select name="role" required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-black dark:focus:border-white rounded-2xl outline-none transition-all appearance-none">
                            <option value="staff" :selected="currentUser.role === 'staff'">Staff (Cashier)</option>
                            <option value="admin" :selected="currentUser.role === 'admin'">Admin (Owner)</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full bg-black text-white dark:bg-white dark:text-black py-4 rounded-2xl font-bold text-lg hover:opacity-90 transition-all">
                        <span x-text="editMode ? 'Save Changes' : 'Create Account'"></span>
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
