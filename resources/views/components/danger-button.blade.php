<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#A32D2D] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#8a2525] active:bg-[#6f1e1e] focus:outline-none focus:ring-2 focus:ring-[#A32D2D] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>