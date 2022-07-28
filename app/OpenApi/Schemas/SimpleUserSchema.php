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

class SimpleUserSchema extends SchemaFactory implements Reusable
{
    /**
     * @return AllOf|OneOf|AnyOf|Not|Schema
     */
    public function build(): SchemaContract
    {
        return Schema::object('simple_user')
            ->description('Dieses Schema wird zurück gegeben, wenn der aktuelle Nutzer nicht genug' .
                'Rechte hat um private informationen über den Nutzer zu sehen')
            ->properties(
                Schema::integer('id')->example(1)
                    ->description('Id des Nutzers'),
                Schema::string('username')->example("max musterman")
                    ->description('Name des nutzers'),
                Schema::boolean('anonymous')->example(true)
                    ->description('Gibt an ab dieser User ein anonymer nutzer ist'),
                Schema::string('created_at')->format(Schema::FORMAT_DATE_TIME)
                    ->description('Zeitpunkt an dem dieses Nutzerkonto erstellt wurde. Wenn dieses Nutzerkonto aus einem anonymen Konto erstellt wurde, wird das erstelldatum den anonymen Kontos angezeigt')
            );
    }
}
