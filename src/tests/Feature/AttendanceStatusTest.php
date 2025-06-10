<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceStatusTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_勤務外の場合_ステータスが勤務外と表示される()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 打刻画面にアクセス
        $response = $this->get(route('attendance.show'));

        // 勤務外ステータスが表示されているか確認
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_出勤中の場合_ステータスが勤務中と表示される()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤打刻
        $this->post(route('attendance.clockIn'));

        // 打刻画面にアクセス
        $response = $this->get(route('attendance.show'));

        // 勤務中のステータスが表示されているかを確認
        $response->assertStatus(200);
        $response->assertSee('勤務中');
    }

    public function test_休憩中の場合_ステータスが休憩中と表示される()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤
        $this->post(route('attendance.clockIn'));

        // 休憩開始
        $this->post(route('attendance.breakStart'));

        // 勤怠打刻画面を確認
        $response = $this->get(route('attendance.show'));

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_退勤済の場合_ステータスが退勤済と表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 出勤済みの状態を作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
        ]);

        // 勤務中ユーザーとしてログイン
        $this->actingAs($user);

        // 退勤ボタンが表示されていることを確認
        $response = $this->get(route('attendance.show'));
        $response->assertSee('退勤');

        // 退勤処理を実行
        $this->post(route('attendance.clockOut'));

        // 再度画面表示（退勤済ステータス確認）
        $response = $this->get(route('attendance.show'));
        $response->assertSee('退勤済み');
    }
}
