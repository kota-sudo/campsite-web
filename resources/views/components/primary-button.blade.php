<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#2d5a1b] dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-[#1c3a0e] dark:hover:bg-white focus:bg-[#1c3a0e] dark:focus:bg-white active:bg-[#0d2208] dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-[#2d5a1b] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
