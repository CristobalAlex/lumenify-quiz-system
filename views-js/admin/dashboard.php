<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location:login.html");
    exit();
}
?>
<html class="bg-slate-100">
<head>
    <title>Admin Dashboard - Lumenify</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../../js/Vue.js"></script>
    <script src="../../js/VueRouter.js"></script>
    <script src="../../js/tailwindcss.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <style>
        [v-cloak] { display: none; }
        .fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
        .fade-enter-from, .fade-leave-to { opacity: 0; }
        .router-link-active {
            background-color: #4f46e5 !important;
            color: white !important;
        }
    </style>
</head>
<body class="font-sans text-slate-800 h-screen overflow-hidden flex">
    <div id="app" v-cloak class="flex w-full h-full">
        <aside class="w-64 bg-slate-900 text-white flex flex-col flex-shrink-0 transition-transform duration-300 md:translate-x-0 fixed md:relative h-full z-50" 
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-64'">
            <div class="p-6 flex items-center gap-3 border-b border-slate-800">
              <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-md">
                    <span class="text-white font-bold text-lg">L</span>
                </div>
                <h1 class="font-bold text-lg text-slate-800 dark:text-white">Lumenify</h1>
            </div>
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <router-link to="/overview" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-slate-400 hover:bg-slate-800 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Overview
                </router-link>
                <router-link to="/users" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-slate-400 hover:bg-slate-800 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Users
                </router-link>
                <router-link to="/account" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-slate-400 hover:bg-slate-800 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    My Account
                </router-link>
            </nav>
            <div class="p-4 border-t border-slate-800">
                <a href="../../api/admin/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition-all font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </a>
            </div>
        </aside>
        <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-40 md:hidden transition-opacity"></div>
        <main class="flex-1 h-full overflow-y-auto bg-slate-50 relative">
            <div class="md:hidden bg-white p-4 flex items-center justify-between border-b border-slate-200 sticky top-0 z-30">
                <span class="font-bold text-lg text-slate-800">Lumenify Admin</span>
                <button @click="sidebarOpen = true" class="text-slate-600 p-1 hover:bg-slate-100 rounded">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
            <div class="p-6 md:p-10 max-w-7xl mx-auto">
                <router-view v-slot="{ Component }">
                    <transition name="fade" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </div>
        </main>
    </div>
<script>
    const { createApp } = Vue;
    const { createRouter, createWebHashHistory } = VueRouter;
    const Toast = Swal.mixin({
        heightAuto: false, scrollbarPadding: false, background: '#ffffff', buttonsStyling: false,
        customClass: {
            popup: 'rounded-3xl p-6 font-sans border border-slate-100 shadow-2xl bg-white',
            title: 'text-xl font-bold text-slate-800 mb-1',
            htmlContainer: 'text-sm text-slate-500',
            confirmButton: 'bg-slate-900 text-white px-6 py-2.5 rounded-xl font-bold mt-4 hover:bg-slate-800 transition-colors shadow-lg',
            cancelButton: 'bg-slate-100 text-slate-600 px-6 py-2.5 rounded-xl font-bold mt-4 hover:bg-slate-200 transition-colors mr-2'
        }
    });
    const showSuccessAlert = (title, message) => {
        Toast.fire({
            html: `<div class="flex justify-center mb-4 mt-2"><div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center animate-[bounce_1s_ease-in-out_1]"><svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div></div><h3 class="text-xl font-bold text-slate-800 mb-1">${title}</h3><p class="text-sm text-slate-500">${message}</p>`,
            showConfirmButton: false, timer: 2000
        });
    };
    const showErrorAlert = (title, message) => {
        Toast.fire({
            html: `<div class="flex justify-center mb-4 mt-2"><div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center"><svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg></div></div><h3 class="text-xl font-bold text-slate-800 mb-1">${title}</h3><p class="text-sm text-slate-500">${message}</p>`,
            showConfirmButton: true, confirmButtonText: 'Try Again'
        });
    };

    const formatDate = (date) => new Date(date).toLocaleDateString();
    const Overview = {
        data() { return { stats: { users: 0, quizzes: 0 } } },
        mounted() { this.fetchStats(); },
        methods: {
            async fetchStats() {
                try {
                    const res = await fetch('../../api/admin/dashboard_stats.php');
                    const json = await res.json();
                    if(json.status === 'success') this.stats = json.data;
                } catch (e) {}
            }
        },
        template: `
            <div class="space-y-6">
                <h2 class="text-2xl font-bold text-slate-800">Dashboard Overview</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
                        <div><p class="text-sm font-bold text-slate-400 uppercase tracking-wide mb-1">Total Users</p><p class="text-4xl font-black text-slate-800">{{ stats.users }}</p></div>
                        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                    </div>
                    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
                        <div><p class="text-sm font-bold text-slate-400 uppercase tracking-wide mb-1">Total Quizzes</p><p class="text-4xl font-black text-slate-800">{{ stats.quizzes }}</p></div>
                        <div class="w-16 h-16 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg></div>
                    </div>
                </div>
            </div>
        `
    };
    const Users = {
        data() {
            return {
                users: [],
                searchQuery: '',
                filterStart: '',
                filterEnd: '',
                currentPage: 1,
                itemsPerPage: 10
            }
        },
        mounted() { this.fetchUsers(); },
        computed: {
            filteredUsers() {
                let result = this.users;
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    result = result.filter(u => u.username.toLowerCase().includes(q));
                }
                if (this.filterStart) {
                    result = result.filter(u => new Date(u.created_at) >= new Date(this.filterStart));
                }
                if (this.filterEnd) {
                    const endDate = new Date(this.filterEnd);
                    endDate.setHours(23, 59, 59, 999);
                    result = result.filter(u => new Date(u.created_at) <= endDate);
                }

                return result;
            },
            totalPages() {
                return Math.ceil(this.filteredUsers.length / this.itemsPerPage) || 1;
            },
            paginatedUsers() {
                const start = (this.currentPage - 1) * this.itemsPerPage;
                const end = start + this.itemsPerPage;
                return this.filteredUsers.slice(start, end);
            }
        },
        methods: {
            formatDate,
            async fetchUsers() {
                const res = await fetch('../../api/admin/get_users.php');
                const json = await res.json();
                if(json.status === 'success') this.users = json.data;
            },
            changePage(page) {
                if (page >= 1 && page <= this.totalPages) {
                    this.currentPage = page;
                }
            },
            exportExcel() {
                const ws = XLSX.utils.json_to_sheet(this.filteredUsers.map(u => ({
                    Username: u.username,
                    'Date Joined': formatDate(u.created_at)
                })));
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Users");
                XLSX.writeFile(wb, "Lumenify_Users.xlsx");
            },
            exportPDF() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                doc.text("Lumenify User Report", 14, 15);
                doc.setFontSize(10);
                doc.text(`Generated on: ${new Date().toLocaleDateString()}`, 14, 22);

                const tableColumn = ["#", "Username", "Date Joined"];
                const tableRows = [];

                this.filteredUsers.forEach((user, index) => {
                    const userData = [
                        index + 1,
                        user.username,
                        formatDate(user.created_at)
                    ];
                    tableRows.push(userData);
                });

                doc.autoTable({
                    head: [tableColumn],
                    body: tableRows,
                    startY: 30,
                });

                doc.save("Lumenify_Users.pdf");
            }
        },
        template: `
            <div class="space-y-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <h2 class="text-2xl font-bold text-slate-800">User Management</h2>
                    <div class="flex gap-2">
                        <button @click="exportExcel" class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Excel
                        </button>
                        <button @click="exportPDF" class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg> PDF
                        </button>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 items-center">
                    <div class="relative w-full md:w-1/3">
                        <input v-model="searchQuery" type="text" placeholder="Search by username..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none transition-all text-sm">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <input v-model="filterStart" type="date" class="px-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 outline-none text-slate-600">
                        <span class="text-slate-400">-</span>
                        <input v-model="filterEnd" type="date" class="px-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 outline-none text-slate-600">
                    </div>
                    <button @click="fetchUsers" class="ml-auto text-indigo-600 hover:text-indigo-800 text-sm font-bold flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Refresh
                    </button>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-bold">
                                <tr>
                                    <th class="p-4 border-b border-slate-100 w-16">#</th>
                                    <th class="p-4 border-b border-slate-100">Username</th>
                                    <th class="p-4 border-b border-slate-100 text-right">Date Created</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-slate-700">
                                <tr v-for="(user, index) in paginatedUsers" :key="user.id" class="hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0">
                                    <td class="p-4 text-slate-400 font-mono">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                                    <td class="p-4 font-bold text-slate-800">{{ user.username }}</td>
                                    <td class="p-4 text-right text-slate-500 font-mono">{{ formatDate(user.created_at) }}</td>
                                </tr>
                                <tr v-if="filteredUsers.length === 0">
                                    <td colspan="3" class="p-8 text-center text-slate-400">No users found matching your criteria.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="totalPages > 1" class="p-4 border-t border-slate-100 flex justify-between items-center bg-slate-50">
                        <span class="text-xs text-slate-500 font-bold">Page {{ currentPage }} of {{ totalPages }}</span>
                        <div class="flex gap-2">
                            <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 rounded-lg bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed">Prev</button>
                            <button v-for="page in totalPages" :key="page" @click="changePage(page)" :class="currentPage === page ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'" class="w-8 h-8 flex items-center justify-center rounded-lg border text-xs font-bold transition-all">
                                {{ page }}
                            </button>
                            <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-1 rounded-lg bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                        </div>
                    </div>
                </div>
            </div>`
    };
    const Account = {
        data() { return { admin: { username: 'Loading...', email: '...' }, step: 1, otp: '', newPass: '', confPass: '', loading: false } },
        mounted() { this.fetchInfo(); },
        methods: {
            async fetchInfo() {
                const res = await fetch('../../api/admin/get_info.php');
                const json = await res.json();
                if(json.status === 'success') this.admin = json.data;
            },
            async requestCode() {
                this.loading = true;
                try {
                    const res = await fetch('../../api/admin/request_password_otp.php');
                    const json = await res.json();
                    if (json.status === 'success') {
                        this.step = 2;
                        showSuccessAlert('Code Sent', `We sent a 6-digit code to ${this.admin.email}`);
                    } else { showErrorAlert('Error', json.message); }
                } catch (e) { showErrorAlert('Connection Failed', 'Could not reach server.'); } 
                finally { this.loading = false; }
            },
            async saveNewPassword() {
                if (this.otp.length !== 6) return showErrorAlert('Invalid Input', 'Enter the 6-digit code');
                if (this.newPass.length < 6) return showErrorAlert('Weak Password', 'Password must be at least 6 characters');
                if (this.newPass !== this.confPass) return showErrorAlert('Mismatch', 'Passwords do not match');

                this.loading = true;
                try {
                    const res = await fetch('../../api/admin/verify_update_password.php', {
                        method: 'POST', body: JSON.stringify({ otp: this.otp, new_password: this.newPass })
                    });
                    const json = await res.json();
                    if (json.status === 'success') {
                        showSuccessAlert('Password Updated', 'Your admin password has been changed.');
                        this.step = 1; this.otp = ''; this.newPass = ''; this.confPass = '';
                    } else { showErrorAlert('Error', json.message); }
                } catch (e) { showErrorAlert('Error', 'Connection failed'); } 
                finally { this.loading = false; }
            }
        },
        template: `
            <div class="max-w-4xl mx-auto space-y-8">
                <h2 class="text-2xl font-bold text-slate-800">My Account</h2>
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-6">
                    <div class="w-20 h-20 bg-slate-900 text-white rounded-full flex items-center justify-center text-3xl font-bold">{{ admin.username.charAt(0).toUpperCase() }}</div>
                    <div class="flex-1"><h3 class="text-xl font-bold text-slate-800">{{ admin.username }}</h3><p class="text-slate-500">{{ admin.email }}</p><span class="inline-block mt-2 px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full uppercase">Super Admin</span></div>
                </div>
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex justify-between items-center mb-6"><h3 class="font-bold text-slate-800">Security Settings</h3><button v-if="step === 2" @click="step = 1" class="text-sm text-slate-500 hover:text-slate-800 transition-colors">Cancel</button></div>
                    <div v-if="step === 1" class="text-center py-6"><p class="text-slate-600 mb-6">Verify identity via email to change password.</p><button @click="requestCode" :disabled="loading" class="px-8 py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-all flex items-center justify-center gap-2 mx-auto shadow-lg"><span v-if="loading" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span><span v-else>Send Verification Code</span></button></div>
                    <div v-if="step === 2" class="max-w-md mx-auto space-y-4 animate-fade-in">
                        <input type="text" v-model="otp" maxlength="6" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-center tracking-widest font-mono text-lg outline-none focus:border-indigo-500 transition-all" placeholder="000000">
                        <input type="password" v-model="newPass" class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-indigo-500 transition-all" placeholder="New Password">
                        <input type="password" v-model="confPass" class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-indigo-500 transition-all" placeholder="Confirm Password">
                        <button @click="saveNewPassword" :disabled="loading" class="w-full py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-md">{{ loading ? 'Verifying...' : 'Save Password' }}</button>
                    </div>
                </div>
            </div>
        `
    };
    const routes = [
        { path: '/', redirect: '/overview' },
        { path: '/overview', component: Overview },
        { path: '/users', component: Users },
        { path: '/account', component: Account }
    ];
    const router = createRouter({
        history: createWebHashHistory(),
        routes
    });
    const app = createApp({
        data() { return { sidebarOpen: false } }
    });
    app.use(router);
    app.mount('#app');
</script>
</body>
</html>