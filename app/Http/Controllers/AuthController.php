<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PDO;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        try {
            $pdo = DB::getPdo();
            $usuario_id = null;

            $stmt = $pdo->prepare('BEGIN PKG_USUARIOS.AUTENTICAR_USUARIO(:email, :password, :usuario_id); END;');
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 10);
            $stmt->execute();

            if ($usuario_id) {
                $user_id = null;
                $nombre = null;
                $email = null;
                $rol_id = null;
                $rol_nombre = null;

                $stmt2 = $pdo->prepare('BEGIN PKG_USUARIOS.OBTENER_USUARIO_COMPLETO(:p_usuario_id, :user_id, :nombre, :email, :rol_id, :rol_nombre); END;');
                $stmt2->bindParam(':p_usuario_id', $usuario_id);
                $stmt2->bindParam(':user_id', $user_id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 10);
                $stmt2->bindParam(':nombre', $nombre, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 100);
                $stmt2->bindParam(':email', $email, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 100);
                $stmt2->bindParam(':rol_id', $rol_id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 10);
                $stmt2->bindParam(':rol_nombre', $rol_nombre, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 100);
                $stmt2->execute();

                if ($user_id) {
                    Session::put([
                        'usuario_id' => $user_id,
                        'usuario_nombre' => $nombre,
                        'usuario_email' => $email,
                        'usuario_rol_id' => $rol_id,
                        'usuario_rol_nombre' => $rol_nombre
                    ]);

                    return redirect()->route('dashboard')->with('success', '¡Bienvenido, ' . $nombre . '!');
                }
            }

            return back()->withInput()->with('error', 'Credenciales incorrectas.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error en el sistema. Intente nuevamente.');
        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'password' => 'required|string|min:4',
            'rol_id' => 'required|integer'
        ]);

        $nombre = $request->input('nombre');
        $email = $request->input('email');
        $password = $request->input('password');
        $rol_id = $request->input('rol_id');

        try {
            $pdo = DB::getPdo();
            $usuario_id = null;

            $stmt = $pdo->prepare('BEGIN PKG_USUARIOS.REGISTRAR_USUARIO(:nombre, :email, :password, :rol_id, :usuario_id); END;');
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':rol_id', $rol_id);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 10);
            $stmt->execute();

            if ($usuario_id) {
                $user_id = null;
                $nombre = null;
                $email = null;
                $rol_id = null;
                $rol_nombre = null;

                $stmt2 = $pdo->prepare('BEGIN PKG_USUARIOS.OBTENER_USUARIO_COMPLETO(:p_usuario_id, :user_id, :nombre, :email, :rol_id, :rol_nombre); END;');
                $stmt2->bindParam(':p_usuario_id', $usuario_id);
                $stmt2->bindParam(':user_id', $user_id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 10);
                $stmt2->bindParam(':nombre', $nombre, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 100);
                $stmt2->bindParam(':email', $email, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 100);
                $stmt2->bindParam(':rol_id', $rol_id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 10);
                $stmt2->bindParam(':rol_nombre', $rol_nombre, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 100);
                $stmt2->execute();

                if ($user_id) {
                    Session::put([
                        'usuario_id' => $user_id,
                        'usuario_nombre' => trim($nombre),
                        'usuario_email' => trim($email),
                        'usuario_rol_id' => $rol_id,
                        'usuario_rol_nombre' => trim($rol_nombre)
                    ]);

                    return redirect()->route('dashboard')->with('success', '¡Registro exitoso! Bienvenido, ' . trim($nombre) . '.');
                }
            }

            return back()->withInput()->with('error', 'No se pudo registrar el usuario.');
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            if (strpos($errorMessage, 'ORA-20001') !== false) {
                return back()->withInput()->with('error', 'El email ya está registrado.');
            } elseif (strpos($errorMessage, 'ORA-20002') !== false) {
                return back()->withInput()->with('error', 'El rol especificado no existe.');
            } else {
                return back()->withInput()->with('error', 'Error al registrar usuario. Intente nuevamente.');
            }
        }
    }

    public function showLogin()
    {
        if (Session::has('usuario_id')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function showRegister()
    {
        if (Session::has('usuario_id')) {
            return redirect()->route('dashboard');
        }

        try {
            $pdo = DB::getPdo();

            $stmt = $pdo->prepare('
                DECLARE
                    v_cursor SYS_REFCURSOR;
                    v_id NUMBER;
                    v_nombre VARCHAR2(100);
                    v_result CLOB := \'\';
                    v_first BOOLEAN := TRUE;
                BEGIN
                    PKG_USUARIOS.OBTENER_ROLES_ACTIVOS(v_cursor);
                    
                    LOOP
                        FETCH v_cursor INTO v_id, v_nombre;
                        EXIT WHEN v_cursor%NOTFOUND;
                        
                        IF NOT v_first THEN
                            v_result := v_result || \'|\';
                        END IF;
                        v_result := v_result || v_id || \',\' || v_nombre;
                        v_first := FALSE;
                    END LOOP;
                    
                    CLOSE v_cursor;
                    
                    :result := v_result;
                END;
            ');

            $result = '';
            $stmt->bindParam(':result', $result, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);
            $stmt->execute();

            $roles = [];
            if (!empty($result)) {
                $rolesPairs = explode('|', $result);
                foreach ($rolesPairs as $pair) {
                    if (!empty($pair)) {
                        $parts = explode(',', $pair, 2);
                        if (count($parts) == 2) {
                            $roles[] = [
                                'id' => trim($parts[0]),
                                'nombre' => trim($parts[1])
                            ];
                        }
                    }
                }
            }

            return view('auth.register', compact('roles'));
        } catch (Exception $e) {
            return view('auth.register', [
                'roles' => [],
                'error' => 'Error al cargar los roles. Por favor, contacte al administrador.'
            ]);
        }
    }

    public function dashboard()
    {
        if (!Session::has('usuario_id')) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder al sistema');
        }

        try {
            $usuarioId = Session::get('usuario_id');

            $pdo = DB::getPdo();

            $user_id = null;
            $nombre = null;
            $email = null;
            $rol_id = null;
            $rol_nombre = null;
            $ultimo_acceso = null;
            $fecha_creacion = null;

            $stmt = $pdo->prepare('BEGIN PKG_USUARIOS.OBTENER_USUARIO_COMPLETO(:p_usuario_id, :user_id, :nombre, :email, :rol_id, :rol_nombre); END;');
            $stmt->bindParam(':p_usuario_id', $usuarioId);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 10);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 100);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 100);
            $stmt->bindParam(':rol_id', $rol_id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 10);
            $stmt->bindParam(':rol_nombre', $rol_nombre, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 100);
            $stmt->execute();

            if (!$user_id) {
                return $this->logout();
            }

            Session::put('usuario_nombre', $nombre);
            Session::put('usuario_email', $email);
            Session::put('usuario_rol', $rol_nombre);
            Session::put('usuario_rol_id', $rol_id);

            $usuarioData = [
                'id' => $user_id,
                'nombre' => $nombre,
                'email' => $email,
                'rol' => $rol_nombre,
                'rol_id' => $rol_id,
                'ultimo_acceso' => $ultimo_acceso,
                'fecha_creacion' => $fecha_creacion
            ];

            return view('dashboard', ['usuario' => $usuarioData]);
        } catch (Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Error al cargar el dashboard. Por favor, inicie sesión nuevamente.');
        }
    }

    public function logout()
    {
        Session::flush();

        return redirect()->route('login')
            ->with('success', 'Ha cerrado sesión correctamente');
    }
}
