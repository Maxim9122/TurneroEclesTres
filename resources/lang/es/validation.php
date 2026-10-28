<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'required_with' => 'El campo :attribute es obligatorio cuando se completa :values.',
    'required_without' => 'El campo :attribute es obligatorio cuando no se completa :values.',
    'email' => 'El campo :attribute debe ser un email válido.',
    'unique' => 'Ya existe un registro con ese :attribute.',
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'file' => 'El archivo :attribute debe pesar al menos :min KB.',
        'array' => 'Elegí al menos :min en :attribute.',
    ],
    'max' => [
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
        'numeric' => 'El campo :attribute no puede ser mayor a :max.',
        'file' => 'El archivo :attribute no puede pesar más de :max KB.',
        'array' => 'El campo :attribute no puede tener más de :max elementos.',
    ],
    'between' => [
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'file' => 'El archivo :attribute debe pesar entre :min y :max KB.',
        'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
    ],
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'string' => 'El campo :attribute debe ser texto.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'decimal' => 'El campo :attribute debe tener :decimal decimales.',
    'array' => 'El campo :attribute no es válido.',
    'in' => 'El valor seleccionado para :attribute no es válido.',
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'date' => 'El campo :attribute debe ser una fecha válida.',
    'date_format' => 'El campo :attribute debe tener el formato :format.',
    'after' => 'El campo :attribute debe ser posterior a :date.',
    'after_or_equal' => 'El campo :attribute debe ser igual o posterior a :date.',
    'starts_with' => 'El campo :attribute debe empezar con: :values.',
    'url' => 'El campo :attribute debe ser una dirección web válida.',
    'image' => 'El archivo :attribute debe ser una imagen.',
    'mimes' => 'El archivo :attribute debe ser de tipo: :values.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'uploaded' => 'No se pudo subir :attribute. Probá de nuevo o con un archivo más liviano.',
    'exists' => 'El :attribute seleccionado no es válido.',
    'regex' => 'El formato del campo :attribute no es válido.',

    /*
    |--------------------------------------------------------------------------
    | Mensajes puntuales
    |--------------------------------------------------------------------------
    | Las imágenes se achican solas al guardarse, así que el límite es generoso.
    */
    'custom' => [
        'foto' => ['max' => 'La foto no puede pesar más de 10 MB.'],
        'imagenes_adicionales.*' => ['max' => 'Cada foto adicional puede pesar hasta 10 MB.'],
        'logo' => ['max' => 'El logo no puede pesar más de 10 MB.'],
        'imagen_fondo' => ['max' => 'La imagen de fondo no puede pesar más de 10 MB.'],
    ],

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
        'password_actual' => 'contraseña actual',
        'telefono' => 'teléfono',
        'empresa_nombre' => 'nombre del negocio',
        'admin_nombre' => 'tu nombre',
        'admin_email' => 'tu email',
        'rubro' => 'rubro',
        'foto' => 'foto',
        'imagenes_adicionales.*' => 'foto adicional',
        'logo' => 'logo',
        'imagen_fondo' => 'imagen de fondo',
        'color_fondo' => 'color de fondo',
        'duracion_minutos' => 'duración',
        'precio' => 'precio',
        'stock' => 'stock',
        'descripcion' => 'descripción',
        'dias_renovacion' => 'días para renovar',
        'porcentaje_comision' => 'porcentaje de comisión',
        'fecha' => 'fecha',
        'hora' => 'hora',
        'hora_inicio' => 'hora de inicio',
        'hora_fin' => 'hora de fin',
        'dias' => 'días',
        'servicios' => 'servicios',
        'profesional' => 'profesional',
        'profesional_id' => 'profesional',
        'metodo_entrega' => 'método de entrega',
        'cliente_nombre_nuevo' => 'nombre del cliente',
        'motivo' => 'motivo',
    ],
];
