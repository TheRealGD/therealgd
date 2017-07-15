<?php

namespace Raddit\AppBundle\Validator\Constraints;

use Sabberworm\CSS\CSSList\CSSBlockList;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\RuleSet;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\CSSFunction;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\RuleValueList;
use Sabberworm\CSS\Value\URL;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates untrusted CSS as being safe to display to visitors.
 *
 * Safe CSS does not run arbitrary code (e.g. execute JavaScript) or load
 * external resources. While modern browsers will do their utmost to prevent
 * script execution from within CSS, the same is not true for older browsers,
 * where various facilities exist to execute arbitrary code.
 *
 * The following things are blocked:
 *
 * - `charset` @-rule with non UTF-8 setting: could possibly be used to evade
 *   the safeguards of this validator.
 *
 * - `import` @-rule: can cause cross-origin requests and execute arbitrary
 *    code through CSS.
 *
 * - `-ms-filter` property: can cause cross-origin requests.
 *
 * - `-moz-binding` property: can execute arbitrary code in Firefox <= 3.0.
 *
 * - `-o-link` property: can execute arbitrary code in Presto-based Opera
 *   builds.
 *
 * - `behavior` property: can execute arbitrary code in Internet Explorer <= 11.
 *
 * - `expression` function: can execute arbitrary code in Internet Explorer <=
 *   10. This includes string values starting with '`expression(`', because IE
 *   is a piece of shit.
 *
 * - `progid:` syntax used for IE-flavoured filter property. See `-ms-filter`.
 *
 * - `url` with external resource: can cause data leakage, i.e. external servers
 *   could be tracking visitors. Interruption through HTTP authentication
 *   dialogs could also occur. Everything with a scheme counts as an external
 *   resource.
 *
 * - Special `javascript:`/`vbscript:` schemes: can execute arbitrary code in
 *   some older browsers.
 *
 * @see http://blog.innerht.ml/cascading-style-scripting/
 * @see https://www.owasp.org/index.php/XSS_(Cross_Site_Scripting)_Prevention_Cheat_Sheet
 */
class CssValidator extends ConstraintValidator {
    // PHP <= 7.1: in_array() is slow, so do a hash table lookup instead.
    // replace with regular array and use in_array() when PHP 7.1 is obsolete.
    const UNSAFE_PROPERTIES = [
        '-moz-binding' => true,
        '-ms-filter' => true,
        '-o-link' => true,
        'behavior' => true,
    ];

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint) {
        if (!$constraint instanceof Css) {
            throw new UnexpectedTypeException($value, Css::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $value = (string) $value;

        $this->assertDoesNotContainDxFilters($value, $constraint);

        $parserSettings = Settings::create()
            // prevent calls to mb_* functions with bad, user-provided charsets
            ->withMultibyteSupport(false)
        ;

        $parser = new Parser($value, $parserSettings);

        // the parser isn't very robust, so we have to handle PHP notices too
        $oldErrorHandler = set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }

            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $document = $parser->parse();

            $this->validateBlockList($document, $constraint);
        } catch (SourceException $e) {
            $this->context->buildViolation($constraint->cssParseErrorMessage)
                ->setParameter('{{ error }}', $e->getMessage())
                ->setCode(Css::CSS_PARSE_ERROR)
                ->addViolation();
        } catch (\ErrorException $e) {
            $this->context->buildViolation($constraint->cssParseErrorMessage)
                ->setParameter('{{ error }}', $e->getMessage())
                ->setCode(Css::CSS_PARSE_ERROR)
                ->addViolation();
        } finally {
            set_error_handler($oldErrorHandler);
        }
    }

    /**
     * Special check for IE-specific `filter` syntax, as the CSS parser lets
     * these slip. Since we can't use the parser, let's just check for the
     * phrase 'progid:' and add a violation if it exists.
     *
     * @param string $css
     * @param Css    $constraint
     */
    private function assertDoesNotContainDxFilters(string $css, Css $constraint) {
        preg_match_all('/\b[pP][rR][oO][gG][iI][dD]:/', $css, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($matches as $match) {
            $this->context->buildViolation($constraint->progidNotAllowedMessage)
                ->setParameter('{{ line }}', substr_count($css, "\n", null, $match[0][1]) + 1)
                ->setCode(Css::PROGID_NOT_ALLOWED)
                ->addViolation();
        }
    }

    /**
     * @param CSSBlockList $cssValue
     * @param Css          $constraint
     */
    private function validateBlockList(CSSBlockList $cssValue, Css $constraint) {
        foreach ($cssValue->getContents() as $cssElement) {
            if ($cssElement instanceof Charset) {
                $this->validateCharset($cssElement, $constraint);
            } elseif ($cssElement instanceof Import) {
                $this->validateImport($cssElement, $constraint);
            } elseif ($cssElement instanceof RuleSet) {
                $this->validateRuleSet($cssElement, $constraint);
            } elseif ($cssElement instanceof CSSBlockList) {
                $this->validateBlockList($cssElement, $constraint);
            }
        }
    }

    private function validateCharset(Charset $cssValue, Css $constraint) {
        /** @noinspection PhpUndefinedMethodInspection */
        if (!preg_match('/^utf-?8$/i', $cssValue->getCharset()->getString())) {
            $this->context->buildViolation($constraint->nonUtf8NotAllowedMessage)
                ->setParameter('{{ line }}', $cssValue->getLineNo())
                ->setCode(Css::NON_UTF8_NOT_ALLOWED)
                ->addViolation();
        }
    }

    private function validateCssValue($cssValue, Css $constraint) {
        if (is_object($cssValue)) {
            if ($cssValue instanceof CSSFunction) {
                $this->validateFunction($cssValue, $constraint);
            } elseif ($cssValue instanceof RuleValueList) {
                foreach ($cssValue->getListComponents() as $component) {
                    $this->validateCssValue($component, $constraint);
                }
            } elseif ($cssValue instanceof CSSString) {
                $this->validateString($cssValue, $constraint);
            } elseif ($cssValue instanceof URL) {
                $this->validateUrl($cssValue, $constraint);
            }
        }
    }

    private function validateFunction(CSSFunction $cssValue, Css $constraint) {
        /** @noinspection PhpParamsInspection */
        $name = trim($cssValue->getName());

        if (strtolower($name) === 'expression') {
            $this->context->buildViolation($constraint->expressionSyntaxNotAllowedMessage)
                ->setParameter('{{ line }}', $cssValue->getLineNo())
                ->setCode(Css::EXPRESSION_SYNTAX_NOT_ALLOWED)
                ->addViolation();
        }
    }

    private function validateImport(Import $cssValue, Css $constraint) {
        $this->context->buildViolation($constraint->importNotAllowedMessage)
            ->setParameter('{{ line }}', $cssValue->getLineNo())
            ->setCode(Css::IMPORT_NOT_ALLOWED)
            ->addViolation();
    }

    private function validateRuleSet(RuleSet $cssRuleSet, Css $constraint) {
        /** @var Rule $cssRule */
        foreach ($cssRuleSet->getRules() as $cssRule) {
            $property = strtolower(trim($cssRule->getRule()));

            if (isset(self::UNSAFE_PROPERTIES[$property])) {
                $this->context->buildViolation($constraint->propertyNotAllowedMessage)
                    ->setParameter('{{ property }}', $property)
                    ->setParameter('{{ line }}', $cssRule->getLineNo())
                    ->setCode(Css::PROPERTY_NOT_ALLOWED)
                    ->addViolation();
            }

            $cssValue = $cssRule->getValue();

            $this->validateCssValue($cssValue, $constraint);
        }
    }

    private function validateString(CSSString $cssValue, Css $constraint) {
        if (preg_match('/^\s*expression\s*\(.*\)/i', $cssValue->getString())) {
            $this->context->buildViolation($constraint->expressionStringNotAllowedMessage)
                ->setParameter('{{ line }}', $cssValue->getLineNo())
                ->setCode(Css::EXPRESSION_STRING_NOT_ALLOWED)
                ->addViolation();
        }
    }

    private function validateUrl(URL $cssValue, Css $constraint) {
        $url = trim($cssValue->getURL()->getString());

        if (stripos($url, 'javascript:') === 0 || stripos($url, 'vbscript:') === 0) {
            $this->context->buildViolation($constraint->scriptNotAllowedMessage)
                ->setParameter('{{ line }}', $cssValue->getLineNo())
                ->setCode(Css::SCRIPT_NOT_ALLOWED)
                ->addViolation();
        }

        if (stripos($url, 'data:') === 0) {
            $this->context->buildViolation($constraint->dataNotAllowedMessage)
                ->setParameter('{{ line }}', $cssValue->getLineNo())
                ->setCode(Css::DATA_NOT_ALLOWED)
                ->addViolation();
        }

        if (strpos($url, '://') !== false || strpos($url, '//') === 0) {
            $this->context->buildViolation($constraint->externalResourceNotAllowedMessage)
                ->setParameter('{{ line }}', $cssValue->getLineNo())
                ->setCode(Css::EXTERNAL_RESOURCE_NOT_ALLOWED)
                ->addViolation();
        }
    }
}
