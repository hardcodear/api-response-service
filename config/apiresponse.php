<?php

return [
    // Route patterns where the global exception formatter should be applied.
    'api_patterns' => ['api', 'api/*'],

    // Default messages can be overridden by publishing this config.
    'messages' => [
        'access_denied' => 'No tiene permiso para ejecutar esta API',
        'not_found' => 'URL no encontrada',
        'too_many_requests' => 'Demasiadas peticiones',
        'route_not_found' => 'Token invalido o no enviado',
        'authentication' => 'Token de autorizacion requerido o invalido',
        'method_not_allowed' => 'Metodo HTTP no permitido para esta ruta',
        'validation' => 'Error de validacion',
        'authorization' => 'No autorizado para este recurso',
        'client_error' => 'Error en la solicitud',
        'server_error' => 'Error interno del servidor',
        'service_unavailable' => 'El sistema se encuentra en mantenimiento',
    ],
];
