<?php

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class AvailabilityResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        return Response::ok('availability')->description('Zeigt ob eine Ressource verfügbar ist')
            ->content(
                MediaType::json()
                    ->schema(
                        Schema::object()->properties(
                            Schema::object('data')->properties(
                                Schema::boolean('available')
                                    ->description('Ob die ressource verfügbar ist')
                                    ->example(false),
                                Schema::string('reason')
                                    ->description('Information wieso dieses Ressource nicht verfügbar ist. Es könnten noch weitere Enum werte hinzukommen')
                                    ->enum('taken', 'blocked', 'invalid')
                                    ->example('taken')
                            )
                        )
                    )
            );
    }
}
