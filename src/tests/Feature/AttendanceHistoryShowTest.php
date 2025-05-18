<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceHistoryShowTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細ページにログインユーザーの名前が表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['name' => 'テスト太郎']);
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
        ]);

        // 勤怠詳細ページにアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSeeText('テスト太郎');
    }

    /** @test */
    public function 勤怠詳細ページに勤怠データの日付が表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $date = Carbon::create(2025, 5, 10); // 任意の日付を明示
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
        ]);

        // 勤怠詳細ページにアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSeeText('2025年05月10日');
    }

    /** @test */
    public function 勤怠詳細ページに出勤退勤の打刻が正しく表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 任意の打刻時刻
        $clockIn = Carbon::createFromTime(9, 0);
        $clockOut = Carbon::createFromTime(18, 0);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ]);

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        // 出勤・退勤欄に打刻が表示されているか確認（H:i 形式）
        $response->assertSee('value="09:00"', false); // 出勤
        $response->assertSee('value="18:00"', false); // 退勤
    }

    /** @test */
    public function 勤怠詳細ページに休憩時間が正しく表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
        ]);

        // 休憩時間を1件登録
        $breakStart = Carbon::createFromTime(12, 0);
        $breakEnd = Carbon::createFromTime(13, 0);
        
        $break = $attendance->breaks()->create([
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
        ]);

        // 勤怠詳細画面にアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        // 休憩時間の value 属性をチェック（HTML内の input value）
        $response->assertSee('value="12:00"', false);
        $response->assertSee('value="13:00"', false);
    }
}
