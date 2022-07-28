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

class CompleteUserSchema extends SchemaFactory implements Reusable
{
    /**
     * @return AllOf|OneOf|AnyOf|Not|Schema
     */
    public function build(): SchemaContract
    {
        return AllOf::create('complete_user')
            ->schemas(
                SimpleUserSchema::ref(),
                Schema::object()
                    ->description('Diese Daten sind nur mit den ausreichenden Rechten verfügbar')
                    ->properties(
                        Schema::string('email')->nullable()
                            ->description('Email des Nutzers'),
                        Schema::array('owned_saves')->items(Schema::integer())
                            ->description('Id der Speicherstände, welche dieser Nutzer besitzt'),

                        Schema::array('schared_saves')->items(SharedSaveOfUserSchema::ref())
                            ->description('Id der Speicherstände, welchem diesem Nutzer geteilt wurden und die dieser angenommen hat'),
                        Schema::array('invitations')->items(SharedSaveOfUserSchema::ref())
                            ->description('Id der Speicherstände, zu denen dieser Nutzer eingeladen wurde und die noch nicht angenommen wurden'),
                    )
            );
    }
}
