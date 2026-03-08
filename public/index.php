<?php

use App\Kernel;

// Augmenter le temps d'exécution pour les opérations de paiement
set_time_limit(120);
ini_set('max_execution_time', '120');

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
