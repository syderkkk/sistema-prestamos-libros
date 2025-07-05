<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetupLibrosController extends Controller
{
    public function setupLibros()
    {
        try {
            Log::info('Iniciando setup de libros');

            $this->verifyOracleConnection();

            $this->createSequencesIfNotExists();

            $this->createTablesIfNotExists();

            $this->createIndexesIfNotExists();

            $this->createOrReplacePackages();

            $this->createOrReplaceTriggers();

            $this->insertInitialDataIfNotExists();

            Log::info('Setup de libros completado exitosamente');

            return response()->json([
                'success' => true,
                'message' => 'Módulo de gestión de libros configurado correctamente'
            ]);
        } catch (Exception $e) {
            Log::error('Error en setup de libros: ' . $e->getMessage());
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

    private function createSequencesIfNotExists()
    {
        Log::info('Verificando y creando secuencias para libros');

        $sequences = [
            'SEQ_CATEGORIAS' => 'CREATE SEQUENCE SEQ_CATEGORIAS START WITH 1 INCREMENT BY 1 NOCACHE',
            'SEQ_AUTORES' => 'CREATE SEQUENCE SEQ_AUTORES START WITH 1 INCREMENT BY 1 NOCACHE',
            'SEQ_LIBROS' => 'CREATE SEQUENCE SEQ_LIBROS START WITH 1 INCREMENT BY 1 NOCACHE'
        ];

        foreach ($sequences as $name => $sql) {
            try {
                $checkSql = "SELECT COUNT(*) as count FROM user_sequences WHERE sequence_name = ?";
                $result = DB::selectOne($checkSql, [strtoupper($name)]);

                if ($result->count == 0) {
                    DB::statement($sql);
                    Log::info("Secuencia {$name} creada");
                } else {
                    Log::info("Secuencia {$name} ya existe - omitiendo");
                }
            } catch (Exception $e) {
                try {
                    DB::statement($sql);
                    Log::info("Secuencia {$name} creada (método directo)");
                } catch (Exception $e2) {
                    if (strpos($e2->getMessage(), 'ORA-00955') !== false) {
                        Log::info("Secuencia {$name} ya existe");
                    } else {
                        Log::warning("No se pudo crear secuencia {$name}: " . $e2->getMessage());
                    }
                }
            }
        }
    }

    private function createTablesIfNotExists()
    {
        Log::info('Verificando y creando tablas para libros');

        if (!$this->tableExists('CATEGORIAS')) {
            try {
                $createCategorias = "
                CREATE TABLE CATEGORIAS (
                    ID NUMBER(10) PRIMARY KEY,
                    NOMBRE VARCHAR2(100) NOT NULL UNIQUE,
                    DESCRIPCION VARCHAR2(500),
                    ESTADO CHAR(1) DEFAULT 'A' CHECK (ESTADO IN ('A', 'I')),
                    FECHA_CREACION DATE DEFAULT SYSDATE,
                    FECHA_MODIFICACION DATE DEFAULT SYSDATE
                )";

                DB::statement($createCategorias);
                Log::info('Tabla CATEGORIAS creada');
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'ORA-00955') === false) {
                    throw $e;
                }
                Log::info('Tabla CATEGORIAS ya existe');
            }
        } else {
            Log::info('Tabla CATEGORIAS ya existe - omitiendo');
        }

        if (!$this->tableExists('AUTORES')) {
            try {
                $createAutores = "
                CREATE TABLE AUTORES (
                    ID NUMBER(10) PRIMARY KEY,
                    NOMBRE VARCHAR2(100) NOT NULL,
                    APELLIDO VARCHAR2(100) NOT NULL,
                    NACIONALIDAD VARCHAR2(50),
                    FECHA_NACIMIENTO DATE,
                    BIOGRAFIA CLOB,
                    ESTADO CHAR(1) DEFAULT 'A' CHECK (ESTADO IN ('A', 'I')),
                    FECHA_CREACION DATE DEFAULT SYSDATE,
                    FECHA_MODIFICACION DATE DEFAULT SYSDATE
                )";

                DB::statement($createAutores);
                Log::info('Tabla AUTORES creada');
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'ORA-00955') === false) {
                    throw $e;
                }
                Log::info('Tabla AUTORES ya existe');
            }
        } else {
            Log::info('Tabla AUTORES ya existe - omitiendo');
        }

        if (!$this->tableExists('LIBROS')) {
            try {
                $createLibros = "
                CREATE TABLE LIBROS (
                    ID NUMBER(10) PRIMARY KEY,
                    TITULO VARCHAR2(200) NOT NULL,
                    ISBN VARCHAR2(20) UNIQUE,
                    AUTOR_ID NUMBER(10) NOT NULL,
                    CATEGORIA_ID NUMBER(10) NOT NULL,
                    EDITORIAL VARCHAR2(100),
                    FECHA_PUBLICACION DATE,
                    NUMERO_PAGINAS NUMBER(5),
                    IDIOMA VARCHAR2(50) DEFAULT 'Español',
                    RESUMEN CLOB,
                    UBICACION VARCHAR2(50),
                    CANTIDAD_TOTAL NUMBER(5) DEFAULT 1,
                    CANTIDAD_DISPONIBLE NUMBER(5) DEFAULT 1,
                    ESTADO CHAR(1) DEFAULT 'A' CHECK (ESTADO IN ('A', 'I')),
                    FECHA_CREACION DATE DEFAULT SYSDATE,
                    FECHA_MODIFICACION DATE DEFAULT SYSDATE,
                    CONSTRAINT FK_LIBROS_AUTOR FOREIGN KEY (AUTOR_ID) REFERENCES AUTORES(ID),
                    CONSTRAINT FK_LIBROS_CATEGORIA FOREIGN KEY (CATEGORIA_ID) REFERENCES CATEGORIAS(ID),
                    CONSTRAINT CHK_CANTIDAD_DISPONIBLE CHECK (CANTIDAD_DISPONIBLE <= CANTIDAD_TOTAL)
                )";

                DB::statement($createLibros);
                Log::info('Tabla LIBROS creada');
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'ORA-00955') === false) {
                    throw $e;
                }
                Log::info('Tabla LIBROS ya existe');
            }
        } else {
            Log::info('Tabla LIBROS ya existe - omitiendo');
        }
    }

    private function createIndexesIfNotExists()
    {
        Log::info('Verificando y creando índices para libros');

        $indexes = [
            'IDX_CATEGORIAS_NOMBRE' => 'CREATE INDEX IDX_CATEGORIAS_NOMBRE ON CATEGORIAS(NOMBRE)',
            'IDX_AUTORES_NOMBRE' => 'CREATE INDEX IDX_AUTORES_NOMBRE ON AUTORES(NOMBRE, APELLIDO)',
            'IDX_LIBROS_TITULO' => 'CREATE INDEX IDX_LIBROS_TITULO ON LIBROS(TITULO)',
            'IDX_LIBROS_ISBN' => 'CREATE INDEX IDX_LIBROS_ISBN ON LIBROS(ISBN)',
            'IDX_LIBROS_AUTOR' => 'CREATE INDEX IDX_LIBROS_AUTOR ON LIBROS(AUTOR_ID)',
            'IDX_LIBROS_CATEGORIA' => 'CREATE INDEX IDX_LIBROS_CATEGORIA ON LIBROS(CATEGORIA_ID)',
            'IDX_LIBROS_ESTADO' => 'CREATE INDEX IDX_LIBROS_ESTADO ON LIBROS(ESTADO)'
        ];

        foreach ($indexes as $name => $sql) {
            try {
                $checkSql = "SELECT COUNT(*) as count FROM user_indexes WHERE index_name = ?";
                $result = DB::selectOne($checkSql, [strtoupper($name)]);

                if ($result->count == 0) {
                    DB::statement($sql);
                    Log::info("Índice {$name} creado");
                } else {
                    Log::info("Índice {$name} ya existe - omitiendo");
                }
            } catch (Exception $e) {
                try {
                    DB::statement($sql);
                    Log::info("Índice {$name} creado (método directo)");
                } catch (Exception $e2) {
                    if (
                        strpos($e2->getMessage(), 'ORA-01408') !== false ||
                        strpos($e2->getMessage(), 'ORA-00955') !== false
                    ) {
                        Log::info("Índice {$name} ya existe");
                    } else {
                        Log::warning("No se pudo crear índice {$name}: " . $e2->getMessage());
                    }
                }
            }
        }
    }

    private function createOrReplacePackages()
    {
        Log::info('Creando/actualizando paquetes PL/SQL para libros');

        $packageSpec = "
        CREATE OR REPLACE PACKAGE PKG_LIBROS AS
            -- Funciones para CATEGORIAS
            FUNCTION crear_categoria(
                p_nombre IN VARCHAR2,
                p_descripcion IN VARCHAR2
            ) RETURN NUMBER;
            
            PROCEDURE obtener_categorias(p_cursor OUT SYS_REFCURSOR);
            
            PROCEDURE actualizar_categoria(
                p_id IN NUMBER,
                p_nombre IN VARCHAR2,
                p_descripcion IN VARCHAR2
            );
            
            PROCEDURE eliminar_categoria(p_id IN NUMBER);
            
            -- Funciones para AUTORES
            FUNCTION crear_autor(
                p_nombre IN VARCHAR2,
                p_apellido IN VARCHAR2,
                p_nacionalidad IN VARCHAR2,
                p_fecha_nacimiento IN DATE,
                p_biografia IN CLOB
            ) RETURN NUMBER;
            
            PROCEDURE obtener_autores(p_cursor OUT SYS_REFCURSOR);
            
            PROCEDURE actualizar_autor(
                p_id IN NUMBER,
                p_nombre IN VARCHAR2,
                p_apellido IN VARCHAR2,
                p_nacionalidad IN VARCHAR2,
                p_fecha_nacimiento IN DATE,
                p_biografia IN CLOB
            );
            
            PROCEDURE eliminar_autor(p_id IN NUMBER);
            
            -- Funciones para LIBROS
            FUNCTION crear_libro(
                p_titulo IN VARCHAR2,
                p_isbn IN VARCHAR2,
                p_autor_id IN NUMBER,
                p_categoria_id IN NUMBER,
                p_editorial IN VARCHAR2,
                p_fecha_publicacion IN DATE,
                p_numero_paginas IN NUMBER,
                p_idioma IN VARCHAR2,
                p_resumen IN CLOB,
                p_ubicacion IN VARCHAR2,
                p_cantidad_total IN NUMBER
            ) RETURN NUMBER;
            
            PROCEDURE obtener_libros(p_cursor OUT SYS_REFCURSOR);
            
            PROCEDURE obtener_libro_por_id(
                p_id IN NUMBER,
                p_cursor OUT SYS_REFCURSOR
            );
            
            PROCEDURE actualizar_libro(
                p_id IN NUMBER,
                p_titulo IN VARCHAR2,
                p_isbn IN VARCHAR2,
                p_autor_id IN NUMBER,
                p_categoria_id IN NUMBER,
                p_editorial IN VARCHAR2,
                p_fecha_publicacion IN DATE,
                p_numero_paginas IN NUMBER,
                p_idioma IN VARCHAR2,
                p_resumen IN CLOB,
                p_ubicacion IN VARCHAR2,
                p_cantidad_total IN NUMBER
            );
            
            PROCEDURE eliminar_libro(p_id IN NUMBER);
            
            -- Funciones de validación
            FUNCTION validar_isbn_unico(
                p_isbn IN VARCHAR2,
                p_libro_id IN NUMBER DEFAULT NULL
            ) RETURN NUMBER;
            
        END PKG_LIBROS;";

        $packageBody = "
        CREATE OR REPLACE PACKAGE BODY PKG_LIBROS AS
            
            -- CATEGORIAS
            FUNCTION crear_categoria(
                p_nombre IN VARCHAR2,
                p_descripcion IN VARCHAR2
            ) RETURN NUMBER IS
                v_categoria_id NUMBER;
                v_count NUMBER;
            BEGIN
                SELECT COUNT(*) INTO v_count 
                FROM CATEGORIAS 
                WHERE UPPER(NOMBRE) = UPPER(p_nombre) AND ESTADO = 'A';
                
                IF v_count > 0 THEN
                    RAISE_APPLICATION_ERROR(-20001, 'Ya existe una categoría con ese nombre');
                END IF;
                
                INSERT INTO CATEGORIAS (NOMBRE, DESCRIPCION, ESTADO, FECHA_CREACION, FECHA_MODIFICACION)
                VALUES (p_nombre, p_descripcion, 'A', SYSDATE, SYSDATE)
                RETURNING ID INTO v_categoria_id;
                
                RETURN v_categoria_id;
                
            EXCEPTION
                WHEN DUP_VAL_ON_INDEX THEN
                    RAISE_APPLICATION_ERROR(-20001, 'Ya existe una categoría con ese nombre');
                WHEN OTHERS THEN
                    RAISE_APPLICATION_ERROR(-20003, 'Error al crear categoría: ' || SQLERRM);
            END crear_categoria;
            
            PROCEDURE obtener_categorias(p_cursor OUT SYS_REFCURSOR) IS
            BEGIN
                OPEN p_cursor FOR
                    SELECT ID, NOMBRE, DESCRIPCION, ESTADO, FECHA_CREACION 
                    FROM CATEGORIAS 
                    WHERE ESTADO = 'A' 
                    ORDER BY NOMBRE;
            END obtener_categorias;
            
            PROCEDURE actualizar_categoria(
                p_id IN NUMBER,
                p_nombre IN VARCHAR2,
                p_descripcion IN VARCHAR2
            ) IS
                v_count NUMBER;
            BEGIN
                SELECT COUNT(*) INTO v_count 
                FROM CATEGORIAS 
                WHERE UPPER(NOMBRE) = UPPER(p_nombre) AND ESTADO = 'A' AND ID != p_id;
                
                IF v_count > 0 THEN
                    RAISE_APPLICATION_ERROR(-20001, 'Ya existe una categoría con ese nombre');
                END IF;
                
                UPDATE CATEGORIAS 
                SET NOMBRE = p_nombre, 
                    DESCRIPCION = p_descripcion,
                    FECHA_MODIFICACION = SYSDATE
                WHERE ID = p_id AND ESTADO = 'A';
                
                IF SQL%ROWCOUNT = 0 THEN
                    RAISE_APPLICATION_ERROR(-20002, 'Categoría no encontrada');
                END IF;
                
            EXCEPTION
                WHEN OTHERS THEN
                    RAISE_APPLICATION_ERROR(-20003, 'Error al actualizar categoría: ' || SQLERRM);
            END actualizar_categoria;
            
            PROCEDURE eliminar_categoria(p_id IN NUMBER) IS
            BEGIN
                UPDATE CATEGORIAS 
                SET ESTADO = 'I', FECHA_MODIFICACION = SYSDATE 
                WHERE ID = p_id;
                
                IF SQL%ROWCOUNT = 0 THEN
                    RAISE_APPLICATION_ERROR(-20002, 'Categoría no encontrada');
                END IF;
                
            EXCEPTION
                WHEN OTHERS THEN
                    RAISE_APPLICATION_ERROR(-20003, 'Error al eliminar categoría: ' || SQLERRM);
            END eliminar_categoria;
            
            -- AUTORES
            FUNCTION crear_autor(
                p_nombre IN VARCHAR2,
                p_apellido IN VARCHAR2,
                p_nacionalidad IN VARCHAR2,
                p_fecha_nacimiento IN DATE,
                p_biografia IN CLOB
            ) RETURN NUMBER IS
                v_autor_id NUMBER;
            BEGIN
                INSERT INTO AUTORES (NOMBRE, APELLIDO, NACIONALIDAD, FECHA_NACIMIENTO, BIOGRAFIA, ESTADO, FECHA_CREACION, FECHA_MODIFICACION)
                VALUES (p_nombre, p_apellido, p_nacionalidad, p_fecha_nacimiento, p_biografia, 'A', SYSDATE, SYSDATE)
                RETURNING ID INTO v_autor_id;
                
                RETURN v_autor_id;
                
            EXCEPTION
                WHEN OTHERS THEN
                    RAISE_APPLICATION_ERROR(-20003, 'Error al crear autor: ' || SQLERRM);
            END crear_autor;
            
            PROCEDURE obtener_autores(p_cursor OUT SYS_REFCURSOR) IS
            BEGIN
                OPEN p_cursor FOR
                    SELECT ID, NOMBRE, APELLIDO, NACIONALIDAD, FECHA_NACIMIENTO, BIOGRAFIA, ESTADO, FECHA_CREACION 
                    FROM AUTORES 
                    WHERE ESTADO = 'A' 
                    ORDER BY APELLIDO, NOMBRE;
            END obtener_autores;
            
            PROCEDURE actualizar_autor(
                p_id IN NUMBER,
                p_nombre IN VARCHAR2,
                p_apellido IN VARCHAR2,
                p_nacionalidad IN VARCHAR2,
                p_fecha_nacimiento IN DATE,
                p_biografia IN CLOB
            ) IS
            BEGIN
                UPDATE AUTORES 
                SET NOMBRE = p_nombre,
                    APELLIDO = p_apellido,
                    NACIONALIDAD = p_nacionalidad,
                    FECHA_NACIMIENTO = p_fecha_nacimiento,
                    BIOGRAFIA = p_biografia,
                    FECHA_MODIFICACION = SYSDATE
                WHERE ID = p_id AND ESTADO = 'A';
                
                IF SQL%ROWCOUNT = 0 THEN
                    RAISE_APPLICATION_ERROR(-20002, 'Autor no encontrado');
                END IF;
                
            EXCEPTION
                WHEN OTHERS THEN
                    RAISE_APPLICATION_ERROR(-20003, 'Error al actualizar autor: ' || SQLERRM);
            END actualizar_autor;
            
            PROCEDURE eliminar_autor(p_id IN NUMBER) IS
            BEGIN
                UPDATE AUTORES 
                SET ESTADO = 'I', FECHA_MODIFICACION = SYSDATE 
                WHERE ID = p_id;
                
                IF SQL%ROWCOUNT = 0 THEN
                    RAISE_APPLICATION_ERROR(-20002, 'Autor no encontrado');
                END IF;
                
            EXCEPTION
                WHEN OTHERS THEN
                    RAISE_APPLICATION_ERROR(-20003, 'Error al eliminar autor: ' || SQLERRM);
            END eliminar_autor;
            
            -- LIBROS
            FUNCTION crear_libro(
                p_titulo IN VARCHAR2,
                p_isbn IN VARCHAR2,
                p_autor_id IN NUMBER,
                p_categoria_id IN NUMBER,
                p_editorial IN VARCHAR2,
                p_fecha_publicacion IN DATE,
                p_numero_paginas IN NUMBER,
                p_idioma IN VARCHAR2,
                p_resumen IN CLOB,
                p_ubicacion IN VARCHAR2,
                p_cantidad_total IN NUMBER
            ) RETURN NUMBER IS
                v_libro_id NUMBER;
                v_count NUMBER;
            BEGIN
                IF p_isbn IS NOT NULL AND validar_isbn_unico(p_isbn) = 0 THEN
                    RAISE_APPLICATION_ERROR(-20001, 'El ISBN ya está registrado');
                END IF;
                
                SELECT COUNT(*) INTO v_count 
                FROM AUTORES 
                WHERE ID = p_autor_id AND ESTADO = 'A';
                
                IF v_count = 0 THEN
                    RAISE_APPLICATION_ERROR(-20002, 'El autor especificado no existe');
                END IF;
                
                SELECT COUNT(*) INTO v_count 
                FROM CATEGORIAS 
                WHERE ID = p_categoria_id AND ESTADO = 'A';
                
                IF v_count = 0 THEN
                    RAISE_APPLICATION_ERROR(-20002, 'La categoría especificada no existe');
                END IF;
                
                INSERT INTO LIBROS (TITULO, ISBN, AUTOR_ID, CATEGORIA_ID, EDITORIAL, FECHA_PUBLICACION, 
                                   NUMERO_PAGINAS, IDIOMA, RESUMEN, UBICACION, CANTIDAD_TOTAL, 
                                   CANTIDAD_DISPONIBLE, ESTADO, FECHA_CREACION, FECHA_MODIFICACION)
                VALUES (p_titulo, p_isbn, p_autor_id, p_categoria_id, p_editorial, p_fecha_publicacion, 
                       p_numero_paginas, p_idioma, p_resumen, p_ubicacion, p_cantidad_total, 
                       p_cantidad_total, 'A', SYSDATE, SYSDATE)
                RETURNING ID INTO v_libro_id;
                
                RETURN v_libro_id;
                
            EXCEPTION
                WHEN DUP_VAL_ON_INDEX THEN
                    RAISE_APPLICATION_ERROR(-20001, 'El ISBN ya está registrado');
                WHEN OTHERS THEN
                    RAISE_APPLICATION_ERROR(-20003, 'Error al crear libro: ' || SQLERRM);
            END crear_libro;
            
            PROCEDURE obtener_libros(p_cursor OUT SYS_REFCURSOR) IS
            BEGIN
                OPEN p_cursor FOR
                    SELECT l.ID, l.TITULO, l.ISBN, l.EDITORIAL, l.FECHA_PUBLICACION, 
                           l.NUMERO_PAGINAS, l.IDIOMA, l.RESUMEN, l.UBICACION, 
                           l.CANTIDAD_TOTAL, l.CANTIDAD_DISPONIBLE, l.ESTADO, l.FECHA_CREACION, l.CATEGORIA_ID,
                           a.NOMBRE as AUTOR_NOMBRE, a.APELLIDO as AUTOR_APELLIDO,
                           c.NOMBRE as CATEGORIA_NOMBRE
                    FROM LIBROS l
                    JOIN AUTORES a ON l.AUTOR_ID = a.ID
                    JOIN CATEGORIAS c ON l.CATEGORIA_ID = c.ID
                    WHERE l.ESTADO = 'A'
                    ORDER BY l.TITULO;
            END obtener_libros;
            
            PROCEDURE obtener_libro_por_id(
                p_id IN NUMBER,
                p_cursor OUT SYS_REFCURSOR
            ) IS
            BEGIN
                OPEN p_cursor FOR
                    SELECT l.*, a.NOMBRE as AUTOR_NOMBRE, a.APELLIDO as AUTOR_APELLIDO,
                           c.NOMBRE as CATEGORIA_NOMBRE
                    FROM LIBROS l
                    JOIN AUTORES a ON l.AUTOR_ID = a.ID
                    JOIN CATEGORIAS c ON l.CATEGORIA_ID = c.ID
                    WHERE l.ID = p_id AND l.ESTADO = 'A';
            END obtener_libro_por_id;
            
            PROCEDURE actualizar_libro(
                p_id IN NUMBER,
                p_titulo IN VARCHAR2,
                p_isbn IN VARCHAR2,
                p_autor_id IN NUMBER,
                p_categoria_id IN NUMBER,
                p_editorial IN VARCHAR2,
                p_fecha_publicacion IN DATE,
                p_numero_paginas IN NUMBER,
                p_idioma IN VARCHAR2,
                p_resumen IN CLOB,
                p_ubicacion IN VARCHAR2,
                p_cantidad_total IN NUMBER
            ) IS
                v_count NUMBER;
            BEGIN
                IF p_isbn IS NOT NULL AND validar_isbn_unico(p_isbn, p_id) = 0 THEN
                    RAISE_APPLICATION_ERROR(-20001, 'El ISBN ya está registrado');
                END IF;
                
                SELECT COUNT(*) INTO v_count 
                FROM AUTORES 
                WHERE ID = p_autor_id AND ESTADO = 'A';
                
                IF v_count = 0 THEN
                    RAISE_APPLICATION_ERROR(-20002, 'El autor especificado no existe');
                END IF;
                
                SELECT COUNT(*) INTO v_count 
                FROM CATEGORIAS 
                WHERE ID = p_categoria_id AND ESTADO = 'A';
                
                IF v_count = 0 THEN
                    RAISE_APPLICATION_ERROR(-20002, 'La categoría especificada no existe');
                END IF;
                
                UPDATE LIBROS 
                SET TITULO = p_titulo,
                    ISBN = p_isbn,
                    AUTOR_ID = p_autor_id,
                    CATEGORIA_ID = p_categoria_id,
                    EDITORIAL = p_editorial,
                    FECHA_PUBLICACION = p_fecha_publicacion,
                    NUMERO_PAGINAS = p_numero_paginas,
                    IDIOMA = p_idioma,
                    RESUMEN = p_resumen,
                    UBICACION = p_ubicacion,
                    CANTIDAD_TOTAL = p_cantidad_total,
                    FECHA_MODIFICACION = SYSDATE
                WHERE ID = p_id AND ESTADO = 'A';
                
                IF SQL%ROWCOUNT = 0 THEN
                    RAISE_APPLICATION_ERROR(-20002, 'Libro no encontrado');
                END IF;
                
            EXCEPTION
                WHEN OTHERS THEN
                    RAISE_APPLICATION_ERROR(-20003, 'Error al actualizar libro: ' || SQLERRM);
            END actualizar_libro;
            
            PROCEDURE eliminar_libro(p_id IN NUMBER) IS
            BEGIN
                UPDATE LIBROS 
                SET ESTADO = 'I', FECHA_MODIFICACION = SYSDATE 
                WHERE ID = p_id;
                
                IF SQL%ROWCOUNT = 0 THEN
                    RAISE_APPLICATION_ERROR(-20002, 'Libro no encontrado');
                END IF;
                
            EXCEPTION
                WHEN OTHERS THEN
                    RAISE_APPLICATION_ERROR(-20003, 'Error al eliminar libro: ' || SQLERRM);
            END eliminar_libro;
            
            FUNCTION validar_isbn_unico(
                p_isbn IN VARCHAR2,
                p_libro_id IN NUMBER DEFAULT NULL
            ) RETURN NUMBER IS
                v_count NUMBER;
            BEGIN
                IF p_libro_id IS NULL THEN
                    SELECT COUNT(*) INTO v_count 
                    FROM LIBROS 
                    WHERE ISBN = p_isbn;
                ELSE
                    SELECT COUNT(*) INTO v_count 
                    FROM LIBROS 
                    WHERE ISBN = p_isbn AND ID != p_libro_id;
                END IF;
                
                RETURN CASE WHEN v_count = 0 THEN 1 ELSE 0 END;
            END validar_isbn_unico;
            
        END PKG_LIBROS;";

        try {
            DB::statement($packageSpec);
            Log::info('Paquete PKG_LIBROS spec creado/actualizado');

            DB::statement($packageBody);
            Log::info('Paquete PKG_LIBROS body creado/actualizado');
        } catch (Exception $e) {
            Log::error('Error creando paquete de libros: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createOrReplaceTriggers()
    {
        Log::info('Creando/actualizando triggers para libros');

        $triggers = [
            'TRG_CATEGORIAS_ID' => "
                CREATE OR REPLACE TRIGGER TRG_CATEGORIAS_ID
                BEFORE INSERT ON CATEGORIAS
                FOR EACH ROW
                BEGIN
                    IF :NEW.ID IS NULL THEN
                        SELECT SEQ_CATEGORIAS.NEXTVAL INTO :NEW.ID FROM DUAL;
                    END IF;
                END;",

            'TRG_AUTORES_ID' => "
                CREATE OR REPLACE TRIGGER TRG_AUTORES_ID
                BEFORE INSERT ON AUTORES
                FOR EACH ROW
                BEGIN
                    IF :NEW.ID IS NULL THEN
                        SELECT SEQ_AUTORES.NEXTVAL INTO :NEW.ID FROM DUAL;
                    END IF;
                END;",

            'TRG_LIBROS_ID' => "
                CREATE OR REPLACE TRIGGER TRG_LIBROS_ID
                BEFORE INSERT ON LIBROS
                FOR EACH ROW
                BEGIN
                    IF :NEW.ID IS NULL THEN
                        SELECT SEQ_LIBROS.NEXTVAL INTO :NEW.ID FROM DUAL;
                    END IF;
                END;",

            'TRG_CATEGORIAS_AUDIT' => "
                CREATE OR REPLACE TRIGGER TRG_CATEGORIAS_AUDIT
                BEFORE UPDATE ON CATEGORIAS
                FOR EACH ROW
                BEGIN
                    :NEW.FECHA_MODIFICACION := SYSDATE;
                END;",

            'TRG_AUTORES_AUDIT' => "
                CREATE OR REPLACE TRIGGER TRG_AUTORES_AUDIT
                BEFORE UPDATE ON AUTORES
                FOR EACH ROW
                BEGIN
                    :NEW.FECHA_MODIFICACION := SYSDATE;
                END;",

            'TRG_LIBROS_AUDIT' => "
                CREATE OR REPLACE TRIGGER TRG_LIBROS_AUDIT
                BEFORE UPDATE ON LIBROS
                FOR EACH ROW
                BEGIN
                    :NEW.FECHA_MODIFICACION := SYSDATE;
                END;"
        ];

        foreach ($triggers as $name => $sql) {
            try {
                DB::statement($sql);
                Log::info("Trigger {$name} creado/actualizado");
            } catch (Exception $e) {
                Log::error("Error con trigger {$name}: " . $e->getMessage());
            }
        }
    }

    private function insertInitialDataIfNotExists()
    {
        Log::info('Verificando y insertando datos iniciales para libros');

        try {
            $countCategorias = "SELECT COUNT(*) as count FROM CATEGORIAS";
            $result = DB::selectOne($countCategorias);

            if ($result->count == 0) {
                $categorias = [
                    ['Ficción', 'Libros de ficción y novelas'],
                    ['No Ficción', 'Libros de no ficción, ensayos y biografías'],
                    ['Ciencia', 'Libros científicos y técnicos'],
                    ['Historia', 'Libros de historia y cultura'],
                    ['Literatura', 'Obras literarias clásicas y contemporáneas']
                ];

                foreach ($categorias as $categoria) {
                    try {
                        $insertSql = "INSERT INTO CATEGORIAS (NOMBRE, DESCRIPCION, ESTADO, FECHA_CREACION, FECHA_MODIFICACION) 
                                 VALUES (:nombre, :descripcion, 'A', SYSDATE, SYSDATE)";
                        DB::statement($insertSql, [
                            'nombre' => $categoria[0],
                            'descripcion' => $categoria[1]
                        ]);
                        Log::info("Categoría '{$categoria[0]}' insertada");
                    } catch (Exception $e) {
                        if (strpos($e->getMessage(), 'ORA-00001') !== false) {
                            Log::info("Categoría '{$categoria[0]}' ya existe");
                        } else {
                            Log::warning("Error insertando categoría '{$categoria[0]}': " . $e->getMessage());
                        }
                    }
                }
                DB::statement("COMMIT");
                Log::info('Categorías iniciales insertadas');
            } else {
                Log::info('Ya existen categorías - omitiendo inserción');
            }

            // Insertar autores de ejemplo si no existen
            $countAutores = "SELECT COUNT(*) as count FROM AUTORES";
            $resultAutores = DB::selectOne($countAutores);

            if ($resultAutores->count == 0) {
                $autores = [
                    ['Gabriel', 'García Márquez', 'Colombiana', '1927-03-06', 'Autor de Cien años de soledad'],
                    ['Isabel', 'Allende', 'Chilena', '1942-08-02', 'Autora de La casa de los espíritus'],
                    ['Stephen', 'Hawking', 'Británica', '1942-01-08', 'Físico teórico y autor de Breve historia del tiempo'],
                    ['Yuval', 'Harari', 'Israelí', '1976-02-24', 'Autor de Sapiens: De animales a dioses']
                ];

                foreach ($autores as $autor) {
                    try {
                        $insertSql = "INSERT INTO AUTORES (NOMBRE, APELLIDO, NACIONALIDAD, FECHA_NACIMIENTO, BIOGRAFIA, ESTADO, FECHA_CREACION, FECHA_MODIFICACION) 
                                 VALUES (:nombre, :apellido, :nacionalidad, TO_DATE(:fecha_nacimiento, 'YYYY-MM-DD'), :biografia, 'A', SYSDATE, SYSDATE)";
                        DB::statement($insertSql, [
                            'nombre' => $autor[0],
                            'apellido' => $autor[1],
                            'nacionalidad' => $autor[2],
                            'fecha_nacimiento' => $autor[3],
                            'biografia' => $autor[4]
                        ]);
                        Log::info("Autor '{$autor[0]} {$autor[1]}' insertado");
                    } catch (Exception $e) {
                        if (strpos($e->getMessage(), 'ORA-00001') !== false) {
                            Log::info("Autor '{$autor[0]} {$autor[1]}' ya existe");
                        } else {
                            Log::warning("Error insertando autor '{$autor[0]} {$autor[1]}': " . $e->getMessage());
                        }
                    }
                }
                DB::statement("COMMIT");
                Log::info('Autores iniciales insertados');
            } else {
                Log::info('Ya existen autores - omitiendo inserción');
            }

            $countLibros = "SELECT COUNT(*) as count FROM LIBROS";
            $resultLibros = DB::selectOne($countLibros);

            if ($resultLibros->count == 0) {
                $autores = DB::select("SELECT ID, NOMBRE, APELLIDO FROM AUTORES WHERE ESTADO = 'A'");
                $categorias = DB::select("SELECT ID, NOMBRE FROM CATEGORIAS WHERE ESTADO = 'A'");

                $mapAutores = [];
                foreach ($autores as $a) {
                    $mapAutores[$a->nombre . ' ' . $a->apellido] = $a->id;
                }
                $mapCategorias = [];
                foreach ($categorias as $c) {
                    $mapCategorias[$c->nombre] = $c->id;
                }

                $libros = [
                    [
                        'titulo' => 'Cien años de soledad',
                        'isbn' => '9780307474728',
                        'autor' => 'Gabriel García Márquez',
                        'categoria' => 'Ficción',
                        'editorial' => 'Sudamericana',
                        'fecha_publicacion' => '1967-05-30',
                        'numero_paginas' => 471,
                        'idioma' => 'Español',
                        'resumen' => 'Obra maestra del realismo mágico.',
                        'ubicacion' => 'A1',
                        'cantidad_total' => 5
                    ],
                    [
                        'titulo' => 'La casa de los espíritus',
                        'isbn' => '9788408172177',
                        'autor' => 'Isabel Allende',
                        'categoria' => 'Ficción',
                        'editorial' => 'Plaza & Janés',
                        'fecha_publicacion' => '1982-01-01',
                        'numero_paginas' => 400,
                        'idioma' => 'Español',
                        'resumen' => 'Saga familiar y política en Chile.',
                        'ubicacion' => 'A2',
                        'cantidad_total' => 3
                    ],
                    [
                        'titulo' => 'Breve historia del tiempo',
                        'isbn' => '9780553176988',
                        'autor' => 'Stephen Hawking',
                        'categoria' => 'Ciencia',
                        'editorial' => 'Bantam Books',
                        'fecha_publicacion' => '1988-04-01',
                        'numero_paginas' => 256,
                        'idioma' => 'Español',
                        'resumen' => 'Introducción a la cosmología moderna.',
                        'ubicacion' => 'C1',
                        'cantidad_total' => 4
                    ],
                    [
                        'titulo' => 'Sapiens: De animales a dioses',
                        'isbn' => '9788499924213',
                        'autor' => 'Yuval Harari',
                        'categoria' => 'Historia',
                        'editorial' => 'Debate',
                        'fecha_publicacion' => '2011-01-01',
                        'numero_paginas' => 496,
                        'idioma' => 'Español',
                        'resumen' => 'Historia de la humanidad desde la prehistoria.',
                        'ubicacion' => 'H1',
                        'cantidad_total' => 2
                    ]
                ];

                foreach ($libros as $libro) {
                    try {
                        $autor_id = $mapAutores[$libro['autor']] ?? null;
                        $categoria_id = $mapCategorias[$libro['categoria']] ?? null;
                        if (!$autor_id || !$categoria_id) {
                            Log::warning("No se encontró autor o categoría para el libro '{$libro['titulo']}'");
                            continue;
                        }
                        $sql = "
                        DECLARE
                            v_id NUMBER;
                        BEGIN
                            v_id := PKG_LIBROS.CREAR_LIBRO(
                                :titulo,
                                :isbn,
                                :autor_id,
                                :categoria_id,
                                :editorial,
                                TO_DATE(:fecha_publicacion, 'YYYY-MM-DD'),
                                :numero_paginas,
                                :idioma,
                                :resumen,
                                :ubicacion,
                                :cantidad_total
                            );
                        END;";
                        DB::statement($sql, [
                            'titulo' => $libro['titulo'],
                            'isbn' => $libro['isbn'],
                            'autor_id' => $autor_id,
                            'categoria_id' => $categoria_id,
                            'editorial' => $libro['editorial'],
                            'fecha_publicacion' => $libro['fecha_publicacion'],
                            'numero_paginas' => $libro['numero_paginas'],
                            'idioma' => $libro['idioma'],
                            'resumen' => $libro['resumen'],
                            'ubicacion' => $libro['ubicacion'],
                            'cantidad_total' => $libro['cantidad_total']
                        ]);
                        Log::info("Libro '{$libro['titulo']}' insertado");
                    } catch (Exception $e) {
                        if (strpos($e->getMessage(), 'ORA-00001') !== false) {
                            Log::info("Libro '{$libro['titulo']}' ya existe");
                        } else {
                            Log::warning("Error insertando libro '{$libro['titulo']}': " . $e->getMessage());
                        }
                    }
                }
                DB::statement("COMMIT");
                Log::info('Libros de ejemplo insertados');
            } else {
                Log::info('Ya existen libros - omitiendo inserción');
            }
        } catch (Exception $e) {
            Log::warning('Error verificando/insertando datos iniciales: ' . $e->getMessage());
        }
    }

    private function tableExists($tableName)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM user_tables WHERE table_name = ?";
            $result = DB::selectOne($sql, [strtoupper($tableName)]);
            return $result->count > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
