<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
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

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $users = User::factory()->count(3)->create(['role' => 'user']);

        $response = $this->actingAs($admin)->get(route('admin.staff.index'));

        $response->assertStatus(200);
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        // 前月の日付で勤怠登録
        $prevMonth = now()->subMonth()->startOfMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $prevMonth,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendances.index', ['date' => $prevMonth->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSee($attendance->date->format('Y/m/d'));
    }

    public function test_「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $nextMonth = now()->addMonth()->startOfMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendances.index', ['date' => $nextMonth->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSee($attendance->date->format('Y/m/d'));
    }

    public function test_admin_can_access_attendance_detail_page()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($attendance->date->format('Y年m月d日'));
    }
}
