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

        $response = $this->get(route('attendance.show'));
        $response->assertSee('休憩入り');

        $this->post(route('attendance.breakStart'));

        $response = $this->get(route('attendance.show'));
        $response->assertSee('休憩中');
    }

    public function test_休憩は一日に何回でもできる()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'clock_in' => now()->subHours(3),
        ]);

        $this->actingAs($user);

        // 1回目の休憩
        $this->post(route('attendance.breakStart'));
        $this->post(route('attendance.breakEnd'));

        $response = $this->get(route('attendance.show'));
        $response->assertSee('休憩入り');
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'clock_in' => now()->subHours(3),
        ]);

        $this->actingAs($user);

        // 休憩
        $this->post(route('attendance.breakStart'));
        $this->post(route('attendance.breakEnd'));

        $response = $this->get(route('attendance.show'));
        $response->assertSee('勤務中');
    }

    // public function test_休憩戻は一日に何回でもできる()
    // {
    //     /** @var \App\Models\User */
    //     $user = User::factory()->create();

    //     Attendance::create([
    //         'user_id' => $user->id,
    //         'date' => today()->toDateString(),
    //         'clock_in' => now()->subHours(3),
    //     ]);

    //     // 1回目
    //     $this->actingAs($user)->post(route('attendance.breakStart'));
    //     $this->actingAs($user)->post(route('attendance.breakEnd'));

    //     // 2回目
    //     $this->actingAs($user)->post(route('attendance.breakStart'));

    //     $response = $this->actingAs($user)->get(route('attendance.show'));
    //     $response->assertSee('休憩戻り');
    // }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()
        ->for($user)
        ->create();

        $this->actingAs($user);

        // 勤怠一覧画面へアクセス
        $response = $this->get(route('staff.attendances.index'));
        $response->assertSee($attendance->formatted_total_break);
    }
}