<div {{ $attributes->merge([
        'class' => 'bg-[#fdf5e6] rounded-2xl p-7 border border-[#e8d9b9]
                   shadow-lg shadow-black/10 font-serif leading-relaxed
                   [box-shadow:inset_0_1px_0_rgba(255,255,255,0.4)]
                   [background-image:radial-gradient(rgba(0,0,0,0.03)_1px,transparent_0),
                                      radial-gradient(rgba(0,0,0,0.02)_1px,transparent_0)]
                   [background-size:6px_6px,10px_10px]
                   flex flex-col h-full'
]) }}>
    {{ $slot }}
</div>
