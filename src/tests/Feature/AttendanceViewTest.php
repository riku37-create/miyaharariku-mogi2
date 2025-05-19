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

    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        // 任意の固定日時を設定
        $fixedNow = Carbon::create(2025, 5, 6, 9, 30, 0);
        Carbon::setTestNow($fixedNow); // 現在時刻を固定

        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee($fixedNow->locale('ja')->isoFormat('YYYY年M月D日(ddd)'));
        $response->assertSee($fixedNow->format('H:i'));
    }
}
