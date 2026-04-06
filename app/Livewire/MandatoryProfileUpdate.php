<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;

class MandatoryProfileUpdate extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public bool $shouldShowModal = false;

    public function mount(): void
    {
        $user = filament()->auth()->user();
        \Illuminate\Support\Facades\Log::info('Mounting MandatoryProfileUpdate', [
            'user_id' => $user->id ?? 'null',
            'profile_updated_at' => $user->profile_updated_at ?? 'null'
        ]);

        if ($user && $user->profile_updated_at === null) {
            $this->shouldShowModal = true;
            $this->form->fill([
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->unique(User::class, 'email', fn ($record) => auth()->user()),
                TextInput::make('phone_number')
                    ->label('Nomor Handphone')
                    ->tel()
                    ->required(),
                TextInput::make('password')
                    ->label('Password Baru')
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->helperText('Ganti password default Anda untuk keamanan.'),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $user = auth()->user();
        $formData = $this->form->getState();

        $user->update([
            'name' => $formData['name'],
            'email' => $formData['email'],
            'phone_number' => $formData['phone_number'],
            'password' => Hash::make($formData['password']),
            'profile_updated_at' => now(),
        ]);

        Notification::make()
            ->title('Profil Diperbarui')
            ->body('Akun Anda telah berhasil diperbarui. Selamat bekerja!')
            ->success()
            ->send();

        $this->shouldShowModal = false;
        $this->redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.mandatory-profile-update-modal');
    }
}
