document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.boton-mostrar-password').forEach(function (boton) {
        boton.addEventListener('click', function () {
            var campo = document.getElementById(boton.dataset.objetivo);
            if (!campo) {
                return;
            }
            campo.type = campo.type === 'password' ? 'text' : 'password';
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var botonHamburguesa = document.getElementById('boton-hamburguesa');
    var barraLateral = document.querySelector('.barra-lateral');
    var fondo = document.getElementById('barra-lateral-fondo');

    if (!botonHamburguesa || !barraLateral) {
        return;
    }

    var esMovil = window.matchMedia('(max-width: 780px)').matches;
    var preferenciaGuardada = localStorage.getItem('sidebarColapsado');

    // En móvil, si el usuario nunca lo tocó antes, arranca cerrado (es una gaveta que tapa todo
    // el contenido); en escritorio arranca abierto, como parte fija del layout.
    var colapsadoInicial = preferenciaGuardada !== null ? preferenciaGuardada === '1' : esMovil;

    if (colapsadoInicial) {
        barraLateral.classList.add('colapsada');
    }

    function actualizarFondo() {
        if (!fondo) {
            return;
        }
        fondo.classList.toggle('visible', !barraLateral.classList.contains('colapsada'));
    }

    function alternar() {
        barraLateral.classList.toggle('colapsada');
        localStorage.setItem('sidebarColapsado', barraLateral.classList.contains('colapsada') ? '1' : '0');
        actualizarFondo();
    }

    actualizarFondo();
    botonHamburguesa.addEventListener('click', alternar);

    if (fondo) {
        fondo.addEventListener('click', function () {
            if (!barraLateral.classList.contains('colapsada')) {
                alternar();
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    var boton = document.getElementById('menu-usuario-boton');
    var dropdown = document.getElementById('menu-usuario-dropdown');

    if (!boton || !dropdown) {
        return;
    }

    boton.addEventListener('click', function (evento) {
        evento.stopPropagation();
        dropdown.classList.toggle('abierto');
    });

    document.addEventListener('click', function (evento) {
        if (!dropdown.contains(evento.target) && !boton.contains(evento.target)) {
            dropdown.classList.remove('abierto');
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modal-recuperar-password');
    var enlaceAbrir = document.getElementById('enlace-recuperar-password');

    if (!modal || !enlaceAbrir) {
        return;
    }

    var botonCerrar = document.getElementById('boton-cerrar-modal-recuperar');
    var mensaje = document.getElementById('recuperar-mensaje');
    var pasoCorreo = document.getElementById('recuperar-paso-correo');
    var pasoCodigo = document.getElementById('recuperar-paso-codigo');
    var campoCorreo = document.getElementById('recuperar-correo');
    var campoCodigo = document.getElementById('recuperar-codigo');
    var campoPasswordNueva = document.getElementById('recuperar-password-nueva');
    var campoPasswordConfirmar = document.getElementById('recuperar-password-confirmar');
    var botonEnviarCodigo = document.getElementById('boton-recuperar-enviar-codigo');
    var botonConfirmar = document.getElementById('boton-recuperar-confirmar');
    var enlaceReenviar = document.getElementById('enlace-recuperar-reenviar');
    var correoSolicitado = '';

    function mostrarMensaje(texto, esExito) {
        mensaje.textContent = texto;
        mensaje.className = esExito ? 'mensaje-exito' : 'mensaje-error';
        mensaje.style.display = texto ? 'block' : 'none';
    }

    function irAPasoCorreo() {
        pasoCorreo.style.display = '';
        pasoCodigo.style.display = 'none';
        mostrarMensaje('', false);
    }

    function irAPasoCodigo() {
        pasoCorreo.style.display = 'none';
        pasoCodigo.style.display = '';
    }

    function abrirModal() {
        irAPasoCorreo();
        campoCorreo.value = '';
        campoCodigo.value = '';
        campoPasswordNueva.value = '';
        campoPasswordConfirmar.value = '';
        modal.classList.add('abierto');
        campoCorreo.focus();
    }

    function cerrarModal() {
        modal.classList.remove('abierto');
    }

    function solicitarCodigo() {
        var correo = campoCorreo.value.trim();

        if (!correo) {
            mostrarMensaje('Ingresa tu correo electrónico.', false);
            return;
        }

        botonEnviarCodigo.disabled = true;
        botonEnviarCodigo.textContent = 'Enviando...';
        mostrarMensaje('', false);

        var datos = new URLSearchParams();
        datos.set('correo', correo);

        fetch('index.php?ruta=recuperar-password-solicitar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: datos.toString()
        })
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (resultado) {
                if (resultado.exito) {
                    correoSolicitado = correo;
                    mostrarMensaje(resultado.mensaje, true);
                    irAPasoCodigo();
                } else {
                    mostrarMensaje(resultado.mensaje || 'No se pudo generar el código.', false);
                }
            })
            .catch(function () {
                mostrarMensaje('No se pudo conectar con el servidor. Intenta de nuevo.', false);
            })
            .finally(function () {
                botonEnviarCodigo.disabled = false;
                botonEnviarCodigo.textContent = 'Enviar código';
            });
    }

    function confirmarNuevaPassword() {
        var codigo = campoCodigo.value.trim();
        var password = campoPasswordNueva.value;
        var confirmar = campoPasswordConfirmar.value;

        if (!codigo || !password || !confirmar) {
            mostrarMensaje('Completa todos los campos.', false);
            return;
        }

        botonConfirmar.disabled = true;
        botonConfirmar.textContent = 'Guardando...';
        mostrarMensaje('', false);

        var datos = new URLSearchParams();
        datos.set('correo', correoSolicitado);
        datos.set('codigo', codigo);
        datos.set('password', password);
        datos.set('confirmar', confirmar);

        fetch('index.php?ruta=recuperar-password-confirmar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: datos.toString()
        })
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (resultado) {
                mostrarMensaje(resultado.mensaje, resultado.exito);
                if (resultado.exito) {
                    setTimeout(cerrarModal, 2000);
                }
            })
            .catch(function () {
                mostrarMensaje('No se pudo conectar con el servidor. Intenta de nuevo.', false);
            })
            .finally(function () {
                botonConfirmar.disabled = false;
                botonConfirmar.textContent = 'Restablecer contraseña';
            });
    }

    enlaceAbrir.addEventListener('click', function (evento) {
        evento.preventDefault();
        abrirModal();
    });

    botonCerrar.addEventListener('click', cerrarModal);

    modal.addEventListener('click', function (evento) {
        if (evento.target === modal) {
            cerrarModal();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrarModal();
        }
    });

    botonEnviarCodigo.addEventListener('click', solicitarCodigo);
    botonConfirmar.addEventListener('click', confirmarNuevaPassword);

    enlaceReenviar.addEventListener('click', function (evento) {
        evento.preventDefault();
        irAPasoCorreo();
        campoCorreo.value = correoSolicitado;
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var modalAgregar = document.getElementById('modal-agregar-usuario');
    var botonAbrir = document.getElementById('boton-abrir-modal-usuario');

    if (!modalAgregar || !botonAbrir) {
        return;
    }

    var botonCerrar = document.getElementById('boton-cerrar-modal-usuario');
    var casillaAdmin = document.getElementById('usuario-es-administrador');
    var seccionAlcance = document.getElementById('usuario-seccion-alcance');

    function cerrar() {
        modalAgregar.classList.remove('abierto');
    }

    function actualizarVisibilidadAlcance() {
        if (casillaAdmin && seccionAlcance) {
            seccionAlcance.style.display = casillaAdmin.checked ? 'none' : '';
        }
    }

    if (casillaAdmin) {
        casillaAdmin.addEventListener('change', actualizarVisibilidadAlcance);
    }

    botonAbrir.addEventListener('click', function () {
        modalAgregar.classList.add('abierto');
        if (casillaAdmin) {
            casillaAdmin.checked = false;
        }
        actualizarVisibilidadAlcance();
    });

    if (botonCerrar) {
        botonCerrar.addEventListener('click', cerrar);
    }

    modalAgregar.addEventListener('click', function (evento) {
        if (evento.target === modalAgregar) {
            cerrar();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrar();
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var modalEditar = document.getElementById('modal-editar-usuario');

    if (!modalEditar) {
        return;
    }

    var botonCerrar = document.getElementById('boton-cerrar-modal-editar-usuario');
    var campoId = document.getElementById('editar-usuario-id');
    var campoNombre = document.getElementById('editar-usuario-nombre');
    var campoCorreo = document.getElementById('editar-usuario-correo');
    var campoPassword = document.getElementById('editar-usuario-password');
    var casillaAdmin = document.getElementById('editar-usuario-es-administrador');
    var campoVentanilla = document.getElementById('editar-usuario-ventanilla');
    var seccionAlcance = document.getElementById('editar-usuario-seccion-alcance');

    var datosCategorias = {};
    var datosSubcategorias = {};
    try {
        datosCategorias = JSON.parse((document.getElementById('datos-alcance-categorias-usuario') || {}).textContent || '{}');
        datosSubcategorias = JSON.parse((document.getElementById('datos-alcance-subcategorias-usuario') || {}).textContent || '{}');
    } catch (e) {
        datosCategorias = {};
        datosSubcategorias = {};
    }

    function cerrar() {
        modalEditar.classList.remove('abierto');
    }

    function actualizarVisibilidadAlcance() {
        if (casillaAdmin && seccionAlcance) {
            seccionAlcance.style.display = casillaAdmin.checked ? 'none' : '';
        }
    }

    if (casillaAdmin) {
        casillaAdmin.addEventListener('change', actualizarVisibilidadAlcance);
    }

    document.querySelectorAll('.boton-editar-usuario').forEach(function (boton) {
        boton.addEventListener('click', function () {
            campoId.value = boton.dataset.id;
            campoNombre.value = boton.dataset.nombre;
            campoCorreo.value = boton.dataset.correo;
            campoPassword.value = '';

            if (casillaAdmin) {
                casillaAdmin.checked = boton.dataset.esAdministrador === '1';
            }

            if (campoVentanilla) {
                campoVentanilla.value = boton.dataset.ventanillaId && boton.dataset.ventanillaId !== '0' ? boton.dataset.ventanillaId : '';
            }

            var categoriasUsuario = datosCategorias[boton.dataset.id] || [];
            var subcategoriasUsuario = datosSubcategorias[boton.dataset.id] || [];

            modalEditar.querySelectorAll('input[name="categorias[]"]').forEach(function (casilla) {
                casilla.checked = categoriasUsuario.indexOf(parseInt(casilla.value, 10)) !== -1;
            });
            modalEditar.querySelectorAll('input[name="subcategorias[]"]').forEach(function (casilla) {
                casilla.checked = subcategoriasUsuario.indexOf(parseInt(casilla.value, 10)) !== -1;
            });

            actualizarVisibilidadAlcance();
            modalEditar.classList.add('abierto');
        });
    });

    if (botonCerrar) {
        botonCerrar.addEventListener('click', cerrar);
    }

    modalEditar.addEventListener('click', function (evento) {
        if (evento.target === modalEditar) {
            cerrar();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrar();
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.form-eliminar-usuario').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            if (!confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.')) {
                evento.preventDefault();
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var modalEditar = document.getElementById('modal-editar-categoria-turnero');

    if (!modalEditar) {
        return;
    }

    var botonCerrar = document.getElementById('boton-cerrar-modal-editar-categoria-turnero');
    var campoId = document.getElementById('editar-categoria-turnero-id');
    var campoNombre = document.getElementById('editar-categoria-turnero-nombre');
    var campoPrefijo = document.getElementById('editar-categoria-turnero-prefijo');
    var campoColor = document.getElementById('editar-categoria-turnero-color');
    var campoVentanilla = document.getElementById('editar-categoria-turnero-ventanilla');
    var campoHoraInicio = document.getElementById('editar-categoria-turnero-hora-inicio');
    var campoHoraFin = document.getElementById('editar-categoria-turnero-hora-fin');
    var casillasDias = document.querySelectorAll('.editar-categoria-turnero-dia');

    function cerrar() {
        modalEditar.classList.remove('abierto');
    }

    document.querySelectorAll('.boton-editar-categoria-turnero').forEach(function (boton) {
        boton.addEventListener('click', function () {
            campoId.value = boton.dataset.id;
            campoNombre.value = boton.dataset.nombre;
            campoPrefijo.value = boton.dataset.prefijo;
            campoColor.value = boton.dataset.color;
            if (campoVentanilla) {
                campoVentanilla.value = boton.dataset.ventanillaId && boton.dataset.ventanillaId !== '0' ? boton.dataset.ventanillaId : '';
            }
            if (campoHoraInicio) {
                campoHoraInicio.value = boton.dataset.horaInicio || '';
            }
            if (campoHoraFin) {
                campoHoraFin.value = boton.dataset.horaFin || '';
            }
            var diasSeleccionados = (boton.dataset.diasSemana || '').split(',').filter(Boolean);
            casillasDias.forEach(function (casilla) {
                casilla.checked = diasSeleccionados.indexOf(casilla.value) !== -1;
            });
            modalEditar.classList.add('abierto');
        });
    });

    if (botonCerrar) {
        botonCerrar.addEventListener('click', cerrar);
    }

    modalEditar.addEventListener('click', function (evento) {
        if (evento.target === modalEditar) {
            cerrar();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrar();
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.form-eliminar-categoria-turnero').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            if (!confirm('¿Eliminar esta categoría? Esta acción no se puede deshacer.')) {
                evento.preventDefault();
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var modalAgregar = document.getElementById('modal-agregar-beneficiario');
    var botonAbrir = document.getElementById('boton-abrir-modal-beneficiario');

    if (!modalAgregar || !botonAbrir) {
        return;
    }

    var botonCerrar = document.getElementById('boton-cerrar-modal-beneficiario');

    function cerrar() {
        modalAgregar.classList.remove('abierto');
    }

    botonAbrir.addEventListener('click', function () {
        modalAgregar.classList.add('abierto');
    });

    if (botonCerrar) {
        botonCerrar.addEventListener('click', cerrar);
    }

    modalAgregar.addEventListener('click', function (evento) {
        if (evento.target === modalAgregar) {
            cerrar();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrar();
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var modalEditar = document.getElementById('modal-editar-beneficiario');

    if (!modalEditar) {
        return;
    }

    var botonCerrar = document.getElementById('boton-cerrar-modal-editar-beneficiario');
    var campoId = document.getElementById('editar-beneficiario-id');
    var campoTipo = document.getElementById('editar-beneficiario-tipo');
    var campoDocumento = document.getElementById('editar-beneficiario-documento');
    var campoPrimerApellido = document.getElementById('editar-beneficiario-primer-apellido');
    var campoSegundoApellido = document.getElementById('editar-beneficiario-segundo-apellido');
    var campoPrimerNombre = document.getElementById('editar-beneficiario-primer-nombre');
    var campoSegundoNombre = document.getElementById('editar-beneficiario-segundo-nombre');

    function cerrar() {
        modalEditar.classList.remove('abierto');
    }

    document.querySelectorAll('.boton-editar-beneficiario').forEach(function (boton) {
        boton.addEventListener('click', function () {
            campoId.value = boton.dataset.id;
            campoTipo.value = boton.dataset.tipo;
            campoDocumento.value = boton.dataset.documento;
            campoPrimerApellido.value = boton.dataset.primerApellido;
            campoSegundoApellido.value = boton.dataset.segundoApellido;
            campoPrimerNombre.value = boton.dataset.primerNombre;
            campoSegundoNombre.value = boton.dataset.segundoNombre;
            modalEditar.classList.add('abierto');
        });
    });

    if (botonCerrar) {
        botonCerrar.addEventListener('click', cerrar);
    }

    modalEditar.addEventListener('click', function (evento) {
        if (evento.target === modalEditar) {
            cerrar();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrar();
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.form-eliminar-beneficiario').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            if (!confirm('¿Eliminar este beneficiario? Esta acción no se puede deshacer.')) {
                evento.preventDefault();
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modal-subcategorias-turnero');
    var datosElemento = document.getElementById('datos-subcategorias-turnero');

    if (!modal || !datosElemento) {
        return;
    }

    var subcategoriasPorCategoria = {};
    try {
        subcategoriasPorCategoria = JSON.parse(datosElemento.textContent || '{}');
    } catch (e) {
        subcategoriasPorCategoria = {};
    }

    var botonCerrar = document.getElementById('boton-cerrar-modal-subcategorias-turnero');
    var tituloCategoria = document.getElementById('subcategorias-turnero-nombre-categoria');
    var campoCategoriaId = document.getElementById('subcategorias-turnero-categoria-id');
    var lista = document.getElementById('subcategorias-turnero-lista');

    function cerrar() {
        modal.classList.remove('abierto');
    }

    function renderizarLista(categoriaId) {
        var subcategorias = subcategoriasPorCategoria[String(categoriaId)] || [];
        lista.innerHTML = '';

        if (subcategorias.length === 0) {
            var vacio = document.createElement('p');
            vacio.className = 'texto-atenuado';
            vacio.textContent = 'Esta categoría todavía no tiene subcategorías.';
            lista.appendChild(vacio);
            return;
        }

        subcategorias.forEach(function (subcategoria) {
            var fila = document.createElement('div');
            fila.className = 'lista-subcategorias-item';

            var nombre = document.createElement('span');
            nombre.textContent = subcategoria.nombre;

            var formEliminar = document.createElement('form');
            formEliminar.method = 'POST';
            formEliminar.action = 'index.php?ruta=categorias-turnero';
            formEliminar.addEventListener('submit', function (evento) {
                if (!confirm('¿Eliminar esta subcategoría?')) {
                    evento.preventDefault();
                }
            });

            var campoAccion = document.createElement('input');
            campoAccion.type = 'hidden';
            campoAccion.name = 'accion';
            campoAccion.value = 'eliminar_subcategoria';

            var campoId = document.createElement('input');
            campoId.type = 'hidden';
            campoId.name = 'id';
            campoId.value = subcategoria.id;

            var botonEliminar = document.createElement('button');
            botonEliminar.type = 'submit';
            botonEliminar.className = 'boton-accion boton-accion-eliminar';
            botonEliminar.textContent = 'Eliminar';

            formEliminar.appendChild(campoAccion);
            formEliminar.appendChild(campoId);
            formEliminar.appendChild(botonEliminar);

            fila.appendChild(nombre);
            fila.appendChild(formEliminar);
            lista.appendChild(fila);
        });
    }

    document.querySelectorAll('.boton-subcategorias-turnero').forEach(function (boton) {
        boton.addEventListener('click', function () {
            tituloCategoria.textContent = boton.dataset.nombre;
            campoCategoriaId.value = boton.dataset.id;
            renderizarLista(boton.dataset.id);
            modal.classList.add('abierto');
        });
    });

    if (botonCerrar) {
        botonCerrar.addEventListener('click', cerrar);
    }

    modal.addEventListener('click', function (evento) {
        if (evento.target === modal) {
            cerrar();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrar();
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var modalEditar = document.getElementById('modal-editar-ventanilla');

    if (!modalEditar) {
        return;
    }

    var botonCerrar = document.getElementById('boton-cerrar-modal-editar-ventanilla');
    var campoId = document.getElementById('editar-ventanilla-id');
    var campoNombre = document.getElementById('editar-ventanilla-nombre');

    function cerrar() {
        modalEditar.classList.remove('abierto');
    }

    document.querySelectorAll('.boton-editar-ventanilla').forEach(function (boton) {
        boton.addEventListener('click', function () {
            campoId.value = boton.dataset.id;
            campoNombre.value = boton.dataset.nombre;
            modalEditar.classList.add('abierto');
        });
    });

    if (botonCerrar) {
        botonCerrar.addEventListener('click', cerrar);
    }

    modalEditar.addEventListener('click', function (evento) {
        if (evento.target === modalEditar) {
            cerrar();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrar();
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.form-eliminar-ventanilla').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            if (!confirm('¿Eliminar esta ventanilla?')) {
                evento.preventDefault();
            }
        });
    });
});

