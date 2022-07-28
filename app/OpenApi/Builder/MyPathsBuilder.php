<?php

namespace App\OpenApi\Builder;

use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use Illuminate\Support\Facades\Log;

class MyPathsBuilder extends \Vyuldashev\LaravelOpenApi\Builders\PathsBuilder
{
    public function build(string $collection, array $middlewares): array
    {
        $basicPaths = parent::build($collection, $middlewares);

        $newPaths = array(
            // TODO add token routes
        );

        return array_merge_recursive($basicPaths, $newPaths);
    }


}
