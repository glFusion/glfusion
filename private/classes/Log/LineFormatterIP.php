<?php declare(strict_types=1);
/**
* glFusion CMS
*
* LineFormatterIP
*
* @license MIT License
*
*  (c) Jordi Boggiano <j.boggiano@seld.be>
*
* Modified version of LineFormatter class to include IP address in the output
*  Modified by Mark R. Evans - mark AT glFusion DOT org
*
*/

namespace glfusion\Log;

use Monolog\Utils;

/**
 * Formats incoming records into a one-line string
 *
 * This is especially useful for logging to files
 *
 * Modified by Mark R. Evan @ mark AT glfusion DOT org
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Christophe Coevoet <stof@notk.org>
 */
class LineFormatterIP extends \Monolog\Formatter\NormalizerFormatter
{
    public const SIMPLE_FORMAT = "[%datetime%] %ipaddress%: %channel%.%level_name%: %message% %context% %extra%\n";

    protected $format;
    protected $allowInlineLineBreaks;
    protected $ignoreEmptyContextAndExtra;
    protected $includeStacktraces;

    /**
     * @param string|null $format                     The format of the message
     * @param string|null $dateFormat                 The format of the timestamp: one supported by DateTime::format
     * @param bool        $allowInlineLineBreaks      Whether to allow inline line breaks in log entries
     * @param bool        $ignoreEmptyContextAndExtra
     */
    public function __construct(?string $format = null, ?string $dateFormat = null, bool $allowInlineLineBreaks = false, bool $ignoreEmptyContextAndExtra = false)
    {
        $this->format = $format === null ? static::SIMPLE_FORMAT : $format;
        $this->allowInlineLineBreaks = $allowInlineLineBreaks;
        $this->ignoreEmptyContextAndExtra = $ignoreEmptyContextAndExtra;
        parent::__construct($dateFormat);
    }

    public function includeStacktraces(bool $include = true)
    {
        $this->includeStacktraces = $include;
        if ($this->includeStacktraces) {
            $this->allowInlineLineBreaks = true;
        }
    }

    public function allowInlineLineBreaks(bool $allow = true)
    {
        $this->allowInlineLineBreaks = $allow;
    }

    public function ignoreEmptyContextAndExtra(bool $ignore = true)
    {
        $this->ignoreEmptyContextAndExtra = $ignore;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record): string
    {
        $vars = parent::format($record);

        if (!isset($_SERVER['REAL_ADDR'])) {
            $vars['ipaddress'] = sprintf("%15.15s",$_SERVER['REMOTE_ADDR']);
        } else {
            $vars['ipaddress'] = sprintf("%15.15s",$_SERVER['REAL_ADDR']);
        }

        $output = $this->format;

        foreach ($vars['extra'] as $var => $val) {
            if (false !== strpos($output, '%extra.'.$var.'%')) {
                $output = str_replace('%extra.'.$var.'%', $this->stringify($val), $output);
                unset($vars['extra'][$var]);
            }
        }

        foreach ($vars['context'] as $var => $val) {
            if (false !== strpos($output, '%context.'.$var.'%')) {
                $output = str_replace('%context.'.$var.'%', $this->stringify($val), $output);
                unset($vars['context'][$var]);
            }
        }

        if ($this->ignoreEmptyContextAndExtra) {
            if (empty($vars['context'])) {
                unset($vars['context']);
                $output = str_replace('%context%', '', $output);
            }

            if (empty($vars['extra'])) {
                unset($vars['extra']);
                $output = str_replace('%extra%', '', $output);
            }
        }

        foreach ($vars as $var => $val) {
            if (false !== strpos($output, '%'.$var.'%')) {
                $output = str_replace('%'.$var.'%', $this->stringify($val), $output);
            }
        }

        // remove leftover %extra.xxx% and %context.xxx% if any
        if (false !== strpos($output, '%')) {
            $output = preg_replace('/%(?:extra|context)\..+?%/', '', $output);
        }

        return $output;
    }

    public function formatBatch(array $records): string
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }

    public function stringify($value): string
    {
        return $this->replaceNewlines($this->convertToString($value));
    }

    protected function convertToString($data): string
    {
        if (null === $data || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        return (string) $this->toJson($data, true);
    }

    protected function replaceNewlines(string $str): string
    {
        if ($this->allowInlineLineBreaks) {
            if (0 === strpos($str, '{')) {
                return str_replace(array('\r', '\n'), array("\r", "\n"), $str);
            }

            return $str;
        }

        return str_replace(["\r\n", "\r", "\n"], ' ', $str);
    }

}