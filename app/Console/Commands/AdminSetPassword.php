<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class AdminSetPassword extends Command
{
    protected $signature = 'admin:set-password {password : The new password} {--user=admin : Admin username}';

    protected $description = 'Set admin password (use when tinker is not available, e.g. shared hosting)';

    public function handle()
    {
        $username = $this->option('user');
        $password = $this->argument('password');

        $admin = Admin::where('admin_username', $username)->first();
        if (!$admin) {
            $this->error("Admin user '{$username}' not found.");
            return 1;
        }

        $admin->admin_password = Crypt::encrypt($password);
        $admin->save();

        $this->info("Password for '{$username}' has been updated. You can log in with this password now.");
        return 0;
    }
}
