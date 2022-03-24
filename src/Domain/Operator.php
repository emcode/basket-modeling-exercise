<?php declare(strict_types=1);

namespace Acme\Domain;

enum Operator: string
{
    case LESS_THAN = "<";
    case LESS_OR_EQUAL_THAN = "<=";
    case GREATER_THAN = ">";
    case GREATER_OR_EQUAL_THAN = ">=";
}