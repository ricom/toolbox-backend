<?php

namespace App\OpenApi\RequestBodies;


use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class StoreUserRequestBody extends RequestBodyFactory
{
    public function build(): RequestBody
    {
        return RequestBody::create('user_create')
            ->description('Nutzerdaten')
            ->content(
                MediaType::json()->schema(
                    Schema::object()
                        ->required('username', 'email', 'password')
                        ->properties(
                            Schema::string('username')->example('max'),
                            Schema::string('email')->example('max.mustermann@jade-hs.de'),
                            Schema::string('password')->example('asdfasdfsadf'),
                            Schema::integer('anonymous_id')->example(30)
                                ->description('id des anonymen users von dem alle Einstellungen und Daten übernommen werden sollen'),
                            Schema::string('anonymous_password')->example("passwortd")
                                ->description('password des anonymen benutzerkontos, welcher durch die anonymous_id angegeben wird.'),
                        )
                )
            );
    }
}
