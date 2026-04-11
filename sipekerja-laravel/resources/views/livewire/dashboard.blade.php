<div>
    @if($role === 'Pimpinan')
        <livewire:dashboards.pimpinan-dashboard />
    @elseif($role === 'Ketua Tim')
        <livewire:dashboards.ketua-tim-dashboard />
    @elseif($role === 'Admin' || $role === 'Super Admin')
        <livewire:dashboards.admin-dashboard />
    @elseif($role === 'Pegawai')
        <livewire:dashboards.pegawai-dashboard />
    @else
        <div class="p-10 text-center bg-white rounded-[2.5rem] border border-slate-200 shadow-sm">
            <h1 class="text-3xl font-black text-bps-blue italic uppercase tracking-tight mb-2">Selamat Datang di SIPEKERJA</h1>
            <p class="text-slate-500 font-medium font-outfit uppercase tracking-widest text-xs mt-4">Pilih role yang sesuai untuk mengakses dashboard kinerja Anda.</p>
        </div>
    @endif
</div>
