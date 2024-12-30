<?php

declare(strict_types=1);

namespace App\Livewire\Forms\People;

use App\Rules\DobValid;
use App\Rules\YobValid;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProfileForm extends Form
{
    // -----------------------------------------------------------------------
    public $firstname = null;
    public $surname = null;
    public $marriedname = null;
    public $nickname = null;
    public $sex = null;
    #[Validate]
    public $yob = null;
    #[Validate]
    public $dob = null;
    public $pob = null;
    public $summary = null;

    // -----------------------------------------------------------------------
    public function rules(): array
    {
        return [
            'firstname' => ['nullable', 'string', 'max:255'],
            'surname'   => ['required', 'string', 'max:255'],
            'marriedname' => ['nullable', 'string', 'max:255'],
            'nickname'  => ['nullable', 'string', 'max:255'],
            'sex'       => ['required', 'in:m,f'],
            'yob' => [
                'nullable',
                'integer',
                'min:1',
                'max:' . date('Y'),
                new YobValid,
            ],
            'dob' => [
                'nullable',
                'date_format:Y-m-d',
                'before_or_equal:today',
                new DobValid,
            ],
            'pob' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:65535'],
        ];
    }

    public function messages(): array
    {
        return [];
    }

    public function validationAttributes(): array
    {
        return [
            'firstname' => __('person.firstname'),
            'surname'   => __('person.surname'),
            'marriedname' => __('person.marriedname'),
            'nickname'  => __('person.nickname'),
            'sex'       => __('person.sex'),
            'yob' => __('person.yob'),
            'dob' => __('person.dob'),
            'pob' => __('person.pob'),
            'summary' => __('person.summary'),
        ];
    }
}