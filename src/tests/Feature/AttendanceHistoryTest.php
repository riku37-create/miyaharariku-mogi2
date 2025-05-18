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

    /** @test */
    public function 自分が行った勤怠情報が全て表示されている()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 当月の任意の日に勤怠データを3件作成
        $date = Carbon::now()->startOfMonth();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9, 0),
            'clock_out' => $date->copy()->setTime(18, 0),
            'total_clock' => 9 * 3600,
            'total_break' => 3600,
        ]);

        // 他人の勤怠データも作成（表示されてはいけない）
        Attendance::factory()->create([
            'user_id' => User::factory()->create()->id,
            'date' => Carbon::now()->startOfMonth()->addDays(5),
        ]);

        // 勤怠一覧ページへアクセス
        $response = $this->get(route('staff.attendances.index', [
            'month' => Carbon::now()->format('Y-m'),
        ]));

        // 正常ステータスと、3件の自分の勤怠が表示されていることを確認
        $response->assertStatus(200);


        $response->assertSeeText($date->format('n/j'));
        $response->assertSeeText($attendance->clock_in->format('H:i'));
        $response->assertSeeText($attendance->clock_out->format('H:i'));
        $response->assertSeeText($attendance->formatted_total_break);
        $response->assertSeeText($attendance->formatted_total_clock);

        // 他人のデータの日付が含まれていないことも確認
        $response->assertDontSee(Carbon::now()->startOfMonth()->addDays(5)->format('n/j'));
    }

    /** @test */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
    /** @var \App\Models\User $user */
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

    /** @test */
    public function 勤怠一覧画面で前月を指定すると前月の情報が表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $lastMonth = Carbon::now()->subMonth();
        $lastMonthFormatted = $lastMonth->format('Y/m');

        // 前月を指定して勤怠一覧にアクセス
        $response = $this->get(route('staff.attendances.index', [
            'month' => $lastMonth->format('Y-m')
        ]));

        $response->assertStatus(200);
        $response->assertSeeText($lastMonthFormatted);
    }

    /** @test */
    public function 勤怠一覧画面で翌月を指定すると翌月の情報が表示される()
    {
        /** @var \App\Models\User $user */
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


    /** @test */
    public function 勤怠一覧画面の詳細リンクから勤怠詳細ページへ遷移できる()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->startOfMonth(),
            'clock_in' => Carbon::now()->startOfMonth()->setTime(9, 0),
            'clock_out' => Carbon::now()->startOfMonth()->setTime(18, 0),
        ]);

        // 勤怠一覧ページにアクセスし、「詳細」リンクが存在することを確認
        $response = $this->get(route('staff.attendances.index', [
            'month' => Carbon::now()->format('Y-m')
        ]));
        $response->assertStatus(200);

        // 勤怠詳細ページにアクセスできることを確認
        $detailResponse = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $detailResponse->assertStatus(200);
    }

}
