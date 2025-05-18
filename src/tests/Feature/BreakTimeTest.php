<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreakTimeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /** @test */
    public function 勤務中のユーザーは休憩入ボタンが表示され_休憩後にステータスが休憩中になる()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'clock_in' => now()->subHours(3),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.show'));
        $response->assertSee('休憩入り');

        $this->post(route('attendance.breakStart'));

        $response = $this->get(route('attendance.show'));
        $response->assertSee('休憩中');
    }

    // /** @test */
    // public function 出勤中のユーザーは休憩を何度でも行える()
    // {
    //     /** @var \App\Models\User $user */
    //     $user = User::factory()->create();

    //     $attendance = Attendance::create([
    //         'user_id' => $user->id,
    //         'date' => today()->toDateString(),
    //         'clock_in' => now()->subHours(4),
    //     ]);

    //     $this->actingAs($user);

    //     // 1回目の休憩
    //     $this->post(route('attendance.breakStart'));
    //     $this->post(route('attendance.breakEnd'));

    //     // 2回目の休憩
    //     $this->post(route('attendance.breakStart'));

    //     $response = $this->get(route('attendance.show'));
    //     $response->assertSee('休憩戻り');
    // }

    /** @test */
    public function 休憩中のユーザーは休憩戻り後に出勤中になる()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'clock_in' => now()->subHours(3),
        ]);

        $this->actingAs($user);

        // 休憩開始
        $this->post(route('attendance.breakStart'));

        // 休憩戻り
        $this->post(route('attendance.breakEnd'));

        $response = $this->get(route('attendance.show'));
        $response->assertSee('勤務中');
    }

    // /** @test */
    // public function 休憩戻りは複数回行える()
    // {
    //     /** @var \App\Models\User $user */
    //     $user = User::factory()->create();

    //     $attendance = Attendance::create([
    //         'user_id' => $user->id,
    //         'date' => today()->toDateString(),
    //         'clock_in' => now()->subHours(5),
    //     ]);

    //     $this->actingAs($user);

    //     // 1回目
    //     $this->post(route('attendance.breakStart'));
    //     $this->post(route('attendance.breakEnd'));

    //     // 2回目
    //     $this->post(route('attendance.breakStart')); // 休憩開始
    //     $response = $this->get(route('attendance.show'));
    //     $response->assertSee('休憩戻り');
    // }

    // /** @test */
    // public function 管理画面で休憩時刻が確認できる()
    // {
    //     /** @var \App\Models\User $admin */
    //     $admin = User::factory()->create(['role' => 'admin']);
    //     /** @var \App\Models\User $user */
    //     $user = User::factory()->create();

    //     Attendance::factory()->create([
    //         'user_id' => $user->id,
    //         'date' => now()->toDateString(),
    //         'total_break' => 5400, // 1時間30分
    //     ]);

    //     $this->actingAs($admin);

    //     // 勤怠一覧画面へアクセス
    //     $response = $this->get(route('admin.attendances.index', ['date' => now()->toDateString()]));

    //     // 「1時間30分」が表示されていることを確認
    //     $response->assertSee('1時間30分');
    // }
}
