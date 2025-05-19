<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_出勤ボタンが正しく機能する()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤前に「出勤」ボタンが表示される
        $response = $this->get(route('attendance.show'));
        $response->assertSee('出勤');

        // 出勤処理を実行
        $this->post(route('attendance.clockIn'));

        // 出勤後はステータスが「勤務中」になる
        $response = $this->get(route('attendance.show'));
        $response->assertSee('勤務中');
    }

    public function test_出勤は一日一回のみできる()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤・退勤処理を行う
        $this->post(route('attendance.clockIn'));
        $this->post(route('attendance.clockOut'));

        // 出勤ボタンが表示されないことを確認
        $response = $this->get(route('attendance.show'));
        $response->assertDontSee('出勤');
    }

    public function test_出勤時刻が管理画面で確認できる()
    {
        /** @var \App\Models\User*/
        $admin = User::factory()->create(['role' => 'admin']);
        /** @var \App\Models\User*/
        $user = User::factory()->create();

        // 一般ユーザー出勤
        $this->actingAs($user);
        $this->post(route('attendance.clockIn'));

        $this->actingAs($admin);

        // 管理画面
        $response = $this->get(route('admin.attendances.index'));

        // 出勤記録の表示を確認
        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->format('H:i')); // 時間が数分ずれる場合もあるので曖昧一致に注意
    }

    // /** @test */
    // public function 勤務中のユーザーは退勤ボタンが表示され_退勤後にステータスが退勤済になる()
    // {
    //     /** @var \App\Models\User $user */
    //     $user = User::factory()->create(['name' => 'テストユーザー']);

    //     // 出勤済みの状態を作成
    //     Attendance::create([
    //         'user_id' => $user->id,
    //         'date' => now()->toDateString(),
    //         'clock_in' => now()->subHours(8),
    //     ]);

    //     // 勤務中ユーザーとしてログイン
    //     $this->actingAs($user);

    //     // 退勤ボタンが表示されていることを確認
    //     $response = $this->get(route('attendance.show'));
    //     $response->assertSee('退勤');

    //     // 退勤処理を実行
    //     $this->post(route('attendance.clockOut'));

    //     // 再度画面表示（退勤済ステータス確認）
    //     $response = $this->get(route('attendance.show'));
    //     $response->assertSee('退勤済み');
    // }

    // /** @test */
    // public function 管理者は退勤時刻を管理画面で確認できる()
    // {
    //     /** @var \App\Models\User $admin */
    //     $admin = User::factory()->create(['role' => 'admin']);
    //     /** @var \App\Models\User $user */
    //     $user = User::factory()->create(['name' => 'テストユーザー']);

    //     $clockIn = now()->subHours(9);
    //     $clockOut = now()->subHours(1);

    //     Attendance::create([
    //         'user_id' => $user->id,
    //         'date' => now()->toDateString(),
    //         'clock_in' => $clockIn,
    //         'clock_out' => $clockOut,
    //     ]);

    //     $this->actingAs($admin);

    //     $response = $this->get(route('admin.attendances.index'));
    //     $response->assertStatus(200);
    //     $response->assertSee('テストユーザー');
    //     $response->assertSee($clockOut->format('H:i')); // 時刻フォーマットに合わせる
    // }
}
