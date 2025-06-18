---
applyTo: '**'
---
Coding standards, domain knowledge, and preferences that AI should follow.

# Instrucciones para el Copilot de AUTOEXAM2
Evita dar explicaciones largas o dar código de ejemplo que gaste tokens y haga lenta la respuesta o solución.

Evita crear archivos de diagnostico o de test para comprobar errores o dar soluciones, si es posible.

Se trabaja en producción siempre, ya que el servidor es de IONOS y no permite pruebas de la base de datos remotas. Siempre 
se harán en local desde el servidor.

La guía principal para saber lo que hay que implementar siempre será /documentacion donde podrá encontrarse cómo deben
implementarse las funcionalidades, cómo deben estructurarse los archivos y qué requisitos técnicos deben cumplirse.

No debes inventar ni suponer nada. Todo está en /documentacion y si no lo está pregunta. 

Sugiere siempre pasos sencillos que puedan comprobarse siempre en producción para luego mejorarlos en complejidad.

En /base_datos están los sql de la aplicación. Deberá utilizarse para añadir, editar o borar tablas, en la medida de lo posible, 
001_esquema_completo.sql

EN /almacenamiento están los logs, copias de seguridad, /tmp y archivos de subidas de los usuarios

Siempre que se vaya a crear un archivo de diagnostico o test, que luego ya no se utilice se hará en /publico/diagnostico

EL dominio de la aplicación siempre apunta a /publico y el punto de entrada es index.php

considera siempre la posibilidad de que la documentación no esté al 100% documentando lo que ya hay implementado.

NO hardcodees nunca datos, buscalos o referencialos siempre de .env, de la base de datos o de config.php

NO cambies código nunca a desarrollo ni fuerces a trabajar en local, sobre todo la base de datos.

Todas nombres de las variables, funciones, clases, archivos, directorios, deben ser en español 
