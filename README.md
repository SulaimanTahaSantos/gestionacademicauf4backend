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
    "nota": 9.0,
    "comentario": "Excelente trabajo, muy completo",
    "estudiante_id": 2,
    "evaluador_id": 3,
    "entrega_id": 1
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "id": 5,
        "nota": 9.0,
        "comentario": "Excelente trabajo, muy completo",
        "fecha_evaluacion": "2025-05-30T16:00:00.000000Z",
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
    },
    "message": "Nota creada correctamente"
}
```

#### **25. Actualizar Entrega**
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
        "id": 1,
        "descripcion": "Entrega actualizada del proyecto",
        "fecha_entrega": "2025-06-25T23:59:59.000000Z",
        "archivo_url": "https://ejemplo.com/archivo-actualizado.pdf",
        "nota": 8.8,
        "updated_at": "2025-05-30T16:30:00.000000Z"
    },
    "message": "Entrega actualizada correctamente"
}
```

#### **26. Crear Rúbrica**
**Endpoint:** `POST /api/rubricas`  
**Middleware:** isAdmin

**Request:**
```json
{
    "nombre": "Rúbrica Proyecto Web",
    "descripcion": "Evaluación de proyectos web completos",
    "criterios": [
        {
            "nombre": "Diseño UI/UX",
            "descripcion": "Calidad del diseño de interfaz",
            "peso": 25
        },
        {
            "nombre": "Funcionalidad Backend",
            "descripcion": "Correcto funcionamiento del backend",
            "peso": 35
        }
    ]
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "id": 3,
        "nombre": "Rúbrica Proyecto Web",
        "descripcion": "Evaluación de proyectos web completos",
        "criterios": [
            {
                "id": 5,
                "nombre": "Diseño UI/UX",
                "descripcion": "Calidad del diseño de interfaz",
                "peso": 25,
                "rubrica_id": 3
            },
            {
                "id": 6,
                "nombre": "Funcionalidad Backend",
                "descripcion": "Correcto funcionamiento del backend",
                "peso": 35,
                "rubrica_id": 3
            }
        ]
    },
    "message": "Rúbrica creada correctamente"
}
```

#### **27. Crear Enunciado**
**Endpoint:** `POST /api/enunciados`  
**Middleware:** isAdmin

**Request:**
```json
{
    "descripcion": "Desarrollar una API REST completa con Laravel",
    "fecha_limite": "2025-07-15T23:59:59",
    "practica_id": 2,
    "modulo_id": 1,
    "user_id": 3,
    "rubrica_id": 1,
    "grupo_id": 1
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "id": 3,
        "descripcion": "Desarrollar una API REST completa con Laravel",
        "fecha_limite": "2025-07-15T23:59:59.000000Z",
        "practica": {
            "id": 2,
            "nombre": "API REST Backend"
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
        },
        "rubrica": {
            "id": 1,
            "nombre": "Rúbrica Proyecto Final"
        },
        "grupo": {
            "id": 1,
            "nombre": "Grupo DAW 2025"
        }
    },
    "message": "Enunciado creado correctamente"
}
```

---

### **📌 RUTAS DE PROFESOR (isProfesor)**

#### **28. Obtener Grupos del Profesor**
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

#### **29. Crear Grupo (Profesor)**
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

#### **30. Actualizar Grupo (Profesor)**
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

#### **31. Eliminar Grupo (Profesor)**
**Endpoint:** `DELETE /api/profesor/grupos/{id}`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "message": "Grupo 'Grupo DAM 2025 - Actualizado' eliminado exitosamente junto con todos sus módulos y relaciones"
}
```

#### **32. Obtener Módulos del Profesor**
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

#### **33. Crear Módulo (Profesor)**
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

#### **34. Actualizar Módulo (Profesor)**
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

#### **35. Eliminar Módulo (Profesor)**
**Endpoint:** `DELETE /api/profesor/modulos/{id}`  
**Middleware:** isProfesor

**Response (200):**
```json
{
    "success": true,
    "message": "Módulo eliminado correctamente"
}
```

#### **36. Obtener Clases del Profesor**
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

#### **37. Crear Clase (Profesor)**
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

#### **38. Actualizar Clase (Profesor)**
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

#### **39. Eliminar Clase (Profesor)**
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

#### **40. Obtener Entregas del Profesor**
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

#### **41. Obtener Notas del Profesor**
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

#### **42. Crear Nota (Profesor)**
**Endpoint:** `POST /api/profesor/notas`  
**Middleware:** isProfesor

**Request:**
```json
{
    "nota": 9.2,
    "comentario": "Excelente proyecto, muy bien documentado",
    "estudiante_id": 4,
    "entrega_id": 3
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "id": 8,
        "nota": 9.2,
        "comentario": "Excelente proyecto, muy bien documentado",
        "fecha_evaluacion": "2025-05-30T18:00:00.000000Z",
        "estudiante": {
            "id": 4,
            "name": "Ana",
            "surname": "López"
        },
        "evaluador": {
            "id": 3,
            "name": "Juan",
            "surname": "Pérez"
        }
    },
    "message": "Nota creada correctamente"
}
```

#### **43. Obtener Rúbricas del Profesor**
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

#### **44. Crear Rúbrica (Profesor)**
**Endpoint:** `POST /api/profesor/rubricas`  
**Middleware:** isProfesor

**Request:**
```json
{
    "nombre": "Rúbrica Frontend",
    "descripcion": "Evaluación de proyectos frontend",
    "criterios": [
        {
            "nombre": "Responsive Design",
            "descripcion": "Adaptabilidad a diferentes dispositivos",
            "peso": 25
        },
        {
            "nombre": "Interactividad",
            "descripcion": "Elementos interactivos y UX",
            "peso": 30
        }
    ]
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "id": 5,
        "nombre": "Rúbrica Frontend",
        "descripcion": "Evaluación de proyectos frontend",
        "profesor": {
            "id": 3,
            "name": "Juan",
            "surname": "Pérez"
        },
        "criterios": [
            {
                "id": 10,
                "nombre": "Responsive Design",
                "descripcion": "Adaptabilidad a diferentes dispositivos",
                "peso": 25,
                "rubrica_id": 5
            },
            {
                "id": 11,
                "nombre": "Interactividad",
                "descripcion": "Elementos interactivos y UX",
                "peso": 30,
                "rubrica_id": 5
            }
        ]
    },
    "message": "Rúbrica creada correctamente"
}
```

#### **45. Obtener Enunciados del Profesor**
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

#### **46. Crear Enunciado (Profesor)**
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
    "grupo_id": 1
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "id": 6,
        "descripcion": "Crear una aplicación móvil con React Native",
        "fecha_limite": "2025-08-15T23:59:59.000000Z",
        "practica": {
            "id": 3,
            "nombre": "Proyecto Mobile"
        },
        "modulo": {
            "id": 13,
            "nombre": "Desarrollo Mobile Avanzado",
            "codigo": "MOD004-UPD"
        },
        "profesor": {
            "id": 3,
            "name": "Juan",
            "surname": "Pérez"
        },
        "rubrica": {
            "id": 5,
            "nombre": "Rúbrica Frontend"
        },
        "grupo": {
            "id": 1,
            "nombre": "Grupo DAW 2025"
        }
    },
    "message": "Enunciado creado correctamente"
}
```

#### **47. Obtener Información Completa del Profesor**
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
| Autenticados | 11 | isUserAuth |
| Administrador | 14 | isAdmin |
| Profesor | 20 | isProfesor |
| **TOTAL** | **47** | **3 Middleware** |

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

4. **Paginación:** Para listas grandes, considera implementar paginación en el frontend.

5. **CORS:** Configurado para permitir requests desde diferentes dominios.

6. **Rate Limiting:** Implementado para prevenir abuso de la API.

---

## 📞 **Soporte y Contacto**

Para dudas, reportes de bugs o solicitudes de nuevas funcionalidades, contacta con el equipo de desarrollo.

**Fecha de última actualización:** Mayo 30, 2025  
**Versión de la API:** v1.0  
**Estado:** Producción
