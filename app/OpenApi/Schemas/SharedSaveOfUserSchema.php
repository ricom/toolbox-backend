<?php

namespace App\OpenApi\Schemas;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AnyOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Not;
use GoldSpecDigital\ObjectOrientedOAS\Objects\OneOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\SchemaFactory;

class SharedSaveOfUserSchema extends SchemaFactory implements Reusable
{
    /**
     * @return AllOf|OneOf|AnyOf|Not|Schema
     */
    public function build(): SchemaContract
    {
        return Schema::object('shared_save_of_user')
            ->properties(
                Schema::integer('save_id')
                    ->description('Id des Speicherstands, welcher geteilt wurde'),
                Schema::integer('permission')
                    ->enum(0, 1, 2)
                    ->description('Rechte die die geteilte person besitzt. 0: Leserechte \t 1: Schreib- und Leserechte \t 2: Adminrechte')
            );
    }
}
