<div x-data="{ show: false, id: null, eventName: '', title: '', message: '' }"
    @open-delete-modal.window="
        show = true;
        id = $event.detail.id;
        eventName = $event.detail.eventName;
        title = $event.detail.title || 'Konfirmasi Hapus';
        message = $event.detail.message || 'Data yang dihapus tidak dapat dikembalikan.';
    "
    @keydown.escape.window="show = false" x-show="show" style="display: none;"
    class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div @click.outside="show = false"
        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-2xl w-full max-w-md overflow-hidden transform"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full dark:bg-red-900/30">
                <svg class="w-6 h-6 text-red-600 dark:text-red-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
            </div>
            <div class="mt-5 text-center">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="title"></h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" x-text="message"></p>
            </div>
        </div>

        <div class="flex gap-3 p-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800">
            <button @click="show = false" type="button"
                class="w-full px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-4 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700 transition">
                Batal
            </button>

            <button @click="$dispatch(eventName, { id: id }); show = false" type="button"
                class="w-full px-4 py-2.5 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900 transition">
                Ya, Hapus Permanen
            </button>
        </div>
    </div>
</div>
