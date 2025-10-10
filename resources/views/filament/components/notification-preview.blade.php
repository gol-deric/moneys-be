<div class="rounded-lg border border-gray-300 dark:border-gray-600 p-4 bg-white dark:bg-gray-800">
    <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                {{ $title }}
            </p>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $body }}
            </p>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                Just now
            </p>
        </div>
    </div>
</div>
