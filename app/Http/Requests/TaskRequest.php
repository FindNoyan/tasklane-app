<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * No auth gate for this demo board: anyone who can reach the
     * form may create or edit a task, exactly as before.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The validation rules, kept identical to the inline rules they
     * replace in TaskController@store and TaskController@update.
     * The two actions differ in one field each, so those rules are
     * added by HTTP method: creating (POST) requires a board_id,
     * editing (PUT) requires a status.
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
        ];

        if ($this->isMethod('post')) {
            $rules['board_id'] = ['required', 'exists:boards,id'];
        }

        if ($this->isMethod('put')) {
            $rules['status'] = ['required', 'in:todo,in_progress,done'];
        }

        return $rules;
    }
}
