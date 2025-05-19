<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class AdminAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'breaks.*.start' => ['required', 'date_format:H:i'],
            'breaks.*.end' => ['required', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in.required' => '出勤時間は必須です',
            'clock_in.date_format' => '出勤時間の形式が不正です',
            'clock_out.required' => '退勤時間は必須です',
            'clock_out.date_format' => '退勤時間の形式が不正です',
            'breaks.*.start.required' => '休憩開始時間は必須です',
            'breaks.*.end.required' => '休憩終了時間は必須です',
            'breaks.*.start.date_format' => '休憩開始時間の形式が不正です',
            'breaks.*.end.date_format' => '休憩終了時間の形式が不正です',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $baseDate = $this->route('attendance')->date ?? now();

            try {
                $clockIn = Carbon::parse($baseDate)->setTimeFromTimeString($this->input('clock_in'));
                $clockOut = Carbon::parse($baseDate)->setTimeFromTimeString($this->input('clock_out'));

                if ($clockIn->greaterThan($clockOut)) {
                    $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                }

                foreach ($this->input('breaks', []) as $index => $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        try {
                            $breakStart = Carbon::parse($baseDate)->setTimeFromTimeString($break['start']);
                            $breakEnd = Carbon::parse($baseDate)->setTimeFromTimeString($break['end']);

                            if ($breakStart->lessThan($clockIn) ||
                                $breakEnd->greaterThan($clockOut) ||
                                $breakStart->greaterThan($breakEnd)
                            ) {
                                $validator->errors()->add("breaks.$index.start", '休憩時間が勤務時間外です');
                            }
                        } catch (\Exception $e) {
                            $validator->errors()->add("breaks.$index.start", '時間の解析に失敗しました');
                        }
                    }
                }
            } catch (\Exception $e) {
                $validator->errors()->add('clock_in', '時間の解析に失敗しました');
            }
        });
    }
}
