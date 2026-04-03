<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;
use Closure;
use App\Models\Activity;

class CustomLogin extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        // Generate random numbers for math captcha
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);

        session()->put('math_captcha_answer', $num1 + $num2);
        session()->put('math_captcha_question', "Berapa hasil dari {$num1} + {$num2}?");
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Selamat Datang!';
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return 'Masuk ke akun Kencana Anda';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),

                TextInput::make('captcha')
                    ->label(fn() => session('math_captcha_question', 'Security Question'))
                    ->placeholder('Masukkan hasil penjumlahan di atas')
                    ->required()
                    ->rules([
                        fn(): Closure => function (string $attribute, $value, Closure $fail) {
                            if ((int) $value !== (int) session('math_captcha_answer')) {
                                $fail('Jawaban salah. Anda terdeteksi sebagai Bot.');
                            }
                        },
                    ])
                    ->helperText('Autentikasi keamanan untuk memastikan Anda manusia (Anti-DDoS).'),
            ]);
    }

    /**
     * Override authenticate to ensure standard Login event is fired and captured by listeners.
     */
    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        return parent::authenticate();
    }
}
