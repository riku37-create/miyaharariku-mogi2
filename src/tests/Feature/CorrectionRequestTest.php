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

    public function test_出勤時間が退勤時間より後の場合はバリデーションエラーが表示される()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create();

        $response = $this->from(route('attendance.detail', $attendance->id))
            ->post(route('attendance.correction.request', $attendance->id), [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'reason' => 'テストのため',
                'breaks' => [], // 空でもOK
            ]);

        $response->assertRedirect(route('attendance.detail', $attendance->id));
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_休憩開始時間が退勤時間より後の場合はバリデーションエラーが表示される()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()
        ->for($user)
        ->create();

        $response = $this->from(route('attendance.detail', $attendance->id))
            ->post(route('attendance.correction.request', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'reason' => 'テスト',
                'breaks' => [
                    [
                        'id' => $attendance->breaks->first()->id,
                        'start' => '19:00', // ← 退勤後
                        'end' => '20:00',
                    ],
                ],
            ]);

        $response->assertRedirect(route('attendance.detail', $attendance->id));
        $response->assertSessionHasErrors(['breaks.0.start' => '休憩時間が勤務時間外です',]);
    }

    public function test_休憩終了時間が退勤時間より後の場合はバリデーションエラーが表示される()
    {
        /** @var \App\Models\User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()
        ->for($user)
        ->create();

        $response = $this->from(route('attendance.detail', $attendance->id))
            ->post(route('attendance.correction.request', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'reason' => 'テスト',
                'breaks' => [
                    [
                        'id' => $attendance->breaks->first()->id,
                        'start' => '12:00',
                        'end' => '19:00', // ← 退勤後
                    ],
                ],
            ]);

        $response->assertRedirect(route('attendance.detail', $attendance->id));
        $response->assertSessionHasErrors(['breaks.0.start' => '休憩時間が勤務時間外です',]);
    }

    public function test_備考欄が未入力の場合はバリデーションエラーが表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create();

        $response = $this->from(route('attendance.detail', $attendance->id))
            ->post(route('attendance.correction.request', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'reason' => '', // 備考を未入力
            ]);

        $response->assertRedirect(route('attendance.detail', $attendance->id));
        $response->assertSessionHasErrors(['reason' => '備考を記入してください',]);
    }

    public function test_「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    {
        /** @var \App\Models\User */
        $admin = User::factory()->create(['role' => 'admin']);
        /** @var \App\Models\User */
        $user = User::factory()->create();

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create();

        // 修正申請を送信
        $this->actingAs($user)->post(route('attendance.correction.request', $attendance->id), [
            'clock_in' => '09:15',
            'clock_out' => '18:15',
            'reason' => 'テスト'
        ]);

        $this->actingAs($user)
            ->get(route('staff.correction_requests.index'))
            ->assertSee('承認待ち')
            ->assertSee($user->name)
            ->assertSee('テスト');
    }

    public function test_「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create();

        // 修正申請を送信
        $this->actingAs($user)
        ->post(route('attendance.correction.request', $attendance), [
                'clock_in' => '09:15',
                'clock_out' => '18:10',
                'reason' => 'テスト'
            ]);

        $correctionRequest = CorrectionRequest::first();

        // 管理者が承認処理
        $this->actingAs($admin)
        ->put(route('admin.correction_requests.approve', $correctionRequest->id));

        $this->actingAs($user)
            ->get(route('staff.correction_requests.index', ['status' => 'approved']))
            ->assertSee('承認済み')
            ->assertSee($user->name)
            ->assertSee('テスト');
    }

    public function test_各申請の「詳細」を押下すると申請詳細画面に遷移する()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()
        ->noBreaks()
        ->for($user)
        ->create();

        $this->actingAs($user)
        ->post(route('attendance.correction.request', $attendance), [
                'clock_in' => '09:15',
                'clock_out' => '18:10',
                'reason' => 'テスト'
        ]);

        $response = $this->actingAs($user)
        ->get(route('staff.correction_requests.index'));

        $response->assertStatus(200);

        // 詳細リンクが画面内に存在するか（ルートURLの文字列で確認）
        $response->assertSee(route('attendance.detail', $attendance->id));

    // 実際に詳細画面に遷移できるか確認
        $detailResponse = $this->actingAs($user)
        ->get(route('attendance.detail', $attendance->id));

        $detailResponse->assertStatus(200);
    }
}
