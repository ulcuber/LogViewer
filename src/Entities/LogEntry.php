<?php

namespace Arcanedev\LogViewer\Entities;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * Class     LogEntry
 *
 * @package  Arcanedev\LogViewer\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntry implements Arrayable, Jsonable, JsonSerializable
{
    protected static $regex = '/(?:\[([^\[\]]*?)\])?(?:\[([^\[\]]*?)\])? ([a-z]+)\.([A-Z]+): (.*)/';
    protected static $propertyGroups = [
        1 => 'setUuid',
        3 => 'setEnv',
        4 => 'setLevel',
    ];

    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var string */
    public $uuid;

    /** @var string */
    public $env;

    /** @var string */
    public $level;

    /** @var \Carbon\Carbon */
    protected $datetime;

    /** @var string */
    public $header;

    /** @var string */
    public $stack;

    /** @var array */
    public $context = [];

    public static function configure()
    {
        static::$regex = '/' . config('log-viewer.parser.regex') . '/';
        foreach (config('log-viewer.parser.property_groups') as $group => $property) {
            static::$propertyGroups[$group] = 'set' . ucfirst($property);
        }
    }

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Construct the log entry instance.
     *
     * @param  array       $header
     * @param  string|null  $stack
     */
    public function __construct(array $header, $stack = null)
    {
        $this->setHeader($header);
        $this->setStack($stack);
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Set entry uuid.
     *
     * @param  string  $uuid
     *
     * @return self
     */
    protected function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Set entry environment.
     *
     * @param  string  $env
     *
     * @return self
     */
    protected function setEnv($env)
    {
        $this->env = $env;

        return $this;
    }

    /**
     * Set the entry level.
     *
     * @param  string  $level
     *
     * @return self
     */
    protected function setLevel($level)
    {
        $this->level = strtolower($level);

        return $this;
    }

    /**
     * Get the entry date time.
     *
     * @param  string  $format
     * @param  string  $datetime
     *
     * @return \Carbon\Carbon
     */
    public function getDatetime()
    {
        if (is_callable($this->datetime)) {
            $this->datetime = ($this->datetime)();
        }
        return $this->datetime;
    }

    /**
     * Set the entry date time.
     *
     * @param  string  $format
     * @param  string  $datetime
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntry
     */
    protected function setDatetime($format, $datetime)
    {
        // defer Carbon instances as they are too heavy
        $this->datetime = function () use ($format, $datetime) {
            return Carbon::createFromFormat($format, $datetime);
        };

        return $this;
    }

    /**
     * Set the entry header.
     *
     * @param  array  $header
     *
     * @return self
     */
    protected function setHeader(array $header)
    {
        $this->setDatetime(...$this->extractDatetime($header));

        $reminder = array_pop($header);
        preg_match_all(static::$regex, $reminder, $matches);
        foreach ($matches as $index => $value) {
            if (!empty($value) && isset(static::$propertyGroups[$index])) {
                call_user_func([$this, static::$propertyGroups[$index]], $value[0]);
            }
        }

        $reminder = array_pop($matches)[0];
        // EXTRACT CONTEXT (Regex from https://stackoverflow.com/a/21995025)
        preg_match_all('/{(?:[^{}]|(?R))*}/x', $reminder, $out);
        if (isset($out[0][0]) && !is_null($context = json_decode($out[0][0], true))) {
            $reminder = str_replace($out[0][0], '', $reminder);
            $this->setContext($context);
        }

        $this->header = trim($reminder);

        return $this;
    }

    /**
     * Set the entry stack.
     *
     * @param  string  $stack
     *
     * @return self
     */
    protected function setStack($stack)
    {
        $this->stack = $stack;

        return $this;
    }

    /**
     * Set the context.
     *
     * @param  array  $context
     *
     * @return $this
     */
    protected function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get translated level name with icon.
     *
     * @return string
     */
    public function level()
    {
        return $this->icon()->toHtml() . ' ' . $this->name();
    }

    /**
     * Get translated level name.
     *
     * @return string
     */
    public function name()
    {
        return log_levels()->get($this->level);
    }

    /**
     * Get level icon.
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function icon()
    {
        return log_styler()->icon($this->level);
    }

    /**
     * Get the entry stack.
     *
     * @return string
     */
    public function stack()
    {
        return trim(htmlentities($this->stack));
    }

    /**
     * Get the entry context as json pretty print.
     *
     * @return string
     */
    public function context()
    {
        return json_encode($this->context, JSON_PRETTY_PRINT);
    }

    /* -----------------------------------------------------------------
     |  Convert Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the log entry as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'uuid'    => $this->uuid,
            'level'    => $this->level,
            'datetime' => $this->getDatetime()->format('Y-m-d H:i:s'),
            'header'   => $this->header,
            'stack'    => $this->stack,
        ];
    }

    /**
     * Convert the log entry to its JSON representation.
     *
     * @param  int  $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Serialize the log entry object to json data.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if the entry has a stack.
     *
     * @return bool
     */
    public function hasStack()
    {
        return $this->stack !== "\n";
    }

    /**
     * Check if the entry has a context.
     *
     * @return bool
     */
    public function hasContext()
    {
        return !empty($this->context);
    }

    /**
     * Check if same log level.
     *
     * @param  string  $level
     *
     * @return bool
     */
    public function isSameLevel($level)
    {
        return $this->level === $level;
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Extract datetime from the header.
     *
     * @param  array  $header
     *
     * @return array
     */
    protected function extractDatetime(array &$header)
    {
        $separator = ($header[2] ?? null) === 'T' ? '\T' : ' ';
        $ms = ($header[3] ?? null) ? '.u' : '';
        $tz = ($header[4] ?? null) ? 'P' : '';
        $format = "Y-m-d{$separator}H:i:s{$ms}{$tz}";
        $datetime = $header[1];

        return [$format, $datetime];
    }
}
