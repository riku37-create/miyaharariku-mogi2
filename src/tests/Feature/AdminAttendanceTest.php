<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        // 一般ユーザーを2名作成
        $user1 = User::factory()->create(['name' => '山田太郎']);
        $user2 = User::factory()->create(['name' => '佐藤花子']);

        // 今日の日付の勤怠情報を作成
        $date = now()->toDateString();

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => $date,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => $date,
            'clock_in' => '08:30',
            'clock_out' => '17:30',
        ]);

        // 管理者として勤怠一覧ページへアクセス
        $response = $this->actingAs($admin)->get(route('admin.attendances.index'));

        // レスポンス確認とデータ確認
        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('佐藤花子');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        // 本日の日付を取得
        $today = now()->format('Y/m/d');

        // 管理者として勤怠一覧にアクセス
        $response = $this->actingAs($admin)->get(route('admin.attendances.index'));

        // ステータスと日付の表示を確認
        $response->assertStatus(200);
        $response->assertSee($today);
    }

    public function test_前日・翌日の勤怠情報が表示される()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $today = now();
        $yesterday = $today->copy()->subDay()->format('Y/m/d');
        $tomorrow = $today->copy()->addDay()->format('Y/m/d');

        // 前日画面の確認
        $this->actingAs($admin)
            ->get(route('admin.attendances.index', ['date' => $today->copy()->subDay()->toDateString()]))
            ->assertStatus(200)
            ->assertSee($yesterday);

        // 翌日画面の確認
        $this->actingAs($admin)
            ->get(route('admin.attendances.index', ['date' => $today->copy()->addDay()->toDateString()]))
            ->assertStatus(200)
            ->assertSee($tomorrow);
    }
}
