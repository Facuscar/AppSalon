<?php 

namespace Model;

class Servicio extends ActiveRecord{
    //Base de datos
    protected static $tabla = 'servicios';
    protected static $columnasDB = ['id', 'nombre','precio'];
    public $id;
    public $nombre;
    public $precio;

    public function __construct($args = []){
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->precio = $args ['precio'] ?? '';
    }

    public function validar(){
        if(!$this->nombre){
            self::$alertas['error'][]='El nombre no puede ir vacio';
        }

        if(!$this->precio){
            self::$alertas['error'][] = 'El precio no puede ir vacio';
        }

        if(!is_numeric($this->precio)){
            self::$alertas['error'][] = 'El formato del precio debe ir en numeros';
        }
        return self::$alertas;
    }
}