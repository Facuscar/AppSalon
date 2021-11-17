<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;



class LoginController{
    public static function login(Router $router){
        $alertas = [];

        if(isset($_SESSION)){
            if(isset($_SESSION['admin']) && $_SESSION['admin'] === "1"){
                header('Location: /admin');
            }
            else if( isset($_SESSION['login']) && $_SESSION['login'] == true){
                header('Location: /cita');
            }
        } 
        
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
           $auth = new Usuario($_POST);
        
           //Se valida que los campos estén completos
           $alertas = $auth->validarLogin();

           if(empty($alertas)){
               $usuario = Usuario::where('email', $auth->email);

               if($usuario){//Si existe el usuario
                   //verifiamos el password
                  if($usuario->comprobarPasswordAndVerificado($auth->password)) {
                      //Autenticar el usuario
                      session_start();

                      $_SESSION['id'] = $usuario->id;
                      $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
                      $_SESSION['email'] = $usuario->email;
                      $_SESSION['login'] = true;

                      //Redireccionamiento
                      if($usuario->admin == 1){
                        $_SESSION['admin'] = $usuario->admin ?? null;
                        header('Location: /admin');
                      } else{
                          header('Location: /cita');
                      }

                      debuguear($_SESSION);
                  }
               } else{
                   Usuario::setAlerta('error','usuario no encontrado');
               }
           }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/login', [
            'alertas' => $alertas
        ]);
    }

    public static function logout(){
        if(!isset($_SESSION)) 
        { 
            session_start(); 
        } 

        $_SESSION = [];

        header('Location: /');
    }

    public static function olvide(Router $router){

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if(empty($alertas)){
                $usuario = Usuario::where('email', $auth->email);

                if($usuario && $usuario->confirmado === "1"){
                    //Generamos un token de un solo uso
                    $usuario->crearToken();
                    $usuario->guardar();

                    Usuario::setAlerta('exito', 'Enviamos las instrucciones a tu correo electronico');

                    //Enviar el email
                    $email = new Email ($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();
                } else{
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                }

                $alertas = Usuario::getAlertas();
            }
        }

        $router->render('auth/olvide-password', [
            'alertas' => $alertas
        ]);
    }

    public static function recuperar(Router $router){

        $alertas = [];
        $error = false;

        $token = s($_GET['token']);

        //Buscar usuario por su token
        $usuario = Usuario::where('token',$token);

        if(empty($usuario)){
            Usuario::setAlerta('error', 'Token inválido');
            $error = true;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            //Leer el nuevo password y guardarlo
            $password = new Usuario($_POST);
            $alertas = $password->validarPassword();

            if(empty($alertas)){
                $usuario->password = null;

                $usuario->password = $password->password;
                $usuario->hashPassword();
                $usuario->token = null;

                $resultado = $usuario->guardar();
                if($resultado){
                    header('Location: /');
                }
            }
        }

        $alertas = Usuario::getAlertas();
        
        $router->render('auth/recuperar-password',[
            'alertas'=> $alertas,
            'error' => $error
        ]);
    }

    public static function crear(Router $router){
    
        $usuario = new Usuario;

        //Alertas vacias
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            //Revisar que alertas esté vacio
            if(empty($alertas)){
                //Verificar que el usuario no esté registrado
                $resultado = $usuario->existeUsuario();

                if($resultado->num_rows){
                    $alertas = Usuario::getAlertas();
                } else{
                    //No está registrado, entonces hasheamos el password
                    $usuario->hashPassword();
                    
                    //Generamos un token único para el registro
                    $usuario->crearToken();

                    //Enviar el email
                    $email = new Email($usuario->email,$usuario->nombre,  $usuario->token);

                    $email->enviarConfirmacion();

                    //Crear el usuario
                    $resultado = $usuario->guardar();
                    if($resultado){
                        header('Location: /mensaje');
                    }
                }
            }
        }

        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function mensaje(Router $router){
        $router->render('auth/mensaje');
    }

    public static function confirmar(Router $router){

        $alertas = [];

        $token = s($_GET['token']);

        $usuario = Usuario::where("token", $token);

        if(empty($usuario)){
            //Mostrar mensaje de error
            Usuario::setAlerta('error', 'Token no válido');
        } 
        else{
            //Modificar a usuario confirmado
            $usuario->confirmado = "1";
            $usuario->token = null;

            //Muestra el mensaje de exito
            Usuario::setAlerta('exito', 'Cuenta confirmada correctamente');
            $usuario->guardar();
        }

        //Obtener las alertas
        $alertas = Usuario::getAlertas();

        //Renderizar la vista
        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]);
    }

}

?>