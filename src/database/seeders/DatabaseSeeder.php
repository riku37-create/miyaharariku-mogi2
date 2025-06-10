<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 管理者ユーザー作成
        User::factory()->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 一般ユーザー作成
        $users = User::factory(5)->create(['role' => 'user',]);
        $usedDates = [];

        foreach ($users as $user) {
            $usedDates = [];

            for ($i = 0; $i < 10; $i++) {
                do {
                    $date = now()->startOfMonth()->addDays(rand(0, now()->daysInMonth - 1))->toDateString();
                } while (in_array($date, $usedDates));
                $usedDates[] = $date;

                Attendance::factory()
                    ->for($user)
                    ->state(['date' => $date])
                    ->create();
            }
        }

        // ログインする用
        $loginUser = User::factory()->create([
            'name' => 'test0',
            'email' => 'test0@example.com',
            'password' => bcrypt('password0'),
            'role' => 'user',
        ]);

        for ($i = 0; $i < 5; $i++) {
            Attendance::factory()
                ->for($loginUser)
                ->create();
        }
    }
}
