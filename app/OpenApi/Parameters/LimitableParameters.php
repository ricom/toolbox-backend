<?php

namespace App\OpenApi\Parameters;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class LimitableParameters extends ParametersFactory
{
    /**
     * @return Parameter[]
     */
    public function build(): array
    {
        return [

            Parameter::query()
                ->name('limit')
                ->description('Begrenzt die Anzahl der pro Seite angezeigten Ergebnisse')
                ->required(false)
                ->schema(Schema::integer()->default(50)),

        ];
    }
}
