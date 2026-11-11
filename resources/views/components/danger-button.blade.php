<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn bg-red-700 text-white hover:bg-red-800 active:bg-red-900']) }}>
    {{ $slot }}
</button>
