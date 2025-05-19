<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceHistoryShowTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create(['name' => 'テスト太郎']);
        $this->actingAs($user);

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create();

        // 勤怠詳細ページ
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSeeText('テスト太郎');
    }

    public function test_勤怠詳細画面の「日付」が選択した日付になっている()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $date = Carbon::create(2025, 5, 10);

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create([
            'date' => $date->toDateString(),
        ]);

        // 勤怠詳細ページ
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSeeText('2025年05月10日');
    }

    public function test_「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 任意の打刻時刻
        $clockIn = Carbon::createFromTime(9, 0);
        $clockOut = Carbon::createFromTime(18, 0);

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ]);

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        // 出勤・退勤欄の表示確認（H:i 形式）
        $response->assertSee('value="09:00"', false); // 出勤
        $response->assertSee('value="18:00"', false); // 退勤
    }

    public function test_「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create([
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'date' => today(),
        ]);

        $break = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::today()->setTime(12, 0),
            'break_end' => Carbon::today()->setTime(12, 30),
        ]);

        // 勤怠詳細画面にアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        $response->assertSee('value="12:00"', false);
        $response->assertSee('value="12:30"', false);
    }
}
