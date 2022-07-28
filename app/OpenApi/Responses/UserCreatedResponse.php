<?php

namespace App\OpenApi\Responses;

use App\OpenApi\Schemas\CompleteUserSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Header;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Link;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class UserCreatedResponse extends ResponseFactory
{
    public function build(): Response
    {
        return Response::created('user_created')
            ->description('Successful response')
            ->headers(
                Header::create('Location')
                    ->description('Relativer Link zu der erstellen Ressource')
                    ->schema(Schema::string())
            )
            ->links(
                Link::create()
                    ->operationId('showUser')
                    ->description('Id des erstellten Nutzers kann als path variable in der showUser Route verwendet werden')
            )
            ->content(
                MediaType::json()->schema(Schema::object()->properties(
                    CompleteUserSchema::ref('data')
                ))
            );
    }
}
