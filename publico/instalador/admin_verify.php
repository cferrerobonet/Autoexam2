<?php
/**
 * Funciones para la creación y verificación del usuario administrador
 * Esta biblioteca proporciona funcionalidades avanzadas para el manejo del usuario admin
 */

/**
 * Crea el usuario administrador en la base de datos
 * 
 * @param array $config Configuración de base de datos (host, user, pass, name)
 * @param array $admin_data Datos del administrador (email, pass)
 * @return array Resultado de la operación
 */
function crearUsuarioAdmin($config, $admin_data) {
    $resultado = [
        'success' => false,
        'messages' => [],
        'user_id' => null
    ];
    
    try {
        // Validar datos de entrada
        if (empty($admin_data['admin_email']) || empty($admin_data['admin_pass'])) {
            $resultado['messages'][] = [
                'tipo' => 'error',
                'texto' => 'Faltan datos del administrador'
            ];
            return $resultado;
        }
        
        // Validar email
        if (!filter_var($admin_data['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $resultado['messages'][] = [
                'tipo' => 'error',
                'texto' => 'El formato del correo electrónico es inválido'
            ];
            return $resultado;
        }
        
        // Conectar a la BD
        $mysqli = new mysqli(
            $config['db_host'], 
            $config['db_user'], 
            $config['db_pass'], 
            $config['db_name']
        );
        
        if ($mysqli->connect_error) {
            throw new Exception("Error de conexión: " . $mysqli->connect_error);
        }
        
        // Verificar si ya existe el usuario
        $email = $mysqli->real_escape_string($admin_data['admin_email']);
        $check_query = "SELECT id_usuario FROM usuarios WHERE correo = '$email'"; // Corregido: id a id_usuario
        $check_result = $mysqli->query($check_query);
        
        if ($check_result && $check_result->num_rows > 0) {
            $row = $check_result->fetch_assoc();
            $user_id = $row['id_usuario']; // Corregido: id a id_usuario
            
            // Actualizar el usuario existente
            $password_hash = password_hash($admin_data['admin_pass'], PASSWORD_DEFAULT);
            
            // Configurar rutas de imagen de usuario
            $img_config = configurarImagenUsuario();
            $avatar_path = $img_config['avatar_path'];
            
            $update_query = "UPDATE usuarios SET 
                contrasena = '$password_hash',
                activo = 1,
                foto = '$avatar_path',
                rol = 'admin'
                WHERE id_usuario = $user_id"; // Corregido: id a id_usuario
                
            if ($mysqli->query($update_query)) {
                $resultado['success'] = true;
                $resultado['user_id'] = $user_id;
                $resultado['messages'][] = [
                    'tipo' => 'info',
                    'texto' => 'El usuario administrador ya existía y ha sido actualizado'
                ];
            } else {
                throw new Exception("Error al actualizar el usuario: " . $mysqli->error);
            }
        } else {
            // Crear nuevo usuario administrador usando datos del .env
            $password_hash = password_hash($admin_data['admin_pass'], PASSWORD_DEFAULT);
            
            // Obtener datos del administrador del .env
            $admin_nombre = $admin_data['admin_nombre'] ?? 'Administrador';
            $admin_apellidos = $admin_data['admin_apellidos'] ?? 'Sistema';
            
            // Fecha actual para el registro
            $fecha_actual = date('Y-m-d H:i:s');
            
            // Configurar rutas de imagen de usuario
            $img_config = configurarImagenUsuario();
            $avatar_path = $img_config['avatar_path'];
            
            $insert_query = "INSERT INTO usuarios (
                nombre, 
                apellidos, 
                correo, 
                contrasena, 
                rol,
                foto, 
                activo
            ) VALUES (
                '$admin_nombre',
                '$admin_apellidos',
                '$email',
                '$password_hash',
                'admin',
                '$avatar_path',
                1
            )";
            
            if ($mysqli->query($insert_query)) {
                $user_id = $mysqli->insert_id;
                $resultado['success'] = true;
                $resultado['user_id'] = $user_id;
                $resultado['messages'][] = [
                    'tipo' => 'success',
                    'texto' => 'Usuario administrador creado correctamente'
                ];
                
                // Crear registro de actividad
                $activity_query = "INSERT INTO registros_actividad (
                    usuario_id,
                    accion,
                    detalles,
                    fecha,
                    ip
                ) VALUES (
                    $user_id,
                    'creacion_cuenta',
                    'Usuario administrador creado durante la instalación',
                    '$fecha_actual',
                    '" . $_SERVER['REMOTE_ADDR'] . "'
                )";
                
                $mysqli->query($activity_query);
                
            } else {
                throw new Exception("Error al crear el usuario administrador: " . $mysqli->error);
            }
        }
        
        $mysqli->close();
    } catch (Exception $e) {
        $resultado['messages'][] = [
            'tipo' => 'error',
            'texto' => $e->getMessage()
        ];
    }
    
    return $resultado;
}

/**
 * Configura las rutas de avatares y copia la imagen predeterminada
 * 
 * @return array Información sobre las rutas y estado de la operación
 */
function configurarImagenUsuario() {
    // Ruta pública relativa para el avatar por defecto (se guardará en BD)
    $avatar_public_subpath = 'recursos/subidas/avatares';
    $default_avatar_filename = 'avatar_usuario_defecto.png';
    $default_avatar_public_path = $avatar_public_subpath . '/' . $default_avatar_filename;

    // Ruta física de almacenamiento para los avatares
    // __DIR__ es /publico/instalador/, por lo que ../ es /publico/
    $avatars_storage_dir = __DIR__ . '/../' . $avatar_public_subpath;

    $info = [
        'avatar_path' => $default_avatar_public_path, // Ruta que se guardará en la BD
        'avatars_storage_dir_exists' => false,
        'default_avatar_source_exists' => false,
        'default_avatar_copied' => false,
        'messages' => []
    ];

    // Crear directorio de almacenamiento de avatares si no existe
    if (!is_dir($avatars_storage_dir)) {
        if (@mkdir($avatars_storage_dir, 0755, true)) {
            $info['messages'][] = ['tipo' => 'info', 'texto' => "Directorio de avatares creado: $avatars_storage_dir"];
        } else {
            $info['messages'][] = ['tipo' => 'error', 'texto' => "No se pudo crear el directorio de avatares: $avatars_storage_dir"];
            // No continuar si no se puede crear el directorio principal
            return $info; 
        }
    }
    $info['avatars_storage_dir_exists'] = is_dir($avatars_storage_dir);

    // Ruta de origen para la imagen por defecto
    // Asumimos que está en publico/recursos/img/user_image_default.png
    $default_avatar_source_path = __DIR__ . '/../recursos/img/user_image_default.png';
    $info['default_avatar_source_exists'] = file_exists($default_avatar_source_path);

    // Ruta de destino para la imagen por defecto en el directorio de avatares públicos
    $default_avatar_destination_path = $avatars_storage_dir . '/' . $default_avatar_filename;

    // Copiar la imagen por defecto si existe en origen y no en destino, o si la de origen es más nueva
    if ($info['default_avatar_source_exists']) {
        if (!file_exists($default_avatar_destination_path) || filemtime($default_avatar_source_path) > filemtime($default_avatar_destination_path)) {
            if (@copy($default_avatar_source_path, $default_avatar_destination_path)) {
                $info['default_avatar_copied'] = true;
                $info['messages'][] = ['tipo' => 'success', 'texto' => "Imagen de avatar por defecto copiada a: $default_avatar_destination_path"];
            } else {
                $info['messages'][] = ['tipo' => 'error', 'texto' => "No se pudo copiar la imagen de avatar por defecto desde $default_avatar_source_path a $default_avatar_destination_path"];
            }
        } else {
             $info['messages'][] = ['tipo' => 'info', 'texto' => "La imagen de avatar por defecto ya existe y está actualizada en: $default_avatar_destination_path"];
             $info['default_avatar_copied'] = true; // Considerar como "lista" si ya existe
        }
    } else {
        $info['messages'][] = ['tipo' => 'warning', 'texto' => "No se encontró la imagen de avatar por defecto en origen: $default_avatar_source_path. Se usará la ruta pero la imagen podría faltar."];
    }
    
    // La función `crearUsuarioAdmin` usará $info['avatar_path'] para la BD.
    return $info;
}

/**
 * Generar una contraseña segura aleatoria
 * 
 * @param int $longitud Longitud deseada para la contraseña
 * @return string Contraseña generada
 */
function generarContrasenaSegura($longitud = 12) {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_=+[]{}|;:,.<>?';
    $caracteresLongitud = strlen($caracteres);
    $contrasena = '';
    
    for ($i = 0; $i < $longitud; $i++) {
        $contrasena .= $caracteres[random_int(0, $caracteresLongitud - 1)];
    }
    
    return $contrasena;
}

/**
 * Verifica si una contraseña cumple con los requisitos mínimos de seguridad
 * 
 * @param string $password Contraseña a verificar
 * @return array Resultado de la verificación
 */
function verificarSeguridadContrasena($password) {
    $resultado = [
        'segura' => false,
        'puntuacion' => 0,
        'recomendaciones' => []
    ];
    
    // Verificar longitud mínima
    if (strlen($password) < 8) {
        $resultado['recomendaciones'][] = 'La contraseña debe tener al menos 8 caracteres';
    } else {
        $resultado['puntuacion']++;
    }
    
    // Verificar presencia de letras minúsculas
    if (!preg_match('/[a-z]/', $password)) {
        $resultado['recomendaciones'][] = 'Debe incluir al menos una letra minúscula';
    } else {
        $resultado['puntuacion']++;
    }
    
    // Verificar presencia de letras mayúsculas
    if (!preg_match('/[A-Z]/', $password)) {
        $resultado['recomendaciones'][] = 'Debe incluir al menos una letra mayúscula';
    } else {
        $resultado['puntuacion']++;
    }
    
    // Verificar presencia de números
    if (!preg_match('/[0-9]/', $password)) {
        $resultado['recomendaciones'][] = 'Debe incluir al menos un número';
    } else {
        $resultado['puntuacion']++;
    }
    
    // Verificar presencia de caracteres especiales
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $resultado['recomendaciones'][] = 'Debe incluir al menos un carácter especial';
    } else {
        $resultado['puntuacion']++;
    }
    
    // Verificar que no sea una contraseña común
    $contrasenas_comunes = ['password', 'admin123', '12345678', 'qwerty', 'welcome', 'admin'];
    if (in_array(strtolower($password), $contrasenas_comunes)) {
        $resultado['recomendaciones'][] = 'La contraseña es demasiado común o predecible';
        $resultado['puntuacion'] = 0;
    }
    
    // La contraseña es segura si tiene una puntuación mínima
    $resultado['segura'] = ($resultado['puntuacion'] >= 4);
    
    return $resultado;
}
