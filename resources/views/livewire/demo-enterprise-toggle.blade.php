<div class="flex shrink-0 whitespace-nowrap items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <span class="text-xs font-medium {{ $isEnterprise ? 'text-gray-500 dark:text-gray-400' : 'text-primary-600 dark:text-primary-400' }}">Community</span>
    <button 
        type="button" 
        wire:click="toggle"
        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 {{ $isEnterprise ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}"
        role="switch" 
        aria-checked="{{ $isEnterprise ? 'true' : 'false' }}"
    >
        <span class="sr-only">Toggle Enterprise Mode</span>
        <span 
            aria-hidden="true" 
            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isEnterprise ? 'translate-x-4' : 'translate-x-0' }}"
        ></span>
    </button>
    <span class="text-xs font-medium {{ $isEnterprise ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400' }}">Enterprise</span>
</div>
