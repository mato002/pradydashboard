<?php

return array_values(array_filter([
    App\Providers\AppServiceProvider::class,
    class_exists(\Laravel\Horizon\HorizonApplicationServiceProvider::class)
        ? App\Providers\HorizonServiceProvider::class
        : null,
]));
