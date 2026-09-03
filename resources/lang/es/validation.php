<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'email' => 'El campo :attribute debe ser un email válido.',
    'unique' => 'Ya existe un registro con ese :attribute.',
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
    ],
    'max' => [
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
        'numeric' => 'El campo :attribute no puede ser mayor a :max.',
    ],
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'string' => 'El campo :attribute debe ser texto.',
    'in' => 'El valor seleccionado para :attribute no es válido.',
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'date' => 'El campo :attribute debe ser una fecha válida.',
    'image' => 'El campo :attribute debe ser una imagen.',
    'mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'exists' => 'El :attribute seleccionado no es válido.',
    'regex' => 'El formato del campo :attribute no es válido.',

    /*
    |--------------------------------------------------------------------------
    | Nombres de los campos, en español (para que el mensaje quede natural)
    |--------------------------------------------------------------------------
    */
    'attributes' => [
        'nombre' => 'nombre',
        'email' => 'email',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
        'telefono' => 'teléfono',
        'empresa_nombre' => 'nombre del negocio',
        'admin_nombre' => 'tu nombre',
        'admin_email' => 'tu email',
        'rubro' => 'rubro',
    ],
];