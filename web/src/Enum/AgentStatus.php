<?php

namespace App\Enum;

enum AgentStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
    case Unresponsible = 'unresponsible';
}