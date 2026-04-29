<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Component;

class ChangePassword extends Component
{
    public bool $isOpen = false;
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $confirmPassword = '';
    public string $state = 'idle'; // idle | saving | success | error
    public string $errorMessage = '';

    #[On('open-change-password')]
    public function open(): void
    {
        $this->reset(['currentPassword', 'newPassword', 'confirmPassword', 'errorMessage']);
        $this->state = 'idle';
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->reset(['currentPassword', 'newPassword', 'confirmPassword', 'errorMessage']);
        $this->state = 'idle';
    }

    public function save(): void
    {
        $this->errorMessage = '';
        $this->state = 'saving';

        $this->validate([
            'currentPassword' => 'required',
            'newPassword'     => 'required|min:8',
            'confirmPassword' => 'required|same:newPassword',
        ], [
            'currentPassword.required' => 'Password lama wajib diisi.',
            'newPassword.required'     => 'Password baru wajib diisi.',
            'newPassword.min'          => 'Password baru minimal 8 karakter.',
            'confirmPassword.required' => 'Konfirmasi password wajib diisi.',
            'confirmPassword.same'     => 'Konfirmasi password tidak cocok dengan password baru.',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->currentPassword, $user->password)) {
            $this->state = 'error';
            $this->errorMessage = 'Password lama tidak sesuai.';
            return;
        }

        if ($this->currentPassword === $this->newPassword) {
            $this->state = 'error';
            $this->errorMessage = 'Password baru tidak boleh sama dengan password lama.';
            return;
        }

        $user->update(['password' => Hash::make($this->newPassword)]);
        $this->state = 'success';
        $this->reset(['currentPassword', 'newPassword', 'confirmPassword']);
    }

    public function render()
    {
        return view('livewire.auth.change-password');
    }
}
