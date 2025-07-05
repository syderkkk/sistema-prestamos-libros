<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetupUsuariosController extends Controller
{

    public function setupUsuarios()
    {
        try {
            Log::info('Iniciando setup de usuarios');

            $this->verifyOracleConnection();

            $this->dropExistingObjects();

            $this->createSequences();

            $this->createTables();

            $this->createIndexes();

            $this->createPackages();

            $this->insertInitialData();

            Log::info('Setup de usuarios completado exitosamente');

            return response()->json([
                'success' => true,
                'message' => 'Módulo de usuarios configurado correctamente'
            ]);
        } catch (Exception $e) {
            Log::error('Error en setup de usuarios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al configurar el módulo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function verifyOracleConnection()
    {
        try {
            $result = DB::selectOne("SELECT 'Oracle conectado' as status FROM DUAL");
            Log::info('Conexión Oracle verificada: ' . $result->status);
        } catch (Exception $e) {
            throw new Exception('No se puede conectar a Oracle: ' . $e->getMessage());
        }
    }

    private function dropExistingObjects()
    {
        Log::info('Eliminando objetos existentes si es necesario');

        $dropStatements = [
            "DROP PACKAGE PKG_USUARIOS",
            "DROP TABLE USUARIOS CASCADE CONSTRAINTS",
            "DROP TABLE ROLES CASCADE CONSTRAINTS",
            "DROP SEQUENCE SEQ_USUARIOS",
            "DROP SEQUENCE SEQ_ROLES"
        ];

        foreach ($dropStatements as $sql) {
            try {
                DB::statement($sql);
                Log::info("Ejecutado: $sql");
            } catch (Exception $e) {
                Log::info("Objeto no existía: $sql");
            }
        }
    }

    private function createSequences()
    {
        Log::info('Creando secuencias');

        $sequences = [
            "CREATE SEQUENCE SEQ_ROLES START WITH 1 INCREMENT BY 1 NOCACHE",
            "CREATE SEQUENCE SEQ_USUARIOS START WITH 1 INCREMENT BY 1 NOCACHE"
        ];

        foreach ($sequences as $sql) {
            try {
                DB::statement($sql);
                Log::info("Secuencia creada exitosamente");
            } catch (Exception $e) {
                throw new Exception("Error creando secuencia: " . $e->getMessage());
            }
        }
    }

    private function createTables()
    {
        Log::info('Creando tablas');

        $createRoles = "
        CREATE TABLE ROLES (
            ID NUMBER(10) PRIMARY KEY,
            NOMBRE VARCHAR2(50) NOT NULL UNIQUE,
            DESCRIPCION VARCHAR2(200),
            ESTADO CHAR(1) DEFAULT 'A' CHECK (ESTADO IN ('A', 'I')),
            FECHA_CREACION DATE DEFAULT SYSDATE,
            FECHA_MODIFICACION DATE DEFAULT SYSDATE
        )";

        $createUsuarios = "
        CREATE TABLE USUARIOS (
            ID NUMBER(10) PRIMARY KEY,
            NOMBRE VARCHAR2(100) NOT NULL,
            EMAIL VARCHAR2(150) NOT NULL UNIQUE,
            PASSWORD_HASH VARCHAR2(255) NOT NULL,
            ROL_ID NUMBER(10) NOT NULL,
            ESTADO CHAR(1) DEFAULT 'A' CHECK (ESTADO IN ('A', 'I')),
            ULTIMO_ACCESO DATE,
            FECHA_CREACION DATE DEFAULT SYSDATE,
            FECHA_MODIFICACION DATE DEFAULT SYSDATE,
            CONSTRAINT FK_USUARIOS_ROL FOREIGN KEY (ROL_ID) REFERENCES ROLES(ID)
        )";

        try {
            DB::statement($createRoles);
            Log::info('Tabla ROLES creada');

            DB::statement($createUsuarios);
            Log::info('Tabla USUARIOS creada');
        } catch (Exception $e) {
            throw new Exception("Error creando tablas: " . $e->getMessage());
        }
    }

    private function createIndexes()
    {
        Log::info('Creando índices');

        $indexes = [
            "CREATE INDEX IDX_USUARIOS_EMAIL ON USUARIOS(EMAIL)",
            "CREATE INDEX IDX_USUARIOS_ROL ON USUARIOS(ROL_ID)",
            "CREATE INDEX IDX_USUARIOS_ESTADO ON USUARIOS(ESTADO)",
            "CREATE INDEX IDX_ROLES_NOMBRE ON ROLES(NOMBRE)"
        ];

        foreach ($indexes as $sql) {
            try {
                DB::statement($sql);
                Log::info("Índice creado exitosamente");
            } catch (Exception $e) {
                Log::warning("Error creando índice: " . $e->getMessage());
            }
        }
    }

    private function createPackages()
    {
        Log::info('Creando paquetes PL/SQL');

        $packageSpec = "
        CREATE OR REPLACE PACKAGE PKG_USUARIOS AS
            PROCEDURE registrar_usuario(
                p_nombre IN VARCHAR2,
                p_email IN VARCHAR2,
                p_password IN VARCHAR2,
                p_rol_id IN NUMBER,
                p_usuario_id OUT NUMBER
            );
            
            PROCEDURE autenticar_usuario(
                p_email IN VARCHAR2,
                p_password IN VARCHAR2,
                p_usuario_id OUT NUMBER
            );
            
            
            PROCEDURE actualizar_ultimo_acceso(p_usuario_id IN NUMBER);

            PROCEDURE obtener_usuario_por_id(
                p_usuario_id IN NUMBER,
                p_usuario_id_out OUT NUMBER,
                p_nombre OUT VARCHAR2,
                p_email OUT VARCHAR2,
                p_rol OUT VARCHAR2,
                p_rol_id OUT NUMBER,
                p_estado OUT VARCHAR2,
                p_ultimo_acceso OUT VARCHAR2,
                p_fecha_creacion OUT VARCHAR2
            );

            PROCEDURE obtener_usuario_completo(
                p_usuario_id IN NUMBER,
                p_usuario_id_out OUT NUMBER,
                p_nombre OUT VARCHAR2,
                p_email OUT VARCHAR2,
                p_rol_id OUT NUMBER,
                p_rol_nombre OUT VARCHAR2
            );

            PROCEDURE obtener_roles_activos(
                p_cursor OUT SYS_REFCURSOR
            );

        END PKG_USUARIOS;";

        $packageBody = "
        CREATE OR REPLACE PACKAGE BODY PKG_USUARIOS AS
            
            PROCEDURE registrar_usuario(
                p_nombre IN VARCHAR2,
                p_email IN VARCHAR2,
                p_password IN VARCHAR2,
                p_rol_id IN NUMBER,
                p_usuario_id OUT NUMBER
            ) IS
                v_count NUMBER;
                v_password_hash VARCHAR2(255);
            BEGIN
                -- Verificar email único
                SELECT COUNT(*) INTO v_count 
                FROM USUARIOS 
                WHERE UPPER(EMAIL) = UPPER(p_email);
                
                IF v_count > 0 THEN
                    RAISE_APPLICATION_ERROR(-20001, 'El email ya esta registrado');
                END IF;
                
                -- Verificar que el rol existe
                SELECT COUNT(*) INTO v_count 
                FROM ROLES 
                WHERE ID = p_rol_id AND ESTADO = 'A';
                
                IF v_count = 0 THEN
                    RAISE_APPLICATION_ERROR(-20002, 'El rol especificado no existe');
                END IF;
                
                v_password_hash := p_password;
                
                INSERT INTO USUARIOS (ID, NOMBRE, EMAIL, PASSWORD_HASH, ROL_ID, ESTADO, FECHA_CREACION, FECHA_MODIFICACION)
                VALUES (SEQ_USUARIOS.NEXTVAL, p_nombre, p_email, v_password_hash, p_rol_id, 'A', SYSDATE, SYSDATE)
                RETURNING ID INTO p_usuario_id;
                
                COMMIT;
                
            EXCEPTION
                WHEN DUP_VAL_ON_INDEX THEN
                    ROLLBACK;
                    RAISE_APPLICATION_ERROR(-20001, 'El email ya esta registrado');
                WHEN OTHERS THEN
                    ROLLBACK;
                    RAISE_APPLICATION_ERROR(-20003, 'Error al registrar usuario: ' || SQLERRM);
            END registrar_usuario;
            
            PROCEDURE autenticar_usuario(
                p_email IN VARCHAR2,
                p_password IN VARCHAR2,
                p_usuario_id OUT NUMBER
            ) IS
                v_password_hash VARCHAR2(255);
                v_stored_hash VARCHAR2(255);
            BEGIN
                v_password_hash := p_password;
                
                -- Buscar usuario
                BEGIN
                    SELECT u.ID, u.PASSWORD_HASH
                    INTO p_usuario_id, v_stored_hash
                    FROM USUARIOS u
                    WHERE UPPER(u.EMAIL) = UPPER(p_email) AND u.ESTADO = 'A';
                    
                    -- Verificar contraseña
                    IF v_password_hash != v_stored_hash THEN
                        p_usuario_id := NULL;
                    END IF;
                    
                EXCEPTION
                    WHEN NO_DATA_FOUND THEN
                        p_usuario_id := NULL;
                END;
                
            EXCEPTION
                WHEN OTHERS THEN
                    p_usuario_id := NULL;
            END autenticar_usuario;
            
            PROCEDURE actualizar_ultimo_acceso(p_usuario_id IN NUMBER) IS
            BEGIN
                UPDATE USUARIOS 
                SET ULTIMO_ACCESO = SYSDATE,
                    FECHA_MODIFICACION = SYSDATE
                WHERE ID = p_usuario_id;
                
                COMMIT;
                
            EXCEPTION
                WHEN OTHERS THEN
                    NULL;
            END actualizar_ultimo_acceso;
            
            PROCEDURE obtener_usuario_por_id(
                p_usuario_id IN NUMBER,
                p_usuario_id_out OUT NUMBER,
                p_nombre OUT VARCHAR2,
                p_email OUT VARCHAR2,
                p_rol OUT VARCHAR2,
                p_rol_id OUT NUMBER,
                p_estado OUT VARCHAR2,
                p_ultimo_acceso OUT VARCHAR2,
                p_fecha_creacion OUT VARCHAR2
            ) IS
            BEGIN
                SELECT u.ID, u.NOMBRE, u.EMAIL, r.NOMBRE, u.ROL_ID, u.ESTADO,
                       TO_CHAR(u.ULTIMO_ACCESO, 'DD/MM/YYYY HH24:MI:SS'),
                       TO_CHAR(u.FECHA_CREACION, 'DD/MM/YYYY HH24:MI:SS')
                INTO p_usuario_id_out, p_nombre, p_email, p_rol, p_rol_id, p_estado,
                     p_ultimo_acceso, p_fecha_creacion
                FROM USUARIOS u
                JOIN ROLES r ON u.ROL_ID = r.ID
                WHERE u.ID = p_usuario_id AND u.ESTADO = 'A';
                
            EXCEPTION
                WHEN NO_DATA_FOUND THEN
                    p_usuario_id_out := NULL;
                    p_nombre := NULL;
                    p_email := NULL;
                    p_rol := NULL;
                    p_rol_id := NULL;
                    p_estado := NULL;
                    p_ultimo_acceso := NULL;
                    p_fecha_creacion := NULL;
                WHEN OTHERS THEN
                    p_usuario_id_out := NULL;
                    p_nombre := NULL;
                    p_email := NULL;
                    p_rol := NULL;
                    p_rol_id := NULL;
                    p_estado := NULL;
                    p_ultimo_acceso := NULL;
                    p_fecha_creacion := NULL;
            END obtener_usuario_por_id;

            PROCEDURE obtener_usuario_completo(
                p_usuario_id IN NUMBER,
                p_usuario_id_out OUT NUMBER,
                p_nombre OUT VARCHAR2,
                p_email OUT VARCHAR2,
                p_rol_id OUT NUMBER,
                p_rol_nombre OUT VARCHAR2
            ) IS
            BEGIN
                SELECT u.ID, u.NOMBRE, u.EMAIL, u.ROL_ID, r.NOMBRE
                INTO p_usuario_id_out, p_nombre, p_email, p_rol_id, p_rol_nombre
                FROM USUARIOS u
                INNER JOIN ROLES r ON u.ROL_ID = r.ID
                WHERE u.ID = p_usuario_id AND u.ESTADO = 'A';
                
            EXCEPTION
                WHEN NO_DATA_FOUND THEN
                    p_usuario_id_out := NULL;
                    p_nombre := NULL;
                    p_email := NULL;
                    p_rol_id := NULL;
                    p_rol_nombre := NULL;
            END obtener_usuario_completo;

            PROCEDURE obtener_roles_activos(
                p_cursor OUT SYS_REFCURSOR
            ) IS
            BEGIN
                OPEN p_cursor FOR
                    SELECT ID, NOMBRE 
                    FROM ROLES 
                    WHERE ESTADO = 'A' 
                    ORDER BY NOMBRE;
            END obtener_roles_activos;
            
        END PKG_USUARIOS;";

        try {
            DB::statement($packageSpec);
            Log::info('Paquete PKG_USUARIOS spec creado');

            DB::statement($packageBody);
            Log::info('Paquete PKG_USUARIOS body creado');
        } catch (Exception $e) {
            throw new Exception("Error creando paquete: " . $e->getMessage());
        }
    }

    private function insertInitialData()
    {
        Log::info('Insertando datos iniciales');

        try {
            $insertRolUsuario = "INSERT INTO ROLES (ID, NOMBRE, DESCRIPCION, ESTADO, FECHA_CREACION, FECHA_MODIFICACION) 
                                VALUES (SEQ_ROLES.NEXTVAL, 'Usuario', 'Usuario regular del sistema', 'A', SYSDATE, SYSDATE)";

            $insertRolBibliotecario = "INSERT INTO ROLES (ID, NOMBRE, DESCRIPCION, ESTADO, FECHA_CREACION, FECHA_MODIFICACION) 
                                      VALUES (SEQ_ROLES.NEXTVAL, 'Bibliotecario', 'Administrador del sistema', 'A', SYSDATE, SYSDATE)";

            DB::statement($insertRolUsuario);
            DB::statement($insertRolBibliotecario);

            DB::statement("COMMIT");

            Log::info('Datos iniciales insertados');
        } catch (Exception $e) {
            throw new Exception("Error insertando datos iniciales: " . $e->getMessage());
        }
    }

    public function getRoles()
    {
        try {
            $sql = "SELECT ID, NOMBRE, DESCRIPCION FROM ROLES WHERE ESTADO = 'A' ORDER BY NOMBRE";
            $result = DB::select($sql);

            $roles = [];
            foreach ($result as $row) {
                $roles[] = [
                    'id' => $row->id,
                    'nombre' => $row->nombre,
                    'descripcion' => $row->descripcion
                ];
            }

            return $roles;
        } catch (Exception $e) {
            Log::error('Error al obtener roles: ' . $e->getMessage());
            throw $e;
        }
    }
}
