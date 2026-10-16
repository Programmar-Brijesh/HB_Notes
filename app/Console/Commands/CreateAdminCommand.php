<?php

namespace App\Console\Commands;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {--name= : Administrator full name} {--email= : Administrator email address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provision an authorized HB Notes administrator account';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('HB Notes — Provision Administrator Account');

        $name = $this->option('name') ?: $this->ask('Enter Administrator Full Name');
        $email = $this->option('email') ?: $this->ask('Enter Administrator Email Address');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }
            return self::FAILURE;
        }

        $email = strtolower(trim($email));

        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            if ($existingUser->isAdmin()) {
                $this->components->warn("An administrator account for '{$email}' already exists.");
                return self::SUCCESS;
            }

            if (! $this->confirm("A user account for '{$email}' exists with role '{$existingUser->role}'. Promote to administrator?")) {
                $this->components->info('Operation cancelled.');
                return self::FAILURE;
            }

            $existingUser->role = 'admin';
            $existingUser->is_active = true;
            $existingUser->save();

            AdminAuditLog::log('admin_promoted', "User {$existingUser->name} ({$existingUser->email}) promoted to administrator via Artisan command.", $existingUser->id, '127.0.0.1');

            $this->components->info("User '{$email}' has been successfully upgraded to Administrator.");
            return self::SUCCESS;
        }

        $admin = new User([
            'name' => trim($name),
            'email' => $email,
            'password' => Str::random(32), // Secure dummy password (login is passwordless)
        ]);
        $admin->forceFill([
            'role' => 'admin',
            'is_active' => true,
        ])->save();

        AdminAuditLog::log('admin_created', "Administrator {$admin->name} ({$admin->email}) provisioned via Artisan command.", $admin->id, '127.0.0.1');

        $this->components->info("Administrator '{$admin->name}' ({$admin->email}) created successfully.");
        $this->components->bulletList([
            'Role: Administrator (Content & Curriculum Management)',
            'Status: Active',
            'Authentication: Passwordless magic link at ' . route('login'),
        ]);

        return self::SUCCESS;
    }
}
