<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('app:create-admin-user {--email=admin@example.com} {--name=Admin}')]
#[Description('Create (or reset the password for) an admin user who can log into the Filament panel')]
class CreateAdminUser extends Command
{
    public function handle(): int
    {
        $email = $this->option('email');
        $name = $this->option('name');

        if (! $this->option('no-interaction')) {
            $email = text(
                label: 'Admin email address',
                default: $email,
                validate: fn (string $value) => Validator::make(['email' => $value], ['email' => 'required|email'])
                    ->errors()->first('email'),
            );

            $name = text(label: 'Admin name', default: $name);
        }

        $password = password(
            label: 'Admin password',
            validate: fn (string $value) => strlen($value) < 8 ? 'Password must be at least 8 characters.' : null,
        );

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $this->info("Admin user \"{$user->email}\" is ready. You can now log in at /admin.");

        return self::SUCCESS;
    }
}
