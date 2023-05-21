<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create super admin user';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->comment('Super admin user will be created');

        $name = $this->askName();
        $email = $this->askEmail();
        $password = $this->askPassword();

        /** @var User $user */
        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $user->givePermissionTo('super admin');

        $this->comment("Super admin user was created:\n - Login: {$name}\n - Password: {$password}");
    }

    private function askName(): ?string
    {
        $name = $this->ask('Enter user name');
        if (! $name) {
            $this->error('User name is required');
            exit(0);
        }

        if (User::query()->where('name', '=', $name)->exists()) {
            $this->error('This name is already in use');
            exit(0);
        }

        return $name;
    }

    private function askEmail(): ?string
    {
        $email = $this->ask('Enter email address');
        if (! $email) {
            $this->error('Email address is required');
            exit(0);
        }

        if (User::query()->where('email', '=', $email)->exists()) {
            $this->error('This email is already in use');
            exit(0);
        }

        return $email;
    }

    private function askPassword(): ?string
    {
        $password = $this->ask('Password (leave blank to generate)');

        if (! $password) {
            $password = Str::random(12);
        }

        if (Str::length($password) < 6) {
            $this->error('Password must contain at least 6 characters');

            return $this->askPassword();
        }

        return $password;
    }
}
