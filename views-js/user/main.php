<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location:./login.html');
    exit();
}
?>
<html>
<head>
    <title>Lumenify Quiz System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../../js/Vue.js"></script>
    <script src="../../js/VueRouter.js"></script>
    <script src="../../js/tailwindcss.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#151f2e',
                            900: '#0f172a'
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .router-link-active {
            background-color: #4f46e5;
            color: white !important;
            box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.39);
        }

        [v-cloak] {
            display: none;
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.33);
                opacity: 1;
            }

            80%,
            100% {
                opacity: 0;
            }
        }

        .animate-ring::before {
            content: '';
            position: absolute;
            left: -10px;
            top: -10px;
            right: -10px;
            bottom: -10px;
            border-radius: 1rem;
            border: 2px solid #9333ea;
            animation: pulse-ring 1.25s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }
        body,
        div,
        p,
        h1,
        h2,
        h3,
        span,
        button,
        input,
        textarea {
            transition-property: background-color, border-color, color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 200ms;
        }
    </style>
</head>

<body class="bg-slate-50 dark:bg-slate-900 font-sans text-slate-900 dark:text-slate-100 transition-colors duration-300">
    <div id="app" v-cloak class="min-h-screen flex flex-col md:flex-row">
        <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 p-4 flex justify-between items-center md:hidden sticky top-0 z-40 shadow-sm">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-md">
                    <span class="text-white font-bold text-lg">L</span>
                </div>
                <h1 class="font-bold text-lg text-slate-800 dark:text-white">Lumenify</h1>
            </div>
            <div class="flex items-center gap-2">
                <button @click="toggleTheme" class="p-2 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <svg v-if="!isDark" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>
                <button @click="isSidebarOpen = !isSidebarOpen" class="p-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                    <svg v-if="!isSidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </header>
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-black/50 z-30 md:hidden backdrop-blur-sm transition-opacity"></div>
        <aside :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'" class="fixed inset-y-0 left-0 z-40 w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col transition-transform duration-300 ease-in-out md:fixed h-full shadow-xl md:shadow-none">
            <div class="p-6 hidden md:block">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200/50">
                        <span class="text-white font-bold text-xl">L</span>
                    </div>
                    <h1 class="font-bold text-xl tracking-tight text-slate-800 dark:text-white">Lumenify</h1>
                </div>
            </div>
            <nav class="flex-1 px-4 space-y-1 mt-4 md:mt-0 overflow-y-auto">
                <router-link to="/" @click="closeSidebar" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-all group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </router-link>
                <router-link to="/create" @click="closeSidebar" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-all group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="font-medium">Create Quiz</span>
                </router-link>
                <router-link to="/answer" @click="closeSidebar" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-all group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span class="font-medium">Answer Quiz</span>
                </router-link>
                <router-link to="/quizlist" @click="closeSidebar" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-all group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span class="font-medium">Quiz List</span>
                </router-link>
                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Settings</div>
                <button @click="toggleTheme" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-all group text-left">
                    <svg v-if="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="font-medium">{{ isDark ? 'Light Mode' : 'Dark Mode' }}</span>
                </button>
                <router-link to="/account" @click="closeSidebar" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-all group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="font-medium">My Account</span>
                </router-link>
            </nav>
            <div class="p-4 border-t border-slate-100 dark:border-slate-700 mt-auto">
                <a href="../../api/user/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all font-medium group">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        </aside>
        <main class="flex-1 w-full md:ml-64 p-4 md:p-8 transition-all duration-300">
            <header class="hidden md:flex justify-between items-center mb-10">
                <div>
                    <h2 class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Welcome back,</h2>
                    <p class="text-2xl font-bold text-slate-800 dark:text-white"><?php echo $_SESSION['username']; ?></p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 border-2 border-white dark:border-slate-700 shadow-sm flex items-center justify-center overflow-hidden">
                        <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['username']; ?>&background=random" alt="User">
                    </div>
                </div>
            </header>
            <div class="md:hidden mb-6">
                <p class="text-xl font-bold text-slate-800 dark:text-white">Hi, <?php echo $_SESSION['username']; ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl md:rounded-3xl p-4 md:p-8 shadow-sm border border-slate-200 dark:border-slate-700 min-h-[70vh] transition-all">
                <router-view v-slot="{ Component }">
                    <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </div>
        </main>
    </div>

<script>
    const {createApp} = Vue;
    const {createRouter,createWebHashHistory} = VueRouter;
    const SwalTheme = Swal.mixin
    ({
        customClass: {
            confirmButton: 'bg-indigo-600 text-white font-bold py-3 px-6 rounded-xl hover:bg-indigo-700 transition-all mx-2',
            cancelButton: 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold py-3 px-6 rounded-xl hover:bg-slate-300 dark:hover:bg-slate-600 transition-all mx-2',
            popup: 'rounded-2xl font-sans dark:bg-slate-850 dark:text-white',
            title: 'text-slate-800 dark:text-white font-bold text-xl',
            htmlContainer: 'text-slate-500 dark:text-slate-400'
        },
        buttonsStyling: false
    });
    const SwalDanger = Swal.mixin
    ({
        customClass: {
            confirmButton: 'bg-red-500 text-white font-bold py-3 px-6 rounded-xl hover:bg-red-600 transition-all mx-2',
            cancelButton: 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold py-3 px-6 rounded-xl hover:bg-slate-300 dark:hover:bg-slate-600 transition-all mx-2',
            popup: 'rounded-2xl font-sans dark:bg-slate-850 dark:text-white',
            title: 'text-slate-800 dark:text-white font-bold text-xl',
            htmlContainer: 'text-slate-500 dark:text-slate-400'
        },
        buttonsStyling: false
    });
    const Dashboard = {
        data() {
            return {
                loading: true,
                stats: {
                    total_quizzes: 0,
                    total_attempts: 0
                }
            }
        },
        mounted() {
            this.fetchStats();
        },
        methods: {
            async fetchStats() {
                try {
                    const res = await fetch('../../api/quiz/dashboard.php');
                    const json = await res.json();
                    if (json.status === 'success') {
                        this.stats = json.data;
                    }
                } catch (error) {
                    console.error("Error loading dashboard:", error);
                } finally {
                    this.loading = false;
                }
            }
        },
        template: `
        <div class="max-w-5xl mx-auto">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 dark:text-white mb-2">Dashboard</h1>
            <p class="text-slate-500 dark:text-slate-400 mb-8">Overview of your learning progress.</p>
            <div v-if="loading" class="animate-pulse">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="h-32 bg-slate-200 dark:bg-slate-700 rounded-2xl"></div>
                    <div class="h-32 bg-slate-200 dark:bg-slate-700 rounded-2xl"></div>
                </div>
                <div class="h-48 bg-slate-200 dark:bg-slate-700 rounded-2xl"></div>
            </div>
            <div v-else>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-8">
                    <div class="p-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                        <div class="absolute right-0 top-0 p-4 opacity-30 group-hover:opacity-50 transition-opacity">
                            <svg class="w-24 h-24 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                            </svg>
                        </div>
                        <p class="text-indigo-600 dark:text-indigo-400 font-bold text-sm uppercase tracking-wide mb-1 relative z-10">My Quizzes</p>
                        <p class="text-4xl font-black text-slate-800 dark:text-white relative z-10">{{ stats.total_quizzes }}</p>
                    </div>
                    <div class="p-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                            <div class="absolute right-0 top-0 p-4 opacity-30 group-hover:opacity-50 transition-opacity">
                                <svg class="w-24 h-24 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                </div>
                        <p class="text-emerald-600 dark:text-emerald-400 font-bold text-sm uppercase tracking-wide mb-1 relative z-10">Quizzes Taken</p>
                        <p class="text-4xl font-black text-slate-800 dark:text-white relative z-10">{{ stats.total_attempts }}</p>
                    </div>
                </div>
                <div class="bg-indigo-900 dark:bg-indigo-950 rounded-3xl p-8 md:p-12 text-white shadow-xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-indigo-500 rounded-full blur-3xl opacity-20"></div>
                    <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-64 h-64 bg-purple-500 rounded-full blur-3xl opacity-20"></div>
                    <div class="relative z-10 max-w-xl text-center md:text-left">
                        <h2 class="text-3xl md:text-4xl font-extrabold mb-4 leading-tight">Ready to learn?</h2>
                        <p class="text-indigo-200 text-lg mb-0">Create a quiz from your documents instantly using AI, or challenge yourself with your existing quizzes.</p>
                    </div>
                    <div class="relative z-10 flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                        <router-link to="/create" class="px-8 py-4 bg-white text-indigo-900 font-bold rounded-xl hover:bg-indigo-50 transition-transform hover:scale-105 shadow-lg text-center flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Create Quiz
                        </router-link>
                        <router-link to="/answer" class="px-8 py-4 bg-indigo-800 text-white font-bold rounded-xl border border-indigo-700 hover:bg-indigo-700 transition-transform hover:scale-105 shadow-lg text-center flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Start Learning
                        </router-link>
                    </div>
                </div>
            </div>
        </div>`
    };
       
    const Create = {
        data() {
            return {
                activeTab: 'upload', 
                title: '',
                description: '',
                file: null,
                fileName: '',
                extractedText: '',
                loading: false,
                isSaving: false,
                loadingText: 'Loading...',
                questionCount: 5,
                quizType: 'multiple_choice', 
                manualQuestions: [{ 
                    type: 'MCQ', 
                    text: '', 
                    options: { A: '', B: '', C: '', D: '' }, 
                    correct: 'A' 
                }],
                isAiGenerated: false 
            }
        },
        methods: {
            async handleFileUpload(event) {
                this.file = event.target.files[0];
                if (!this.file) return;
                this.fileName = this.file.name;
                this.isAiGenerated = false;
                this.extractedText = '';
                const originalText = this.loadingText;
                this.loading = true;
                this.loadingText = "Reading file...";
                try {
                    if (this.file.type === 'application/pdf') {
                        this.extractedText = await this.extractPdfText(this.file);
                    } 
                    else if (this.file.type === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') {
                        this.extractedText = await this.extractPptxText(this.file);
                    } else {
                        SwalTheme.fire({ icon: 'error', title: 'Invalid File', text: 'Please upload a PDF or PPTX file.' });
                        this.file = null;
                        this.fileName = '';
                    }
                } catch (error) {
                    console.error(error);
                    SwalTheme.fire({ icon: 'error', title: 'Read Error', text: 'Could not parse this file.' });
                } finally {
                    this.loading = false;
                    this.loadingText = originalText;
                }
            },
            async handleCsvUpload(event) {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    try {
                        const text = e.target.result;
                        const rows = text.split("\n").filter(row => row.trim() !== "");
                        
                        const importedQuestions = rows.slice(1).map(row => {
                            const parts = row.match(/(".*?"|[^",]+)(?=\s*,|\s*$)/g);
                            if (!parts || parts.length < 7) return null;

                            const clean = (str) => str.replace(/^"|"$/g, '').replace(/""/g, '"').trim();

                            return {
                                text: clean(parts[0]),
                                type: clean(parts[1]).toUpperCase() === 'MCQ' ? 'MCQ' : 'IDENT',
                                options: {
                                    A: clean(parts[2]),
                                    B: clean(parts[3]),
                                    C: clean(parts[4]),
                                    D: clean(parts[5])
                                },
                                correct: clean(parts[6])
                            };
                        }).filter(q => q !== null);

                        if (importedQuestions.length > 0) {
                            this.manualQuestions = importedQuestions;
                            this.activeTab = 'manual';
                            this.isAiGenerated = true; 
                            SwalTheme.fire({ icon: 'success', title: 'CSV Imported', text: `Loaded ${importedQuestions.length} questions.` });
                        } else {
                            throw new Error("No valid rows found.");
                        }
                    } catch (err) {
                        SwalTheme.fire({ icon: 'error', title: 'Import Failed', text: 'Please check your CSV format.' });
                    }
                    event.target.value = ''; 
                };
                reader.readAsText(file);
            },
            async extractPdfText(file) {
                const arrayBuffer = await file.arrayBuffer();
                const pdf = await pdfjsLib.getDocument(arrayBuffer).promise;
                let fullText = "";
                for (let i = 1; i <= pdf.numPages; i++) {
                    const page = await pdf.getPage(i);
                    const textContent = await page.getTextContent();
                    const pageText = textContent.items.map(item => item.str).join(" ");
                    fullText += pageText + "\n";
                }
                return fullText;
            },
            async extractPptxText(file) {
                const zip = await JSZip.loadAsync(file);
                let fullText = "";
                const slideFiles = Object.keys(zip.files).filter(name => name.startsWith("ppt/slides/slide"));
                slideFiles.sort((a, b) => {
                    const numA = parseInt(a.match(/slide(\d+)\.xml/)[1]);
                    const numB = parseInt(b.match(/slide(\d+)\.xml/)[1]);
                    return numA - numB;
                });
                const parser = new DOMParser();
                for (const filename of slideFiles) {
                    const content = await zip.file(filename).async("string");
                    const xmlDoc = parser.parseFromString(content, "text/xml");
                    const textNodes = xmlDoc.getElementsByTagName("a:t");
                    for (let i = 0; i < textNodes.length; i++) {
                        fullText += textNodes[i].textContent + " ";
                    }
                    fullText += "\n";
                }
                return fullText;
            },
            async generateFromAI() {
                if (!this.extractedText) {
                    SwalTheme.fire({ icon: 'warning', title: 'No Content', text: 'Please upload a file first.' });
                    return;
                }

                let totalRequested = parseInt(this.questionCount);
                if (totalRequested > 100) totalRequested = 100;

                const batchSize = 20; 
                const totalBatches = Math.ceil(totalRequested / batchSize);
                
                this.loading = true;
                this.manualQuestions = []; 

                try {
                    for (let i = 0; i < totalBatches; i++) {
                        const remaining = totalRequested - (i * batchSize);
                        const currentBatchCount = Math.min(batchSize, remaining);
                        
                        this.loadingText = `AI generating batch ${i + 1} of ${totalBatches}...`;

                        let formData = new FormData();
                        formData.append('extractedText', this.extractedText);
                        formData.append('questionCount', currentBatchCount);
                        formData.append('quizType', this.quizType);

                        const response = await fetch('../../api/quiz/generate_ai.php', { method: 'POST', body: formData });
                        const result = await response.json();

                        if (result.status === 'success') {
                            this.manualQuestions.push(...result.data);
                        } else {
                            throw new Error(result.message);
                        }
                    }

                    this.activeTab = 'manual';
                    this.isAiGenerated = true;
                    SwalTheme.fire({ icon: 'success', title: 'Success!', text: `Generated ${this.manualQuestions.length} questions.` });

                } catch (error) {
                    SwalTheme.fire({ icon: 'error', title: 'AI Error', text: error.message });
                } finally {
                    this.loading = false;
                    this.loadingText = 'Loading...';
                }
            },
            addQuestion() {
                this.manualQuestions.push({ type: 'MCQ', text: '', options: { A: '', B: '', C: '', D: '' }, correct: 'A' });
            },
            removeQuestion(index) {
                if (this.manualQuestions.length > 1) {
                    this.manualQuestions.splice(index, 1);
                }
            },
            toggleQuestionType(index) {
                const q = this.manualQuestions[index];
                if (q.type === 'MCQ') {
                    q.type = 'IDENT';
                    q.correct = '';
                } else {
                    q.type = 'MCQ';
                    q.correct = 'A';
                }
            },
            async submitQuiz() {
                if (!this.title) { 
                    SwalTheme.fire({ icon: 'warning', title: 'Missing Title', text: 'Please enter a quiz title.' }); 
                    return; 
                }
                this.isSaving = true;
                let formData = new FormData();
                formData.append('title', this.title);
                formData.append('description', this.description);
                formData.append('mode', this.activeTab);
                formData.append('questions', JSON.stringify(this.manualQuestions));
                try {
                    const response = await fetch('../../api/quiz/create.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.status === 'success') {
                        await SwalTheme.fire({ icon: 'success', title: 'Quiz Saved!', text: 'Redirecting...', timer: 1500, showConfirmButton: false });
                        this.$router.push('/');
                    } else {
                        SwalTheme.fire({ icon: 'error', title: 'Error', text: result.message });
                    }
                } catch (error) {
                    SwalTheme.fire({ icon: 'error', title: 'System Error', text: 'An error occurred while saving.' });
                } finally {
                this.isSaving = false;
                }
            }
        },
        template: `
            <div class="max-w-4xl mx-auto">
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 dark:text-white mb-8">Create New Quiz</h1>
                <div class="bg-white dark:bg-slate-800 p-4 md:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 mb-6 space-y-4 shadow-sm">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Quiz Title</label>
                        <input v-model="title" type="text" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 focus:border-indigo-500 dark:text-white outline-none transition-all" placeholder="e.g., Filipino History">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Description</label>
                        <textarea v-model="description" rows="2" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 focus:border-indigo-500 dark:text-white outline-none transition-all" placeholder="Optional details..."></textarea>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-2 md:gap-4 mb-6">
                    <button @click="activeTab = 'upload'" 
                        :class="activeTab === 'upload' ? 'bg-indigo-600 text-white ring-2 ring-indigo-200 dark:ring-indigo-900' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 border-slate-200 dark:border-slate-700'" 
                        class="flex-1 py-3 px-4 rounded-xl font-bold transition-all shadow-sm border flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        AI Generator
                    </button>
                    <label 
                        :class="activeTab === 'csv' ? 'bg-indigo-600 text-white ring-2 ring-indigo-200 dark:ring-indigo-900' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 border-slate-200 dark:border-slate-700'"
                        class="flex-1 py-3 px-4 rounded-xl font-bold cursor-pointer transition-all shadow-sm border flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import CSV
                        <input type="file" class="hidden" accept=".csv" @change="handleCsvUpload" @click="activeTab = 'csv'" />
                    </label>

                    <button @click="activeTab = 'manual'" 
                        :class="activeTab === 'manual' ? 'bg-indigo-600 text-white ring-2 ring-indigo-200 dark:ring-indigo-900' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 border-slate-200 dark:border-slate-700'" 
                        class="flex-1 py-3 px-4 rounded-xl font-bold transition-all shadow-sm border flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Manual
                    </button>
                </div>
                <div v-if="activeTab === 'upload'" class="bg-white dark:bg-slate-800 p-4 md:p-8 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm relative">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-4">Upload PDF or PPTX</label>
                    <label class="flex flex-col items-center justify-center w-full h-48 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-2xl cursor-pointer bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all text-center p-4 group relative overflow-hidden">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6 z-10">
                            <svg class="w-10 h-10 mb-3 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            <p v-if="!fileName" class="mb-2 text-sm text-slate-500 dark:text-slate-400"><span class="font-semibold">Click to upload</span></p>
                            <p v-if="fileName" class="mt-2 text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1 rounded-lg border border-indigo-100 dark:border-indigo-800 text-sm flex items-center gap-2">{{ fileName }}</p>
                        </div>
                        <input type="file" class="hidden" accept=".pdf,.pptx" @change="handleFileUpload" />
                    </label>
                    <div v-if="fileName" class="mt-6 border-t border-slate-100 dark:border-slate-700 pt-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Number of Questions</label>
                                <input type="number" v-model="questionCount" min="1" max="100" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 focus:border-indigo-500 dark:text-white outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Quiz Type</label>
                                <select v-model="quizType" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 focus:border-indigo-500 dark:text-white outline-none">
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="identification">Identification</option>
                                    <option value="mixed">Mixed</option>
                                </select>
                            </div>
                        </div>
                        <button @click="generateFromAI" :disabled="loading" class="w-full py-3 px-6 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold transition-all flex items-center justify-center gap-2 disabled:opacity-50 h-[50px]">
                            <svg v-if="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>{{ loading ? loadingText : 'Generate Quiz with AI' }}</span>
                        </button>
                    </div>
                </div>
                <div v-if="activeTab === 'manual'" class="space-y-6">
                    <div v-for="(question, index) in manualQuestions" :key="index" class="bg-white dark:bg-slate-800 p-4 md:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm relative group">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-800 dark:text-white">Question {{ index + 1 }}</h3>
                                <span class="text-xs font-bold px-2 py-1 rounded bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 cursor-pointer hover:bg-indigo-100 dark:hover:bg-indigo-900 transition-colors" @click="toggleQuestionType(index)">
                                    {{ question.type === 'MCQ' ? 'MC' : 'Ident' }} (Swap)
                                </span>
                            </div>
                            <button v-if="manualQuestions.length > 1" @click="removeQuestion(index)" class="text-slate-400 hover:text-red-500 text-sm font-medium transition-colors">Remove</button>
                        </div>
                        <input v-model="question.text" type="text" class="w-full mb-4 px-4 py-3 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 focus:border-indigo-500 dark:text-white outline-none font-medium" placeholder="Enter your question here...">
                        <div v-if="question.type === 'MCQ'" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div v-for="opt in ['A', 'B', 'C', 'D']" :key="opt" class="flex items-center gap-2">
                                <span class="font-bold text-slate-400 w-6 flex-shrink-0">{{ opt }}.</span>
                                <input v-model="question.options[opt]" type="text" class="flex-1 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-sm focus:border-indigo-500 dark:text-white outline-none transition-colors" :placeholder="'Option ' + opt">
                            </div>
                        </div>
                        <div v-else class="mb-4">
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Correct Answer</label>
                            <input v-model="question.correct" type="text" class="w-full px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400 font-bold outline-none" placeholder="Type answer here...">
                        </div>
                        <div v-if="question.type === 'MCQ'" class="flex flex-wrap items-center gap-2 md:gap-4 text-sm bg-slate-50 dark:bg-slate-700/50 p-3 rounded-lg border border-slate-100 dark:border-slate-700">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Correct Answer:</span>
                            <div class="flex gap-4">
                                <label v-for="opt in ['A', 'B', 'C', 'D']" :key="opt" class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" :name="'correct-' + index" :value="opt" v-model="question.correct" class="text-indigo-600">
                                    <span class="text-slate-600 dark:text-slate-300 font-bold">{{ opt }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <button @click="addQuestion" type="button" class="w-full py-4 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl text-slate-500 dark:text-slate-400 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex justify-center items-center gap-2">Add Question</button>
                </div>
                <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-700">
                    <button @click="submitQuiz" :disabled="loading || isSaving" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-xl shadow-lg transition-all flex justify-center items-center gap-2 disabled:opacity-50">
                    <svg v-if="isSaving" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ isSaving ? 'Saving Quiz to Database...' : 'Save Quiz' }}</span>
                    </button>
                </div>
            </div>`
    };

    const Answer = {
        data() {
            return {
                view: 'list',
                loading: false,
                quizzes: [],
                currentQuiz: null,
                questions: [],
                userAnswers: {},
                attemptHistory: [],
                reviewQuestions: []
            }
        },
        mounted() {
            this.fetchQuizzes();
        },
        methods: {
            formatDate(dateString) {
                if (!dateString) return '';
                return new Date(dateString).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },
            async fetchQuizzes() {
                this.loading = true;
                try {
                    const res = await fetch('../../api/quiz/read.php');
                    const json = await res.json();
                    if (json.status === 'success') this.quizzes = json.data;
                } finally {
                    this.loading = false;
                }
            },
            handleQuizClick(quiz) {
                if (quiz.file_type !== 'manual') {
                    const path = quiz.file_path.replace('./', '../../api/quiz/');
                    window.open(path, '_blank');
                } else if (quiz.last_attempt_date) {
                    this.showAttempts(quiz);
                } else {
                    this.startQuiz(quiz);
                }
            },
            async showAttempts(quiz) {
                this.loading = true;
                this.currentQuiz = quiz;
                try {
                    const res = await fetch(`../../api/quiz/get_attempts.php?quiz_id=${quiz.id}`);
                    const json = await res.json();
                    this.attemptHistory = json.data;
                    this.view = 'attempts_history';
                } finally {
                    this.loading = false;
                }
            },
            async startQuiz(quiz) {
                this.loading = true;
                this.currentQuiz = quiz;
                this.userAnswers = {};
                try {
                    const res = await fetch(`../../api/quiz/get_questions.php?id=${quiz.id}`);
                    const json = await res.json();
                    this.questions = json.data;
                    this.view = 'taking';
                } finally {
                    this.loading = false;
                }
            },
            async submitQuiz() {
                const unansweredCount = this.questions.length - Object.keys(this.userAnswers).length;

                if (unansweredCount > 0) {
                    const result = await SwalTheme.fire({
                        title: 'Unanswered Questions',
                        text: `You have ${unansweredCount} unanswered questions. Submit anyway?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Submit'
                    });
                    if (!result.isConfirmed) return;
                }
                this.loading = true;
                try {
                    const response = await fetch('../../api/quiz/save_score.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            quiz_id: this.currentQuiz.id,
                            details: this.userAnswers
                        })
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        SwalTheme.fire({
                            title: 'Quiz Completed!',
                            text: `You scored ${result.score} out of ${result.total}`,
                            icon: 'success'
                        });
                        this.fetchQuizzes();
                        await this.showAttempts(this.currentQuiz);
                    } else {
                        SwalTheme.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message
                        });
                    }
                } catch (error) {
                    SwalTheme.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Network error saving score.'
                    });
                } finally {
                    this.loading = false;
                }
            },
            async openReview(attempt) {
                this.loading = true;
                try {
                    const res = await fetch(`../../api/quiz/get_review.php?attempt_id=${attempt.id}`);
                    const json = await res.json();
                    this.reviewQuestions = json.data;
                    this.view = 'review';
                } finally {
                    this.loading = false;
                }
            }
        },
        template: `
        <div class="max-w-5xl mx-auto">
            <div v-if="view === 'list'">
                <h1 class="text-2xl font-extrabold text-slate-800 dark:text-white mb-6">Available Quizzes</h1>
                <div v-if="quizzes.length === 0" class="text-center py-10 text-slate-500 dark:text-slate-400">No quizzes available.</div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="quiz in quizzes" :key="quiz.id" class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all flex flex-col">
                        <div class="flex justify-between items-start mb-4">
                            <span v-if="quiz.last_attempt_date" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded">Done</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">{{ quiz.title }}</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6 line-clamp-2 flex-1">{{ quiz.description || 'No description.' }}</p>
                        <div v-if="quiz.last_attempt_date" class="mb-4 pt-3 border-t border-slate-100 dark:border-slate-700 flex justify-between items-center text-sm">
                            <span class="text-slate-400 dark:text-slate-500">Last Score:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ quiz.last_score }} / {{ quiz.last_total }}</span>
                        </div>
                        <button @click="handleQuizClick(quiz)" class="w-full py-3 rounded-xl font-bold transition-all flex justify-center items-center gap-2" :class="quiz.last_attempt_date ? 'bg-white dark:bg-slate-800 border-2 border-indigo-600 dark:border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'bg-indigo-600 text-white'">
                            <span v-if="quiz.file_type !== 'manual'">Download</span>
                            <span v-else-if="quiz.last_attempt_date">See Attempts</span>
                            <span v-else>Start Quiz</span>
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="view === 'attempts_history'">
                <div class="flex items-center gap-4 mb-6">
                    <button @click="view='list'" class="text-slate-500 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-700 px-3 py-2 rounded-lg">← Back</button>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">History: {{ currentQuiz.title }}</h1>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm mb-6">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 text-xs uppercase font-semibold border-b border-slate-200 dark:border-slate-600">
                            <tr>
                                <th class="p-4">Date</th>
                                <th class="p-4">Score</th>
                                <th class="p-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
                            <tr v-for="attempt in attemptHistory" :key="attempt.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <td class="p-4 text-slate-600 dark:text-slate-300">{{ formatDate(attempt.attempted_at) }}</td>
                                <td class="p-4 font-bold">{{ attempt.score }} / {{ attempt.total_questions }}</td>
                                <td class="p-4 text-right">
                                    <button @click="openReview(attempt)" class="text-indigo-600 dark:text-indigo-400 font-bold text-sm hover:underline">Review →</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button @click="startQuiz(currentQuiz)" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-700">Retake Quiz</button>
            </div>
            <div v-if="view === 'taking'">
                <div class="flex items-center gap-4 mb-6">
                    <button @click="view='list'" class="text-slate-500 dark:text-slate-400">Cancel</button>
                    <h1 class="text-xl font-bold text-slate-800 dark:text-white">{{ currentQuiz.title }}</h1>
                </div>
                <div class="space-y-6">
                    <div v-for="(q, index) in questions" :key="q.id" class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4">
                            <span class="text-slate-400 dark:text-slate-500 mr-2">{{ index + 1 }}.</span>
                            {{ q.question_text }}
                        </h3>
                        <div v-if="q.option_a" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label v-for="opt in ['A','B','C','D']" :key="opt" 
                                class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700" 
                                :class="userAnswers[q.id] === opt ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 dark:border-indigo-500 ring-1 ring-indigo-600 dark:ring-indigo-500' : 'border-slate-100 dark:border-slate-600'">
                                <input type="radio" :name="'q'+q.id" :value="opt" v-model="userAnswers[q.id]" class="text-indigo-600">
                                <span class="font-bold text-slate-400 dark:text-slate-500 w-6">{{ opt }}.</span>
                                <span class="text-slate-700 dark:text-slate-300">{{ q['option_'+opt.toLowerCase()] }}</span>
                            </label>
                        </div>
                        <div v-else>
                            <label class="block text-sm font-bold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wide">Your Answer:</label>
                            <input type="text" v-model="userAnswers[q.id]" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:border-indigo-500 dark:focus:border-indigo-400 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-white outline-none transition-all"
                                placeholder="Type your answer here...">
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button @click="submitQuiz" :disabled="loading" class="px-8 py-4 bg-indigo-600 text-white font-bold rounded-xl shadow-xl hover:bg-indigo-700">
                        {{ loading ? 'Calculating...' : 'Submit Quiz' }}
                    </button>
                </div>
            </div>
            <div v-if="view === 'review'">
                <div class="flex items-center gap-4 mb-6">
                    <button @click="view='attempts_history'" class="text-slate-500 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-700 px-3 py-2 rounded-lg">← Back</button>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Review Answers</h1>
                </div>
                <div class="space-y-6">
                    <div v-for="(q, index) in reviewQuestions" :key="q.id" 
                        class="bg-white dark:bg-slate-800 p-6 rounded-2xl border-2 shadow-sm relative overflow-hidden" 
                        :class="(q.user_answer || '').trim().toLowerCase() === q.correct_option.trim().toLowerCase() ? 'border-emerald-100 dark:border-emerald-900' : 'border-red-100 dark:border-red-900'">
                        <div class="absolute top-0 right-0 px-4 py-1 text-xs font-bold text-white rounded-bl-xl" 
                                :class="(q.user_answer || '').trim().toLowerCase() === q.correct_option.trim().toLowerCase() ? 'bg-emerald-500' : (q.user_answer ? 'bg-red-500' : 'bg-gray-400')">
                            {{ (q.user_answer || '').trim().toLowerCase() === q.correct_option.trim().toLowerCase() ? 'Correct' : (q.user_answer ? 'Incorrect' : 'Skipped') }}
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 pr-12">
                            <span class="text-slate-400 mr-2">{{ index + 1 }}.</span> {{ q.question_text }}
                        </h3>
                        <div v-if="q.option_a" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div v-for="opt in ['A','B','C','D']" :key="opt" 
                                class="p-3 rounded-lg border flex items-center gap-3 transition-colors" 
                                :class="{
                                    'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-500 text-emerald-900 dark:text-emerald-400 font-bold ring-1 ring-emerald-500': q.correct_option === opt, 
                                    'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-800 text-red-900 dark:text-red-400': q.user_answer === opt && q.user_answer !== q.correct_option, 
                                    'bg-slate-50 dark:bg-slate-700 border-slate-100 dark:border-slate-600 text-slate-400 dark:text-slate-500 opacity-60': q.user_answer !== opt && q.correct_option !== opt
                                }">
                                <div class="flex items-center gap-2 min-w-[50px]">
                                    <span v-if="q.correct_option === opt">✅</span>
                                    <span v-else-if="q.user_answer === opt">❌</span>
                                    <span v-else class="inline-block w-5"></span> <span class="font-bold">{{ opt }}.</span>
                                </div>
                                <span class="text-sm">{{ q['option_'+opt.toLowerCase()] }}</span>
                            </div>
                        </div>
                        <div v-else>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600">
                                    <span class="text-xs font-bold text-slate-400 uppercase">Your Answer</span>
                                    <p class="text-lg font-medium" :class="(q.user_answer || '').trim().toLowerCase() === q.correct_option.trim().toLowerCase() ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                                        {{ q.user_answer || '(No Answer)' }}
                                    </p>
                                </div>
                                <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
                                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase">Correct Answer</span>
                                    <p class="text-lg font-bold text-emerald-800 dark:text-emerald-300">{{ q.correct_option }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`
    };
    const QuizList = {
        data() {
            return {
                loading: false,
                quizzes: [],
                expandedQuizId: null,
                expandedQuizData: null
            }
        },
        mounted() {
            this.fetchQuizzes();
        },
        methods: {
            async fetchQuizzes() {
                this.loading = true;
                try {
                    const res = await fetch('../../api/quiz/read.php');
                    const json = await res.json();
                    if (json.status === 'success') {
                        this.quizzes = json.data;
                    }
                } catch (error) {
                    console.error(error);
                } finally {
                    this.loading = false;
                }
            },
            async toggleDetails(quizId) {
                if (this.expandedQuizId === quizId) {
                    this.expandedQuizId = null;
                    this.expandedQuizData = null;
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch(`../../api/quiz/get_full_quiz.php?id=${quizId}`);
                    const json = await res.json();
                    if (json.status === 'success') {
                        this.expandedQuizData = json.data;
                        this.expandedQuizId = quizId;
                    }
                } catch (e) {
                    SwalTheme.fire({ icon: 'error', title: 'Error', text: 'Failed to load details' });
                } finally {
                    this.loading = false;
                }
            },
            downloadCSV(quiz) {
                if (!this.expandedQuizData || !this.expandedQuizData.questions) return;
                
                let csvContent = "Question,Type,Option A,Option B,Option C,Option D,Correct Answer\n";
                
                this.expandedQuizData.questions.forEach(q => {
                    const row = [
                        `"${(q.question_text || '').replace(/"/g, '""')}"`,
                        q.option_a ? "MCQ" : "IDENT",
                        `"${(q.option_a || '').replace(/"/g, '""')}"`,
                        `"${(q.option_b || '').replace(/"/g, '""')}"`,
                        `"${(q.option_c || '').replace(/"/g, '""')}"`,
                        `"${(q.option_d || '').replace(/"/g, '""')}"`,
                        `"${(q.correct_option || '').replace(/"/g, '""')}"`
                    ];
                    csvContent += row.join(",") + "\n";
                });

                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement("a");
                const url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", `${quiz.title.replace(/\s+/g, '_')}.csv`);
                link.click();
            },
            async deleteQuiz(quiz) {
                const result = await SwalDanger.fire({
                    title: `Delete "${quiz.title}"?`,
                    text: "All data for this quiz will be erased permanentely.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                });
                if (!result.isConfirmed) return;
                try {
                    const res = await fetch('../../api/quiz/delete.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: quiz.id })
                    });
                    const json = await res.json();
                    if (json.status === 'success') {
                        SwalTheme.fire({ icon: 'success', title: 'Deleted!', timer: 1500, showConfirmButton: false });
                        this.fetchQuizzes();
                        this.expandedQuizId = null;
                    }
                } catch (e) {
                    SwalTheme.fire({ icon: 'error', title: 'Error', text: 'Could not delete quiz.' });
                }
            }
        },
        template: `
            <div class="max-w-5xl mx-auto">
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 dark:text-white mb-8">Manage Quizzes</h1>
                <div v-if="!loading && quizzes.length === 0" class="text-center py-10 text-slate-500">No quizzes created yet.</div>
                <div class="space-y-6">
                    <div v-for="quiz in quizzes" :key="quiz.id" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden hover:shadow-md transition-all">
                        <div class="p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-slate-800 dark:text-white">{{ quiz.title }}</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm">{{ quiz.description || 'No description provided.' }}</p>
                            </div>
                            <div class="flex items-center gap-3 w-full md:w-auto">
                                <button @click="toggleDetails(quiz.id)" class="px-4 py-2 rounded-lg border font-bold text-sm bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-slate-600">
                                    {{ expandedQuizId === quiz.id ? 'Hide' : 'View Content' }}
                                </button>
                                <button @click="deleteQuiz(quiz)" class="px-4 py-2 rounded-lg border border-red-200 text-red-600 font-bold text-sm">Delete</button>
                            </div>
                        </div>
                        <div v-if="expandedQuizId === quiz.id && expandedQuizData" class="border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="font-bold text-slate-700 dark:text-slate-400 uppercase tracking-wider text-xs">Quiz Content</h4>
                                <button v-if="quiz.file_type === 'manual'" @click="downloadCSV(quiz)" class="text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center gap-1 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Export to CSV
                                </button>
                            </div>
                            <div v-if="quiz.file_type !== 'manual'" class="text-center py-4">
                                <a :href="quiz.file_path.replace('./', '../../api/quiz/')" target="_blank" class="text-blue-600 font-bold hover:underline">Download Original File</a>
                            </div>
                            <div v-else class="grid gap-4">
                                <div v-for="(q, idx) in expandedQuizData.questions" :key="q.id" class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <p class="font-bold text-slate-800 dark:text-white mb-2">{{ idx + 1 }}. {{ q.question_text }}</p>
                                    <div v-if="q.option_a" class="grid grid-cols-2 gap-2 text-sm ml-4">
                                        <div v-for="opt in ['A','B','C','D']" :key="opt" :class="q.correct_option === opt ? 'text-emerald-600 font-bold' : 'text-slate-500'">
                                            {{ opt }}: {{ q['option_'+opt.toLowerCase()] }} {{ q.correct_option === opt ? '✅' : '' }}
                                        </div>
                                    </div>
                                    <div v-else class="ml-4 text-sm text-emerald-600 font-bold">Answer: {{ q.correct_option }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`
    };

    const Account = {
        data() {
            return {
                user: {
                    username: 'Loading...',
                    email: '...',
                    created_at: ''
                },
                loading: false,
                message: '',
                isError: false,
                showPassModal: false,
                step: 1,
                otp: '',
                newPassword: '',
                confirmPassword: ''
            }
        },
        mounted() {
            this.fetchUserInfo();
        },
        methods: {
            async fetchUserInfo() {
                try {
                    const res = await fetch('../../api/user/get_info.php');
                    const json = await res.json();
                    if (json.status === 'success') this.user = json.data;
                } catch (e) {
                    console.error(e);
                }
            },
            openPasswordModal() {
                this.showPassModal = true;
                this.step = 1;
                this.message = '';
                this.otp = '';
                this.newPassword = '';
                this.confirmPassword = '';
            },
            async sendCode() {
                this.loading = true;
                this.message = '';
                try {
                    const res = await fetch('../../api/user/request_otp.php');
                    const json = await res.json();
                    if (json.status === 'success') {
                        this.step = 2;
                        this.isError = false;
                        this.message = `Code sent to ${this.user.email}`;
                    } else {
                        this.isError = true;
                        this.message = json.message;
                    }
                } catch (e) {
                    this.isError = true;
                    this.message = "Network error";
                } finally {
                    this.loading = false;
                }
            },
            async updatePassword() {
                if (!this.otp || this.otp.length !== 6) {
                    this.isError = true;
                    this.message = "Please enter the valid 6-digit code.";
                    return;
                }
                if (this.newPassword.length < 6) {
                    this.isError = true;
                    this.message = "New password must be at least 6 characters.";
                    return;
                }
                if (this.newPassword !== this.confirmPassword) {
                    this.isError = true;
                    this.message = "Passwords do not match!";
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch('../../api/user/update_password.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            otp: this.otp,
                            new_password: this.newPassword
                        })
                    });
                    const json = await res.json();
                    if (json.status === 'success') {
                        SwalTheme.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Password Changed Successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        this.showPassModal = false;
                    } else {
                        this.isError = true;
                        this.message = json.message;
                    }
                } catch (e) {
                    this.isError = true;
                    this.message = "Server Error";
                } finally {
                    this.loading = false;
                }
            }
        },
        template: `
            <div class="max-w-4xl mx-auto">
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 dark:text-white mb-8">My Account</h1>
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-8">
                    <div class="p-8 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-indigo-600 text-white flex items-center justify-center text-2xl font-bold uppercase shadow-lg shadow-indigo-200 dark:shadow-none">
                            {{ user.username.charAt(0) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-800 dark:text-white">{{ user.username }}</h2>
                            <p class="text-slate-500 dark:text-slate-400 text-sm">Member since {{ new Date(user.created_at).getFullYear() }}</p>
                        </div>
                    </div>
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Username</label>
                                <p class="text-lg font-medium text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700 p-3 rounded-lg border border-slate-100 dark:border-slate-600">{{ user.username }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Email Address</label>
                                <p class="w-full text-lg font-medium text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700 p-3 rounded-lg border border-slate-100 dark:border-slate-600 overflow-hidden text-ellipsis">{{ user.email }}</p>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-700">
                            <h3 class="font-bold text-slate-800 dark:text-white mb-4">Security</h3>
                            <button @click="openPasswordModal" class="px-6 py-3 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-bold rounded-xl hover:border-indigo-600 dark:hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Change Password via Email
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="showPassModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 relative animate-scale-in dark:border dark:border-slate-700">
                        <button @click="showPassModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">✕</button>
                        
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Change Password</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">We will send a verification code to <strong>{{ user.email }}</strong>.</p>
                        
                        <div v-if="message" class="mb-4 p-3 rounded-lg text-sm font-medium" :class="isError ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' : 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400'">{{ message }}</div>
                        
                        <div v-if="step === 1">
                            <button @click="sendCode" :disabled="loading" class="w-full py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all flex justify-center items-center gap-2">
                                <span v-if="loading" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                                <span>{{ loading ? 'Sending...' : 'Send Verification Code' }}</span>
                            </button>
                        </div>
                        <div v-if="step === 2" class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Enter 6-Digit Code</label>
                                <input v-model="otp" type="text" maxlength="6" class="w-full p-3 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-center text-2xl font-mono tracking-widest focus:border-indigo-500 dark:text-white outline-none" placeholder="000000">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">New Password (6 Digits)</label>
                                <input v-model="newPassword" type="password" maxlength="6" class="w-full p-3 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-center text-lg tracking-wider focus:border-indigo-500 dark:text-white outline-none" placeholder="6-char PIN">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Confirm New Password</label>
                                <input v-model="confirmPassword" type="password" maxlength="6" class="w-full p-3 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-center text-lg tracking-wider focus:border-indigo-500 dark:text-white outline-none" 
                                    :class="confirmPassword && newPassword !== confirmPassword ? 'border-red-300 dark:border-red-500 bg-red-50 dark:bg-red-900/20' : ''"
                                    placeholder="Confirm PIN">
                                <p v-if="confirmPassword && newPassword !== confirmPassword" class="text-xs text-red-500 dark:text-red-400 mt-1">Passwords do not match</p>
                            </div>
                            <button @click="updatePassword" :disabled="loading" class="w-full py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all flex justify-center items-center gap-2">
                                <span v-if="loading" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                                <span>{{ loading ? 'Updating...' : 'Save New Password' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`
    };
    const routes = [
        {path: '/',component: Dashboard},
        {path: '/account',component: Account},
        {path: '/create',component: Create},
        {path: '/answer',component: Answer},
        {path: '/quizlist',component: QuizList},
        ];
    const router = createRouter({
        history: createWebHashHistory(),
        routes
        });
        const app = createApp({
            data() {
                return {
                    isSidebarOpen: false,
                    isDark: false
                }
            },
            mounted() {
                if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    this.isDark = true;
                    document.documentElement.classList.add('dark');
                } else {
                    this.isDark = false;
                    document.documentElement.classList.remove('dark');
                }
            },
            methods: {
                closeSidebar() {
                    this.isSidebarOpen = false;
                },
                toggleTheme() {
                    this.isDark = !this.isDark;
                    if (this.isDark) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    }
                }
            }
        });
        app.use(router);
        app.mount('#app');
    </script>
</body>

</html>