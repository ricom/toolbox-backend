<?php

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class ValidationFailedResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        return Response::unprocessableEntity('validation_failed')->description('Ein oder mehrere Argumente sind invalide')
            ->content(MediaType::json()->schema(
                Schema::object()->properties(
                    Schema::string('message')
                        ->description('Eine Nachricht die einem Nutzer angezeigt werden könnte'),
                    Schema::array('errors')
                        ->description('Keys des Objects haben den selben namen wie in dem Request Body')
                        ->items(
                            Schema::object()->description('Fehlermeldungen eines Arguments. Der Key hat den selben namen wie das Argument')
                                ->additionalProperties(
                                    Schema::array('Liste an Fehlermeldungen')->items(Schema::string())
                                )
                        ),
                )
            ));
    }
}
