<?php

namespace App\Support;

final class MachineAuthorizationReason
{
    public const AUTHORIZED = 'AUTHORIZED';
    public const CARD_NOT_FOUND = 'CARD_NOT_FOUND';
    public const CARD_INACTIVE = 'CARD_INACTIVE';
    public const USER_INACTIVE = 'USER_INACTIVE';
    public const MACHINE_NOT_FOUND = 'MACHINE_NOT_FOUND';
    public const MACHINE_INACTIVE = 'MACHINE_INACTIVE';
    public const PERMISSION_NOT_FOUND = 'PERMISSION_NOT_FOUND';
    public const PERMISSION_DENIED = 'PERMISSION_DENIED';
    public const PERMISSION_NOT_YET_VALID = 'PERMISSION_NOT_YET_VALID';
    public const PERMISSION_EXPIRED = 'PERMISSION_EXPIRED';
    public const INVALID_REQUEST = 'INVALID_REQUEST';

    private function __construct()
    {
    }
}
