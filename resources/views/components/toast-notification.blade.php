<div x-data="notificationToast()"
     class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 w-full max-w-sm pointer-events-none">
    
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4"
             x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0 scale-90"
             class="pointer-events-auto w-full bg-white shadow-xl rounded-2xl p-4 border-l-4 overflow-hidden flex items-start gap-3 relative"
             :class="{
                 'border-blue-500': toast.type === 'new_message',
                 'border-emerald-500': toast.type === 'global_announcement',
                 'border-amber-500': toast.type === 'achievement_unlocked',
                 'border-purple-500': toast.type === 'level_up',
                 'border-gray-500': !['new_message', 'global_announcement', 'achievement_unlocked', 'level_up'].includes(toast.type)
             }">
            
            <!-- Glow Effect -->
            <div class="absolute inset-0 bg-gradient-to-r from-transparent to-white opacity-50 pointer-events-none"></div>

            <div class="shrink-0 mt-0.5" :class="{
                 'text-blue-500': toast.type === 'new_message',
                 'text-emerald-500': toast.type === 'global_announcement',
                 'text-amber-500': toast.type === 'achievement_unlocked',
                 'text-purple-500': toast.type === 'level_up',
                 'text-gray-500': !['new_message', 'global_announcement', 'achievement_unlocked', 'level_up'].includes(toast.type)
             }">
                <i :class="toast.icon || 'fas fa-info-circle'" class="text-xl drop-shadow-sm"></i>
            </div>
            
            <div class="flex-1 w-0 relative z-10">
                <p class="text-xs font-black text-gray-900 uppercase tracking-widest" x-text="toast.title"></p>
                <p class="mt-1 text-[11px] font-medium text-gray-600 leading-snug line-clamp-2" x-text="toast.message"></p>
            </div>
            
            <div class="shrink-0 flex relative z-10">
                <button @click="remove(toast.id)" class="bg-gray-50 rounded-full w-6 h-6 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]">
                    <span class="sr-only">Tutup</span>
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notificationToast', () => ({
            toasts: [],
            lastNotificationId: null,
            pollInterval: null,

            add(toast) {
                const id = Date.now();
                this.toasts.push({
                    id: id,
                    type: toast.type || 'info',
                    title: toast.title || 'Notifikasi',
                    message: toast.message || '',
                    icon: toast.icon || 'fas fa-bell',
                    visible: true
                });
                
                setTimeout(() => {
                    this.remove(id);
                }, toast.timeout || 6000);
            },
            
            remove(id) {
                const toast = this.toasts.find(t => t.id === id);
                if (toast) {
                    toast.visible = false;
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 300);
                }
            },

            init() {
                // Listen to window events
                this.$watch('$store.notifications.latest', (val) => {
                    if (val && val.id !== this.lastNotificationId) {
                        this.lastNotificationId = val.id;
                        this.add({
                            type: val.data.type,
                            title: val.data.title,
                            message: val.data.body || val.data.subtitle,
                            icon: val.data.icon || this.getIconForType(val.data.type)
                        });
                    }
                });

                // Polling for new notifications every 15 seconds
                this.startPolling();
            },

            getIconForType(type) {
                switch(type) {
                    case 'new_message': return 'fas fa-envelope';
                    case 'global_announcement': return 'fas fa-bullhorn';
                    case 'achievement_unlocked': return 'fas fa-trophy';
                    case 'level_up': return 'fas fa-arrow-up';
                    default: return 'fas fa-bell';
                }
            },

            async startPolling() {
                // Fetch latest immediately to set baseline
                await this.fetchLatest();
                
                this.pollInterval = setInterval(async () => {
                    await this.fetchLatest();
                }, 15000);
            },

            async fetchLatest() {
                try {
                    const response = await fetch('/communication/notifications/latest-unread', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.notification) {
                            if (this.lastNotificationId !== data.notification.id) {
                                // If this is a new notification we haven't seen yet
                                if (this.lastNotificationId !== null) {
                                    // Only show toast if it isn't the first load
                                    this.add({
                                        type: data.notification.data.type,
                                        title: data.notification.data.title,
                                        message: data.notification.data.body || data.notification.data.subtitle,
                                        icon: data.notification.data.icon || this.getIconForType(data.notification.data.type)
                                    });
                                }
                                this.lastNotificationId = data.notification.id;
                                
                                // Optionally dispatch event to update the bell counter
                                this.$dispatch('update-unread-count', data.unread_count);
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error polling notifications:', error);
                }
            }
        }));
    });
</script>
