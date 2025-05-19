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
        User::factory(5)->create([
            'role' => 'user',
        ])->each(function ($user) {
            // 各ユーザーに対して勤怠レコードを5日分作成
            Attendance::factory(5)
            ->create([
                'user_id' => $user->id,
            ]);
        });

        // ログインする用
        User::factory()->create([
            'name' => 'test0',
            'email' => 'test0@example.com',
            'password' => bcrypt('password0'),
            'role' => 'user',
        ])->each(function ($user) {
            Attendance::factory(5)
            ->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
