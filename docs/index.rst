.. title:: EasyCron | PHP library for easily creating cronjobs

======
EasyCron
======

EasyCron is library to easily create cronjobs and exposes ECLang, a simple language to create cronjobs without having
to be familiar with it's syntax.

.. code-block:: php

    $easyCron = new EasyCron\Cron\Creator();
    $easyCron->every(2, 'minute')
    $easyCron->in(array('december'));
    $easyCron->execute("/my/awesome/script.sh");
    echo $easyCron->getLine();
    // */2 * * 12 * /my/awesome/script.sh

    