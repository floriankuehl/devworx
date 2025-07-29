<?php

namespace Cascade\Enums;

enum ParserMode: string {
    case Html = 'html';
    case Script = 'script';
    case Attribute = 'attribute';
    case Sleep = 'sleep';
}