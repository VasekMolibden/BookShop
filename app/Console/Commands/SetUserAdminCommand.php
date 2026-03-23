<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * CLI helper to grant or revoke the bookshop admin flag on a user account.
 */
class SetUserAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookshop:user-admin
                            {email : Email пользователя в базе}
                            {--revoke : Снять права администратора вместо выдачи}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Выдать или снять права администратора у пользователя (поле is_admin)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = (string) $this->argument('email');

        /** @var User|null $user */
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $this->components->error("Пользователь не найден: {$email}");

            return self::FAILURE;
        }

        if ($this->option('revoke')) {
            $user->is_admin = false;
            $user->save();
            $this->components->info("Права администратора сняты: {$email}");

            return self::SUCCESS;
        }

        $user->is_admin = true;
        $user->save();
        $this->components->info("Права администратора выданы: {$email}");

        return self::SUCCESS;
    }
}
