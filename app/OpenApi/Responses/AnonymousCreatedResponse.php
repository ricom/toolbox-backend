<?php

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Header;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class AnonymousCreatedResponse extends ResponseFactory
{
    public function build(): Response
    {
        return Response::created()->description('Successful response')
            ->headers(
                Header::create('Location')
                    ->description('Relativer Link zu der erstellen Ressource')
                    ->schema(Schema::string())
            )
            ->content(
                MediaType::json()->schema(
                    Schema::object()->properties(
                        Schema::string('username')->example('anonymous136289')
                            ->description('Nutzername des erstellten anonymen Nutzers. Muss genutzt werden um den anonymen Nutzer anzumelden'),
                        Schema::string('password')->example('dsfakdösfjöldsaf')
                            ->description('Passwort des erstellten anonymen Nutzers. Muss genutzt werden um den anonymen Nutzer anzumelden'),
                    )
                )
            );
    }
}
