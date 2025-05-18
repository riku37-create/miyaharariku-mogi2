<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use App\Models\RequestAttendance;

class CorrectionRequestTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */

    use RefreshDatabase;

    public function 出勤時間が退勤時間より後の場合はバリデーションエラーが表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 修正リクエストを送信（出勤が退勤より遅い）
        $response = $this->from(route('attendance.detail', $attendance->id))
            ->post(route('attendance.correction.request', $attendance->id), [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'reason' => 'テストのため',
                'breaks' => [], // 空でもOK
            ]);

        // リダイレクトされて、元のページに戻る
        $response->assertRedirect(route('attendance.detail', $attendance->id));
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合はバリデーションエラーが表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 勤怠 + 休憩データ作成（退勤18:00）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $break = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        // 修正リクエスト（休憩開始が退勤後）
        $response = $this->from(route('attendance.detail', $attendance->id))
            ->post(route('attendance.correction.request', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'reason' => 'テストのため',
                'breaks' => [
                    [
                        'id' => $break->id,
                        'start' => '19:00', // ← 退勤後
                        'end' => '20:00',
                    ],
                ],
            ]);

        $response->assertRedirect(route('attendance.detail', $attendance->id));
        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が勤務時間外です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合はバリデーションエラーが表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 勤怠 + 休憩データ作成（退勤18:00）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $break = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        // 修正リクエスト（休憩終了が退勤後）
        $response = $this->from(route('attendance.detail', $attendance->id))
            ->post(route('attendance.correction.request', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'reason' => 'テストのため',
                'breaks' => [
                    [
                        'id' => $break->id,
                        'start' => '12:00',
                        'end' => '19:00', // ← 退勤後
                    ],
                ],
            ]);

        $response->assertRedirect(route('attendance.detail', $attendance->id));
        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が勤務時間外です',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合はバリデーションエラーが表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->from(route('attendance.detail', $attendance->id))
            ->post(route('attendance.correction.request', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'reason' => '', // 備考を未入力
            ]);

        $response->assertRedirect(route('attendance.detail', $attendance->id));
        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }

    public function test_修正申請が管理者の画面に表示される()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        $break = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '12:30:00',
        ]);

        // ユーザーとしてログインし修正申請を送信
        $this->actingAs($user)->post(route('attendance.correction.request', $attendance->id), [
            'clock_in' => '09:15',
            'clock_out' => '18:15',
            'reason' => '打刻ミスのため',
            'breaks' => [
                [
                    'id' => $break->id,
                    'start' => '12:10',
                    'end' => '12:40',
                ]
            ],
        ]);

        // 管理者としてログインして一覧を確認
        $this->actingAs($admin)
            ->get(route('admin.correction_requests.index'))
            ->assertSee('承認待ち')
            ->assertSee($user->name)
            ->assertSee(today()->format('Y年m月d日'))
            ->assertSee('打刻ミスのため');

        // 詳細画面も確認
        $correctionRequest = CorrectionRequest::where('user_id', $user->id)->first();
        $this->get(route('admin.correction_requests.show', $correctionRequest->id))
            ->assertSee('打刻ミスのため')
            ->assertSee('承認待ち')
            ->assertSee('09:15')
            ->assertSee('18:15')
            ->assertSee('12:10')
            ->assertSee('12:40');
    }

    public function test_修正申請後_管理者の申請一覧に表示される()
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

        $break = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        // ログインして修正申請を送信
        $this->actingAs($user)
            ->post(route('attendance.correction.request', $attendance), [
                'clock_in' => '09:15',
                'clock_out' => '18:10',
                'reason' => '退勤打刻漏れのため修正',
                'breaks' => [
                    [
                        'id' => $break->id,
                        'start' => '12:05',
                        'end' => '13:05',
                    ],
                ],
            ])->assertRedirect(route('staff.attendances.index'));

        // 管理者としてログインして承認待ち一覧を確認
        $this->actingAs($admin)
            ->get(route('admin.correction_requests.index', ['status' => 'pending']))
            ->assertSee('承認待ち')
            ->assertSee($user->name)
            ->assertSee(today()->format('Y年m月d日'))
            ->assertSee('退勤打刻漏れのため修正');
    }

    public function test_修正申請が承認された後_承認済み一覧に表示される()
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

        $break = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        // ユーザーが修正申請を送信
        $this->actingAs($user)
            ->post(route('attendance.correction.request', $attendance), [
                'clock_in' => '09:30',
                'clock_out' => '18:10',
                'reason' => '遅刻のため',
                'breaks' => [
                    [
                        'id' => $break->id,
                        'start' => '12:15',
                        'end' => '13:05',
                    ],
                ],
            ])->assertRedirect(route('staff.attendances.index'));

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

    public function test_各申請の「詳細」を押下すると申請詳細画面に遷移する()
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
            'breaks' => [],
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
}
