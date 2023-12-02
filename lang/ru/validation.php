<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted_if' => 'The :attribute field must be accepted when :other is :value.',
    'after_or_equal' => 'The :attribute field must be a date after or equal to :date.',
    'ascii' => 'The :attribute field must only contain single-byte alphanumeric characters and symbols.',
    'before_or_equal' => 'The :attribute field must be a date before or equal to :date.',
    'boolean' => 'The :attribute field must be true or false.',
    'can' => 'The :attribute field contains an unauthorized value.',
    'current_password' => 'The password is incorrect.',
    'date_equals' => 'The :attribute field must be a date equal to :date.',
    'decimal' => 'The :attribute field must have :decimal decimal places.',
    'declined' => 'The :attribute field must be declined.',
    'declined_if' => 'The :attribute field must be declined when :other is :value.',
    'dimensions' => 'The :attribute field has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_end_with' => 'The :attribute field must not end with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute field must not start with one of the following: :values.',
    'ends_with' => 'The :attribute field must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute field must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'array' => 'The :attribute field must have more than :value items.',
        'file' => 'The :attribute field must be greater than :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than :value.',
        'string' => 'The :attribute field must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute field must have :value items or more.',
        'file' => 'The :attribute field must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than or equal to :value.',
        'string' => 'The :attribute field must be greater than or equal to :value characters.',
    ],
    'in_array' => 'The :attribute field must exist in :other.',
    'ipv4' => 'The :attribute field must be a valid IPv4 address.',
    'ipv6' => 'The :attribute field must be a valid IPv6 address.',
    'json' => 'The :attribute field must be a valid JSON string.',
    'lowercase' => 'The :attribute field must be lowercase.',
    'lt' => [
        'array' => 'The :attribute field must have less than :value items.',
        'file' => 'The :attribute field must be less than :value kilobytes.',
        'numeric' => 'The :attribute field must be less than :value.',
        'string' => 'The :attribute field must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute field must not have more than :value items.',
        'file' => 'The :attribute field must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be less than or equal to :value.',
        'string' => 'The :attribute field must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute field must be a valid MAC address.',
    'max_digits' => 'The :attribute field must not have more than :max digits.',
    'mimetypes' => 'The :attribute field must be a file of type: :values.',
    'min_digits' => 'The :attribute field must have at least :min digits.',
    'missing' => 'The :attribute field must be missing.',
    'missing_if' => 'The :attribute field must be missing when :other is :value.',
    'missing_unless' => 'The :attribute field must be missing unless :other is :value.',
    'missing_with' => 'The :attribute field must be missing when :values is present.',
    'missing_with_all' => 'The :attribute field must be missing when :values are present.',
    'multiple_of' => 'The :attribute field must be a multiple of :value.',
    'not_regex' => 'The :attribute field format is invalid.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'The :attribute field must be present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'starts_with' => 'The :attribute field must start with one of the following: :values.',
    'string' => 'The :attribute field must be a string.',
    'timezone' => 'The :attribute field must be a valid timezone.',
    'uploaded' => 'The :attribute failed to upload.',
    'uppercase' => 'The :attribute field must be uppercase.',
    'ulid' => 'The :attribute field must be a valid ULID.',
    'uuid' => 'The :attribute field must be a valid UUID.',

    "accepted"         => "Вы должны принять :attribute.",
    "active_url"       => "Поле :attribute недействительный URL.",
    "after"            => "Поле :attribute должно быть датой после :date.",
    "alpha"            => "Поле :attribute может содержать только буквы.",
    "alpha_dash"       => "Поле :attribute может содержать только буквы, цифры и дефис.",
    "alpha_num"        => "Поле :attribute может содержать только буквы и цифры.",
    "array"            => "Поле :attribute должно быть массивом.",
    "before"           => "Поле :attribute должно быть датой перед :date.",
    "between"          => [
        "numeric" => "Поле :attribute должно быть между :min и :max.",
        "file"    => "Размер :attribute должен быть от :min до :max Килобайт.",
        "string"  => "Длина :attribute должна быть от :min до :max символов.",
        "array"   => "Поле :attribute должно содержать :min - :max элементов."
    ],
    "confirmed"        => "Поле :attribute не совпадает с подтверждением.",
    "date"             => "Поле :attribute не является датой.",
    "date_format"      => "Поле :attribute не соответствует формату :format.",
    "different"        => "Поля :attribute и :other должны различаться.",
    "digits"           => "Длина цифрового поля :attribute должна быть :digits.",
    "digits_between"   => "Длина цифрового поля :attribute должна быть между :min и :max.",
    "email"            => "Поле :attribute имеет ошибочный формат.",
    "exists"           => "Выбранное значение для :attribute уже существует.",
    "image"            => "Поле :attribute должно быть изображением.",
    "in"               => "Выбранное значение для :attribute ошибочно.",
    "integer"          => "Поле :attribute должно быть целым числом.",
    "ip"               => "Поле :attribute должно быть действительным IP-адресом.",
    "max"              => [
        "numeric" => "Поле :attribute должно быть не больше :max.",
        "file"    => "Поле :attribute должно быть не больше :max Килобайт.",
        "string"  => "Поле :attribute должно быть не длиннее :max символов.",
        "array"   => "Поле :attribute должно содержать не более :max элементов."
    ],
    "mimes"            => "Поле :attribute должно быть файлом одного из типов: :values.",
    "extensions"       => "Поле :attribute должно иметь одно из расширений: :values.",
    "min"              => [
        "numeric" => "Поле :attribute должно быть не менее :min.",
        "file"    => "Поле :attribute должно быть не менее :min Килобайт.",
        "string"  => "Поле :attribute должно быть не короче :min символов.",
        "array"   => "Поле :attribute должно содержать не менее :min элементов."
    ],
    "not_in"           => "Выбранное значение для :attribute ошибочно.",
    "numeric"          => "Поле :attribute должно быть числом.",
    "regex"            => "Поле :attribute имеет ошибочный формат.",
    "required"         => "Поле :attribute обязательно для заполнения.",
    "required_if"      => "Поле :attribute обязательно для заполнения, когда :other равно :value.",
    "required_with"    => "Поле :attribute обязательно для заполнения, когда :values указано.",
    "required_without" => "Поле :attribute обязательно для заполнения, когда :values не указано.",
    "same"             => "Значение :attribute должно совпадать с :other.",
    "size"             => [
        "numeric" => "Поле :attribute должно быть :size.",
        "file"    => "Поле :attribute должно быть :size Килобайт.",
        "string"  => "Поле :attribute должно быть длиной :size символов.",
        "array"   => "Количество элементов в поле :attribute должно быть :size."
    ],
    "unique"           => "Такое значение поля :attribute уже существует.",
    "url"              => "Поле :attribute имеет ошибочный формат.",

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
