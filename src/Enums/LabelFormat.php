<?php

namespace SmartDato\Dpd\Enums;

enum LabelFormat: string
{
    case PDF = 'PDF';
    case ZPL = 'ZPL';
    case ZPL300 = 'ZPL300';
}
