<?php

namespace App\Http\Controllers;

use App\Http\Middleware\VerificarSesionUsuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use OCILob;
use PDO;

use Illuminate\Routing\Controller as BaseController;

class LibroController extends BaseController
{

    public function __construct()
    {
        $this->middleware(VerificarSesionUsuario::class);
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $usuario = [
                'id'    => session('usuario_id'),
                'nombre' => session('usuario_nombre'),
                'email' => session('usuario_email'),
                'rol'   => session('usuario_rol'),
                'rol_id' => session('usuario_rol_id')
            ];

            $filtros = [
                'titulo'       => $request->input('titulo', ''),
                'autor'        => $request->input('autor', ''),
                'categoria_id' => $request->input('categoria_id', '')
            ];

            $pdo = DB::getPdo();

            $categorias = [];
            $stmtCat = $pdo->prepare("
            DECLARE
                v_cursor SYS_REFCURSOR;
            BEGIN
                PKG_LIBROS.OBTENER_CATEGORIAS(v_cursor);
                :cursor := v_cursor;
            END;
        ");
            $stmtCat->bindParam(':cursor', $cursorCat, PDO::PARAM_STMT);
            $stmtCat->execute();

            oci_execute($cursorCat);
            while (($row = oci_fetch_assoc($cursorCat)) != false) {
                $categorias[] = [
                    'ID' => $row['ID'],
                    'NOMBRE' => $row['NOMBRE']
                ];
            }
            oci_free_statement($cursorCat);

            $libros = [];
            $stmt = $pdo->prepare("
            DECLARE
                v_cursor SYS_REFCURSOR;
            BEGIN
                PKG_LIBROS.OBTENER_LIBROS(v_cursor);
                :cursor := v_cursor;
            END;
        ");
            $stmt->bindParam(':cursor', $cursor, PDO::PARAM_STMT);
            $stmt->execute();

            oci_execute($cursor);
            while (($row = oci_fetch_assoc($cursor)) != false) {
                $coincide = true;
                if ($filtros['titulo'] && stripos($row['TITULO'], $filtros['titulo']) === false) {
                    $coincide = false;
                }
                if ($filtros['autor']) {
                    $autorCompleto = $row['AUTOR_NOMBRE'] . ' ' . $row['AUTOR_APELLIDO'];
                    if (stripos($autorCompleto, $filtros['autor']) === false) {
                        $coincide = false;
                    }
                }
                if ($filtros['categoria_id'] && $row['CATEGORIA_ID'] != $filtros['categoria_id']) {
                    $coincide = false;
                }
                if ($coincide) {
                    $libros[] = $row;
                }
            }
            oci_free_statement($cursor);

            return view('libros.index', [
                'usuario'    => $usuario,
                'libros'     => $libros,
                'categorias' => $categorias,
                'filtros'    => $filtros
            ]);
        } catch (Exception $e) {
            return back()->with('error', 'Error al cargar el catálogo de libros: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Session::has('usuario_id')) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder al sistema');
        }
        try {
            $pdo = DB::getPdo();

            $usuario = [
                'id'    => session('usuario_id'),
                'nombre' => session('usuario_nombre'),
                'email' => session('usuario_email'),
                'rol'   => session('usuario_rol'),
                'rol_id' => session('usuario_rol_id')
            ];

            $categorias = [];
            $stmtCat = $pdo->prepare("
            DECLARE
                v_cursor SYS_REFCURSOR;
            BEGIN
                PKG_LIBROS.OBTENER_CATEGORIAS(v_cursor);
                :cursor := v_cursor;
            END;
        ");
            $stmtCat->bindParam(':cursor', $cursorCat, PDO::PARAM_STMT);
            $stmtCat->execute();
            oci_execute($cursorCat);
            while (($row = oci_fetch_assoc($cursorCat)) != false) {
                $categorias[] = [
                    'ID' => $row['ID'],
                    'NOMBRE' => $row['NOMBRE']
                ];
            }
            oci_free_statement($cursorCat);

            $autores = [];
            $stmtAut = $pdo->prepare("
            DECLARE
                v_cursor SYS_REFCURSOR;
            BEGIN
                PKG_LIBROS.OBTENER_AUTORES(v_cursor);
                :cursor := v_cursor;
            END;
        ");
            $stmtAut->bindParam(':cursor', $cursorAut, PDO::PARAM_STMT);
            $stmtAut->execute();
            oci_execute($cursorAut);
            while (($row = oci_fetch_assoc($cursorAut)) != false) {
                $autores[] = [
                    'ID' => $row['ID'],
                    'NOMBRE' => $row['NOMBRE'],
                    'APELLIDO' => $row['APELLIDO']
                ];
            }
            oci_free_statement($cursorAut);

            return view('libros.create', compact('categorias', 'autores', 'usuario'));
        } catch (Exception $e) {
            return back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Session::has('usuario_id')) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder al sistema');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:50',
            'autor_id' => 'required|integer',
            'categoria_id' => 'required|integer',
            'editorial' => 'nullable|string|max:100',
            'fecha_publicacion' => 'nullable|date',
            'numero_paginas' => 'nullable|integer',
            'idioma' => 'nullable|string|max:50',
            'resumen' => 'nullable|string',
            'ubicacion' => 'nullable|string|max:50',
            'cantidad_total' => 'required|integer|min:1'
        ]);

        try {
            $pdo = DB::getPdo();
            $sql = "
            DECLARE
                v_id NUMBER;
            BEGIN
                v_id := PKG_LIBROS.CREAR_LIBRO(
                    :titulo, :isbn, :autor_id, :categoria_id, :editorial,
                    TO_DATE(:fecha_publicacion, 'YYYY-MM-DD'), :numero_paginas, :idioma,
                    :resumen, :ubicacion, :cantidad_total
                );
            END;
        ";
            $pdo->prepare($sql)->execute([
                'titulo' => $request->titulo,
                'isbn' => $request->isbn,
                'autor_id' => $request->autor_id,
                'categoria_id' => $request->categoria_id,
                'editorial' => $request->editorial,
                'fecha_publicacion' => $request->fecha_publicacion,
                'numero_paginas' => $request->numero_paginas,
                'idioma' => $request->idioma,
                'resumen' => $request->resumen,
                'ubicacion' => $request->ubicacion,
                'cantidad_total' => $request->cantidad_total
            ]);
            return redirect()->route('libros.index')->with('success', 'Libro creado correctamente.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error al crear libro: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!Session::has('usuario_id')) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder al sistema');
        }
        try {
            $pdo = DB::getPdo();

            $usuario = [
                'id'    => session('usuario_id'),
                'nombre' => session('usuario_nombre'),
                'email' => session('usuario_email'),
                'rol'   => session('usuario_rol'),
                'rol_id' => session('usuario_rol_id')
            ];

            $stmt = $pdo->prepare("
            DECLARE
                v_cursor SYS_REFCURSOR;
            BEGIN
                PKG_LIBROS.OBTENER_LIBRO_POR_ID(:id, v_cursor);
                :cursor := v_cursor;
            END;
        ");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':cursor', $cursor, PDO::PARAM_STMT);
            $stmt->execute();

            oci_execute($cursor);
            $libro = oci_fetch_assoc($cursor);
            oci_free_statement($cursor);

            if ($libro && isset($libro['RESUMEN']) && $libro['RESUMEN'] instanceof OCILob) {
                $libro['RESUMEN'] = $libro['RESUMEN']->load();
            }

            if (!$libro) {
                return back()->with('error', 'Libro no encontrado.');
            }

            return view('libros.show', compact('libro', 'usuario'));
        } catch (Exception $e) {
            return back()->with('error', 'Error al obtener libro: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (!Session::has('usuario_id')) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder al sistema');
        }
        try {
            $pdo = DB::getPdo();

            $usuario = [
                'id'    => session('usuario_id'),
                'nombre' => session('usuario_nombre'),
                'email' => session('usuario_email'),
                'rol'   => session('usuario_rol'),
                'rol_id' => session('usuario_rol_id')
            ];

            $stmt = $pdo->prepare("
            DECLARE
                v_cursor SYS_REFCURSOR;
            BEGIN
                PKG_LIBROS.OBTENER_LIBRO_POR_ID(:id, v_cursor);
                :cursor := v_cursor;
            END;
        ");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':cursor', $cursor, PDO::PARAM_STMT);
            $stmt->execute();
            oci_execute($cursor);
            $libro = oci_fetch_assoc($cursor);
            oci_free_statement($cursor);

            if ($libro && isset($libro['RESUMEN']) && $libro['RESUMEN'] instanceof OCILob) {
                $libro['RESUMEN'] = $libro['RESUMEN']->load();
            }

            $categorias = [];
            $stmtCat = $pdo->prepare("
            DECLARE
                v_cursor SYS_REFCURSOR;
            BEGIN
                PKG_LIBROS.OBTENER_CATEGORIAS(v_cursor);
                :cursor := v_cursor;
            END;
        ");
            $stmtCat->bindParam(':cursor', $cursorCat, PDO::PARAM_STMT);
            $stmtCat->execute();
            oci_execute($cursorCat);
            while (($row = oci_fetch_assoc($cursorCat)) != false) {
                $categorias[] = [
                    'ID' => $row['ID'],
                    'NOMBRE' => $row['NOMBRE']
                ];
            }
            oci_free_statement($cursorCat);

            $autores = [];
            $stmtAut = $pdo->prepare("
            DECLARE
                v_cursor SYS_REFCURSOR;
            BEGIN
                PKG_LIBROS.OBTENER_AUTORES(v_cursor);
                :cursor := v_cursor;
            END;
        ");
            $stmtAut->bindParam(':cursor', $cursorAut, PDO::PARAM_STMT);
            $stmtAut->execute();
            oci_execute($cursorAut);
            while (($row = oci_fetch_assoc($cursorAut)) != false) {
                $autores[] = [
                    'ID' => $row['ID'],
                    'NOMBRE' => $row['NOMBRE'],
                    'APELLIDO' => $row['APELLIDO']
                ];
            }
            oci_free_statement($cursorAut);

            return view('libros.edit', compact('libro', 'categorias', 'autores', 'usuario'));
        } catch (Exception $e) {
            return back()->with('error', 'Error al cargar libro: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!Session::has('usuario_id')) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder al sistema');
        }
        $request->validate([
            'titulo' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:50',
            'autor_id' => 'required|integer',
            'categoria_id' => 'required|integer',
            'editorial' => 'nullable|string|max:100',
            'fecha_publicacion' => 'nullable|date',
            'numero_paginas' => 'nullable|integer',
            'idioma' => 'nullable|string|max:50',
            'resumen' => 'nullable|string',
            'ubicacion' => 'nullable|string|max:50',
            'cantidad_total' => 'required|integer|min:1'
        ]);

        try {
            $pdo = DB::getPdo();
            $sql = "
            BEGIN
                PKG_LIBROS.ACTUALIZAR_LIBRO(
                    :id, :titulo, :isbn, :autor_id, :categoria_id, :editorial,
                    TO_DATE(:fecha_publicacion, 'YYYY-MM-DD'), :numero_paginas, :idioma,
                    :resumen, :ubicacion, :cantidad_total
                );
            END;
        ";
            $pdo->prepare($sql)->execute([
                'id' => $id,
                'titulo' => $request->titulo,
                'isbn' => $request->isbn,
                'autor_id' => $request->autor_id,
                'categoria_id' => $request->categoria_id,
                'editorial' => $request->editorial,
                'fecha_publicacion' => $request->fecha_publicacion,
                'numero_paginas' => $request->numero_paginas,
                'idioma' => $request->idioma,
                'resumen' => $request->resumen,
                'ubicacion' => $request->ubicacion,
                'cantidad_total' => $request->cantidad_total
            ]);
            return redirect()->route('libros.index')->with('success', 'Libro actualizado correctamente.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error al actualizar libro: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!Session::has('usuario_id')) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder al sistema');
        }
        try {
            $pdo = DB::getPdo();
            $sql = "
            BEGIN
                PKG_LIBROS.ELIMINAR_LIBRO(:id);
            END;
        ";
            $pdo->prepare($sql)->execute(['id' => $id]);
            return redirect()->route('libros.index')->with('success', 'Libro eliminado correctamente.');
        } catch (Exception $e) {
            return back()->with('error', 'Error al eliminar libro: ' . $e->getMessage());
        }
    }
}
