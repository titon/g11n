<?php
/**
 * @copyright   2010-2013, The Titon Project
 * @license     http://opensource.org/licenses/bsd-license.php
 * @link        http://titon.io
 */

namespace Titon\G11n\Utility;

use Titon\Common\Registry;
use Titon\G11n\G11n;
use Titon\G11n\Exception\MissingPatternException;

/**
 * Enhance the parent Format class by providing localized formatting rule support.
 *
 * @package Titon\G11n\Utility
 */
class Format extends \Titon\Utility\Format {

    /**
     * {@inheritdoc}
     */
    public static function date($time, $format = '%Y-%m-%d') {
        return parent::date($time, self::get('date', $format));
    }

    /**
     * {@inheritdoc}
     */
    public static function datetime($time, $format = '%Y-%m-%d %H:%M:%S') {
        return parent::datetime($time, self::get('datetime', $format));
    }

    /**
     * {@inheritdoc}
     *
     * @uses Titon\Common\Registry
     *
     * @throws \Titon\G11n\Exception\MissingPatternException
     */
    public static function get($key, $fallback = null) {
        $pattern = G11n::registry()->current()->getFormatPatterns($key) ?: $fallback;

        if (!$pattern) {
            throw new MissingPatternException(sprintf('Format pattern %s does not exist', $key));
        }

        return $pattern;
    }

    /**
     * {@inheritdoc}
     */
    public static function phone($value, $format = null) {
        return parent::phone($value, self::get('phone', $format));
    }

    /**
     * {@inheritdoc}
     *
     * @uses Titon\Common\Registry
     */
    public static function relativeTime($time, array $options = array()) {
        $g11n = G11n::registry();
        $msg = function($key) use ($g11n) {
            return $g11n->translate('core.format.relativeTime.' . $key);
        };

        // TODO Find a more optimized way to do this.
        $options = $options + array(
            'seconds' => array($msg('sec'), $msg('second'), $msg('seconds')),
            'minutes' => array($msg('min'), $msg('minute'), $msg('minutes')),
            'hours' => array($msg('hr'), $msg('hour'), $msg('hours')),
            'days' => array($msg('dy'), $msg('day'), $msg('days')),
            'weeks' => array($msg('wk'), $msg('week'), $msg('weeks')),
            'months' => array($msg('mon'), $msg('month'), $msg('months')),
            'years' => array($msg('yr'), $msg('year'), $msg('years')),
            'now' => $msg('now'),
            'in' => $msg('in'),
            'ago' => $msg('ago')
        );

        return parent::relativeTime($time, $options);
    }

    /**
     * {@inheritdoc}
     */
    public static function ssn($value, $format = null) {
        return parent::ssn($value, self::get('ssn', $format));
    }

    /**
     * {@inheritdoc}
     */
    public static function time($time, $format = '%H:%M:%S') {
        return parent::time($time, self::get('time', $format));
    }

}