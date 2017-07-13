<?php

namespace Raddit\AppBundle\Validator\Constraints;

use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\RuleSet;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\CSSFunction;
use Sabberworm\CSS\Value\CSSString;
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

        $this->assertDoesNotContainDxFilters($value);
        $this->assertDoesNotContainImportRule($value);

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

            foreach ($document->getAllRuleSets() as $cssRuleSet) {
                $this->validateRuleSet($cssRuleSet);
            }
        } catch (SourceException $e) {
            $this->context->buildViolation('Error from CSS parser: {{ error }}')
                ->setParameter('{{ error }}', $e->getMessage())
                ->addViolation();
        } catch (\ErrorException $e) {
            $this->context->buildViolation('Error from CSS parser: {{ error }}')
                ->setParameter('{{ error }}', $e->getMessage())
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
     */
    private function assertDoesNotContainDxFilters(string $css) {
        if (preg_match('/\b[pP][rR][oO][gG][iI][dD]:/', $css, $matches, PREG_OFFSET_CAPTURE)) {
            $this->context->buildViolation('"progid:" syntax is not allowed on line {{ line }}')
                ->setParameter('{{ line }}', substr_count($css, "\n", null, $matches[0][1]) + 1)
                ->addViolation();
        }
    }

    /**
     * Special check for import at-rule, since the parser strangely enough won't
     * handle this either.
     *
     * @param string $css
     */
    private function assertDoesNotContainImportRule(string $css) {
        if (preg_match('/^(?:.*;)?\s*(\@[iI][mM][pP][oO][rR][tT])\b/', $css, $matches, PREG_OFFSET_CAPTURE)) {
            $this->context->buildViolation('@import syntax is not allowed on line {{ line }}')
                ->setParameter('{{ line }}', substr_count($css, "\n", null, $matches[1][1]) + 1)
                ->addViolation();
        }
    }

    private function validateRuleSet(RuleSet $cssRuleSet) {
        /** @var Rule $cssRule */
        foreach ($cssRuleSet->getRules() as $cssRule) {
            $property = strtolower(trim($cssRule->getRule()));

            if (isset(self::UNSAFE_PROPERTIES[$property])) {
                $this->context->buildViolation('Property "{{ property }}" is not allowed on line {{ line }}')
                    ->setParameter('{{ property }}', $property)
                    ->setParameter('{{ line }}', $cssRule->getLineNo())
                    ->addViolation();
            }

            $cssValue = $cssRule->getValue();

            $this->validateCssValue($cssValue, 1);
        }
    }

    private function validateCssValue($cssValue, int $recursionDepth) {
        if ($recursionDepth > 5) {
            $this->context->addViolation('Recursion limit reached');
        }

        if (is_object($cssValue)) {
            switch (get_class($cssValue)) {
            case Charset::class:
                $this->validateCharset($cssValue);
                break;
            case CSSFunction::class:
                $this->validateFunction($cssValue);
                break;
            case CSSList::class:
                // TODO: not sure if this works properly
                /** @noinspection PhpUndefinedMethodInspection */
                foreach ($cssValue->getContents() as $cssListValue) {
                    $this->validateCssValue($cssListValue, $recursionDepth + 1);
                }
                break;
            case CSSString::class:
                $this->validateString($cssValue);
                break;
            case URL::class:
                $this->validateUrl($cssValue);
                break;
            }
        }
    }

    private function validateCharset(Charset $cssValue) {
        /** @noinspection PhpUndefinedMethodInspection */
        if (!preg_match('/^utf-?8$/i', $cssValue->getCharset()->getString())) {
            $this->context->buildViolation('Non UTF-8 charset is not allowed on line {{ line }}')
                ->setParameter('{{ line }}', $cssValue->getLineNo())
                ->addViolation();
        }
    }

    private function validateUrl(URL $cssValue) {
        $url = trim($cssValue->getURL()->getString());

        if (stripos($url, 'javascript:') === 0 || stripos($url, 'vbscript:') === 0) {
            $this->context->buildViolation('Script URL is not allowed on line {{ line }}')
                ->setParameter('{{ line }}', $cssValue->getLineNo())
                ->addViolation();
        }

        if (stripos($url, 'data:') === 0) {
            $this->context->buildViolation('Embedded data is not allowed on line {{ line }}')
                ->setParameter('{{ line }}', $cssValue->getLineNo())
                ->addViolation();
        }

        if (strpos($url, '://') !== false || strpos($url, '//') === 0) {
            $this->context->buildViolation('External resource is not allowed on line {{ line }}')
                ->setParameter('{{ line }}', $cssValue->getLineNo())
                ->addViolation();
        }
    }

    private function validateFunction(CSSFunction $cssValue) {
        /** @noinspection PhpParamsInspection */
        $name = trim($cssValue->getName());

        if (strtolower($name) === 'expression') {
            $this->context->buildViolation('expression() syntax is not allowed on line {{ line }}')
                ->setParameter('{{ line }}', $cssValue->getLineNo())
                ->addViolation();
        }
    }

    private function validateString(CSSString $cssValue) {
        if (preg_match('/^\s*expression\s*\(.*\)/i', $cssValue->getString())) {
            $this->context->buildViolation('expression() syntax is not allowed in strings on line {{ line }}')
                ->setParameter('{{ line }}', $cssValue->getLineNo())
                ->addViolation();
        }
    }
}
