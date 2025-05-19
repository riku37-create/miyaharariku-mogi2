<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceHistoryTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public function test_自分が行った勤怠情報が全て表示されている()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $date = Carbon::now()->startOfMonth();
        $otherDate = $date->copy()->addDays(5);

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create([
            'date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9, 0),
            'clock_out' => $date->copy()->setTime(18, 0),
            'total_clock' => 9 * 3600,
        ]);

        // 他人の勤怠データも作成（表示されてはいけない）
        Attendance::factory()
        ->noBreaks()
        ->create([
            'user_id' => User::factory()->create()->id,
            'date' => $otherDate,
        ]);

        // 勤怠一覧ページへアクセス
        $response = $this->get(route('staff.attendances.index', ['month' => Carbon::now()->format('Y-m'),]));

        $response->assertStatus(200);
        $response->assertSeeText($date->format('n/j'));
        $response->assertSeeText($attendance->clock_in->format('H:i'));
        $response->assertSeeText($attendance->clock_out->format('H:i'));
        $response->assertSeeText($attendance->formatted_total_break);
        $response->assertSeeText($attendance->formatted_total_clock);

        // 他人のデータの日付が含まれていないことも確認
        $response->assertDontSee($otherDate->format('n/j'));
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
    /** @var \App\Models\User */
    $user = User::factory()->create();
    $this->actingAs($user);

    // 現在の月を取得
    $currentMonth = Carbon::now()->format('Y/m');

    // 勤怠一覧ページにアクセス
    $response = $this->get(route('staff.attendances.index'));

    // ステータス200と、現在の月が表示されていることを確認
    $response->assertStatus(200);
    $response->assertSeeText($currentMonth);
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $lastMonth = Carbon::now()->subMonth();
        $lastMonthFormatted = $lastMonth->format('Y/m');

        // 前月を指定して勤怠一覧にアクセス
        $response = $this->get(route('staff.attendances.index', ['month' => $lastMonth->format('Y-m')]));

        $response->assertStatus(200);
        $response->assertSeeText($lastMonthFormatted);
    }

    public function test_「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $nextMonth = Carbon::now()->addMonth();
        $nextMonthFormatted = $nextMonth->format('Y/m');

        // 翌月を指定して勤怠一覧にアクセス
        $response = $this->get(route('staff.attendances.index', [
            'month' => $nextMonth->format('Y-m')
        ]));

        $response->assertStatus(200);
        $response->assertSeeText($nextMonthFormatted);
    }

    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create([
            'date' => Carbon::now()->startOfMonth(),
            'clock_in' => Carbon::now()->startOfMonth()->setTime(9, 0),
            'clock_out' => Carbon::now()->startOfMonth()->setTime(18, 0),
        ]);

        $response = $this->get(route('staff.attendances.index', ['month' => Carbon::now()->format('Y-m')]));
        $response->assertStatus(200);

        // 勤怠詳細ページにアクセスできることを確認
        $detailResponse = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $detailResponse->assertStatus(200);
    }
}
