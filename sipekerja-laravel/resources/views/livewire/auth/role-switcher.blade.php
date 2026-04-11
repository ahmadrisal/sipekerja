<div class="flex items-center bg-white/10 backdrop-blur-md rounded-full p-1 border border-white/20 shadow-inner">
    @foreach($roles as $role)
        <button 
            wire:click="switchRole('{{ $role }}')"
            class="px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.15em] transition-all transform active:scale-95 flex items-center gap-2 {{ session('active_role') === $role ? 'bg-white text-minimal-indigo shadow-md' : 'text-white/70 hover:text-white hover:bg-white/5' }}"
        >
            @if(session('active_role') === $role)
                <div class="w-1.5 h-1.5 rounded-full bg-minimal-indigo animate-pulse"></div>
            @endif
            {{ $role }}
        </button>
    @endforeach
</div>
