# 📚 **Sistema de Gestión Académica UF4 Backend - Documentación API**

## 📋 **Información General**

**Proyecto:** Sistema de Gestión Académica UF4 Backend  
**Framework:** Laravel 11  
**Base URL Producción:** `https://gestionacademicauf4backend-production.up.railway.app`  
**Base URL Desarrollo:** `http://127.0.0.1:8000`  
**Autenticación:** JWT (JSON Web Tokens)  
**Documentación Generada:** Mayo 2025

---

## 🏗️ **Arquitectura del Sistema**

### **Modelos Principales**
- **User** - Gestión de usuarios (admin, profesor, alumno)
- **Grupo** - Gestión de grupos académicos
- **Clase** - Gestión de clases
- **Modulo** - Gestión de módulos formativos
- **Enunciado** - Gestión de enunciados de prácticas
- **Entrega** - Gestión de entregas de estudiantes
- **Nota** - Gestión de calificaciones
- **Rubrica** - Gestión de rúbricas de evaluación
- **Practica** - Gestión de prácticas
- **Cursar** - Relación estudiante-grupo

### **Middleware de Seguridad**
- **IsUserAuth** - Verificación de autenticación JWT
- **IsAdmin** - Acceso exclusivo para administradores
- **IsProfesor** - Acceso exclusivo para profesores

---

## 🔐 **Sistema de Autenticación**

### **Roles de Usuario**
- **admin** - Acceso completo al sistema
- **profesor** - Gestión de sus recursos académicos
- **user/alumno** - Acceso limitado a sus datos

### **Autenticación JWT**
Todas las rutas protegidas requieren el header:
```
Authorization: Bearer {jwt_token}
```

---

## 🛡️ **Middleware Documentación**

### **1. IsUserAuth**
**Archivo:** `app/Http/Middleware/IsUserAuth.php`
```php
<?php
namespace App\Http\Middleware;

class IsUserAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if(auth('api')->user()){
            return $next($request);
        }else{
            return response()->json([
                'message' => 'Unauthorized invalid token'
            ],401);
        }
    }
}
```

### **2. IsAdmin**
**Archivo:** `app/Http/Middleware/IsAdmin.php`
```php
<?php
namespace App\Http\Middleware;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();
        if ($user && $user->rol === 'admin') {
            return $next($request);
        }
        return response()->json([
            'message' => 'Unauthorized, you are not an admin'
        ], 403);
    }
}
```

### **3. IsProfesor**
**Archivo:** `app/Http/Middleware/IsProfesor.php`
```php
<?php
namespace App\Http\Middleware;

class IsProfesor
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            if ($user->rol !== 'profesor') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. Solo profesores pueden acceder a este recurso.'
                ], 403);
            }

            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido'
            ], 401);
        }
    }
}
```

---

## 🚀 **Endpoints API**

### **📌 RUTAS PÚBLICAS**

#### **1. Registro de Usuario**
**Endpoint:** `POST /api/registro`  
**Middleware:** Ninguno

**Request:**
```json
{
    "name": "Juan",
    "surname": "Pérez",
    "email": "juan@example.com",
    "password": "password123",
    "dni": "12345678A",
    "rol": "profesor",
    "url": "https://mi-perfil.com"
}

{
    "name": "pepe",
    "surname": "Pérez",
    "email": "pepe@example.com",
    "password": "password123",
    "dni": "12345678B",
    "rol": "admin",
    "url": "https://mi-perfil.com"
}
```

**Response (201):**
```json
{
    "status": true,
    "message": "User registered successfully",
    "data": {
        "id": 1,
        "name": "Juan",
        "surname": "Pérez",
        "email": "juan@example.com",
        "dni": "12345678A",
        "rol": "profesor",
        "url": "https://mi-perfil.com",
        "created_at": "2025-05-30T10:00:00.000000Z",
        "updated_at": "2025-05-30T10:00:00.000000Z"
    }
}
```

#### **2. Inicio de Sesión**
**Endpoint:** `POST /api/inicioSesion`  
**Middleware:** Ninguno

**Request:**
```json
{
    "email": "juan@example.com",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "message": "Inicio de sesión exitoso",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

---

### **📌 RUTAS AUTENTICADAS (isUserAuth)**

#### **3. Obtener Usuario Autenticado**
**Endpoint:** `GET /api/me`  
**Middleware:** isUserAuth

**Response (200):**
```json
{
    "message": "Usuario autenticado",
    "data": {
        "id": 1,
        "name": "Juan",
        "surname": "Pérez",
        "email": "juan@example.com",
        "dni": "12345678A",
        "rol": "profesor",
        "url": "https://mi-perfil.com"
    }
}
```

#### **4. Cerrar Sesión**
**Endpoint:** `POST /api/logout`  
**Middleware:** isUserAuth

**Response (200):**
```json
{
    "message": "Logout exitoso"
}
```

#### **5. Actualizar Configuración de Usuario**
**Endpoint:** `PUT /api/updateUserSettings`  
**Middleware:** isUserAuth

**Request:**
```json
{
    "name": "Juan Carlos",
    "surname": "Pérez García",
    "email": "juan.carlos@example.com",
    "url": "https://nuevo-perfil.com"
}
```

**Response (200):**
```json
{
    "message": "User settings updated successfully"
}
```

#### **6. Actualizar Contraseña**
**Endpoint:** `PUT /api/updateUserSettingsPassword`  
**Middleware:** isUserAuth

**Request:**
```json
{
    "password": "nueva_password123"
}
```

**Response (200):**
```json
{
    "message": "User password updated successfully"
}
```

#### **7. Obtener Entregas**
**Endpoint:** `GET /api/entregas`  
**Middleware:** isUserAuth

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "descripcion": "Entrega de proyecto final",
            "fecha_entrega": "2025-06-15T10:30:00.000000Z",
            "nota": 8.5,
            "usuario": {
                "id": 2,
                "name": "María",
                "surname": "García"
            }
        }
    ],
    "message": "Entregas obtenidas correctamente"
}
```

#### **8. Crear Entrega**
**Endpoint:** `POST /api/entregas`  
**Middleware:** isUserAuth

**Request:**
```json
{
    "descripcion": "Entrega del proyecto de Laravel",
    "fecha_entrega": "2025-06-20T23:59:59",
    "archivo_url": "https://ejemplo.com/archivo.pdf",
    "enunciado_id": 5,
    "practica_id": 2,
    "user_id":6,
    "archivo":"fkaopfkafkafakp"
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "id": 5,
        "descripcion": "Entrega del proyecto de Laravel",
        "fecha_entrega": "2025-06-20T23:59:59.000000Z",
        "archivo_url": "https://ejemplo.com/archivo.pdf",
        "enunciado_id": 1,
        "user_id": 2
    },
    "message": "Entrega creada correctamente"
}
```

#### **9. Obtener Enunciados**
**Endpoint:** `GET /api/enunciados`  
**Middleware:** isUserAuth

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "descripcion": "Crear una aplicación web completa",
            "fecha_limite": "2025-06-30T23:59:59.000000Z",
            "practica": {
                "id": 1,
                "nombre": "Proyecto Final Backend"
            },
            "modulo": {
                "id": 1,
                "nombre": "Desarrollo Web Backend",
                "codigo": "DWB001"
            },
            "profesor": {
                "id": 3,
                "name": "Juan",
                "surname": "Pérez"
            }
        }
    ],
    "message": "Enunciados obtenidos correctamente"
}
```

#### **10. Obtener Grupos**
**Endpoint:** `GET /api/grupos`  
**Middleware:** isUserAuth

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nombre": "Grupo DAW 2025",
            "user_id": 3,
            "created_at": "2025-05-30T08:00:00.000000Z",
            "updated_at": "2025-05-30T08:00:00.000000Z",
            "user": {
                "id": 3,
                "name": "Juan",
                "surname": "Pérez",
                "email": "juan@example.com",
                "rol": "profesor"
            },
            "modulos": [
                {
                    "id": 1,
                    "nombre": "Desarrollo Web Frontend",
                    "codigo": "DWF001",
                    "descripcion": "Módulo de frontend con HTML, CSS y JS"
                }
            ]
        }
    ],
    "message": "Grupos obtenidos correctamente"
}
```

#### **11. Obtener Módulos**
**Endpoint:** `GET /api/modulos`  
**Middleware:** isUserAuth

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "modulo_id": 1,
            "modulo_codigo": "DWF001",
            "modulo_nombre": "Desarrollo Web Frontend",
            "modulo_descripcion": "Módulo de frontend",
            "grupo_nombre": "Grupo DAW 2025",
            "profesor_name": "Juan",
            "profesor_surname": "Pérez"
        }
    ],
    "message": "Módulos obtenidos correctamente"
}
```

#### **12. Obtener Notas**
**Endpoint:** `GET /api/getNotas`  
**Middleware:** isUserAuth

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nota": 8.5,
            "comentario": "Excelente trabajo",
            "fecha_evaluacion": "2025-05-30T15:30:00.000000Z",
            "estudiante": {
                "id": 2,
                "name": "María",
                "surname": "García"
            },
            "evaluador": {
                "id": 3,
                "name": "Juan",
                "surname": "Pérez"
            }
        }
    ],
    "message": "Notas obtenidas correctamente"
}
```

#### **13. Obtener Rúbricas**
**Endpoint:** `GET /api/rubricas`  
**Middleware:** isUserAuth

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nombre": "Rúbrica Proyecto Final",
            "descripcion": "Criterios de evaluación para proyecto final",
            "criterios": [
                {
                    "id": 1,
                    "nombre": "Funcionalidad",
                    "descripcion": "La aplicación funciona correctamente",
                    "peso": 40
                },
                {
                    "id": 2,
                    "nombre": "Código",
                    "descripcion": "Calidad del código fuente",
                    "peso": 30
                }
            ]
        }
    ],
    "message": "Rúbricas obtenidas correctamente"
}
```

---

### **📌 RUTAS DE ADMINISTRADOR (isAdmin)**

#### **14. Ver Usuario Específico**
**Endpoint:** `GET /api/users/{id}`  
**Middleware:** isAdmin

**Response (200):**
```json
{
    "id": 1,
    "name": "Juan",
    "surname": "Pérez",
    "email": "juan@example.com",
    "dni": "12345678A",
    "rol": "profesor",
    "url": "https://mi-perfil.com",
    "created_at": "2025-05-30T10:00:00.000000Z",
    "updated_at": "2025-05-30T10:00:00.000000Z"
}
```

#### **15. Crear Usuario Completo con Grupo y Clase**
**Endpoint:** `POST /api/insertUsersAndGroupsAndClasses`  
**Middleware:** isAdmin

**Request:**
```json
{
    "name": "Carlos",
    "surname": "Martínez",
    "email": "carlos@example.com",
    "password": "password123",
    "dni": "87654321B",
    "rol": "user",
    "grupo": {
        "nombre": "Grupo SMR 2025"
    },
    "clase": {
        "nombre": "T2"
    }
}
```

**Response (201):**
```json
{
    "id": 5,
    "name": "Carlos",
    "surname": "Martínez",
    "email": "carlos@example.com",
    "dni": "87654321B",
    "rol": "user",
    "grupo": {
        "id": 3,
        "nombre": "Grupo SMR 2025",
        "user_id": 5
    },
    "clase": {
        "id": 4,
        "nombre": "T2",
        "user_id": 5
    }
}
```

#### **16. Actualizar Usuario con Grupo y Clase**
**Endpoint:** `PUT /api/updateUserAndGroupsAndClasses/{id}`  
**Middleware:** isAdmin

**Request:**
```json
{
    "name": "Carlos Antonio",
    "surname": "Martínez López",
    "email": "carlos.antonio@example.com",
    "password": "nueva_password123",
    "dni": "87654321B",
    "rol": "user",
    "grupo": {
        "nombre": "Grupo SMR 2025 Actualizado"
    },
    "clase": {
        "nombre": "T2 - Actualizado"
    }
}
```

**Response (200):**
```json
{
    "id": 5,
    "name": "Carlos Antonio",
    "surname": "Martínez López",
    "email": "carlos.antonio@example.com",
    "dni": "87654321B",
    "rol": "user",
    "grupo": {
        "id": 3,
        "nombre": "Grupo SMR 2025 Actualizado",
        "user_id": 5
    },
    "clase": {
        "id": 4,
        "nombre": "T2 - Actualizado",
        "user_id": 5
    }
}
```

#### **17. Eliminar Usuario con Grupo y Clase**
**Endpoint:** `DELETE /api/deleteUserAndGroupsAndClasses/{id}`  
**Middleware:** isAdmin

**Response (200):**
```json
{
    "message": "User and related data deleted successfully"
}
```

#### **18. Crear Grupo Completo**
**Endpoint:** `POST /api/grupos`  
**Middleware:** isAdmin

**Request:**
```json
{
    "nombre": "Grupo ASIR 2025",
    "modulos": [
        {
            "nombre": "Sistemas Operativos",
            "codigo": "SO001",
            "descripcion": "Administración de sistemas operativos",
            "usuario": {
                "id": 3
            }
        },
        {
            "nombre": "Redes",
            "codigo": "RED001",
            "descripcion": "Configuración de redes",
            "usuario": null
        }
    ]
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Grupo creado exitosamente",
    "data": {
        "id": 6,
        "nombre": "Grupo ASIR 2025",
        "modulos": [
            {
                "id": 8,
                "nombre": "Sistemas Operativos",
                "codigo": "SO001",
                "descripcion": "Administración de sistemas operativos",
                "usuario": {
                    "id": 3,
                    "nombre": "Juan",
                    "apellido": "Pérez",
                    "email": "juan@example.com",
                    "dni": "12345678A"
                }
            },
            {
                "id": 9,
                "nombre": "Redes",
                "codigo": "RED001",
                "descripcion": "Configuración de redes",
                "usuario": null
            }
        ]
    }
}
```

#### **19. Actualizar Grupo Completo**
**Endpoint:** `PUT /api/grupos/{id}`  
**Middleware:** isAdmin

**Request:**
```json
{
    "nombre": "Grupo ASIR 2025 - Actualizado",
    "modulos": [
        {
            "id": 8,
            "nombre": "Sistemas Operativos Avanzados",
            "codigo": "SO001",
            "descripcion": "Administración avanzada de sistemas",
            "usuario": {
                "id": 3
            }
        }
    ]
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Grupo actualizado exitosamente",
    "data": {
        "id": 6,
        "nombre": "Grupo ASIR 2025 - Actualizado",
        "modulos": [
            {
                "id": 8,
                "nombre": "Sistemas Operativos Avanzados",
                "codigo": "SO001",
                "descripcion": "Administración avanzada de sistemas",
                "usuario": {
                    "id": 3,
                    "nombre": "Juan",
                    "apellido": "Pérez",
                    "email": "juan@example.com",
                    "dni": "12345678A"
                }
            }
        ]
    }
}
```

#### **20. Eliminar Grupo Completo**
**Endpoint:** `DELETE /api/grupos/{id}`  
**Middleware:** isAdmin

**Response (200):**
```json
{
    "success": true,
    "message": "Grupo 'Grupo ASIR 2025 - Actualizado' eliminado exitosamente junto con todos sus módulos y relaciones"
}
```

#### **21. Crear Módulo**
**Endpoint:** `POST /api/modulos`  
**Middleware:** isAdmin

**Request:**
```json
{
    "codigo": "MOD003",
    "nombre": "Base de Datos",
    "descripcion": "Diseño y gestión de bases de datos relacionales",
    "grupo_id": 1,
    "user_id": 3
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "modulo_id": 10,
        "modulo_codigo": "MOD003",
        "modulo_nombre": "Base de Datos",
        "modulo_descripcion": "Diseño y gestión de bases de datos relacionales",
        "grupo_nombre": "Grupo DAW 2025",
        "profesor_name": "Juan",
        "profesor_surname": "Pérez"
    },
    "message": "Módulo creado correctamente"
}
```

#### **22. Actualizar Módulo**
**Endpoint:** `PUT /api/modulos/{id}`  
**Middleware:** isAdmin

**Request:**
```json
{
    "codigo": "MOD003-UPD",
    "nombre": "Base de Datos Avanzadas",
    "descripcion": "Diseño avanzado y gestión de bases de datos",
    "grupo_id": 1,
    "user_id": 3
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "modulo_id": 10,
        "modulo_codigo": "MOD003-UPD",
        "modulo_nombre": "Base de Datos Avanzadas",
        "modulo_descripcion": "Diseño avanzado y gestión de bases de datos",
        "grupo_nombre": "Grupo DAW 2025",
        "profesor_name": "Juan",
        "profesor_surname": "Pérez"
    },
    "message": "Módulo actualizado correctamente"
}
```

#### **23. Eliminar Módulo**
**Endpoint:** `DELETE /api/modulos/{id}`  
**Middleware:** isAdmin

**Response (200):**
```json
{
    "success": true,
    "message": "Módulo eliminado correctamente"
}
```

#### **24. Crear Nota**
**Endpoint:** `POST /api/notas`  
**Middleware:** isAdmin

**Request:**
```json
{
    "nota_final": 9.2,
    "comentario": "Excelente proyecto, muy bien documentado",
    "user_id": 10,
    "entrega_id": 12,
    "rubrica_id":10
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "notas_id": 14,
        "nota_final": 9.2,
        "notas_comentario": "Excelente proyecto, muy bien documentado",
        "entregas_archivo": "fkaopfkafkafakp",
        "alumno_name": "Sulaiman",
        "alumno_surname": "El Taha Santos",
        "evaluador_name": "sulaiman",
        "evaluador_surname": "admin",
        "rubrica_documento": null
    },
    "message": "Nota creada correctamente"
}
```

#### **25. Actualizar Nota (Admin)**
**Endpoint:** `PUT /api/notas/{id}`  
**Middleware:** isAdmin

**Request:**
```json
{
    "nota_final": 10,
    "comentario": "Excelente proyecto, muy bien documentado",
    "user_id": 10,
    "entrega_id": 12,
    "rubrica_id":10
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "notas_id": 14,
        "nota_final": 10,
        "notas_comentario": "Excelente proyecto, muy bien documentado",
        "entregas_archivo": "fkaopfkafkafakp",
        "alumno_name": "Sulaiman",
        "alumno_surname": "El Taha Santos",
        "evaluador_name": "sulaiman",
        "evaluador_surname": "admin",
        "rubrica_documento": null
    },
    "message": "Nota actualizada correctamente"
}
```

#### **26. Eliminar Nota (Admin)**
**Endpoint:** `DELETE /api/notas/{id}`  
**Middleware:** isAdmin

**Response (200):**
```json
{
    "success": true,
    "data": {
        "notas_id": 14,
        "nota_final": "10.00",
        "comentario": "Excelente proyecto, muy bien documentado"
    },
    "message": "Nota eliminada correctamente"
}
```

#### **27. Actualizar Entrega**
**Endpoint:** `PUT /api/entregas/{id}`  
**Middleware:** isAdmin

**Request:**
```json
{
    "descripcion": "Entrega actualizada del proyecto",
    "fecha_entrega": "2025-06-25T23:59:59",
    "archivo_url": "https://ejemplo.com/archivo-actualizado.pdf",
    "nota": 8.8
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "entrega_id": 12,
        "entrega_practica_id": 5,
        "entrega_user_id": 6,
        "fecha_entrega": "2025-06-25T23:59:59",
        "archivo": "fkaopfkafkafakp",
        "alumno_name": "Sulaiman",
        "alumno_surname": "El Taha Santos",
        "alumno_email": "sulat3821@gmail.com",
        "alumno_dni": "54314220L",
        "practica_identificador": "RED001",
        "practica_titulo": "Configuración de Redes",
        "practica_nombre": "Práctica Redes",
        "practica_fecha_entrega": "2025-07-05",
        "profesor_name": "Juan Carlos",
        "profesor_surname": "Pérez García",
        "rubrica_nombre": "Rúbrica Frontend",
        "rubrica_documento": null,
        "nota_final": "9.20",
        "nota_comentario": "Excelente proyecto, muy bien documentado",
        "evaluador_name": "Sulaiman",
        "evaluador_surname": "El Taha Santos"
    },
    "message": "Entrega actualizada correctamente"
}
```

#### **28. Eliminar Entrega (Admin)**
**Endpoint:** `DELETE /api/entregas/{id}`  
**Middleware:** isAdmin

**Response (200):**
```json
{
    "success": true,
    "data": {
        "entrega_id": 12,
        "archivo": "fkaopfkafkafakp",
        "alumno_name": "Sulaiman",
        "alumno_surname": "El Taha Santos",
        "practica_titulo": "Configuración de Redes",
        "practica_identificador": "RED001"
    },
    "message": "Entrega eliminada correctamente"
}
```

#### **29. Crear Rúbrica**
**Endpoint:** `POST /api/rubricas`  
**Middleware:** isAdmin

**Request:**
```json
{
    "nombre": "Rúbrica Frontend",
    "documento":"rubricaFrontend.pdf",
    "descripcion": "Evaluación de proyectos frontend",
    "practica_id": 5,
    "evaluador_id":11,

    "criterios": [
        {
            "nombre": "Responsive Design",
            "descripcion": "Adaptabilidad a diferentes dispositivos",
            "puntuacion_maxima": 25
        },
        {
            "nombre": "Interactividad",
            "descripcion": "Elementos interactivos y UX",
            "puntuacion_maxima": 30
        }
    ]
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Rúbrica creada exitosamente",
    "data": {
        "rubrica": {
            "id": 12,
            "nombre": "Rúbrica Frontend",
            "documento": "rubricaFrontend.pdf",
            "created_at": "2025-05-30T16:38:52.000000Z",
            "updated_at": "2025-05-30T16:38:52.000000Z"
        },
        "practica_asignada": {
            "id": 5,
            "identificador": "RED001",
            "titulo": "Configuración de Redes",
            "descripcion": "Configurar una red empresarial",
            "nombre_practica": "Práctica Redes",
            "fecha_entrega": "2025-07-05",
            "enlace_practica": "https://classroom.google.com/practica5",
            "profesor_id": 11
        },
        "profesor_practica": {
            "id": 11,
            "nombre": "Juan Carlos",
            "apellido": "Pérez García",
            "email": "juan.carlos@example.com",
            "rol": "profesor"
        },
        "evaluador_asignado": {
            "id": 11,
            "nombre": "Juan Carlos",
            "apellido": "Pérez García",
            "email": "juan.carlos@example.com",
            "rol": "profesor"
        },
        "criterios": [
            {
                "id": 13,
                "nombre": "Responsive Design",
                "puntuacion_maxima": 25,
                "descripcion": "Adaptabilidad a diferentes dispositivos"
            },
            {
                "id": 14,
                "nombre": "Interactividad",
                "puntuacion_maxima": 30,
                "descripcion": "Elementos interactivos y UX"
            }
        ]
    }
}
```

#### **30. Actualizar Rúbrica (Admin)**
**Endpoint:** `PUT /api/rubricas/{id}`  
**Middleware:** isAdmin

**Request:**
```json
{
    "nombre": "Rúbrica Frontend",
    "documento":"rubricaFrontend.pdf",
    "descripcion": "Evaluación de proyectos frontend",
    "practica_id": 5,
    "evaluador_id":11,

    "criterios": [
        {
            "nombre": "Responsive Design",
            "descripcion": "Adaptabilidad a diferentes dispositivos",
            "puntuacion_maxima": 25
        },
        {
            "nombre": "Interactividad",
            "descripcion": "Elementos interactivos y UX",
            "puntuacion_maxima": 30
        }
    ]
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Rúbrica actualizada exitosamente",
    "data": {
        "rubrica": {
            "id": 12,
            "nombre": "Rúbrica Frontendddd",
            "documento": "rubricaFrontendddd.pdf",
            "created_at": "2025-05-30T16:38:52.000000Z",
            "updated_at": "2025-05-30T16:40:34.000000Z"
        },
        "practica_asignada": {
            "id": 5,
            "identificador": "RED001",
            "titulo": "Configuración de Redes",
            "descripcion": "Configurar una red empresarial",
            "nombre_practica": "Práctica Redes",
            "fecha_entrega": "2025-07-05",
            "enlace_practica": "https://classroom.google.com/practica5",
            "profesor_id": 11
        },
        "profesor_practica": {
            "id": 11,
            "nombre": "Juan Carlos",
            "apellido": "Pérez García",
            "email": "juan.carlos@example.com",
            "rol": "profesor"
        },
        "evaluador_asignado": {
            "id": 11,
            "nombre": "Juan Carlos",
            "apellido": "Pérez García",
            "email": "juan.carlos@example.com",
            "rol": "profesor"
        },
        "criterios": [
            {
                "id": 15,
                "nombre": "Responsive Design",
                "puntuacion_maxima": 25,
                "descripcion": "Adaptabilidad a diferentes dispositivos"
            },
            {
                "id": 16,
                "nombre": "Interactividad",
                "puntuacion_maxima": 30,
                "descripcion": "Elementos interactivos y UX"
            }
        ]
    }
}
```

#### **31. Eliminar Rúbrica (Admin)**
**Endpoint:** `DELETE /api/rubricas/{id}`  
**Middleware:** isAdmin

**Response (200):**
```json
{
    "success": true,
    "message": "Rúbrica eliminada exitosamente",
    "data": {
        "rubrica": {
            "id": 12,
            "nombre": "Rúbrica Frontendddd",
            "documento": "rubricaFrontendddd.pdf",
            "created_at": "2025-05-30T16:38:52.000000Z",
            "updated_at": "2025-05-30T16:40:34.000000Z"
        },
        "practica_asignada": {
            "id": 5,
            "identificador": "RED001",
            "titulo": "Configuración de Redes",
            "descripcion": "Configurar una red empresarial",
            "nombre_practica": "Práctica Redes",
            "fecha_entrega": "2025-07-05",
            "enlace_practica": "https://classroom.google.com/practica5",
            "profesor_id": 11
        },
        "profesor_practica": {
            "id": 11,
            "nombre": "Juan Carlos",
            "apellido": "Pérez García",
            "email": "juan.carlos@example.com",
            "rol": "profesor"
        },
        "evaluador_asignado": {
            "id": 11,
            "nombre": "Juan Carlos",
            "apellido": "Pérez García",
            "email": "juan.carlos@example.com",
            "rol": "profesor"
        },
        "criterios_eliminados": [
            {
                "id": 15,
                "nombre": "Responsive Design",
                "puntuacion_maxima": 25,
                "descripcion": "Adaptabilidad a diferentes dispositivos"
            },
            {
                "id": 16,
                "nombre": "Interactividad",
                "puntuacion_maxima": 30,
                "descripcion": "Elementos interactivos y UX"
            }
        ],
        "total_criterios_eliminados": 2,
        "notas_eliminadas": 0
    }
}
```

#### **32. Crear Enunciado**
**Endpoint:** `POST /api/enunciados`  
**Middleware:** isAdmin

**Request:**
```json
{
    "descripcion": "Crear una aplicación móvil con React Native",
    "fecha_limite": "2025-08-15T23:59:59",
    "practica_id": 3,
    "modulo_id": 13,
    "rubrica_id": 5,
    "grupo_id": 13
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Enunciado creado exitosamente",
    "data": {
        "enunciado": {
            "id": 7,
            "descripcion": "Crear una aplicación móvil con React Native",
            "fecha_limite": "2025-08-15T23:59:59.000000Z",
            "created_at": "2025-05-30T16:44:40.000000Z",
            "updated_at": "2025-05-30T16:44:40.000000Z"
        },
        "practica": {
            "id": 3,
            "titulo": "App Mobile Android",
            "nombre": "Práctica Mobile",
            "identificador": "MOB001"
        },
        "modulo": {
            "id": 13,
            "nombre": "Desarrollo Backend"
        },
        "profesor": null,
        "rubrica": {
            "id": 5,
            "nombre": "Rúbrica Redes"
        },
        "grupo": {
            "id": 13,
            "nombre": "Grupo de Prueba Profesor"
        }
    }
}
```

#### **33. Actualizar Enunciado (Admin)**
**Endpoint:** `PUT /api/enunciados/{id}`  
**Middleware:** isAdmin

**Request:**
```json
{
    "descripcion": "Crear una aplicación móvil con React Nativeeeeed",
    "fecha_limite": "2025-08-15T23:59:59",
    "practica_id": 3,
    "modulo_id": 13,
    "rubrica_id": 5,
    "grupo_id": 13
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Enunciado actualizado exitosamente",
    "data": {
        "enunciado": {
            "id": 7,
            "descripcion": "Crear una aplicación móvil con React Nativeeeeed",
            "fecha_limite": "2025-08-15T23:59:59.000000Z",
            "created_at": "2025-05-30T16:44:40.000000Z",
            "updated_at": "2025-05-30T16:47:33.000000Z"
        },
        "practica": {
            "id": 3,
            "titulo": "App Mobile Android",
            "nombre": "Práctica Mobile",
            "identificador": "MOB001"
        },
        "modulo": {
            "id": 13,
            "nombre": "Desarrollo Backend"
        },
        "profesor": null,
        "rubrica": {
            "id": 5,
            "nombre": "Rúbrica Redes"
        },
        "grupo": {
            "id": 13,
            "nombre": "Grupo de Prueba Profesor"
        }
    }
}
```

#### **34. Eliminar Enunciado (Admin)**
**Endpoint:** `DELETE /api/enunciados/{id}`  
**Middleware:** isAdmin

**Response (200):**
```json
{
    "success": true,
    "message": "Enunciado eliminado correctamente"
}
```

---

### **📌 RUTAS DE PROFESOR (isProfesor)**

#### **35. Obtener Grupos del Profesor**
**Endpoint:** `GET /api/profesor/grupos`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nombre": "Grupo DAW 2025",
            "user_id": 3,
            "modulos": [
                {
                    "id": 1,
                    "nombre": "Desarrollo Web Frontend",
                    "codigo": "DWF001"
                }
            ]
        }
    ],
    "message": "Grupos del profesor obtenidos correctamente"
}
```

#### **36. Crear Grupo (Profesor)**
**Endpoint:** `POST /api/profesor/grupos`  
**Middleware:** isProfesor

**Request:**
```json
{
    "nombre": "Grupo DAM 2025",
    "modulos": [
        {
            "nombre": "Programación",
            "codigo": "PROG001",
            "descripcion": "Fundamentos de programación",
            "usuario": {
                "id": 5
            }
        }
    ]
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Grupo creado exitosamente",
    "data": {
        "id": 8,
        "nombre": "Grupo DAM 2025",
        "modulos": [
            {
                "id": 12,
                "nombre": "Programación",
                "codigo": "PROG001",
                "descripcion": "Fundamentos de programación",
                "usuario": {
                    "id": 5,
                    "nombre": "María",
                    "apellido": "García",
                    "email": "maria@example.com",
                    "dni": "98765432C"
                }
            }
        ]
    }
}
```

#### **37. Actualizar Grupo (Profesor)**
**Endpoint:** `PUT /api/profesor/grupos/{id}`  
**Middleware:** isProfesor

**Request:**
```json
{
    "nombre": "Grupo DAM 2025 - Actualizadoooo",
    "modulos": [
        {
            "nombre": "Programación Avanzada",
            "codigo": "PROG001",
            "descripcion": "Programación orientada a objetos",
            "usuario": {
                "id": 5
            }
        }
    ]
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Grupo actualizado exitosamente",
    "data": {
        "id": 8,
        "nombre": "Grupo DAM 2025 - Actualizado",
        "modulos": [
            {
                "id": 12,
                "nombre": "Programación Avanzada",
                "codigo": "PROG001",
                "descripcion": "Programación orientada a objetos",
                "usuario": {
                    "id": 5,
                    "nombre": "María",
                    "apellido": "García",
                    "email": "maria@example.com",
                    "dni": "98765432C"
                }
            }
        ]
    }
}
```

#### **38. Eliminar Grupo (Profesor)**
**Endpoint:** `DELETE /api/profesor/grupos/{id}`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "message": "Grupo 'Grupo DAM 2025 - Actualizado' eliminado exitosamente junto con todos sus módulos y relaciones"
}
```

#### **39. Obtener Módulos del Profesor**
**Endpoint:** `GET /api/profesor/modulos`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "modulo_id": 1,
            "modulo_codigo": "DWF001",
            "modulo_nombre": "Desarrollo Web Frontend",
            "modulo_descripcion": "Módulo de frontend",
            "grupo_nombre": "Grupo DAW 2025",
            "profesor_name": "Juan",
            "profesor_surname": "Pérez"
        }
    ],
    "message": "Módulos obtenidos correctamente"
}
```

#### **40. Crear Módulo (Profesor)**
**Endpoint:** `POST /api/profesor/modulos`  
**Middleware:** isProfesor

**Request:**
```json
{
    "codigo": "MOD004",
    "nombre": "Desarrollo Mobile",
    "descripcion": "Desarrollo de aplicaciones móviles",
    "grupo_id": 10
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "modulo_id": 13,
        "modulo_codigo": "MOD004",
        "modulo_nombre": "Desarrollo Mobile",
        "modulo_descripcion": "Desarrollo de aplicaciones móviles",
        "grupo_nombre": "Grupo DAW 2025",
        "profesor_name": "Juan",
        "profesor_surname": "Pérez"
    },
    "message": "Módulo creado correctamente"
}
```

#### **41. Actualizar Módulo (Profesor)**
**Endpoint:** `PUT /api/profesor/modulos/{id}`  
**Middleware:** isProfesor

**Request:**
```json
{
    "codigo": "MOD004-UPD",
    "nombre": "Desarrollo Mobile Avanzado",
    "descripcion": "Desarrollo avanzado de aplicaciones móviles",
    "grupo_id": 10
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "modulo_id": 13,
        "modulo_codigo": "MOD004-UPD",
        "modulo_nombre": "Desarrollo Mobile Avanzado",
        "modulo_descripcion": "Desarrollo avanzado de aplicaciones móviles",
        "grupo_nombre": "Grupo DAW 2025",
        "profesor_name": "Juan",
        "profesor_surname": "Pérez"
    },
    "message": "Módulo actualizado correctamente"
}
```

#### **42. Eliminar Módulo (Profesor)**
**Endpoint:** `DELETE /api/profesor/modulos/{id}`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "message": "Módulo eliminado correctamente"
}
```

#### **43. Obtener Clases del Profesor**
**Endpoint:** `GET /api/profesor/clases`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "message": "Clases del profesor obtenidas exitosamente",
    "data": [
        {
            "clase": {
                "id": 1,
                "nombre": "T1",
                "created_at": "2025-05-30T08:00:00.000000Z",
                "updated_at": "2025-05-30T08:00:00.000000Z"
            },
            "profesor": {
                "id": 3,
                "name": "Juan",
                "surname": "Pérez",
                "email": "juan@example.com",
                "rol": "profesor"
            }
        }
    ],
    "total": 1,
    "profesor": {
        "id": 3,
        "name": "Juan",
        "surname": "Pérez"
    }
}
```

#### **44. Crear Clase (Profesor)**
**Endpoint:** `POST /api/profesor/clases`  
**Middleware:** isProfesor

**Request:**
```json
{
    "nombre": "T3",
    "grupo": {
        "nombre": "Grupo Tarde 2025"
    }
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "clase": {
            "id": 6,
            "nombre": "T3",
            "created_at": "2025-05-30T17:00:00.000000Z",
            "updated_at": "2025-05-30T17:00:00.000000Z"
        },
        "profesor": {
            "id": 3,
            "name": "Juan",
            "surname": "Pérez",
            "email": "juan@example.com",
            "rol": "profesor"
        },
        "grupo": {
            "id": 9,
            "nombre": "Grupo Tarde 2025",
            "created_at": "2025-05-30T17:00:00.000000Z",
            "updated_at": "2025-05-30T17:00:00.000000Z"
        }
    },
    "message": "Clase y grupo creados correctamente"
}
```

#### **45. Actualizar Clase (Profesor)**
**Endpoint:** `PUT /api/profesor/clases/{id}`  
**Middleware:** isProfesor

**Request:**
```json
{
    "nombre": "T3 - Actualizada",
    "grupo": {
        "id": 9,
        "nombre": "Grupo Tarde 2025 - Actualizado"
    }
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "clase": {
            "id": 6,
            "nombre": "T3 - Actualizada",
            "created_at": "2025-05-30T17:00:00.000000Z",
            "updated_at": "2025-05-30T17:30:00.000000Z"
        },
        "profesor": {
            "id": 3,
            "name": "Juan",
            "surname": "Pérez",
            "email": "juan@example.com",
            "rol": "profesor"
        },
        "grupo": {
            "id": 9,
            "nombre": "Grupo Tarde 2025 - Actualizado",
            "created_at": "2025-05-30T17:00:00.000000Z",
            "updated_at": "2025-05-30T17:30:00.000000Z"
        }
    },
    "message": "Clase actualizada correctamente y grupo actualizado"
}
```

#### **46. Eliminar Clase (Profesor)**
**Endpoint:** `DELETE /api/profesor/clases/{id}`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "message": "Clase eliminada correctamente junto con 2 grupo(s) relacionado(s)",
    "grupos_eliminados": [
        "Grupo Tarde 2025 - Actualizado",
        "Grupo DAW 2025"
    ]
}
```

#### **47. Obtener Entregas del Profesor**
**Endpoint:** `GET /api/profesor/entregas`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "descripcion": "Entrega de proyecto Laravel",
            "fecha_entrega": "2025-06-15T10:30:00.000000Z",
            "nota": 8.5,
            "estudiante": {
                "id": 2,
                "name": "María",
                "surname": "García"
            },
            "enunciado": {
                "id": 1,
                "descripcion": "Crear API REST"
            }
        }
    ],
    "message": "Entregas del profesor obtenidas correctamente"
}
```

#### **48. Obtener Notas del Profesor**
**Endpoint:** `GET /api/profesor/notas`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nota": 8.5,
            "comentario": "Buen trabajo",
            "fecha_evaluacion": "2025-05-30T15:30:00.000000Z",
            "estudiante": {
                "id": 2,
                "name": "María",
                "surname": "García"
            }
        }
    ],
    "message": "Notas del profesor obtenidas correctamente"
}
```

#### **49. Crear Nota (Profesor)**
**Endpoint:** `POST /api/profesor/notas`  
**Middleware:** isProfesor

**Request:**
```json
{
    "nota_final": 9.2,
    "comentario": "Excelente proyecto, muy bien documentado",
    "user_id": 6,
    "entrega_id": 12,
    "rubrica_id":10
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "nota_id": 13,
        "nota_final": 9.2,
        "comentario": "Excelente proyecto, muy bien documentado",
        "alumno_name": "Sulaiman",
        "alumno_surname": "El Taha Santos",
        "evaluador_name": "Sulaiman",
        "evaluador_surname": "El Taha Santos",
        "rubrica_nombre": "Rúbrica Frontend"
    },
    "message": "Nota creada correctamente"
}
```

#### **50. Obtener Rúbricas del Profesor**
**Endpoint:** `GET /api/profesor/rubricas`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nombre": "Rúbrica Proyecto Backend",
            "descripcion": "Evaluación de proyectos backend",
            "profesor": {
                "id": 3,
                "name": "Juan",
                "surname": "Pérez"
            },
            "criterios": [
                {
                    "id": 1,
                    "nombre": "API Design",
                    "descripcion": "Diseño de la API REST",
                    "peso": 30
                }
            ]
        }
    ],
    "message": "Rúbricas del profesor obtenidas correctamente"
}
```

#### **51. Crear Rúbrica (Profesor)**
**Endpoint:** `POST /api/profesor/rubricas`  
**Middleware:** isProfesor

**Request:**
```json
{
    "nombre": "Rúbrica Frontend",
    "documento":"rubricaFrontend.pdf",
    "descripcion": "Evaluación de proyectos frontend",
    "practica_id": 5,
    "evaluador_id":11,

    "criterios": [
        {
            "nombre": "Responsive Design",
            "descripcion": "Adaptabilidad a diferentes dispositivos",
            "puntuacion_maxima": 25
        },
        {
            "nombre": "Interactividad",
            "descripcion": "Elementos interactivos y UX",
            "puntuacion_maxima": 30
        }
    ]
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "id": 11,
        "nombre": "Rúbrica Frontend",
        "documento": "rubricaFrontend.pdf",
        "practica_titulo": "Configuración de Redes",
        "profesor_name": "Juan Carlos",
        "profesor_surname": "Pérez García",
        "evaluador_name": "Juan Carlos",
        "evaluador_surname": "Pérez García"
    },
    "message": "Rúbrica creada correctamente"
}
```


#### **52. Actualizar Rúbrica (Profesor)**
**Endpoint:** `PUT /api/profesor/rubricas/{id}`  
**Middleware:** isProfesor

**Request:**
```json
{
    "nombre": "Rúbrica Frontenddddddd",
    "documento":"rubricaFrontend.pdf",
    "descripcion": "Evaluación de proyectos frontend",
    "practica_id": 5,
    "evaluador_id":11,

    "criterios": [
        {
            "nombre": "Responsive Design",
            "descripcion": "Adaptabilidad a diferentes dispositivos",
            "puntuacion_maxima": 25
        },
        {
            "nombre": "Interactividad",
            "descripcion": "Elementos interactivos y UX",
            "puntuacion_maxima": 30
        }
    ]
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 11,
        "nombre": "Rúbrica Frontenddddddd",
        "documento": "rubricaFrontend.pdf",
        "practica_titulo": "Configuración de Redes",
        "profesor_name": "Juan Carlos",
        "profesor_surname": "Pérez García",
        "evaluador_name": "Juan Carlos",
        "evaluador_surname": "Pérez García"
    },
    "message": "Rúbrica actualizada correctamente"
}
```

#### **53. Eliminar Rúbrica (Profesor)**
**Endpoint:** `DELETE /api/profesor/rubricas/{id}`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "message": "Rúbrica eliminada correctamente"
}
```

#### **54. Obtener Enunciados del Profesor**
**Endpoint:** `GET /api/profesor/enunciados`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "descripcion": "Desarrollar una SPA con Vue.js",
            "fecha_limite": "2025-07-01T23:59:59.000000Z",
            "practica": {
                "id": 1,
                "nombre": "Proyecto Frontend"
            },
            "modulo": {
                "id": 1,
                "nombre": "Desarrollo Web Frontend",
                "codigo": "DWF001"
            },
            "grupo": {
                "id": 1,
                "nombre": "Grupo DAW 2025"
            }
        }
    ],
    "message": "Enunciados del profesor obtenidos correctamente"
}
```

#### **55. Crear Enunciado (Profesor)**
**Endpoint:** `POST /api/profesor/enunciados`  
**Middleware:** isProfesor

**Request:**
```json
{
    "descripcion": "Crear una aplicación móvil con React Native",
    "fecha_limite": "2025-08-15T23:59:59",
    "practica_id": 3,
    "modulo_id": 13,
    "rubrica_id": 5,
    "grupo_id": 13
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Enunciado creado exitosamente",
    "data": {
        "enunciado": {
            "id": 6,
            "descripcion": "Crear una aplicación móvil con React Native",
            "fecha_limite": "2025-08-15T23:59:59.000000Z",
            "created_at": "2025-05-30T10:38:34.000000Z",
            "updated_at": "2025-05-30T10:38:34.000000Z"
        },
        "practica": {
            "id": 3,
            "titulo": "App Mobile Android",
            "nombre": "Práctica Mobile",
            "identificador": "MOB001"
        },
        "modulo": {
            "id": 13,
            "nombre": "Desarrollo Backend"
        },
        "profesor": {
            "id": 11,
            "name": "Juan Carlos",
            "rol": "profesor"
        },
        "rubrica": {
            "id": 5,
            "nombre": "Rúbrica Redes"
        },
        "grupo": {
            "id": 13,
            "nombre": "Grupo de Prueba Profesor"
        }
    }
}
```

#### **56. Actualizar Enunciado (Profesor)**
**Endpoint:** `PUT /api/profesor/enunciados/{id}`  
**Middleware:** isProfesor

**Request:**
```json
{
    "descripcion": "Crear una aplicación móvil con React Nativeeeeeee",
    "fecha_limite": "2025-08-15T23:59:59",
    "practica_id": 3,
    "modulo_id": 13,
    "rubrica_id": 5,
    "grupo_id": 13
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Enunciado actualizado exitosamente",
    "data": {
        "enunciado": {
            "id": 6,
            "descripcion": "Crear una aplicación móvil con React Nativeeeeeee",
            "fecha_limite": "2025-08-15T23:59:59.000000Z",
            "created_at": "2025-05-30T10:38:34.000000Z",
            "updated_at": "2025-05-30T11:06:45.000000Z"
        },
        "practica": {
            "id": 3,
            "titulo": "App Mobile Android",
            "nombre": "Práctica Mobile",
            "identificador": "MOB001"
        },
        "modulo": {
            "id": 13,
            "nombre": "Desarrollo Backend"
        },
        "profesor": {
            "id": 11,
            "name": "Juan Carlos",
            "rol": "profesor"
        },
        "rubrica": {
            "id": 5,
            "nombre": "Rúbrica Redes"
        },
        "grupo": {
            "id": 13,
            "nombre": "Grupo de Prueba Profesor"
        }
    }
}
```

#### **57. Eliminar Enunciado (Profesor)**
**Endpoint:** `DELETE /api/profesor/enunciados/{id}`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "message": "Enunciado eliminado correctamente"
}
```

#### **58. Actualizar Entrega (Profesor)**
**Endpoint:** `PUT /api/profesor/entregas/{id}`  
**Middleware:** isProfesor

**Request:**
```json
{
    "descripcion": "Entrega actualizada por el profesor",
    "fecha_entrega": "2025-07-01T23:59:59",
    "archivo_url": "https://nuevo-enlace.com/archivo.pdf",
    "nota": 9.0,
    "practica_id":5,
    "user_id":6
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "entrega_id": 11,
        "archivo": "fkaopfkafkafakp",
        "fecha_entrega": "2025-07-01T23:59:59",
        "practica_titulo": "Configuración de Redes",
        "profesor_name": "Juan Carlos",
        "profesor_surname": "Pérez García",
        "alumno_name": "Sulaiman",
        "alumno_surname": "El Taha Santos"
    },
    "message": "Entrega actualizada correctamente"
}
```
#### **59. Eliminar Entrega (Profesor)**
**Endpoint:** `DELETE /api/profesor/entregas/{id}`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "message": "Entrega eliminada correctamente"
}
```

#### **60. Actualizar Nota (Profesor)**
**Endpoint:** `PUT /api/profesor/notas/{id}`  
**Middleware:** isProfesor

**Request:**
```json
{
    "nota_final": 10,
    "comentario": "Excelente proyecto, muy bien documentado",
    "user_id": 6,
    "entrega_id": 12,
    "rubrica_id":10
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "nota_id": 13,
        "nota_final": 10,
        "comentario": "Excelente proyecto, muy bien documentado",
        "alumno_name": "Sulaiman",
        "alumno_surname": "El Taha Santos",
        "evaluador_name": "Sulaiman",
        "evaluador_surname": "El Taha Santos",
        "rubrica_nombre": "Rúbrica Frontend"
    },
    "message": "Nota actualizada correctamente"
}
```

#### **61. Eliminar Nota (Profesor)**
**Endpoint:** `DELETE /api/profesor/notas/{id}`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "message": "Nota eliminada correctamente"
}
```


#### **62. Obtener Información Completa del Profesor**
**Endpoint:** `GET /api/profesor/usuariosGruposClases`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "message": "Información del profesor obtenida exitosamente",
    "data": {
        "usuario": {
            "id": 3,
            "name": "Juan",
            "surname": "Pérez",
            "email": "juan@example.com",
            "dni": "12345678A",
            "rol": "profesor",
            "url": "https://mi-perfil.com",
            "created_at": "2025-05-30T10:00:00.000000Z",
            "updated_at": "2025-05-30T10:00:00.000000Z"
        },
        "grupo": {
            "id": 1,
            "nombre": "Grupo DAW 2025",
            "created_at": "2025-05-30T08:00:00.000000Z",
            "updated_at": "2025-05-30T08:00:00.000000Z"
        },
        "clase": {
            "id": 1,
            "nombre": "T1",
            "created_at": "2025-05-30T08:00:00.000000Z",
            "updated_at": "2025-05-30T08:00:00.000000Z"
        }
    }
}
```

---

## 🚫 **Códigos de Error Comunes**

### **Error de Autenticación (401)**
```json
{
    "message": "Unauthorized invalid token"
}
```

### **Error de Autorización (403)**
```json
{
    "success": false,
    "message": "Acceso denegado. Solo profesores pueden acceder a este recurso."
}
```

### **Error de Validación (422)**
```json
{
    "success": false,
    "message": "Error de validación",
    "errors": {
        "email": ["El campo email es obligatorio."],
        "password": ["El campo password debe tener al menos 8 caracteres."]
    }
}
```

### **Error de Recurso No Encontrado (404)**
```json
{
    "success": false,
    "message": "Recurso no encontrado o no tienes permisos para acceder a él"
}
```

### **Error del Servidor (500)**
```json
{
    "success": false,
    "message": "Error interno del servidor: [descripción específica del error]"
}
```

---

## 📊 **Resumen de Endpoints**

| **Categoría** | **Cantidad** | **Middleware** |
|---------------|--------------|----------------|
| Públicos | 2 | Ninguno |
| Autenticados | 12 | isUserAuth |
| Administrador | 21 | isAdmin |
| Profesor | 27 | isProfesor |
| **TOTAL** | **62** | **3 Middleware** |

---

## 🔧 **Configuración de Producción**

### **URLs Base**
- **Producción:** `https://gestionacademicauf4backend-production.up.railway.app`
- **Desarrollo:** `http://127.0.0.1:8000`

### **Para usar las APIs en producción:**
Simplemente reemplaza la URL base y añade `/api/` seguido del endpoint:

**Ejemplo:**
```
https://gestionacademicauf4backend-production.up.railway.app/api/registro
https://gestionacademicauf4backend-production.up.railway.app/api/inicioSesion
https://gestionacademicauf4backend-production.up.railway.app/api/me
```

### **Headers Requeridos**
```http
Content-Type: application/json
Authorization: Bearer {jwt_token}  // Solo para rutas protegidas
Accept: application/json
```

---

## 📝 **Notas Importantes**

1. **Tokens JWT:** Los tokens tienen expiración. Implementar renovación automática en el frontend.

2. **Validación de Roles:** El sistema valida estrictamente los roles antes de permitir acceso a recursos.

3. **Relaciones:** Muchos endpoints incluyen relaciones cargadas para evitar múltiples consultas.

4. **CORS:** Configurado para permitir requests desde diferentes dominios.

---

## 📞 **Soporte y Contacto**

Para dudas, reportes de bugs o solicitudes de nuevas funcionalidades, contacta con el equipo de desarrollo.
email: sulat3821@gmail.com

**Fecha de última actualización:** Mayo 30, 2025  
**Versión de la API:** v1.0  
**Estado:** Producción
