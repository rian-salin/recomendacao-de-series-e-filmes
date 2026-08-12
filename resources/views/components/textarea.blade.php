@props(['disabled' => false])

<textarea @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-md border-gray-300 px-3 py-2 text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500']) }}>{{ $slot }}</textarea>
