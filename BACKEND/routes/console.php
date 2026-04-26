<?php
// routes/console.php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Warning;
use App\Models\User;
use App\Models\UserNotification;

Schedule::call(function () {
    // Auto-ban expired warnings
    Warning::where('status', 'active')
        ->where('expires_at', '<=', now())
        ->each(function ($warning) {
            $user = User::find($warning->user_id);
            if ($user && !$user->is_banned) {
                $user->is_banned = true;
                $user->save();
                $warning->status = 'banned';
                $warning->save();

                UserNotification::create([
                    'user_id' => $user->id,
                    'type'    => 'warning',
                    'title'   => '🚫 Account Banned',
                    'message' => 'Your warning period has expired and your account has been banned.',
                    'data'    => ['warning_id' => $warning->id],
                ]);

                // Notify all admins
                User::whereIn('role', ['admin', 'superadmin'])->each(function ($admin) use ($user, $warning) {
                    UserNotification::create([
                        'user_id' => $admin->id,
                        'type'    => 'general',
                        'title'   => '🔨 User Auto-Banned',
                        'message' => "{$user->name} has been automatically banned after warning expired.",
                        'data'    => ['user_id' => $user->id, 'warning_id' => $warning->id],
                    ]);
                });
            }
        });
})->everyMinute();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
