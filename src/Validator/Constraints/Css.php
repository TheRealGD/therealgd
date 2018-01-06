<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 *
 * @see CssValidator
 */
class Css extends Constraint {
    const CSS_PARSE_ERROR = 'c52b1fe0-c1e3-44c1-ad40-14a6bc67c335';
    const DATA_NOT_ALLOWED = '18bd35c6-3c4c-48b8-ae68-3ab1c79e1090';
    const EXPRESSION_STRING_NOT_ALLOWED = '1e386cb7-df64-4af8-a8d6-d66460731cf9';
    const EXPRESSION_SYNTAX_NOT_ALLOWED = 'ba9ab23f-e9bb-4dfa-842b-679e2398b77e';
    const EXTERNAL_RESOURCE_NOT_ALLOWED = 'c1aa8c1b-0340-45ab-99f0-b7f239090888';
    const IMPORT_NOT_ALLOWED = '950356cf-9f72-4cc9-816f-843e9319b951';
    const NON_UTF8_NOT_ALLOWED = '38d5f600-c697-4f86-a5b4-e7e073c3c299';
    const PROGID_NOT_ALLOWED = 'a5586952-efcb-41bd-b125-6b2683fcb1c4';
    const PROPERTY_NOT_ALLOWED = 'ae59007b-70b5-40bc-b2a9-8501c2dcb1dd';
    const SCRIPT_NOT_ALLOWED = 'd0b53417-435f-4b87-8caa-f1bf07416982';

    protected static $errorNames = [
        self::CSS_PARSE_ERROR => 'CSS_PARSE_ERROR',
        self::DATA_NOT_ALLOWED => 'DATA_NOT_ALLOWED',
        self::EXPRESSION_STRING_NOT_ALLOWED => 'EXPRESSION_STRING_NOT_ALLOWED',
        self::EXPRESSION_SYNTAX_NOT_ALLOWED => 'EXPRESSION_SYNTAX_NOT_ALLOWED',
        self::EXTERNAL_RESOURCE_NOT_ALLOWED => 'EXTERNAL_RESOURCE_NOT_ALLOWED',
        self::IMPORT_NOT_ALLOWED => 'IMPORT_NOT_ALLOWED',
        self::NON_UTF8_NOT_ALLOWED => 'NON_UTF8_NOT_ALLOWED',
        self::PROGID_NOT_ALLOWED => 'PROGID_NOT_ALLOWED',
        self::PROPERTY_NOT_ALLOWED => 'PROPERTY_NOT_ALLOWED',
        self::SCRIPT_NOT_ALLOWED => 'SCRIPT_NOT_ALLOWED',
    ];

    public $cssParseErrorMessage = 'Error from CSS parser: {{ error }}';
    public $dataNotAllowedMessage = 'Embedded data is not allowed on line {{ line }}';
    public $expressionStringNotAllowedMessage = 'expression() syntax is not allowed in strings on line {{ line }}';
    public $expressionSyntaxNotAllowedMessage = 'expression() syntax is not allowed on line {{ line }}';
    public $externalResourceNotAllowedMessage = 'External resource is not allowed on line {{ line }}';
    public $importNotAllowedMessage = '@import syntax is not allowed on line {{ line }}';
    public $nonUtf8NotAllowedMessage = 'Non UTF-8 charset is not allowed on line {{ line }}';
    public $progidNotAllowedMessage = '"progid:" syntax is not allowed on line {{ line }}';
    public $propertyNotAllowedMessage = 'Property "{{ property }}" is not allowed on line {{ line }}';
    public $scriptNotAllowedMessage = 'Script URL is not allowed on line {{ line }}';
}
