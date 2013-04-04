pyTools
=======

Utilidades para la gestión de proyectos web en local.

Instalación
------------

    cd ~
    git clone git://github.com/quiqueporta/pyTools.git
    cd pyTools
    curl -s http://getcomposer.org/installer | php
    ./composer.phar install
    sudo ln -s $HOME/pyTools/bin/pytools /usr/bin/pytools
    sudo ln -s $HOME/pyTools/pytools-autocomplete.sh /etc/bash_completion.d/pytools-autocomplete.sh
    
Configuración
-------------

Se deben configurar las opciones del archivo
   config/pytools.yml 

Ejecución
---------

    pytools tarea [opciones] 

Tareas
------

* apache:vhost:add
* apache:vhost:del
* etc:hosts:add
* etc:hosts:del
* svn:add

