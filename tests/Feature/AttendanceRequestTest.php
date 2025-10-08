<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 出勤時間が退勤時間より後になっている場合、バリデーションエラーメッセージが表示される
     */
    public function error_when_clock_in_is_after_clock_out()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2025-09-20 09:00:00',
            'clock_out' => '2025-09-20 18:00:00',
        ]);

        $this->actingAs($user);

        // 勤怠修正申請（出勤時間を退勤時間より後にする）
        $response = $this->post(route('attendance_request.store', $attendance->id), [
            'year' => '2025',
            'month_day' => '09-20',
            'clock_in' => '20:00',
            'clock_out' => '18:00',
            'reason' => 'テスト',
        ]);

        $response->assertStatus(302);

        // バリデーションエラーを確認
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     * @test
     * 休憩開始時間が退勤時間より後になっている場合、バリデーションエラーメッセージが表示される
     */
    public function error_when_break_start_after_clock_out()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2025-10-01 09:00:00',
            'clock_out' => '2025-10-01 18:00:00',
        ]);

        $this->actingAs($user);

        // 勤怠修正申請POST（休憩開始を退勤より後に設定）
        $response = $this->post(route('attendance_request.store', $attendance->id), [
            'year' => '2025',
            'month_day' => '10-01',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                [
                    'break_start' => '19:00',
                    'break_end' => '19:30',
                ]
            ],
            'reason' => 'テスト',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'breaks.0.break_start' => '休憩時間が不適切な値です',
        ]);
    }

    /**
     * @test
     * 休憩終了時間が退勤時間より後になっている場合、バリデーションエラーメッセージが表示される
     */
    public function error_when_break_end_after_clock_out()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2025-10-01 09:00:00',
            'clock_out' => '2025-10-01 18:00:00',
        ]);

        $this->actingAs($user);

        // 勤怠修正申請POST（休憩終了を退勤より後に設定）
        $response = $this->post(route('attendance_request.store', $attendance->id), [
            'year' => '2025',
            'month_day' => '10-01',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                [
                    'break_start' => '17:30',
                    'break_end' => '19:00',
                ]
            ],
            'reason' => 'テスト',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'breaks.0.break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     * @test
     * 備考欄が未入力の場合、バリデーションエラーメッセージが表示される
     */
    public function error_when_reason_is_empty()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2025-10-01 09:00:00',
            'clock_out' => '2025-10-01 18:00:00',
        ]);

        $this->actingAs($user);

        // 勤怠修正申請POST（備考未入力）
        $response = $this->post(route('attendance_request.store', $attendance->id), [
            'year' => '2025',
            'month_day' => '10-01',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                [
                    'break_start' => '12:00',
                    'break_end' => '13:00',
                ]
            ],
            'reason' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください。',
        ]);
    }



    //ここに  修正申請処理が実行される  のテスト！！


    /**
     * @test
     * 申請一覧画面の「承認待ち」にログインユーザーの申請が全て表示される
     */
    public function pending_requests_on_index()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // ログインユーザーの勤怠データ
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2025-10-01 09:00:00',
            'clock_out' => '2025-10-01 18:00:00',
        ]);

        // ログインユーザーの申請を2件作成
        $requests = AttendanceRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => RequestStatus::PENDING,
            'reason' => 'テスト',
        ]);

        // 他ユーザーの申請
        AttendanceRequest::factory()->create([
            'user_id' => $otherUser->id,
            'attendance_id' => $attendance->id,
            'status' => RequestStatus::PENDING,
            'reason' => '他人の申請です',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('attendance_requests.index'));
        $response->assertStatus(200);

        // ログインユーザー自身の申請がすべて表示されていることを確認
        foreach ($requests as $request) {
            $response->assertSee('承認待ち');
            $response->assertSee($user->name);
            $response->assertSee($request->reason);
            $response->assertSee($request->request_date->format('Y/m/d'));
        }

        $response->assertDontSee('他人の申請');
    }


    //ここに  「承認済み」に管理者が承認した修正申請が全て表示されている  のテスト！！

    /**
     * @test
     * 申請一覧画面の「詳細」を押下すると勤怠詳細画面に遷移する
     */
    public function attendance_detail_from_request_list()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2025-10-01 09:00:00',
            'clock_out' => '2025-10-01 18:00:00',
        ]);

        $this->actingAs($user);

        // 勤怠修正申請
        $response = $this->post(route('attendance_request.store', $attendance->id), [
            'year' => '2025',
            'month_day' => '10-01',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                [
                    'break_start' => '12:00',
                    'break_end' => '12:30',
                ]
            ],
            'reason' => 'テスト',
        ]);

        $response->assertStatus(302);

        // 申請一覧画面を開く
        $listResponse = $this->get(route('attendance_requests.index'));
        $listResponse->assertStatus(200);

        $attendanceRequest = $attendance->attendanceRequests()->latest()->first();

        // 「詳細」ページへのリンクを確認して遷移
        $detailResponse = $this->get(route('attendance.detail', [
            'id' => $attendanceRequest->attendance_id,
            'request_id' => $attendanceRequest->id,
            'from' => 'request_list'
        ]));

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($user->name);
        $detailResponse->assertSee('勤怠詳細');
    }
}
