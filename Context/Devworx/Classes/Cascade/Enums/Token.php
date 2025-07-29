<?php

namespace Cascade\Enums;

enum Token : string {
	    
	case HTML = 'HTML';
	case IDENTIFIER = 'IDENTIFIER';	
	case VALUE = 'VALUE';
	case CONSTANT = 'CONSTANT';
	case STRING = 'STRING';
    case NUMBER = 'NUMBER';
	case ARRAY = 'ARRAY';
	case OBJECT = 'OBJECT';
	case FUNCTION = 'FUNCTION';
	case CONDITION = 'CONDITION';
	case OPERATOR = 'OPERATOR';
	case EXPRESSION = 'EXPRESSION';
	case VARIABLE = 'VARIABLE';
	case VIEWHELPER = 'VIEWHELPER';
	case WAKE = 'WAKE';
	case WHITESPACE = ' ';
	
	case OPEN = '{';
	case CLOSE = '}';
	case OPEN_PAREN = '(';
    case CLOSE_PAREN = ')';
	case OPEN_SQUARE = '[';
    case CLOSE_SQUARE = ']';
	
	case DOT = '.';
    case COLON = ':';
    case COMMA = ',';
    
    case PIPE = '->';
	case TERNARY = '?';
	
    case PLUS  = '+';
    case MINUS = '-';
    case MULT  = '*';
    case DIV   = '/';
    case MOD   = '%';
	case EXP   = '**';

	case ASSIGN = '=';
	case PLUS_ASSIGN  = '+=';
    case MINUS_ASSIGN = '-=';
    case MULT_ASSIGN  = '*=';
    case DIV_ASSIGN   = '/=';

	case NOT = '!';
	case EQ = '==';
    case NEQ = '!=';
    case LT = '<';
    case LTE = '<=';
    case GT = '>';
    case GTE = '>=';
	case LGT = '<>';
	
	case AND = '&&';
	case NAND = '!&';
	case AND_ASSIGN = '&=';
	case BITWISE_AND = '&';
	
	case OR = '||';
	case NOR = '!|';
	case OR_ASSIGN = '|=';
	case BITWISE_OR = '|';
	
	case XOR = '^';
	case XNOR = '!^';
	case XOR_ASSIGN = '^=';
	
	case SHIFT_LEFT = '<<';
	case SHIFT_RIGHT = '>>';
	
	case INCREMENT = '++';
	case DECREMENT = '--';
	case COALESCE  = '??';
}
