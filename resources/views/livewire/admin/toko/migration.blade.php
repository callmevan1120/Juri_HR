@if ($activePage === 'migration')
    <x-admin.panel class="border-0 shadow-sm bg-white dark:bg-slate-900">
        <div class="px-6 py-6 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Import Data (Excel / CSV)') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Pilih tipe data dan unggah file Excel/CSV untuk memperbarui database Anda. Pastikan format kolom sesuai dengan template.') }}</p>
        </div>

        <div class="px-6 py-6">
            @if(session('success'))
                <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-100 p-4 text-emerald-800 dark:bg-emerald-950/30 dark:border-emerald-900/40 dark:text-emerald-300">
                    <div class="flex items-center gap-3">
                        <x-heroicon-s-check-circle class="h-5 w-5" />
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-xl bg-rose-50 border border-rose-100 p-4 text-rose-800 dark:bg-rose-950/30 dark:border-rose-900/40 dark:text-rose-300">
                    <div class="flex items-center gap-3">
                        <x-heroicon-s-x-circle class="h-5 w-5" />
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <div class="mb-8 w-full">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">{{ __('Download Template CSV') }}</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ asset('templates/toko-products.csv') }}" download class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 border border-slate-200 shadow-sm transition-all dark:bg-slate-800/50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        <x-heroicon-o-document-arrow-down class="h-4 w-4" />
                        <span>Template Produk</span>
                    </a>
                    <a href="{{ asset('templates/toko-categories.csv') }}" download class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 border border-slate-200 shadow-sm transition-all dark:bg-slate-800/50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        <x-heroicon-o-document-arrow-down class="h-4 w-4" />
                        <span>Template Kategori</span>
                    </a>
                    <a href="{{ asset('templates/toko-brands.csv') }}" download class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 border border-slate-200 shadow-sm transition-all dark:bg-slate-800/50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        <x-heroicon-o-document-arrow-down class="h-4 w-4" />
                        <span>Template Brand</span>
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.toko.import') }}" method="POST" enctype="multipart/form-data" class="w-full">
                @csrf
                <div class="space-y-6">
                    <!-- Import Type -->
                    <div>
                        <label for="import_type" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">{{ __('Tipe Data') }}</label>
                        <select name="import_type" id="import_type" class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
                            <option value="">{{ __('Pilih Tipe Data...') }}</option>
                            <option value="products">{{ __('Data Barang / Produk') }}</option>
                            <option value="categories">{{ __('Data Kategori') }}</option>
                            <option value="brands">{{ __('Data Brand / Merek') }}</option>
                        </select>
                        @error('import_type') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">{{ __('File Upload') }}</label>
                        <div class="mt-2 flex justify-center rounded-2xl border border-dashed border-slate-300 px-6 py-10 hover:border-primary-400 hover:bg-slate-50 transition-all dark:border-slate-700 dark:hover:border-primary-500 dark:hover:bg-slate-800/50 relative">
                            <div class="text-center">
                                <x-heroicon-o-document-arrow-up class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" />
                                <div class="mt-4 flex text-sm leading-6 text-slate-600 dark:text-slate-400 justify-center">
                                    <label for="import_file" class="relative cursor-pointer rounded-md bg-transparent font-semibold text-primary-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-primary-600 focus-within:ring-offset-2 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                                        <span>Unggah file</span>
                                        <input id="import_file" name="import_file" type="file" accept=".csv,.xlsx,.xls" class="sr-only" required>
                                    </label>
                                    <p class="pl-1">atau seret dan lepas kesini</p>
                                </div>
                                <p class="text-xs leading-5 text-slate-500 dark:text-slate-500">CSV, XLS, XLSX hingga 10MB</p>
                            </div>
                            <input type="file" id="import_file_drop" name="import_file_fallback" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".csv,.xlsx,.xls" onchange="document.getElementById('import_file').files = this.files; document.getElementById('file-name').textContent = this.files[0].name;" title="">
                        </div>
                        <p id="file-name" class="mt-2 text-sm font-medium text-primary-600 dark:text-primary-400"></p>
                        @error('import_file') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                        <button type="reset" onclick="document.getElementById('file-name').textContent=''" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 transition-all">
                            Reset
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 px-6 py-2.5 text-sm font-semibold text-white shadow-md transition-all hover:from-primary-500 hover:to-primary-400 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            <x-heroicon-m-arrow-down-tray class="h-4 w-4" />
                            Mulai Import
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </x-admin.panel>
@endif