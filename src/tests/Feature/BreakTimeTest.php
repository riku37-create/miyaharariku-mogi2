<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class BreakTimeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_休憩ボタンが正しく機能する()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'clock_in' => now()->subHours(3),
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.breakStart'));

        $response = $this->get(route('attendance.show'));
        $response->assertSee('休憩中');
    }

    public function test_休憩は一日に何回でもできる()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();

        // 休憩も作成
        $attendance = Attendance::factory()->for($user)->create();

        // 休憩が0件以上あることの確認
        $this->assertGreaterThan(0, $attendance->breaks()->count());

        $this->actingAs($user);

        $this->post(route('attendance.breakStart'));
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'clock_in' => now()->subHours(3),
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.breakStart')); // 休憩開始
        $latestBreak = $attendance->breaks()->latest()->first();
        $latestBreak->update([
            'break_start' => now()->subMinutes(6),
        ]);
        $this->post(route('attendance.breakEnd'));

        $response = $this->get(route('attendance.show'));
        $response->assertSee('勤務中');
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()
        ->for($user)
        ->create();

        $attendance->refresh();

        $this->actingAs($user);

        // 勤怠一覧画面へアクセス
        $response = $this->get(route('staff.attendances.index'));
        $response->assertSee($attendance->formatted_total_break);
    }
}