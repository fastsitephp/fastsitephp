<p align="center">
    <img width="500" src="../FastSitePHP.svg" alt="FastSitePHP">
</p>

# 🌟 ¡Bienvenido a FastSitePHP!

**¡Gracias por su visita!** 🌠👍

FastSitePHP es un moderno marco de código abierto para crear sitios web y API de alto rendimiento con PHP. FastSitePHP ha sido diseñado para un rendimiento rápido, flexibilidad de codificación, estabilidad a largo plazo, facilidad de uso y una mejor experiencia de desarrollo general. FastSitePHP tiene un tamaño mínimo, por lo que es rápido de descargar y fácil de comenzar. FastSitePHP se publicó por primera vez en noviembre de 2019; y se escribió y utilizó durante muchos años antes de su lanzamiento. A partir de 2024 se ha utilizado en una variedad de aplicaciones y sitios web y es extremadamente estable y contiene muchas pruebas unitarias.

Este repositorio contiene el Marco de FastSitePHP y el sitio web principal.

FastSitePHP incluye muchos componentes independientes que se pueden usar sin usar el objeto de aplicación principal o Framework, por lo que es fácil de usar FastSitePHP con otros marcos o proyectos PHP.

## 💫 ¿Por qué usar FastSitePHP?

|<img src="https://www.fastsitephp.com/img/icons/Performance.svg" alt="Gran actuación" width="60">|<img src="https://www.fastsitephp.com/img/icons/Lightswitch.svg" alt="Fácil de configurar y utilizar" width="60">|
|---|---|
|**Gran actuación** Con FastSitePHP, las páginas complejas se pueden generar en miles de segundos utilizando solo una pequeña cantidad de memoria. Este nivel de rendimiento incluso permite que los sitios se ejecuten rápidamente en computadoras de baja potencia.|**Fácil de configurar y utilizar** FastSitePHP está diseñado para que sea fácil de configurar en cualquier sistema operativo, fácil de leer el código, fácil de desarrollar y mucho más. Con FastSitePHP, se pueden desarrollar sitios web y aplicaciones de alta calidad a un ritmo rápido utilizando menos líneas de código y una configuración mínima.|

|<img src="https://www.fastsitephp.com/img/icons/Samples.svg" alt="Rápido para aprender y depurar" width="60">|<img src="https://www.fastsitephp.com/img/icons/Security-Lock.svg" alt="Fuerte seguridad" width="60">|
|---|---|
|**Rápido para aprender y depurar** FastSitePHP está bien documentado y viene con muestras prácticas. FastSitePHP proporciona mensajes de error amigables para el desarrollador para que los errores puedan repararse rápidamente incluso si tiene poca o ninguna experiencia en programación con PHP.|**Fuerte seguridad** La seguridad ha sido cuidadosamente planificada en todas las características de FastSitePHP para que sea segura y fácil de trabajar. Las características de seguridad incluyen cifrado (texto, objetos y archivos), cookies firmadas, JWT, CORS, validación del servidor proxy, limitación de velocidad y más.|

## 🚀 Pruébalo en línea!

El sitio principal de FastSitePHP proporciona un área de juegos de código donde puede desarrollar con PHP, HTML, JavaScript, CSS y más. No hay nada que instalar y puede trabajar con PHP directamente en un servidor. Si nunca ha usado PHP, esta es una excelente manera de aprender PHP.

[https://www.fastsitephp.com/es/playground](https://www.fastsitephp.com/es/playground)

<p align="center">
<img src="https://github.com/fastsitephp/static-files/raw/master/img/screenshots/Playground.png" alt="FastSitePHP Code Playground">
</p>

## 🚀 Empezando

**Comenzar a usar PHP y FastSitePHP es extremadamente fácil.** Si no tiene PHP instalado, consulte las instrucciones para Windows, Mac y Linux en la página de inicio:

<a href="https://www.fastsitephp.com/es/getting-started" target="_blank">https://www.fastsitephp.com/es/getting-started</a>

Una vez que PHP está instalado, puede iniciar el sitio desde la línea de comandos como se muestra a continuación o si utiliza un editor de código o IDE [Visual Studio Code, GitHub Atom, etc.], puede iniciar el sitio directamente desde su editor. Consulte la página de inicio anterior para obtener más información.

### Descargue y ejecute el sitio web principal y el marco completo (~1.2 mb)

~~~
# Descargue este repositorio
cd {directorio raíz}
php -S localhost:3000
~~~

Para incluir soporte para renderizar documentos de rebajas del lado del servidor o soporte para funciones criptográficas con versiones anteriores de PHP (PHP 5) primero ejecute el script de instalación.

~~~
cd {directorio raíz}
php ./scripts/install.php
~~~

### Instalar usando Composer (Dependencia PHP / Administrador de paquetes) (~470 kb)

FastSitePHP Framework también se puede instalar usando Composer. Cuando se instala desde Composer, solo se incluyen los archivos principales de Framework y no este repositorio completo con el sitio web principal. El tamaño de los archivos descargados es pequeño, por lo que es rápido incluirlo en proyectos PHP existentes o usarlo para comenzar nuevos proyectos. Las clases FastSitePHP se pueden usar con Symfony, Laravel, Zend u otros Frameworks PHP existentes cuando se usa Composer.

~~~
composer require fastsitephp/fastsitephp
~~~

### Comience con un sitio de inicio (~67 kb)

También existe un sitio de inicio para FastSitePHP que incluye varias páginas de ejemplos y proporciona una estructura básica de directorio / archivo. Es pequeño y rápido de configurar.

[https://github.com/fastsitephp/starter-site](https://github.com/fastsitephp/starter-site)

<p align="center">
<img src="https://github.com/fastsitephp/static-files/raw/master/img/starter_site/2020-01-10/home-page.png" alt="FastSitePHP Starter Site" width="500">
</p>

## 📄 Código de ejemplo

```php
<?php

// -------------------------------
// Preparar
// -------------------------------

// Configurar un cargador automático de PHP
// Esto permite que las clases se carguen dinámicamente
require '../../../autoload.php';

// O para un sitio mínimo, solo se deben incluir los siguientes 2 archivos
// require '../vendor/fastsitephp/src/Application.php';
// require '../vendor/fastsitephp/src/Route.php';

// Cree el objeto de aplicación con manejo de errores y UTC para la zona horaria
$app = new \FastSitePHP\Application();
$app->setup('UTC');

// -------------------------------
// Definir rutas
// -------------------------------

// Enviar una respuesta de '¡Hola Mundo!' para solicitudes predeterminadas
$app->get('/', function() {
    return '¡Hola Mundo!';
});

// Enviar una respuesta '¡Hola Mundo!' para la URL '/hola' o en el caso de
// la variable opcional [nombre], salga y devuelva un mensaje con el nombre
// de forma segura (ejemplo: '/hola/FastSitePHP' generará '¡Hola FastSitePHP!')
$app->get('/hola/:nombre?', function($nombre = 'Mundo') use ($app) {
    return '¡Hola ' . $app->escape($nombre) . '!';
});

// Enviar una respuesta JSON que contenga un objeto con
// información básica del sitio
$app->get('/site', function() use ($app) {
    return [
        'rootUrl' => $app->rootUrl(),
        'rootDir' => $app->rootDir(),
        'requestedPath' => $app->requestedPath(),
    ];
});

// Enviar una respuesta JSON que contenga información básica de solicitud
$app->get('/request', function() {
    $req = new \FastSitePHP\Web\Request();
    return [
        'acceptEncoding' => $req->acceptEncoding(),
        'acceptLanguage' => $req->acceptLanguage(),
        'origin' => $req->origin(),
        'userAgent' => $req->userAgent(),
        'referrer' => $req->referrer(),
        'clientIp' => $req->clientIp(),
        'protocol' => $req->protocol(),
        'host' => $req->host(),
        'port' => $req->port(),
    ];
});

// Envíe el contenido de este archivo como una respuesta de texto sin formato
// utilizando Encabezados de respuesta HTTP que permiten al usuario final
// almacenar en caché la página hasta que se modifique el archivo
$app->get('/cached-file', function() {
    $file_path = __FILE__;
    $res = new \FastSitePHP\Web\Response();
    return $res->file($file_path, 'text', 'etag:md5', 'private');
});

// Devuelva la dirección IP del usuario como un servicio web JSON que admite el
// uso compartido de recursos de origen cruzado (CORS) y le indica
// específicamente al navegador que no guarde en caché los resultados. En este
// ejemplo, se supone que el servidor web está detrás de un servidor proxy
// (por ejemplo, un equilibrador de carga) y la dirección IP se lee de forma
// segura. Además, la función cors () se llama desde una función de filtro
// que solo se llama si la ruta coincide y permite el manejo correcto de
// una solicitud de OPTIONS.
$app->get('/whats-my-ip', function() {
    $req = new \FastSitePHP\Web\Request();
    return [
        'ipAddress' => $req->clientIp('from proxy', 'trust local'),
    ];
})
->filter(function() use ($app) {
    $app
        ->noCache()
        ->cors('*');
});

// Defina una función que devuelva verdadero si la solicitud web proviene de una
// red local (por ejemplo, 127.0.0.1 o 10.0.0.1). Esta función se usará en un
// filtro para mostrar u ocultar rutas.
$is_local = function() {
    // Compare la solicitud de IP utilizando
    // Classless Inter-Domain Routing (CIDR)
    $req = new \FastSitePHP\Web\Request();
    $private_ips = \FastSitePHP\Net\IP::privateNetworkAddresses();

    return \FastSitePHP\Net\IP::cidr(
        $private_ips, 
        $req->clientIp('from proxy')
    );
};

// Proporcione información detallada del entorno de PHP para los usuarios que
// soliciten la página desde una red local. Si la solicitud proviene de alguien
// en Internet, se devolverá una respuesta 404 "Page not found". Llamar a
// [phpinfo()] genera una respuesta HTML, por lo que la ruta no necesita
// devolver nada.
$app->get('/phpinfo', function() {
    phpinfo();
})
->filter($is_local);

// Proporcionar una respuesta de texto con información del
// servidor para usuarios locales
$app->get('/server', function() {
    $config = new \FastSitePHP\Net\Config();
    $req = new \FastSitePHP\Web\Request();
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            "Host: {$config->fqdn()}",
            "Server IP: {$req->serverIp()}",
            "Network IP: {$config->networkIp()}",
            str_repeat('-', 80),
            $config->networkInfo(),
        ]));
})
->filter($is_local);

// Si la url solicitada comienza con '/examples', cargue un archivo PHP para
// las rutas coincidentes desde el directorio actual. Este es un archivo real
// que proporciona muchos más ejemplos. Si descarga este sitio, este código
// y otros ejemplos se pueden encontrar en [app_data/sample-code].
$app->mount('/examples', 'home-page-en-examples.php');

// -------------------------------
// Ejecuta la aplicación
// -------------------------------
$app->run();
```

## 🤝 Contribuyendo

**Todas las contribuciones son bienvenidas.** Para cambios importantes, incluidas nuevas clases, cambios en el código existente, actualización de gráficos y archivos existentes, abra primero un problema para analizar qué le gustaría cambiar. Algunos ejemplos de artículos para contribuir:

* Errores tipográficos y gramaticales: si ve alguno, corríjalo y envíelo.
* Agregar páginas de demostración adicionales: las páginas de demostración generalmente usan más HTML, CSS y JavaScript que PHP, por lo que si es un desarrollador web y no conoce PHP, puede aprenderlo fácilmente durante el desarrollo.
* Pruebas unitarias adicionales y métodos de prueba
* Documentación adicional y tutoriales
* Clases y características adicionales
* Nuevas ideas: si tiene ideas sobre cómo mejorar, abra un tema para discutir.

El archivo [docs/to-do-list.txt](https://github.com/fastsitephp/fastsitephp/blob/master/docs/to-do-list.txt) contiene la lista completa de elementos que están actualmente pendientes y es un buen lugar para comenzar.

## ❓ Preguntas más frecuentes

**¿Por qué se creó FastSitePHP?**

El código central de FastSitePHP se inició en 2013 cuando el autor principal estaba desarrollando un sitio web con PHP. Originalmente se compararon, probaron marcos PHP populares y se eligió uno inicialmente. Sin embargo, en ese momento (<a href="https://www.techempower.com/benchmarks/">y todavía ahora en su mayor parte</a>) la mayoría de los frameworks PHP eran extremadamente lentos en comparación con los frameworks en otros lenguajes y el lenguaje PHP en sí.

Para el sitio que se está desarrollando, el marco y los componentes se reemplazaron uno por uno en clases separadas y una vez que se eliminaron todos los marcos y clases de terceros, el sitio se desempeñó 60 veces más rápido, usó 10 veces menos memoria, logró un puntaje de 100 en la prueba de velocidad de Google y un servidor inesperado los errores desaparecieron Luego, durante un período de 6 años, el código central se desarrolló en FastSitePHP.

**Ya conozco JavaScript/Node, Python, C #, Java, etc. ¿Por qué debería aprender PHP?**

* PHP es el lenguaje de programación más utilizado en el mundo para sitios web dinámicos del lado del servidor; Esto incluye muchos de los sitios web más populares del mundo.
* PHP tiene una excelente documentación y una gran comunidad de desarrolladores que facilita el aprendizaje y la búsqueda de recursos.
* Soporte de base de datos listo para usar. Todos los principales proveedores (Microsoft, Oracle, etc.) han respaldado PHP durante años con extensiones de base de datos nativas de alto rendimiento.
* Funciona en cualquier ambiente. La última versión de PHP puede funcionar en prácticamente cualquier servidor o computadora. Esto incluye Windows IIS, Linux/Apache, Raspberry Pi e incluso servidores IBM heredados.
* Desarrollo rápido y configuración del servidor: simplemente realice cambios en un archivo PHP y actualice la página. No hay un proceso de compilación para compilar programas ni servicios para detener y reiniciar al hacer cambios.
* Aprender idiomas adicionales le permite aprender nuevas ideas y conceptos, y mejora sus habilidades generales de programación.
* Ingresos: más idiomas = más dinero y un mejor currículum. Mientras que, en promedio, PHP paga menos que muchos otros lenguajes populares; Los sitios grandes y los sitios que dependen de firmas de diseño generalmente pagan los mejores dólares _(altos ingresos)_ para el desarrollo de PHP. Tener PHP en su currículum permite más oportunidades. Además, si está pagando a los desarrolladores para desarrollar un sitio, PHP puede resultar en un sitio más asequible.

**¿Qué tan grande es FastSitePHP?**

- **Marco de referencia** (~19,000 líneas de código PHP, ~470 kb como un archivo zip)
- **Pruebas unitarias** (~25,000 líneas de código)

**¿Qué versiones de PHP son compatibles?**

Todas las versiones de PHP de 5.3 a 7.4.

## 📝 Licencia

Este proyecto está licenciado bajo la **MIT License** - vea el archivo [LICENSE](LICENSE) para más detalles.

Las ilustraciones (archivos SVG) ubicadas en [website/public/img] y [website/public/img/icons] tienen doble licencia bajo **MIT License** y <a href="https://creativecommons.org/licenses/by/4.0/" style="font-weight:bold;"> Creative Commons Attribution 4.0 International License</a>.
