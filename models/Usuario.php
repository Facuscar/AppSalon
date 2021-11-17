<?php

namespace Model;


class Usuario extends ActiveRecord{

    //Base de datos
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id','nombre', 'apellido','email','telefono','password', 'token', 'confirmado', 'admin'];

    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $telefono;
    public $password;
    public $token;
    public $confirmado;
    public $admin;

    public function __construct($args = []){
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->token = $args['token'] ?? null;
        $this->confirmado = $args['confirmado'] ?? '0';
        $this->admin = $args['admin'] ?? '0';
    }

    //Mensajes de validación para la creación de una cuenta
    public function validarNuevaCuenta(){
        if(!$this->nombre){
            self::$alertas['error'][]='Tu nombre es obligatorio';
        }

        if(!$this->apellido){
            self::$alertas['error'][]='Tu apellido es obligatorio';
        }

        if(!$this->email){
            self::$alertas['error'][]='Tu email es obligatorio';
        }

        if(!$this->telefono){
            self::$alertas['error'][]='Tu telefono es obligatorio';
        }

        if(!$this->password){
            self::$alertas['error'][]='Una contraseña es obligatoria';
        }

        if(strlen($this->password)<8){
            self::$alertas['error'][]="Tu contraseña debe tener al menos 8 caracteres";
        }



        return self::$alertas;
    }

    public function validarLogin(){
        if(!$this->email){
            self::$alertas['error'][] = 'El email es obligatorio';
        }

        if(!$this->password){
            self::$alertas['error'][] = 'La contraseña es obligatoria';
        }

        return self::$alertas;
    }

    public function validarEmail(){
        if(!$this->email){
            self::$alertas['error'][] = 'El email es obligatorio';
        }

        return self::$alertas;
    }

    public function validarPassword(){
        if(!$this->password){
            self::$alertas['error'][] = 'La contraseña es obligatoria';
        }

        if(strlen($this->password)<8){
            self::$alertas['error'][] = 'La contraseña debe tener al menos 8 caracteras';
        }

        return self::$alertas;
    }


    //Revisa si el usuario ya existe
    public function existeUsuario(){
        $query = "SELECT * FROM " .self::$tabla . " WHERE email = '". $this->email ."' LIMIT 1";

        $resultado = self::$db->query($query);

        if($resultado->num_rows){
            self::$alertas['error'][] = 'El usuario ya esta registrado';
        }
        return $resultado;
    }

    public function hashPassword(){
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function crearToken(){
        $this->token = uniqid();
    }

    public function comprobarPasswordAndVerificado ($password){
        $resultado = password_verify($password, $this->password);

        if(!$resultado){
             self::$alertas['error'][] = "La contraseña es incorrecta";
        } elseif(!$this->confirmado){
            self::$alertas['error'][] = "Tu cuenta no está confirmada";
        }
        else{
            return true;
        }
    }
}





?>