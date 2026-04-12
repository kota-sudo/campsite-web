@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#2d5a1b] dark:focus:border-[#2d5a1b] focus:ring-[#2d5a1b] dark:focus:ring-[#2d5a1b] rounded-md shadow-sm']) }}>
