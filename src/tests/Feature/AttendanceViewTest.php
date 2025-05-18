<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;


class AttendanceViewTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /** @test */
    public function 勤怠打刻画面に現在の日時が表示される()
    {
        // 任意の固定日時を設定
        $fixedNow = Carbon::create(2025, 5, 6, 9, 30, 0);
        Carbon::setTestNow($fixedNow); // 現在時刻を固定

        // ログインユーザー作成＆認証
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 勤怠打刻画面にアクセス
        $response = $this->get('/attendance');

        // 日付と時刻が画面に表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee($fixedNow->toDateString());   // 2025-05-06
        $response->assertSee($fixedNow->toTimeString());   // 09:30:00
    }
}
