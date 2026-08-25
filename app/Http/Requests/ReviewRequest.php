<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    /**
     * true vì route này đã được bọc middleware 'auth' ở routes/web.php
     * (chỉ user đăng nhập mới gọi tới đây được).
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.min' => 'Đánh giá phải từ 1 đến 5 sao.',
            'rating.max' => 'Đánh giá phải từ 1 đến 5 sao.',
            'comment.max' => 'Nhận xét không được vượt quá 1000 ký tự.',
        ];
    }
}
