<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminAttendanceShowTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        /** @var \App\Models\User */
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

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

        $this->actingAs($admin)
        ->get(route('admin.attendance.detail', $attendance->id))
        ->assertStatus(200)
        ->assertSee($user->name)
        ->assertSee($attendance->date->format('Y年m月d日'))
        ->assertSee($attendance->clock_in->format('H:i'))
        ->assertSee($attendance->clock_out->format('H:i'))
        ->assertSee($break->break_start->format('H:i'))
        ->assertSee($break->break_end->format('H:i'));
    }

    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        /** @var \App\Models\User */
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create();

        $response = $this->actingAs($admin)
        ->post(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'reason' => 'テスト'
        ]);

        $response->assertSessionHasErrors();
        $response->assertSessionHasErrors(['clock_in' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        /** @var \App\Models\User */
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $attendance = Attendance::factory()
        ->for($user)
        ->create();

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.update', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '10:00',
                'reason' => 'テスト',
                'breaks' => [
                    [
                        'id' => $attendance->breaks->first()->id,
                        'start' => '11:00', // 退勤より後
                        'end' => '11:30',
                    ]
                ]
            ]);

        $response->assertSessionHasErrors();
        $response->assertSessionHasErrors(['breaks.0.start' => '休憩時間が勤務時間外です']);
    }

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        /** @var \App\Models\User  */
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $attendance = Attendance::factory()
        ->for($user)
        ->create();

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.update', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '10:00',
                'reason' => 'テスト理由',
                'breaks' => [
                    [
                        'id' => $attendance->breaks->first()->id,
                        'start' => '09:15',
                        'end' => '10:30', // 退勤より後
                    ]
                ]
            ]);

        $response->assertSessionHasErrors();
        $response->assertSessionHasErrors(['breaks.0.start' => '休憩時間が勤務時間外です']);
    }
}
