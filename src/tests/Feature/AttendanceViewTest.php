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
        $fixedNow = Carbon::create(2025, 5, 6, 9, 30, 0); //任意の固定時刻
        Carbon::setTestNow($fixedNow);

        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/server-time');

        $response->assertStatus(200);
        $response->assertJson([
            'now' => $fixedNow->format('H:i'),
        ]);
    }
}
