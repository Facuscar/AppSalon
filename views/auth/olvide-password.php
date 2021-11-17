<h1 class="nombre-pagina">Olvide mi contraseña</h1>
<p class="descripcion-pagina">Reestablece tu contraseña escribiendo tu correo a continuación</p>

<?php include_once __DIR__ . "/../templates/alertas.php" ?>


<form  class="formulario" action="/olvide" method="POST">
    <div class="campo">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="email">
    </div>  

    <input type="submit" class="boton" value="Enviar instrucciones">
</form>

<div class="acciones">
    <a href="/crear-cuenta">¿Aún no tienes una cuenta? Crear una</a>
    <a href="olvide">¿Ya tienes una cuenta? Inicia sesión</a>
</div>