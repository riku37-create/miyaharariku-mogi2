<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AdminUserManagementTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        /** @var \App\Models\User */
        $admin = User::factory()->create(['role' => 'admin']);
        $users = User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.staff.index'));

        $response->assertStatus(200);
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        /** @var \App\Models\User */
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $date = Carbon::now()->startOfMonth();

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create([
            'date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9, 0),
            'clock_out' => $date->copy()->setTime(18, 0),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.staff.show', ['id' => $user->id]));

        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        /** @var \App\Models\User */
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        // 前月の日付で勤怠登録
        $prevMonth = now()->subMonth()->startOfMonth();
        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create([
            'date' => $prevMonth,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendances.index', ['date' => $prevMonth->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSee($attendance->date->format('Y/m/d'));
    }

    public function test_「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        /** @var \App\Models\User */
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $nextMonth = now()->addMonth()->startOfMonth();
        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create([
            'date' => $nextMonth,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendances.index', ['date' => $nextMonth->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSee($attendance->date->format('Y/m/d'));
    }

    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        /** @var \App\Models\User*/
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create();

        $response = $this->actingAs($admin)->get(route('admin.attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($attendance->date->format('Y年m月d日'));
    }
}