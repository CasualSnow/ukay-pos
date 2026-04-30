<?php
/** @var array $staff */
/** @var string $base_url */
require_once __DIR__ . '/../layouts/header.php';

$staff = $staff ?? [];
$base_url = $base_url ?? '';
?>

<div x-data="staffApp()" x-init="init()" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 ml-20 md:ml-64 bg-background min-h-screen p-8">
        <header class="mb-10 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-extrabold text-primary tracking-tight">Manage Staff</h1>
               
            </div>
            <button @click="showCreateModal = true" class="bg-accent text-white px-6 py-3 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i>
                Create Staff
            </button>
        </header>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-secondary/40"></i>
                <input type="text" x-model="search" placeholder="Search by username..." 
                    class="w-full pl-10 pr-4 py-2.5 bg-surface border border-border rounded-lg focus:ring-1 focus:ring-accent focus:border-accent outline-none transition-all text-sm">
            </div>
        </div>

        <!-- Staff Table -->
        <div class="bg-surface rounded-xl border border-border overflow-hidden shadow-sm">
            <table class="w-full">
                <thead>
                    <tr class="bg-background border-b border-border">
                        <th class="px-6 py-4 text-left text-xs font-bold text-secondary uppercase tracking-wider">Full Name</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-secondary uppercase tracking-wider">Username</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-secondary uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-secondary uppercase tracking-wider">Date Created</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-secondary uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <template x-for="staffMember in filteredStaff()" :key="staffMember.id">
                        <tr class="hover:bg-background/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center text-accent font-bold text-sm">
                                        <span x-text="staffMember.fullname ? staffMember.fullname.split(' ').map(n => n[0]).join('').toUpperCase() : staffMember.username.substring(0, 2).toUpperCase()"></span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-primary text-sm" x-text="staffMember.fullname || 'N/A'"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-bold text-primary" x-text="staffMember.username"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="staffMember.status === 'active' ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100'" 
                                    class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                                    x-text="staffMember.status"></span>
                            </td>
                            <td class="px-6 py-4 text-secondary text-sm" x-text="new Date(staffMember.created_at).toLocaleDateString()"></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button @click="toggleStaffStatus(staffMember.id, staffMember.status)" 
                                        :class="staffMember.status === 'active' ? 'bg-red-50 text-red-600 hover:bg-red-600 hover:text-white' : 'bg-green-50 text-green-600 hover:bg-green-600 hover:text-white'"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-all text-sm"
                                        :title="staffMember.status === 'active' ? 'Disable' : 'Enable'">
                                        <i :class="staffMember.status === 'active' ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'"></i>
                                    </button>
                                    <button @click="confirmDelete(staffMember.id, staffMember.username)" 
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all text-sm"
                                        title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div x-show="staff.length === 0" class="p-12 text-center text-secondary/30">
                <i class="fa-solid fa-users text-4xl mb-3 block"></i>
                <p class="text-sm font-medium">No staff accounts yet</p>
            </div>
        </div>
    </main>

    <!-- Create Staff Modal -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
        <div @click.away="showCreateModal = false" class="bg-surface w-full max-w-md rounded-xl overflow-hidden shadow-xl scale-in border border-border">
            <div class="p-6 border-b border-border flex items-center justify-between">
                <h3 class="text-lg font-bold text-primary">Create Staff Account</h3>
                <button @click="showCreateModal = false" class="text-secondary hover:text-primary transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form @submit.prevent="createStaff" class="p-8 space-y-5">
                <div>
                    <label class="block text-xs font-bold text-secondary mb-1.5 uppercase tracking-widest">Full Name</label>
                    <input type="text" x-model="newStaff.fullname" required placeholder="Enter full name"
                        class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-secondary mb-1.5 uppercase tracking-widest">Username</label>
                    <input type="text" x-model="newStaff.username" required placeholder="Enter username"
                        class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-secondary mb-1.5 uppercase tracking-widest">Password</label>
                    <input type="password" x-model="newStaff.password" required placeholder="Enter password (min 5 characters)"
                        class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-secondary mb-1.5 uppercase tracking-widest">Confirm Password</label>
                    <input type="password" x-model="newStaff.password_confirm" required placeholder="Confirm password"
                        class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all">
                </div>
                <button type="submit" :disabled="!newStaff.username || !newStaff.password || !newStaff.password_confirm || loading"
                    class="w-full bg-accent text-white py-3 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all disabled:opacity-50 shadow-sm">
                    <span x-show="!loading">Create Account</span>
                    <span x-show="loading"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Creating...</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
        <div @click.away="showDeleteModal = false" class="bg-surface w-full max-w-sm rounded-xl overflow-hidden shadow-xl scale-in border border-border">
            <div class="p-6 border-b border-border">
                <h3 class="text-lg font-bold text-primary">Delete Staff Account?</h3>
            </div>
            <div class="p-8 space-y-4">
                <p class="text-secondary text-sm">Are you sure you want to permanently delete this staff account? This action cannot be undone.</p>
                <p class="font-bold text-primary text-sm" x-text="'Username: ' + deleteUsername"></p>
            </div>
            <div class="p-6 border-t border-border flex gap-3">
                <button @click="showDeleteModal = false" class="flex-1 bg-background text-primary border border-border py-2.5 rounded-lg font-bold text-sm hover:bg-border transition-all">
                    Cancel
                </button>
                <button @click="deleteStaff()" :disabled="loading" class="flex-1 bg-red-600 text-white py-2.5 rounded-lg font-bold text-sm hover:bg-red-700 transition-all disabled:opacity-50">
                    <span x-show="!loading">Delete</span>
                    <span x-show="loading"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Deleting...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function staffApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true',
        search: '',
        loading: false,
        showCreateModal: false,
        showDeleteModal: false,
        staff: <?php echo json_encode($staff); ?>,
        newStaff: {
            fullname: '',
            username: '',
            password: '',
            password_confirm: ''
        },
        deleteStaffId: null,
        deleteUsername: '',

        init() {
            this.$watch('darkMode', val => localStorage.setItem('darkMode', val));
            window.addEventListener('darkModeChanged', (e) => {
                this.darkMode = e.detail;
            });
        },

        filteredStaff() {
            return this.staff.filter(s => s.username.toLowerCase().includes(this.search.toLowerCase()));
        },

        createStaff() {
            if (!this.newStaff.fullname || !this.newStaff.username || !this.newStaff.password || !this.newStaff.password_confirm) {
                this.showToast('Please fill all fields', 'error');
                return;
            }
            if (this.newStaff.password !== this.newStaff.password_confirm) {
                this.showToast('Passwords do not match', 'error');
                return;
            }

            this.loading = true;
            const formData = new FormData();
            formData.append('fullname', this.newStaff.fullname);
            formData.append('username', this.newStaff.username);
            formData.append('password', this.newStaff.password);
            formData.append('password_confirm', this.newStaff.password_confirm);

            fetch('<?php echo $base_url; ?>/staff/add', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.showToast('Staff account created!', 'success');
                    this.newStaff = { fullname: '', username: '', password: '', password_confirm: '' };
                    this.showCreateModal = false;
                    // Add new staff to list without reload
                    this.staff.push({
                        id: data.id,
                        fullname: data.fullname,
                        username: data.username,
                        status: 'active',
                        created_at: new Date().toISOString()
                    });
                } else {
                    this.showToast(data.message || 'Failed to create account', 'error');
                }
            })
            .catch(error => {
                this.loading = false;
                console.error('Error:', error);
                this.showToast('An error occurred', 'error');
            });
        },

        toggleStaffStatus(id, currentStatus) {
            this.loading = true;
            const formData = new FormData();
            formData.append('id', id);

            fetch('<?php echo $base_url; ?>/staff/toggle-status', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    const staff = this.staff.find(s => s.id === id);
                    if (staff) {
                        staff.status = data.new_status;
                    }
                    this.showToast('Status updated', 'success');
                } else {
                    this.showToast(data.message || 'Failed to update status', 'error');
                }
            })
            .catch(error => {
                this.loading = false;
                console.error('Error:', error);
                this.showToast('An error occurred', 'error');
            });
        },

        confirmDelete(id, username) {
            this.deleteStaffId = id;
            this.deleteUsername = username;
            this.showDeleteModal = true;
        },

        deleteStaff() {
            this.loading = true;
            const formData = new FormData();
            formData.append('id', this.deleteStaffId);

            fetch('<?php echo $base_url; ?>/staff/delete', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.showToast('Staff account deleted', 'success');
                    this.staff = this.staff.filter(s => s.id !== this.deleteStaffId);
                    this.showDeleteModal = false;
                } else {
                    this.showToast(data.message || 'Failed to delete account', 'error');
                }
            })
            .catch(error => {
                this.loading = false;
                console.error('Error:', error);
                this.showToast('An error occurred', 'error');
            });
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `px-5 py-2.5 rounded-lg shadow-xl text-white font-bold mb-3 transform transition-all duration-300 translate-y-10 opacity-0 text-xs ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
            toast.innerHTML = `<div class="flex items-center gap-2"><i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>${message}</div>`;
            
            document.getElementById('toast-container').appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-y-10', 'opacity-0');
            }, 100);

            setTimeout(() => {
                toast.classList.add('translate-y-10', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    };
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
