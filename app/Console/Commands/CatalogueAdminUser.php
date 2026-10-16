<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CatalogueAdminUser extends Command
{
    protected $signature = 'catalogue:admin {email? : Existing or new administrator email}';

    protected $description = 'Interactively create or promote a catalogue administrator';

    public function handle(): int
    {
        $email = strtolower(trim((string) ($this->argument('email') ?? $this->ask('Email address'))));
        Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:255']])->validate();
        $user = User::where('email', $email)->first();
        if (! $user) {
            $name = $this->ask('Name');
            $password = $this->secret('Password');
            $confirmation = $this->secret('Confirm password');
            Validator::make(['name' => $name, 'password' => $password, 'password_confirmation' => $confirmation], ['name' => ['required', 'string', 'max:255'], 'password' => ['required', 'confirmed', Password::min(12)]])->validate();
            $user = User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);
        }
        $user->role = 'admin';
        $user->email_verified_at ??= now();
        $user->save();
        $this->info('Administrator access granted. Sign in at '.url('/login').'.');

        return self::SUCCESS;
    }
}
