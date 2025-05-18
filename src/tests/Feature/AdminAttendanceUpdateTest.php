<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;

class AdminAttendanceUpdateTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_承認待ちの修正申請が全て表示される()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 勤怠データと休憩を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // ログインして修正申請を送信
        $this->actingAs($user)
            ->post(route('attendance.correction.request', $attendance), [
                'clock_in' => '09:15',
                'clock_out' => '18:10',
                'reason' => '退勤打刻漏れのため修正',
            ]);

        // 管理者としてログインして承認待ち一覧を確認
        $this->actingAs($admin)
            ->get(route('admin.correction_requests.index', ['status' => 'pending']))
            ->assertSee('承認待ち')
            ->assertSee($user->name)
            ->assertSee(today()->format('Y年m月d日'))
            ->assertSee('退勤打刻漏れのため修正');
    }

    public function test_承認済みの修正申請が全て表示されている()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 勤怠情報と休憩を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // ユーザーが修正申請を送信
        $this->actingAs($user)
            ->post(route('attendance.correction.request', $attendance), [
                'clock_in' => '09:30',
                'clock_out' => '18:10',
                'reason' => '遅刻のため',
            ]);

        // 修正申請を取得
        $correctionRequest = CorrectionRequest::first();

        // 管理者が承認処理（ルートは仮、必要に応じて調整）
        $this->actingAs($admin)
            ->put(route('admin.correction_requests.approve', $correctionRequest->id))
            ->assertRedirect(route('admin.correction_requests.index')); // 承認後のリダイレクト先に応じて必要なら修正

        // 承認済み一覧タブを確認
        $this->actingAs($admin)
            ->get(route('admin.correction_requests.index', ['status' => 'approved']))
            ->assertSee('承認済み')
            ->assertSee($user->name)
            ->assertSee(today()->format('Y年m月d日'))
            ->assertSee('遅刻のため');
    }

    public function test_修正申請の詳細内容が正しく表示されている()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 修正申請データを登録（申請→出退勤→休憩）
        $this->actingAs($user)->post(route('attendance.correction.request', $attendance), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => 'テスト申請',
        ]);

        // 作成された申請を取得
        $correctionRequest = CorrectionRequest::latest()->first();

        // 管理者として詳細画面にアクセス
        $response = $this->actingAs($admin)->get(
            route('admin.correction_requests.show', $correctionRequest->id)
        );

        $response->assertStatus(200);
        $response->assertSee('テスト申請'); // 内容確認も一応入れる
    }

    public function test_修正申請の承認処理が正しく行われる()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('attendance.correction.request', $attendance), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => 'テスト申請',
        ]);

        $correctionRequest = CorrectionRequest::first();

        $response = $this->actingAs($admin)
            ->put(route('admin.correction_requests.approve', $correctionRequest->id));


        $response->assertRedirect(); // 成功後のリダイレクト確認
        $this->assertDatabaseHas('correction_requests', [
            'id' => $correctionRequest->id,
            'status' => 'approved'
        ]);
    }
}

