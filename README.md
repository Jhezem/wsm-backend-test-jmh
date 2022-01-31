### Como ejecutar usando Docker

Solo se necesita construir la imagen, una vez que la imagen se construyera,
se necesita ingresar a la instancia para poder ejecutar el composer install
el cual permite que todas las dependencias se instale

Lista de comandos

    - docker-compose build
    - docker-compose down
    - docker-compose up


El proyecto sale por el puerto 80 , solo se ingresa a *localhost*

Para los estilos usé boostrap CDN.

Hice dos controladores, uno para obtener los datos usando el MongoClient, y otro con Doctrine ODM, sin embargo
este último no lo terminé completamente.

Por favor ver la ruta /reports