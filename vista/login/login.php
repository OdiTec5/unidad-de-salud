<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · Unidad de Salud</title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body class="pagina-auth">
    <div class="contenedor-login">
        <div class="marca-login">Unidad de Salud</div>
        <h1 class="visualmente-oculto">Iniciar sesión</h1>

        <?php if (!empty($error)): ?>
            <p class="mensaje-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="index.php?ruta=login">
            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" placeholder="nombre@correo.com" value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required autofocus>

            <label for="password">Contraseña</label>
            <div class="campo-password">
                <input type="password" id="password" name="password" placeholder="Contraseña" required>
                <button type="button" class="boton-mostrar-password" data-objetivo="password" aria-label="Mostrar contraseña">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </button>
            </div>

            <button type="submit">Entrar</button>
        </form>

        <p class="enlace-secundario">
            <a href="#" id="enlace-recuperar-password">¿Olvidaste tu contraseña?</a>
        </p>
    </div>

    <div id="modal-recuperar-password" class="modal-fondo">
        <div class="modal-caja">
            <div class="modal-cabecera">
                <h2>Recuperar contraseña</h2>
                <button type="button" id="boton-cerrar-modal-recuperar" class="modal-cerrar" aria-label="Cerrar">&times;</button>
            </div>

            <div class="recuperar-cuerpo">
                <p id="recuperar-mensaje" class="mensaje-error" style="display:none;"></p>

                <div id="recuperar-paso-correo" class="recuperar-paso">
                    <p class="texto-atenuado">Ingresa tu correo electrónico y te daremos un código de 9 dígitos.</p>
                    <div class="campo">
                        <label for="recuperar-correo">Correo electrónico</label>
                        <input type="email" id="recuperar-correo" placeholder="nombre@correo.com" required>
                    </div>
                    <button type="button" id="boton-recuperar-enviar-codigo" class="boton-enviar">Enviar código</button>
                </div>

                <div id="recuperar-paso-codigo" class="recuperar-paso" style="display:none;">
                    <p class="texto-atenuado">Ingresa el código de 9 dígitos junto con tu nueva contraseña.</p>
                    <div class="campo">
                        <label for="recuperar-codigo">Código de 9 dígitos</label>
                        <input type="text" id="recuperar-codigo" inputmode="numeric" maxlength="9" placeholder="000000000" required>
                    </div>
                    <div class="campo">
                        <label for="recuperar-password-nueva">Contraseña nueva</label>
                        <input type="password" id="recuperar-password-nueva" placeholder="Contraseña nueva" required>
                    </div>
                    <div class="campo">
                        <label for="recuperar-password-confirmar">Confirmar contraseña</label>
                        <input type="password" id="recuperar-password-confirmar" placeholder="Confirmar contraseña" required>
                    </div>
                    <button type="button" id="boton-recuperar-confirmar" class="boton-enviar">Restablecer contraseña</button>
                    <p class="enlace-secundario">
                        <a href="#" id="enlace-recuperar-reenviar">¿No te llegó? Enviar otro código</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
