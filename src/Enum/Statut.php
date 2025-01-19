<?php
namespace App\Enum;

enum Statut: string
{
    case PENDING = 'payé';
    //case CONFIRMED = 'confirmé';

    case WAITING = 'en attente';
    case CANCELLED = 'annulé';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING  => 'payé',
           // self::CONFIRMED => 'confirmé',
            self::WAITING => 'en attente', 
            self::CANCELLED => 'annulé',
        };
    }

}