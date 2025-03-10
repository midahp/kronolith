<?php
/**
 * Copyright 1999-2021 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @author  Jan Schneider <jan@horde.org>
 * @package Kronolith
 */

/**
 * Kronolith_Event defines a generic API for events.
 *
 * @property string|null $id
 * @property string|null $creator
 * @property array $geoLocation
 * @property int $indent
 * @property Horde_Date $originalStart
 * @property Horde_Date $originalEnd
 * @property int $overlap
 * @property int $rowspan
 * @property int $span
 * @property array|string|null $tags
 *
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @author  Jan Schneider <jan@horde.org>
 * @package Kronolith
 */
abstract class Kronolith_Event
{
    /**
     * Flag that is set to true if this event has data from either a storage
     * backend or a form or other import method.
     *
     * @var boolean
     */
    public $initialized = false;

    /**
     * Flag that is set to true if this event exists in a storage driver.
     *
     * @var boolean
     */
    public $stored = false;

    /**
     * The driver unique identifier for this event.
     *
     * @var string
     */
    protected $_id = null;

    /**
     * The UID for this event.
     *
     * @var string
     */
    public $uid = null;

    /**
     * The iCalendar SEQUENCE for this event.
     *
     * @var integer
     */
    public $sequence = null;

    /**
     * The iCalendar RECURRENCE-ID for this event exception.
     *
     * @var integer
     */
    public $recurrenceid = null;

    /**
     * The user id of the creator of the event.
     *
     * @var string
     */
    protected $_creator = null;

    /**
     * The email address of the organizer of the event, if known.
     *
     * @var string
     */
    public $organizer = null;

    /**
     * The title of this event.
     *
     * For displaying in the interface use getTitle() instead.
     *
     * @var string
     */
    public $title = '';

    /**
     * The location this event occurs at.
     *
     * @var string
     */
    public $location = '';

    /**
     * The timezone of this event.
     *
     * @var string
     */
    public $timezone;

    /**
     * The status of this event.
     *
     * @var integer
     */
    public $status = Kronolith::STATUS_CONFIRMED;

    /**
     * URL to an icon of this event.
     *
     * @var string
     */
    public $icon = '';

    /**
     * The description for this event.
     *
     * @var string
     */
    public $description = '';

    /**
     * URL of this event.
     *
     * @var string
     */
    public $url = '';

    /**
     * Whether the event is private.
     *
     * @var boolean
     */
    public $private = false;

    /**
     * Event tags from the storage backend (e.g. Kolab)
     *
     * @var array
     */
    protected $_internaltags;

    /**
     * This tag's events.
     *
     * @var array|string
     */
    protected $_tags = null;

    /**
     * Geolocation
     *
     * @var array
     */
    protected $_geoLocation;

    /**
     * Whether this is the event on the first day of a multi-day event.
     *
     * @var boolen
     */
    public $first = true;

    /**
     * Whether this is the event on the last day of a multi-day event.
     *
     * @var boolen
     */
    public $last = true;

    /**
     * All the attendees of this event.
     *
     * @var Kronolith_Attendee_List
     */
    public $attendees;

    /**
     * All resources of this event.
     *
     * This is an associative array where keys are resource uids, values are
     * associative arrays with keys attendance and response.
     *
     * @var array
     */
    protected $_resources = array();

    /**
     * The start time of the event.
     *
     * @var Horde_Date
     */
    public $start;

    /**
     * The end time of the event.
     *
     * @var Horde_Date
     */
    public $end;

    /**
     * The original start time of the event.
     *
     * This may differ from $start on multi-day events where $start is the
     * start time on the current day. For recurring events this is the start
     * time of the current recurrence.
     *
     * @var Horde_Date
     */
    protected $_originalStart;

    /**
     * The original end time of the event.
     *
     * @see $_originalStart for details.
     *
     * @var Horde_Date
     */
    protected $_originalEnd;

    /**
     * The duration of this event in minutes
     *
     * @var integer
     */
    public $durMin = 0;

    /**
     * Whether this is an all-day event.
     *
     * @var boolean
     */
    public $allday = false;

    /**
     * The creation time.
     *
     * @see loadHistory()
     * @var Horde_Date
     */
    public $created;

    /**
     * The creator string.
     *
     * @see loadHistory()
     * @var string
     */
    public $createdby;

    /**
     * The last modification time.
     *
     * @see loadHistory()
     * @var Horde_Date
     */
    public $modified;

    /**
     * The last-modifier string.
     *
     * @see loadHistory()
     * @var string
     */
    public $modifiedby;

    /**
     * Number of minutes before the event starts to trigger an alarm.
     *
     * @var integer
     */
    public $alarm = 0;

    /**
     * Snooze minutes for this event's alarm.
     *
     * @see Horde_Alarm::snooze()
     *
     * @var integer
     */
    protected $_snooze;

    /**
     * The particular alarm methods overridden for this event.
     *
     * @var array
     */
    public $methods;

    /**
     * The identifier of the calender this event exists on.
     *
     * @var string
     */
    public $calendar;

    /**
     * The type of the calender this event exists on.
     *
     * @var string
     */
    public $calendarType;

    /**
     * The HTML background color to be used for this event.
     *
     * @var string
     */
    protected $_backgroundColor = '#dddddd';

    /**
     * The HTML foreground color to be used for this event.
     *
     * @var string
     */
    protected $_foregroundColor = '#000000';

    /**
     * The VarRenderer class to use for printing select elements.
     *
     * @var Horde_Core_Ui_VarRenderer
     */
    private $_varRenderer;

    /**
     * The Horde_Date_Recurrence class for this event.
     *
     * @var Horde_Date_Recurrence
     */
    public $recurrence;

    /**
     * Used in view renderers.
     *
     * @var integer
     */
    protected $_overlap;

    /**
     * Used in view renderers.
     *
     * @var integer
     */
    protected $_indent;

    /**
     * Used in view renderers.
     *
     * @var integer
     */
    protected $_span;

    /**
     * Used in view renderers.
     *
     * @var integer
     */
    protected $_rowspan;

    /**
     * The baseid. For events that represent exceptions this is the UID of the
     * original, recurring event.
     *
     * @var string
     */
    public $baseid;

    /**
     * For exceptions, the date of the original recurring event that this is an
     * exception for.
     *
     * @var Horde_Date
     */
    public $exceptionoriginaldate;

    /**
     * The cached event duration, split up in time units.
     *
     * @see getDuration()
     * @var stdClass
     */
    protected $_duration;

    /**
     * VFS handler
     *
     * @var Horde_Vfs
     */
    protected $_vfs;

    /**
     * List of attributes imported from icalendar which do not map to
     * native Kronolith Event attributes
     *
     * It depends on the backend driver to actually store and recover these
     * 
     * @var array
     */
    protected array $otherAttributes = [];

    /**
     * List of vevent attributes kronolith does handle
     * 
     * If you add capabilities which match with vevent structures,
     * add them here. All unlisted attributes will be saved to otherAttributes
     * and it is up to the driver if they are serialized and restored
     * 
     * @var array 
     */
    protected array $knownAttributes = [
        'ATTACH',
        'ATTENDEE',
        'CATEGORIES',
        'CLASS',
        'CREATED',
        'DESCRIPTION',
        'DTSTART',
        'DTEND',
        'DTSTAMP',
        'EXDATE',
        'GEO',
        'LAST-MODIFIED',
        'LOCATION',
        'ORGANIZER',
        'RRULE',
        'STATUS',
        'SUMMARY',
        'UID',
        'URL',
        'TRANSP',
        'X-FUNAMBOL-ALLDAY',
        'X-HORDE-ATTENDEE'
    ];


    /**
     * Constructor.
     *
     * @param Kronolith_Driver $driver  The backend driver that this event is
     *                                  stored in.
     * @param mixed $eventObject        Backend specific event object
     *                                  that this will represent.
     */
    public function __construct(Kronolith_Driver $driver, $eventObject = null)
    {
        $this->attendees = new Kronolith_Attendee_List();
        $this->calendar = $driver->calendar;
        list($this->_backgroundColor, $this->_foregroundColor) = $driver->colors();

        if (!is_null($eventObject)) {
            $this->fromDriver($eventObject);
        }
    }

    /**
     * Retrieves history information for this event from the history backend.
     */
    public function loadHistory()
    {
        try {
            $log = $GLOBALS['injector']->getInstance('Horde_History')
                ->getHistory('kronolith:' . $this->calendar . ':' . $this->uid);
            $userId = $GLOBALS['registry']->getAuth();
            foreach ($log as $entry) {
                switch ($entry['action']) {
                case 'add':
                    $this->created = new Horde_Date($entry['ts']);
                    if ($userId != $entry['who']) {
                        $this->createdby = sprintf(_("by %s"), Kronolith::getUserName($entry['who']));
                    } else {
                        $this->createdby = _("by me");
                    }
                    break;

                case 'modify':
                    if ($this->modified &&
                        $this->modified->timestamp() >= $entry['ts']) {
                        break;
                    }
                    $this->modified = new Horde_Date($entry['ts']);
                    if ($userId != $entry['who']) {
                        $this->modifiedby = sprintf(_("by %s"), Kronolith::getUserName($entry['who']));
                    } else {
                        $this->modifiedby = _("by me");
                    }
                    break;
                }
            }
        } catch (Horde_Exception $e) {
        }
    }

    /**
     * Setter.
     *
     * Sets the 'id' and 'creator' properties.
     *
     * @param string $name  Property name.
     * @param mixed $value  Property value.
     */
    public function __set($name, $value)
    {
        switch ($name) {
        case 'id':
            if (substr($value, 0, 10) == 'kronolith:') {
                $value = substr($value, 10);
            }
            // Fall through.
        case 'creator':
        case 'geoLocation':
        case 'indent':
        case 'originalStart':
        case 'originalEnd':
        case 'overlap':
        case 'rowspan':
        case 'span':
        case 'tags':
            $this->{'_' . $name} = $value;
            return;
        }
        $trace = debug_backtrace();
        trigger_error('Undefined property via __set(): ' . $name
                      . ' in ' . $trace[0]['file']
                      . ' on line ' . $trace[0]['line'],
                      E_USER_NOTICE);
    }

    /**
     * Getter.
     *
     * Returns the 'id' and 'creator' properties.
     *
     * @param string $name  Property name.
     *
     * @return mixed  Property value.
     */
    public function __get($name)
    {
        switch ($name) {
        case 'id':
        case 'indent':
        case 'overlap':
        case 'rowspan':
        case 'span':
            return $this->{'_' . $name};
        case 'creator':
            if (empty($this->_creator)) {
                $this->_creator = $GLOBALS['registry']->getAuth();
            }
            return $this->_creator;
            break;
        case 'originalStart':
            if (empty($this->_originalStart)) {
                $this->_originalStart = $this->start;
            }
            return $this->_originalStart;
            break;
        case 'originalEnd':
            if (empty($this->_originalEnd)) {
                $this->_originalEnd = $this->start;
            }
            return $this->_originalEnd;
            break;
        case 'tags':
            if (!isset($this->_tags)) {
                $this->synchronizeTags(Kronolith::getTagger()->getTags($this->uid, Kronolith_Tagger::TYPE_EVENT));
            }
            return $this->_tags;
        case 'geoLocation':
            if (!isset($this->_geoLocation)) {
                try {
                    $this->_geoLocation = $GLOBALS['injector']->getInstance('Kronolith_Geo')->getLocation($this->id);
                } catch (Kronolith_Exception $e) {}
            }
            return $this->_geoLocation;
        }

        $trace = debug_backtrace();
        trigger_error('Undefined property via __get(): ' . $name
                      . ' in ' . $trace[0]['file']
                      . ' on line ' . $trace[0]['line'],
                      E_USER_NOTICE);
        return null;
    }

    /**
     * Returns a reference to a driver that's valid for this event.
     *
     * @return Kronolith_Driver  A driver that this event can use to save
     *                           itself, etc.
     */
    public function getDriver()
    {
        return Kronolith::getDriver(str_replace('Kronolith_Event_', '', get_class($this)), $this->calendar);
    }

    /**
     * Returns the share this event belongs to.
     *
     * @return Horde_Share  This event's share.
     * @throws Kronolith_Exception
     */
    public function getShare()
    {
        if ($GLOBALS['calendar_manager']->getEntry(Kronolith::ALL_CALENDARS, $this->calendar) !== false) {
            return $GLOBALS['calendar_manager']->getEntry(Kronolith::ALL_CALENDARS, $this->calendar)->share();
        }
        throw new LogicException('Share not found');
    }

    /**
     * Encapsulates permissions checking.
     *
     * @param integer $permission  The permission to check for.
     * @param string $user         The user to check permissions for.
     *
     * @return boolean
     */
    public function hasPermission($permission, $user = null)
    {
        if ($user === null) {
            $user = $GLOBALS['registry']->getAuth();
        }
        try {
            $share = $this->getShare();
        } catch (Exception $e) {
            return false;
        }

        if ($share->get('owner') == null && $GLOBALS['registry']->isAdmin()) {
            return true;
        }
        return $share->hasPermission($user, $permission, $this->creator);
    }

    /**
     * Saves changes to this event.
     *
     * @return integer  The event id.
     * @throws Kronolith_Exception
     */
    public function save()
    {
        if (!$this->initialized) {
            throw new LogicException('Event not yet initialized');
        }

        /* Check for acceptance/denial of this event's resources. */
        $accepted_resources = Kronolith_Resource::checkResources($this);

        /* Save */
        $result = $this->getDriver()->saveEvent($this);

        /* Now that the event is definitely commited to storage, we can add
         * the event to each resource that has accepted. Not very efficient,
         * but this also solves the problem of not having a GUID for the event
         * until after it's saved. If we add the event to the resources
         * calendar before it is saved, they will have different GUIDs, and
         * hence no longer refer to the same event. */
        foreach ($accepted_resources as $resource) {
            $resource->addEvent($this);
        }

        $hordeAlarm = $GLOBALS['injector']->getInstance('Horde_Alarm');
        if ($alarm = $this->toAlarm(new Horde_Date($_SERVER['REQUEST_TIME']))) {
            $hordeAlarm->set($alarm);
            if ($this->_snooze) {
                $hordeAlarm->snooze($this->uid, $GLOBALS['registry']->getAuth(), $this->_snooze);
            }
        } else {
            $hordeAlarm->delete($this->uid);
        }

        return $result;
    }

    /**
     * Imports a backend specific event object.
     *
     * @param mixed $eventObject  Backend specific event object that this
     *                            object will represent.
     */
    public function fromDriver($event)
    {
    }

    /**
     * Exports this event in iCalendar format.
     *
     * @param Horde_Icalendar $calendar  A Horde_Icalendar object that acts as
     *                                   a container.
     * @param boolean  $includeFiles     Include attached files in the iCalendar
     *                                   file? @since 4.3.0
     *
     * @return array  An array of Horde_Icalendar_Vevent objects for this event.
     */
    public function toiCalendar($calendar, $includeFiles = true)
    {
        $vEvent = Horde_Icalendar::newComponent('vevent', $calendar);
        $v1 = $calendar->getAttribute('VERSION') == '1.0';
        $vEvents = array();

        // For certain recur types, we must output in the event's timezone
        // so that the BYDAY values do not get out of sync with the UTC
        // date-time. See Bug: 11339
        if ($this->recurs()) {
            switch ($this->recurrence->getRecurType()) {
            case Horde_Date_Recurrence::RECUR_WEEKLY:
            case Horde_Date_Recurrence::RECUR_YEARLY_WEEKDAY:
            case Horde_Date_Recurrence::RECUR_MONTHLY_WEEKDAY:
            case Horde_Date_Recurrence::RECUR_MONTHLY_LAST_WEEKDAY:
                if (!$this->timezone) {
                    $this->timezone = date_default_timezone_get();
                }
            }
        }

        if ($this->isAllDay()) {
            $vEvent->setAttribute('DTSTART', $this->start, array('VALUE' => 'DATE'));
            $vEvent->setAttribute('DTEND', $this->end, array('VALUE' => 'DATE'));
            $vEvent->setAttribute('X-FUNAMBOL-ALLDAY', 1);
        } else {
            $this->setTimezone(true);
            $params = array();
            if ($this->timezone) {
                try {
                    if (!$this->baseid) {
                        $tz = $GLOBALS['injector']->getInstance('Horde_Timezone');
                        $vEvents[] = $tz->getZone($this->timezone)->toVtimezone($this->start, $this->end);
                    }
                    $params['TZID'] = $this->timezone;
                } catch (Horde_Exception $e) {
                    Horde::log('Unable to locate the tz database.', 'WARN');
                }
            }

            $vEvent->setAttribute('DTSTART', clone $this->start, $params);
            $vEvent->setAttribute('DTEND', clone $this->end, $params);
        }
        foreach ($this->otherAttributes as $attribute) {
            $vEvent->setAttribute($attribute['name'], $attribute['value'], $attribute['params'], true, $attribute['values']);
        }

        $vEvent->setAttribute('DTSTAMP', $_SERVER['REQUEST_TIME']);
        $vEvent->setAttribute('UID', $this->uid);

        /* Get the event's create and last modify date. */
        $created = $modified = null;
        try {
            $history = $GLOBALS['injector']->getInstance('Horde_History');
            $created = $history->getActionTimestamp(
                'kronolith:' . $this->calendar . ':' . $this->uid, 'add');
            $modified = $history->getActionTimestamp(
                'kronolith:' . $this->calendar . ':' . $this->uid, 'modify');
            /* The history driver returns 0 for not found. If 0 or null does
             * not matter, strip this. */
            if ($created == 0) {
                $created = null;
            }
            if ($modified == 0) {
                $modified = null;
            }
        } catch (Exception $e) {
        }
        if (!empty($created)) {
            $vEvent->setAttribute($v1 ? 'DCREATED' : 'CREATED', $created);
            if (empty($modified)) {
                $modified = $created;
            }
        }
        if (!empty($modified)) {
            $vEvent->setAttribute('LAST-MODIFIED', $modified);
        }

        $vEvent->setAttribute('SUMMARY', $this->getTitle());

        // Organizer
        if ($this->organizer) {
            $vEvent->setAttribute('ORGANIZER', 'mailto:' . $this->organizer, array());
        } elseif (count($this->attendees)) {
            $name = Kronolith::getUserName($this->creator);
            $email = Kronolith::getUserEmail($this->creator);
            $params = array();
            if ($v1) {
                $tmp = new Horde_Mail_Rfc822_Address($email);
                if (!empty($name)) {
                    $tmp->personal = $name;
                }
                $email = strval($tmp);
            } else {
                if (!empty($name)) {
                    $params['CN'] = $name;
                }
                if (!empty($email)) {
                    $email = 'mailto:' . $email;
                }
            }
            $vEvent->setAttribute('ORGANIZER', $email, $params);
        }
        if (!$this->isPrivate()) {
            if (!empty($this->description)) {
                $vEvent->setAttribute('DESCRIPTION', $this->description);
            }

            // Tags
            if ($this->tags) {
                $vEvent->setAttribute('CATEGORIES', '', array(), true, array_values($this->tags));
            }

            // Location
            if (!empty($this->location)) {
                $vEvent->setAttribute('LOCATION', $this->location);
            }
            if ($this->geoLocation) {
                $vEvent->setAttribute('GEO', array('latitude' => $this->geoLocation['lat'], 'longitude' => $this->geoLocation['lon']));
            }

            // URL
            if (!empty($this->url)) {
                $vEvent->setAttribute('URL', $this->url);
            }
        }
        $vEvent->setAttribute('CLASS', $this->private ? 'PRIVATE' : 'PUBLIC');

        // Status.
        switch ($this->status) {
        case Kronolith::STATUS_FREE:
            // This is not an official iCalendar value, but we need it for
            // synchronization.
            $vEvent->setAttribute('STATUS', 'FREE');
            $vEvent->setAttribute('TRANSP', $v1 ? 1 : 'TRANSPARENT');
            break;
        case Kronolith::STATUS_TENTATIVE:
            $vEvent->setAttribute('STATUS', 'TENTATIVE');
            $vEvent->setAttribute('TRANSP', $v1 ? 0 : 'OPAQUE');
            break;
        case Kronolith::STATUS_CONFIRMED:
            $vEvent->setAttribute('STATUS', 'CONFIRMED');
            $vEvent->setAttribute('TRANSP', $v1 ? 0 : 'OPAQUE');
            break;
        case Kronolith::STATUS_CANCELLED:
            if ($v1) {
                $vEvent->setAttribute('STATUS', 'DECLINED');
                $vEvent->setAttribute('TRANSP', 1);
            } else {
                $vEvent->setAttribute('STATUS', 'CANCELLED');
                $vEvent->setAttribute('TRANSP', 'TRANSPARENT');
            }
            break;
        }

        // Attendees.
        foreach ($this->attendees as $attendee) {
            $params = array();
            switch ($attendee->role) {
            case Kronolith::PART_REQUIRED:
                if ($v1) {
                    $params['EXPECT'] = 'REQUIRE';
                } else {
                    $params['ROLE'] = 'REQ-PARTICIPANT';
                }
                break;

            case Kronolith::PART_OPTIONAL:
                if ($v1) {
                    $params['EXPECT'] = 'REQUEST';
                } else {
                    $params['ROLE'] = 'OPT-PARTICIPANT';
                }
                break;

            case Kronolith::PART_NONE:
                if ($v1) {
                    $params['EXPECT'] = 'FYI';
                } else {
                    $params['ROLE'] = 'NON-PARTICIPANT';
                }
                break;
            }

            switch ($attendee->response) {
            case Kronolith::RESPONSE_NONE:
                if ($v1) {
                    $params['STATUS'] = 'NEEDS ACTION';
                    $params['RSVP'] = 'YES';
                } else {
                    $params['PARTSTAT'] = 'NEEDS-ACTION';
                    $params['RSVP'] = 'TRUE';
                }
                break;

            case Kronolith::RESPONSE_ACCEPTED:
                if ($v1) {
                    $params['STATUS'] = 'ACCEPTED';
                } else {
                    $params['PARTSTAT'] = 'ACCEPTED';
                }
                break;

            case Kronolith::RESPONSE_DECLINED:
                if ($v1) {
                    $params['STATUS'] = 'DECLINED';
                } else {
                    $params['PARTSTAT'] = 'DECLINED';
                }
                break;

            case Kronolith::RESPONSE_TENTATIVE:
                if ($v1) {
                    $params['STATUS'] = 'TENTATIVE';
                } else {
                    $params['PARTSTAT'] = 'TENTATIVE';
                }
                break;
            }

            $email = $attendee->email;
            if (strpos($email, '@') === false) {
                $email = '';
            }
            if ($v1) {
                if ($attendee->user) {
                    $attribute = 'X-HORDE-ATTENDEE';
                    $email = $attendee->user;
                } elseif (empty($email)) {
                    $attribute = 'ATTENDEE';
                    if (strlen($attendee->name)) {
                        $email = $attendee->name;
                    }
                } else {
                    $attribute = 'ATTENDEE';
                    $tmp = new Horde_Mail_Rfc822_Address($email);
                    if (strlen($attendee->name)) {
                        $tmp->personal = $attendee->name;
                    }
                    $email = strval($tmp);
                }
            } else {
                if (strlen($attendee->name)) {
                    $params['CN'] = $attendee->name;
                }
                if ($attendee->user) {
                    $attribute = 'X-HORDE-ATTENDEE';
                    $email = $attendee->user;
                } elseif (empty($email)) {
                    $attribute = 'ATTENDEE';
                } else {
                    $attribute = 'ATTENDEE';
                    $email = 'mailto:' . $email;
                }
            }

            $vEvent->setAttribute($attribute, $email, $params);
        }

        // Alarms.
        if (!empty($this->alarm)) {
            if ($v1) {
                $alarm = new Horde_Date($this->start);
                $alarm->min -= $this->alarm;
                $vEvent->setAttribute('AALARM', $alarm);
            } else {
                $vAlarm = Horde_Icalendar::newComponent('valarm', $vEvent);
                $vAlarm->setAttribute('ACTION', 'DISPLAY');
                $vAlarm->setAttribute('DESCRIPTION', $this->getTitle());
                $vAlarm->setAttribute(
                    'TRIGGER;VALUE=DURATION',
                    ($this->alarm > 0 ? '-' : '') . 'PT' . abs($this->alarm) . 'M'
                );
                $vEvent->addComponent($vAlarm);
            }
            $hordeAlarm = $GLOBALS['injector']->getInstance('Horde_Alarm');
            if ($hordeAlarm->exists($this->uid, $GLOBALS['registry']->getAuth()) &&
                $hordeAlarm->isSnoozed($this->uid, $GLOBALS['registry']->getAuth())) {
                $vEvent->setAttribute('X-MOZ-LASTACK', new Horde_Date($_SERVER['REQUEST_TIME']));
                $alarm = $hordeAlarm->get($this->uid, $GLOBALS['registry']->getAuth());
                if (!empty($alarm['snooze'])) {
                    $alarm['snooze']->setTimezone(date_default_timezone_get());
                    $vEvent->setAttribute('X-MOZ-SNOOZE-TIME', $alarm['snooze']);
                }
            }
        }

        // Attached files
        if ($includeFiles && count($this->listFiles())) {
            $vfs = $this->vfsInit();
            foreach ($this->listFiles() as $cnt => $file) {
                try {
                    $data = $vfs->read(Kronolith::VFS_PATH . '/' . $this->getVfsUid(), $file['name']);
                } catch (Horde_Vfs_Exception $e) {
                    Horde::log($e->getMessage, 'ERR');
                }
                if ($data) {
                    try {
                        // We should have filename and type information.
                        $filename = empty($file['name']) ? 'attachment' . $cnt . '.' .'$file[type]'  : $file['name'] ;
                        // At least recent MS clients implementing MS-OXCMSG 23.1 understand X-FILENAME
                        $vEvent->setAttribute('ATTACH', base64_encode($data), [
                              'FMTTYPE' => $file['type'],
                              'X-FILENAME' => $filename,
                              'VALUE' => 'BINARY',
                              'ENCODING' => 'BASE64'
                            ]
                        );
                    } catch (Horde_Icalendar_Exception $e) {
                        Horde::log($e->getMessage(), 'ERR');
                    }
                }
            }
        }

        // Recurrence.
        if ($this->recurs()) {
            if ($v1) {
                $rrule = $this->recurrence->toRRule10($calendar);
            } else {
                $rrule = $this->recurrence->toRRule20($calendar);
            }
            if (!empty($rrule)) {
                $vEvent->setAttribute('RRULE', $rrule);
            }

            // Exceptions. An exception with no replacement event is represented
            // by EXDATE, and those with replacement events are represented by
            // a new vEvent element. We get all known replacement events first,
            // then remove the exceptionoriginaldate from the list of the event
            // exceptions. Any exceptions left should represent exceptions with
            // no replacement.
            $exceptions = $this->recurrence->getExceptions();
            $search = new stdClass();
            $search->baseid = $this->uid;
            $results = $this->getDriver()->search($search);
            foreach ($results as $days) {
                foreach ($days as $exceptionEvent) {
                    // Need to change the UID so it links to the original
                    // recurring event, but only if not using $v1. If using $v1,
                    // we add the date to EXDATE and do NOT change the UID.
                    if (!$v1) {
                        $exceptionEvent->uid = $this->uid;
                    }
                    $vEventException = $exceptionEvent->toiCalendar($calendar);

                    // This should never happen, but protect against it anyway.
                    if (count($vEventException) > 2 ||
                        (count($vEventException) > 1 &&
                         !($vEventException[0] instanceof Horde_Icalendar_Vtimezone) &&
                         !($vEventException[1] instanceof Horde_Icalendar_Vtimezone))) {
                        throw new Kronolith_Exception(_("Unable to parse event."));
                    }
                    $vEventException = array_pop($vEventException);
                    // If $v1, need to add to EXDATE
                    if (!$this->isAllDay()) {
                        $exceptionEvent->setTimezone(true);
                    }
                    if (!$v1) {
                        $vEventException->setAttribute('RECURRENCE-ID', $exceptionEvent->exceptionoriginaldate);
                    } else {
                        $vEvent->setAttribute('EXDATE', array($exceptionEvent->exceptionoriginaldate), array('VALUE' => 'DATE'));
                    }
                    $originaldate = $exceptionEvent->exceptionoriginaldate->format('Ymd');
                    $key = array_search($originaldate, $exceptions);
                    if ($key !== false) {
                        unset($exceptions[$key]);
                    }
                    $vEvents[] = $vEventException;
                }
            }

            /* The remaining exceptions represent deleted recurrences */
            foreach ($exceptions as $exception) {
                if (!empty($exception)) {
                    // Use multiple EXDATE attributes instead of EXDATE
                    // attributes with multiple values to make Apple iCal
                    // happy.
                    list($year, $month, $mday) = sscanf($exception, '%04d%02d%02d');
                    if ($this->isAllDay()) {
                        $vEvent->setAttribute('EXDATE', array(new Horde_Date($year, $month, $mday)), array('VALUE' => 'DATE'));
                    } else {
                        // Another Apple iCal/Calendar fix. EXDATE is only
                        // recognized if the full datetime is present and matches
                        // the time part given in DTSTART.
                        $params = array();
                        if ($this->timezone) {
                            $params['TZID'] = $this->timezone;
                        }
                        $exdate = clone $this->start;
                        $exdate->year = $year;
                        $exdate->month = $month;
                        $exdate->mday = $mday;
                        $vEvent->setAttribute('EXDATE', array($exdate), $params);
                    }
                }
            }
        }
        array_unshift($vEvents, $vEvent);

        $this->setTimezone(false);

        return $vEvents;
    }

    /**
     * Updates the properties of this event from a Horde_Icalendar_Vevent
     * object.
     *
     * @param Horde_Icalendar_Vevent $vEvent  The iCalendar data to update
     *                                        from.
     * @param boolean $parseAttendees         Parse attendees too?
     *                                        @since Kronolith 4.2
     */
    public function fromiCalendar($vEvent, $parseAttendees = false)
    {
        // Unique ID.
        try {
            $uid = $vEvent->getAttribute('UID');
            if (!empty($uid)) {
                $this->uid = $uid;
            }
        } catch (Horde_Icalendar_Exception $e) {}

        // Organizer
        try {
            $organizer = $vEvent->getAttribute('ORGANIZER');
            if (!empty($organizer)) {
                $this->organizer = str_replace(array('MAILTO:', 'mailto:'), '', $organizer);
            }
        } catch (Horde_Icalendar_Exception $e) {}

        // Sequence.
        try {
            $seq = $vEvent->getAttribute('SEQUENCE');
            if (is_int($seq)) {
                $this->sequence = $seq;
            }
        } catch (Horde_Icalendar_Exception $e) {}

        // Title, tags and description.
        try {
            $title = $this->_ensureUtf8($vEvent->getAttribute('SUMMARY'));
            if (!is_array($title)) {
                $this->title = $title;
            }
        } catch (Horde_Icalendar_Exception $e) {}

        // Tags
        try {
            $this->_tags = $vEvent->getAttributeValues('CATEGORIES');
        } catch (Horde_Icalendar_Exception $e) {}

        // Description
        try {
            $desc = $this->_ensureUtf8($vEvent->getAttribute('DESCRIPTION'));
            if (!is_array($desc)) {
                $this->description = $desc;
            }
        } catch (Horde_Icalendar_Exception $e) {}

        // Remote Url
        try {
            $url = $vEvent->getAttribute('URL');
            if (!is_array($url)) {
                $this->url = $url;
            }
        } catch (Horde_Icalendar_Exception $e) {}

        // Location
        try {
            $location = $this->_ensureUtf8($vEvent->getAttribute('LOCATION'));
            if (!is_array($location)) {
                $this->location = $location;
            }
        } catch (Horde_Icalendar_Exception $e) {}

        try {
            $geolocation = $vEvent->getAttribute('GEO');
            $this->geoLocation = array(
                'lat' => $geolocation['latitude'],
                'lon' => $geolocation['longitude']
            );
        } catch (Horde_Icalendar_Exception $e) {}

        // Class
        try {
            $class = $vEvent->getAttribute('CLASS');
            if (!is_array($class)) {
                $class = Horde_String::upper($class);
                $this->private = $class == 'PRIVATE' || $class == 'CONFIDENTIAL';
            }
        } catch (Horde_Icalendar_Exception $e) {}

        // Status.
        try {
            $status = $vEvent->getAttribute('STATUS');
            if (!is_array($status)) {
                $status = Horde_String::upper($status);
                if ($status == 'DECLINED') {
                    $status = 'CANCELLED';
                }
                if (defined('Kronolith::STATUS_' . $status)) {
                    $this->status = constant('Kronolith::STATUS_' . $status);
                }
            }
        } catch (Horde_Icalendar_Exception $e) {}

        // Reset allday flag in case this has changed. Will be recalculated
        // next time isAllDay() is called.
        $this->allday = false;

        // Start and end date.
        $tzid = null;
        try {
            $start = $vEvent->getAttribute('DTSTART');
            $startParams = $vEvent->getAttribute('DTSTART', true);
            // We don't support different timezones for different attributes,
            // so use the DTSTART timezone for the complete event.
            if (isset($startParams[0]['TZID'])) {
                // Horde_Date supports timezone aliases, so try that first.
                $tz = $startParams[0]['TZID'];
                try {
                    // Check if the timezone name is supported by PHP natively.
                    new DateTimeZone($tz);
                    $this->timezone = $tzid = $tz;
                } catch (Exception $e) {
                }
            }
            if (!is_array($start)) {
                // Date-Time field
                $this->start = new Horde_Date($start, $tzid);
            } else {
                // Date field
                $this->start = new Horde_Date(
                    array('year'  => (int)$start['year'],
                          'month' => (int)$start['month'],
                          'mday'  => (int)$start['mday']),
                    $tzid
                );
            }
        } catch (Horde_Icalendar_Exception $e) {
            throw new Kronolith_Exception($e);
        } catch (Horde_Date_Exception $e) {
            throw new Kronolith_Exception($e);
        }

        try {
            $end = $vEvent->getAttribute('DTEND');
            if (!is_array($end)) {
                // Date-Time field
                $this->end = new Horde_Date($end, $tzid);
                // All day events are transferred by many device as
                // DSTART: YYYYMMDDT000000 DTEND: YYYYMMDDT2359(59|00)
                // Convert accordingly
                if (is_object($this->start) && $this->start->hour == 0 &&
                    $this->start->min == 0 && $this->start->sec == 0 &&
                    $this->end->hour == 23 && $this->end->min == 59) {
                    $this->end = new Horde_Date(
                        array('year'  => (int)$this->end->year,
                              'month' => (int)$this->end->month,
                              'mday'  => (int)$this->end->mday + 1),
                        $tzid);
                }
            } else {
                // Date field
                $this->end = new Horde_Date(
                    array('year'  => (int)$end['year'],
                          'month' => (int)$end['month'],
                          'mday'  => (int)$end['mday']),
                    $tzid);
            }
        } catch (Horde_Icalendar_Exception $e) {
            $end = null;
        }

        if (is_null($end)) {
            try {
                $duration = $vEvent->getAttribute('DURATION');
                if (!is_array($duration)) {
                    $this->end = new Horde_Date($this->start);
                    $this->end->sec += $duration;
                    $end = 1;
                }
            } catch (Horde_Icalendar_Exception $e) {}

            if (is_null($end)) {
                // End date equal to start date as per RFC 2445.
                $this->end = new Horde_Date($this->start);
                if (is_array($start)) {
                    // Date field
                    $this->end->mday++;
                }
            }
        }

        // vCalendar 1.0 alarms
        try {
            $alarm = $vEvent->getAttribute('AALARM');
            if (!is_array($alarm) && intval($alarm)) {
                $this->alarm = intval(($this->start->timestamp() - $alarm) / 60);
            }
        } catch (Horde_Icalendar_Exception $e) {}

        // vCalendar 2.0 alarms
        foreach ($vEvent->getComponents() as $alarm) {
            if (!($alarm instanceof Horde_Icalendar_Valarm)) {
                continue;
            }
            try {
                if ($alarm->getAttribute('ACTION') == 'NONE') {
                    continue;
                }
            } catch (Horde_Icalendar_Exception $e) {
            }
            try {
                // @todo consider implementing different ACTION types.
                // $action = $alarm->getAttribute('ACTION');
                $trigger = $alarm->getAttribute('TRIGGER');
                if ($trigger === 0) {
                    // if trigger is explicitly 0 then set it to one minute before the event
                    $trigger = -60;
                }
                $triggerParams = $alarm->getAttribute('TRIGGER', true);
            } catch (Horde_Icalendar_Exception $e) {
                continue;
            }
            if (!is_array($triggerParams)) {
                $triggerParams = array($triggerParams);
            }
            $haveTrigger = false;
            foreach ($triggerParams as $tp) {
                if (isset($tp['VALUE']) &&
                    $tp['VALUE'] == 'DATE-TIME') {
                    if (isset($tp['RELATED']) &&
                        $tp['RELATED'] == 'END') {
                        $this->alarm = intval(($this->end->timestamp() - $trigger) / 60);
                    } else {
                        $this->alarm = intval(($this->start->timestamp() - $trigger) / 60);
                    }
                    $haveTrigger = true;
                    break;
                } elseif (isset($tp['RELATED']) && $tp['RELATED'] == 'END') {
                    $this->alarm = -intval($trigger / 60);
                    $this->alarm -= $this->durMin;
                    $haveTrigger = true;
                    break;
                }
            }
            if (!$haveTrigger) {
                $this->alarm = -intval($trigger / 60);
            }
            break;
        }

        // Alarm snoozing/dismissal
        if ($this->alarm) {
            try {
                // If X-MOZ-LASTACK is set, this event is either dismissed or
                // snoozed.
                $vEvent->getAttribute('X-MOZ-LASTACK');
                try {
                    // If X-MOZ-SNOOZE-TIME is set, this event is snoozed.
                    $snooze = $vEvent->getAttribute('X-MOZ-SNOOZE-TIME');
                    $this->_snooze = intval(($snooze - time()) / 60);
                } catch (Horde_Icalendar_Exception $e) {
                    // If X-MOZ-SNOOZE-TIME is not set, this event is dismissed.
                    $this->_snooze = -1;
                }
            } catch (Horde_Icalendar_Exception $e) {
            }
        }

        // Attached files, we require a UID so we can attach the file in VFS.
        if ($this->uid) {
            $attach = false;
            $attach_params = array();
            try {
                $attach = $vEvent->getAttribute('ATTACH');
                $attach_params = $vEvent->getAttribute('ATTACH', true);
                if (!is_array($attach)) {
                    $attach = array($attach);
                }
                foreach ($attach as $key => $attribute) {
                    if (isset($attach_params[$key]['VALUE']) &&
                        Horde_String::lower($attach_params[$key]['VALUE']) == 'uri') {
                        // @todo
                    } elseif (Horde_String::upper($attach_params[$key]['ENCODING']) == 'BASE64') {
                        $mime_type = !empty($attach_params[$key]['FMTTYPE'])
                            ? $attach_params[$key]['FMTTYPE']
                            : '';
                        // @todo We really should add stream support to VFS
                        // base64_decode will swallow almost anything without strict.
                        // Use strict check to bypass decoding. Advertising base64 and sending plain happens with some clients
                        if (base64_decode($attribute, true) === false) {
                            $file_data = $attribute;
                        } else {
                            $file_data = base64_decode($attribute);
                        }
                        $vfs = $this->vfsInit();
                        $dir = Kronolith::VFS_PATH . '/' . $this->getVfsUid();
                        // Some clients use X-FILENAME to convey filename.
                        // Otherwise, we can only generate a standin filename from type
                        if (!empty($attachment_params[$key]['X-FILENAME'])) {
                            $filename = $attachment_params[$key]['X-FILENAME'];
                        } else {
                            $filename = sprintf(_("File %d.%s"), $key, Horde_Mime_Magic::mimeToExt($mime_type));
                        }
                        try {
                            $vfs->writeData($dir, $filename, $file_data, true);
                        } catch (Horde_Vfs_Exception $e) {
                            Horde::log($e->getMessage(), 'ERR');
                        }
                    }
                }
            } catch (Horde_Icalendar_Exception $e) {}
        }

        // Attendance.
        // Importing attendance may result in confusion: editing an imported
        // copy of an event can cause invitation updates to be sent from
        // people other than the original organizer. So we don't import by
        // default. However to allow updates by synchronization, this behavior
        // can be overriden.
        // X-ATTENDEE is there for historical reasons. @todo remove in
        // Kronolith 5.
        $attendee = $users = null;
        if ($parseAttendees) {
            try {
                $attendee = $vEvent->getAttribute('ATTENDEE');
                $params = $vEvent->getAttribute('ATTENDEE', true);
            } catch (Horde_Icalendar_Exception $e) {
                try {
                    $attendee = $vEvent->getAttribute('X-ATTENDEE');
                    $params = $vEvent->getAttribute('X-ATTENDEE', true);
                } catch (Horde_Icalendar_Exception $e) {
                }
            }
            if ($attendee && !is_array($attendee)) {
                $attendee = array($attendee);
                $params = array($params);
            }
            try {
                $users = $vEvent->getAttribute('X-HORDE-ATTENDEE');
                $userParams = $vEvent->getAttribute('X-HORDE-ATTENDEE', true);
                if (!is_array($users)) {
                    $users = array($users);
                    $userParams = array($userParams);
                }
                foreach ($userParams as &$param) {
                    $param['hordeUser'] = true;
                }
                if ($attendee) {
                    $attendee = array_merge($attendee, $users);
                    $params = array_merge($params, $userParams);
                } else {
                    $attendee = $users;
                    $params = $userParams;
                }
            } catch (Horde_Icalendar_Exception $e) {
            }
        }
        if ($attendee) {
            // Clear the attendees since we might be editing/replacing the event
            $this->attendees = new Kronolith_Attendee_List();
            for ($i = 0; $i < count($attendee); ++$i) {
                // Default according to rfc2445:
                $attendance = Kronolith::PART_REQUIRED;
                // vCalendar 2.0 style:
                if (!empty($params[$i]['ROLE'])) {
                    switch($params[$i]['ROLE']) {
                    case 'OPT-PARTICIPANT':
                        $attendance = Kronolith::PART_OPTIONAL;
                        break;

                    case 'NON-PARTICIPANT':
                        $attendance = Kronolith::PART_NONE;
                        break;
                    }
                }
                // vCalendar 1.0 style;
                if (!empty($params[$i]['EXPECT'])) {
                    switch($params[$i]['EXPECT']) {
                    case 'REQUEST':
                        $attendance = Kronolith::PART_OPTIONAL;
                        break;

                    case 'FYI':
                        $attendance = Kronolith::PART_NONE;
                        break;
                    }
                }
                $response = Kronolith::RESPONSE_NONE;
                if (empty($params[$i]['PARTSTAT']) &&
                    !empty($params[$i]['STATUS'])) {
                    $params[$i]['PARTSTAT']  = $params[$i]['STATUS'];
                }

                if (!empty($params[$i]['PARTSTAT'])) {
                    switch($params[$i]['PARTSTAT']) {
                    case 'ACCEPTED':
                        $response = Kronolith::RESPONSE_ACCEPTED;
                        break;

                    case 'DECLINED':
                        $response = Kronolith::RESPONSE_DECLINED;
                        break;

                    case 'TENTATIVE':
                        $response = Kronolith::RESPONSE_TENTATIVE;
                        break;
                    }
                }

                if (!empty($params[$i]['hordeUser'])) {
                    $this->attendees->add(new Kronolith_Attendee(array(
                        'user' => $attendee[$i],
                        'role' => $attendance,
                        'response' => $response
                    )));
                } else {
                    $tmp = new Horde_Mail_Rfc822_Address(
                        str_replace(array('MAILTO:', 'mailto:'), '', $attendee[$i])
                    );
                    $email = $tmp->bare_address;
                    $name = isset($params[$i]['CN']) ? $this->_ensureUtf8($params[$i]['CN']) : null;
                    $this->addAttendee($email, $attendance, $response, $name);
                }
            }
        }

        $this->_handlevEventRecurrence($vEvent);
        foreach ($vEvent->getAllAttributes() as $attribute) {
            // drop all known attributes
            if (in_array($attribute['name'], $this->knownAttributes)) {
                continue;
            }
            $this->otherAttributes[] = $attribute;
        }

        $this->initialized = true;
    }

    /**
     * Handle parsing recurrence related fields.
     *
     * @param Horde_Icalendar $vEvent
     * @throws Kronolith_Exception
     */
    protected function _handlevEventRecurrence($vEvent)
    {
        // Recurrence.
        try {
            $rrule = $vEvent->getAttribute('RRULE');
            if (!is_array($rrule)) {
                $this->recurrence = new Horde_Date_Recurrence($this->start);
                if (strpos($rrule, '=') !== false) {
                    $this->recurrence->fromRRule20($rrule);
                } else {
                    $this->recurrence->fromRRule10($rrule);
                }

                // Exceptions. EXDATE represents deleted events, just add the
                // exception, no new event is needed.
                $exdates = $vEvent->getAttributeValues('EXDATE');
                if (is_array($exdates)) {
                    foreach ($exdates as $exdate) {
                        if (is_array($exdate)) {
                            $this->recurrence->addException(
                                (int)$exdate['year'],
                                (int)$exdate['month'],
                                (int)$exdate['mday']);
                        }
                    }
                }
            }
        } catch (Horde_Icalendar_Exception $e) {}

        // RECURRENCE-ID indicates that this event represents an exception
        try {
            $this->recurrenceid = $vEvent->getAttribute('RECURRENCE-ID');
            $originaldt = new Horde_Date($this->recurrenceid);
            $this->exceptionoriginaldate = $originaldt;
            $this->baseid = $this->uid;
            $this->uid = null;
            try {
                $originalEvent = $this->getDriver()->getByUID($this->baseid);
                if ($originalEvent->recurrence) {
                    $originalEvent->recurrence->addException(
                        $originaldt->format('Y'),
                        $originaldt->format('m'),
                        $originaldt->format('d')
                    );
                    $originalEvent->save();
                }
            } catch (Horde_Exception_NotFound $e) {
                throw new Kronolith_Exception(_("Unable to locate original event series."));
            }
        } catch (Horde_Icalendar_Exception $e) {}
    }

    /**
     * Disconnect any existing exceptions.
     *
     * @param boolean $delete  If true, disconnected exceptions will be deleted
     *                         completely.
     */
    public function disconnectExceptions($delete = false)
    {
        // Get exceptions that we have bound events for.
        $exceptions = $this->boundExceptions();

        // Remove all exception dates from the recurrence object.
        $ex_dates = $this->recurrence->getExceptions();
        foreach ($ex_dates as $ex_date) {
            list($year, $month, $day) = sscanf($ex_date, '%04d%02d%02d');
            $this->recurrence->deleteException($year, $month, $day);
        }

        // Unbind the event from the base event, but don't delete it to avoid
        // any unpleasent user surprises.
        foreach ($exceptions as $exception) {
            $exception->baseid = null;
            $exception->exceptionoriginaldate = null;
            $exception->save();
            // Must delete after the baseid was removed so it doesn't alter
            // the old base event's history.
            if ($delete) {
                $this->getDriver()->deleteEvent($exception, true);
            }
        }
    }

    /**
     * Handle adding/editing exceptions from EAS 16.0 clients.
     *
     * @param  Horde_ActiveSync_Message_Appointment $message
     *
     * @return boolean
     */
    protected function _handleEas16Exception(Horde_ActiveSync_Message_Appointment $message)
    {
        if (!$this->recurs()) {
            return false;
        }
        $tz = $message->getTimezone();
        $kronolith_driver = $this->getDriver();

        // Do we already have an exception for this day? If so, remove the
        // bound exception (but don't need to remove it from the recurrence
        // object since we are just replacing it).
        $search = new StdClass();
        $search->baseid = $this->uid;
        $results = $kronolith_driver->search($search);
        foreach ($results as $days) {
            foreach ($days as $exception) {
                if ($exception->exceptionoriginaldate->setTimezone('UTC')->format('Ymd\THis\Z') == $message->instanceid) {
                    $kronolith_driver->deleteEvent($exception->id);
                    break;
                }
            }
        }

        // Ensure the exception is added to the recurrence object.
        $original = new Horde_Date($message->instanceid, 'UTC');
        $original->setTimezone($tz);
        $this->recurrence->addException($original->format('Y'), $original->format('m'), $original->format('d'));

        // Create the new exception event.
        $event = $kronolith_driver->getEvent();
        if ($message->starttime) {
            $event->start = clone($message->starttime);
            $event->start->setTimezone($tz);
        } else {
            $event->start = clone($this->start);
        }
        if ($message->endtime) {
            $event->end = clone($message->endtime);
            $event->end->setTimezone($tz);
        } else {
            $event->end = clone($this->end);
        }
        $event->title = $message->subject ? $message->subject : $this->title;
        $event->description = $message->getBody();
        $event->description = empty($event->description) ? $this->description : $event->description;
        $event->baseid = $this->uid;
        $event->exceptionoriginaldate = new Horde_Date($message->instanceid, 'UTC');
        $event->exceptionoriginaldate->setTimezone($tz);
        $event->initialized = true;
        if ($tz != date_default_timezone_get()) {
            $event->timezone = $tz;
        }
        $event->save();

        return true;
    }

    /**
     * Imports the values for this event from a MS ActiveSync Message.
     *
     * @param Horde_ActiveSync_Message_Appointment $message
     * @throws  Kronolith_Exception
     */
    public function fromASAppointment(Horde_ActiveSync_Message_Appointment $message)
    {
        /* New event? */
        if ($this->id === null) {
            $this->creator = $GLOBALS['registry']->getAuth();
        }

        // EAS 16.0 sends new/changed exceptions as "orphaned" instances so
        // they need to be handled separately.
        if ($message->getProtocolVersion() >= Horde_ActiveSync::VERSION_SIXTEEN &&
            !empty($message->instanceid)) {
            if (!$this->_handleEas16Exception($message)) {
                throw new Kronolith_Exception('Error handling EAS 16 exceptions.');
            }
            return;
        }

         // Meeting requests come with their own UID value, but only if we
         // are not using EAS 16.0 (16 sends a ClientUID value, but it's only
         // purpose is to prevent duplicate events. We currently don't store
         // this value.
        if ($message->getProtocolVersion < Horde_ActiveSync::VERSION_SIXTEEN) {
            $client_uid = $message->getUid();
            if (empty($this->uid) && !empty($client_uid)) {
                $this->uid = $message->getUid();
            }
        }

        // EAS 16 disallows the client to send/set the ORGANIZER.
        // Even so, add the extra check of not allowing the organizer to
        // be changed by the client.
        if (!$message->isGhosted('organizer')) {
            $organizer = $message->getOrganizer();
            if ($message->getProtocolVersion() < Horde_ActiveSync::VERSION_SIXTEEN) {
                if ($organizer['email'] && empty($this->organizer)) {
                    $this->organizer =  $organizer['email'];
                }
            }
        }

        if (!$message->isGhosted('subject') &&
            strlen($title = $message->getSubject())) {
            $this->title = $title;
        }

        if ($message->getProtocolVersion() == Horde_ActiveSync::VERSION_TWOFIVE &&
            !$message->isGhosted('body') &&
            strlen($description = $message->getBody())) {
            $this->description = $description;
        } elseif ($message->getProtocolVersion() > Horde_ActiveSync::VERSION_TWOFIVE && !$message->isGhosted('airsyncbasebody')) {
            if ($message->airsyncbasebody->type == Horde_ActiveSync::BODYPREF_TYPE_HTML) {
                $this->description = Horde_Text_Filter::filter($message->airsyncbasebody->data, 'Html2text');
            } else {
                $this->description = $message->airsyncbasebody->data;
            }
        }

        // EAS 16 location property is an AirSyncBaseLocation object, not
        // a string.
        $location = $message->getLocation();
        if (is_object($location)) {
            // @todo - maybe build a more complete name based on city/country?
            $location = $location->displayname;
        }
        if (!$message->isGhosted('location') && strlen($location)) {
            $this->location = $location;
        }

        /* Date/times */
        $dates = $message->getDatetime();
        if (!$message->isGhosted('alldayevent')) {
            $this->allday = $dates['allday'];
        }

        if (!empty($this->id) &&
            $dates['allday'] &&
            $message->getProtocolVersion() == Horde_ActiveSync::VERSION_SIXTEEN) {
            // allday events are handled differently when updating vs creating
            // new when using EAS 16.0
            $this->start = new Horde_Date(array(
                'year' => !$message->isGhosted('starttime') ? $dates['start']->year : $this->start->year,
                'month' => !$message->isGhosted('starttime') ? $dates['start']->month : $this->start->month,
                'mday' => !$message->isGhosted('starttime') ? $dates['start']->mday : $this->start->mday),
                !empty($this->timezone) ? $this->timezone : date_default_timezone_get()
            );
            $this->end = new Horde_Date(array(
                'year' => !$message->isGhosted('endtime') ? $dates['end']->year : $this->end->year,
                'month' => !$message->isGhosted('endtime') ? $dates['end']->month : $this->end->month,
                'mday' => !$message->isGhosted('endtime') ? $dates['end']->mday : $this->end->mday),
                !empty($this->timezone) ? $this->timezone : date_default_timezone_get()
            );
        } else {
            $tz = !$message->isGhosted('timezone') ? $message->getTimezone() : $this->timezone;
            $this->start = !$message->isGhosted('starttime') ? clone($dates['start']) : $this->start;
            try {
                $this->start->setTimezone($tz);
            } catch (Horde_Date_Exception $e) {
                $tz = date_default_timezone_get();
                Horde::log(sprintf('Unable to set timezone. Using %s.', $tz), 'WARN');
                $this->start->setTimezone($tz);
            }
            $this->end = !$message->isGhosted('endtime') ? clone($dates['end']) : $this->end;
            $this->end->setTimezone($tz);
            if ($tz != date_default_timezone_get()) {
                $this->timezone = $tz;
            }
        }

        /* Sensitivity */
        if (!$message->isGhosted('sensitivity')) {
            $this->private = ($message->getSensitivity() == Horde_ActiveSync_Message_Appointment::SENSITIVITY_PRIVATE || $message->getSensitivity() == Horde_ActiveSync_Message_Appointment::SENSITIVITY_CONFIDENTIAL) ? true :  false;
        }

        /* Busy Status */
        if (!$message->isGhosted('meetingstatus')) {
            if ($message->getMeetingStatus() == Horde_ActiveSync_Message_Appointment::MEETING_CANCELLED) {
                $status = Kronolith::STATUS_CANCELLED;
            } else {
                $status = $message->getBusyStatus();
                switch ($status) {
                case Horde_ActiveSync_Message_Appointment::BUSYSTATUS_BUSY:
                case Horde_ActiveSync_Message_Appointment::BUSYSTATUS_ELSEWHERE;
                    $status = Kronolith::STATUS_CONFIRMED;
                    break;

                case Horde_ActiveSync_Message_Appointment::BUSYSTATUS_FREE:
                    $status = Kronolith::STATUS_FREE;
                    break;

                case Horde_ActiveSync_Message_Appointment::BUSYSTATUS_TENTATIVE:
                    $status = Kronolith::STATUS_TENTATIVE;
                    break;
                // @TODO: not sure how "Out" should show in kronolith...
                case Horde_ActiveSync_Message_Appointment::BUSYSTATUS_OUT:
                    $status = Kronolith::STATUS_CONFIRMED;
                default:
                    // EAS Specifies default should be free.
                    $status = Kronolith::STATUS_FREE;
                }
            }
            $this->status = $status;
        }


        // Alarms:
        // EAS allows setting an alarm at the time of the event, and
        // signifies this with a '0' minutes before. Kronolith does not
        // support this, and uses '0' to mean no alarm. Make these fire
        // at 1 minute prior.
        if (!$message->isGhosted('reminder')) {
            $alarm = $message->getReminder();
            if ($alarm === 0 || $alarm === "0") {
                // "At time of event"
                $this->alarm = 1;
            } elseif ($message->getProtocolVersion() >= Horde_ActiveSync::VERSION_SIXTEEN) {
                if (empty($alarm)) {
                    // Client sent an empty reminder tag meaning no alarm.
                    $this->alarm = 0;
                } else {
                    // It was either missing (no alarm) or set with a value.
                    $this->alarm = $alarm;
                }
            } elseif ($alarm) {
               $this->alarm = $alarm;
            } else {
                $this->alarm = 0;
            }
        }

        /* Recurrence */
        if (!$message->isGhosted('recurrence') && ($rrule = $message->getRecurrence())) {
            /* Exceptions */
            /* Since AS keeps exceptions as part of the original event, we need
             * to delete all existing exceptions and re-create them. The only
             * drawback to this is that the UIDs will change. */
            $kronolith_driver = $this->getDriver();

            // EAS 16 doesn't update exception data on edits of the base event
            // but still sends the recurrence rule. We need to replace the
            // recurrence rule if it changed (and overwrite any exceptions),
            // otherwise leave it alone.
            if ($message->getProtocolVersion() >= Horde_ActiveSync::VERSION_SIXTEEN) {
                if (!empty($this->uid) &&
                    !empty($this->recurrence) &&
                    !$this->recurrence->isEqual($rrule)) {
                    $this->disconnectExceptions(true);
                    $this->recurrence = $rrule;
                }
            }

            if (!empty($this->uid) &&
                $message->getProtocolVersion() < Horde_ActiveSync::VERSION_SIXTEEN) {
                // EAS 16.0 NEVER adds exceptions from withing the base event,
                // so we can't delete the existing exceptions - we don't have
                // the current list to replace them with.
                $search = new StdClass();
                $search->baseid = $this->uid;
                $results = $kronolith_driver->search($search);
                foreach ($results as $days) {
                    foreach ($days as $exception) {
                        $kronolith_driver->deleteEvent($exception->id);
                    }
                }

                $erules = $message->getExceptions();
                foreach ($erules as $rule){
                    /* Readd the exception event, but only if not deleted */
                    if (!$rule->deleted) {
                        $event = $kronolith_driver->getEvent();
                        $times = $rule->getDatetime();
                        if ($message->getProtocolVersion() < Horde_ActiveSync::VERSION_SIXTEEN) {
                            $original = $rule->getExceptionStartTime();
                        } else {
                            $original = $rule->instanceid;
                        }
                        try {
                            $original->setTimezone($tz);
                        } catch (Horde_Date_Exception $e) {
                            $tz = date_default_timezone_get();
                            Horde::log(sprintf('Unable to set timezone. Using %s.', $tz), 'WARN');
                            $original->setTimezone($tz);
                        }
                        $this->recurrence->addException($original->format('Y'), $original->format('m'), $original->format('d'));
                        $event->start = $times['start'];
                        $event->end = $times['end'];
                        $event->start->setTimezone($tz);
                        $event->end->setTimezone($tz);
                        $event->allday = $times['allday'];
                        $event->title = $rule->getSubject();
                        $event->title = empty($event->title) ? $this->title : $event->title;
                        $event->description = $rule->getBody();
                        $event->description = empty($event->description) ? $this->description : $event->description;
                        $event->baseid = $this->uid;
                        $event->exceptionoriginaldate = $original;
                        $event->initialized = true;
                        if ($tz != date_default_timezone_get()) {
                            $event->timezone = $tz;
                        }
                        $event->save();
                    } else {
                        /* For exceptions that are deletions, just add the exception */
                        if ($message->getProtocolVersion() < Horde_ActiveSync::VERSION_SIXTEEN) {
                            $exceptiondt = $rule->getExceptionStartTime();
                        } else {
                            $exceptiondt = $rule->instanceid;
                        }
                        try {
                            $exceptiondt->setTimezone($tz);
                        } catch (Horde_Date_Exception $e) {
                            $tz = date_default_timezone_get();
                            Horde::log(sprintf('Unable to set timezone. Using %s.', $tz), 'WARN');
                            $exceptiondt->setTimezone($tz);
                        }
                        $this->recurrence->addException($exceptiondt->format('Y'), $exceptiondt->format('m'), $exceptiondt->format('d'));
                   }
               }
            }
        }

        /* Attendees */
        if (!$message->isGhosted('attendees')) {
            $attendees = $message->getAttendees();
            foreach ($attendees as $attendee) {
                $response_code == false;
                if ($message->getProtocolVersion < Horde_ActiveSync::VERSION_SIXTEEN) {
                    switch ($attendee->status) {
                    case Horde_ActiveSync_Message_Attendee::STATUS_ACCEPT:
                        $response_code = Kronolith::RESPONSE_ACCEPTED;
                        break;
                    case Horde_ActiveSync_Message_Attendee::STATUS_DECLINE:
                        $response_code = Kronolith::RESPONSE_DECLINED;
                        break;
                    case Horde_ActiveSync_Message_Attendee::STATUS_TENTATIVE:
                        $response_code = Kronolith::RESPONSE_TENTATIVE;
                        break;
                    default:
                        $response_code = Kronolith::RESPONSE_NONE;
                    }
                    switch ($attendee->type) {
                    case Horde_ActiveSync_Message_Attendee::TYPE_REQUIRED:
                        $part_type = Kronolith::PART_REQUIRED;
                        break;
                    case Horde_ActiveSync_Message_Attendee::TYPE_OPTIONAL:
                        $part_type = Kronolith::PART_OPTIONAL;
                        break;
                    case Horde_ActiveSync_Message_Attendee::TYPE_RESOURCE:
                        $part_type = Kronolith::PART_REQUIRED;
                    }
                }

                $this->addAttendee($attendee->email,
                                   $part_type,
                                   $response_code,
                                   $attendee->name);
            }
        }

        /* Categories (Tags) */
        if (!$message->isGhosted('categories')) {
            $this->_tags = $message->getCategories();
        }

        // 14.1
        if ($message->getProtocolVersion() >= Horde_ActiveSync::VERSION_FOURTEENONE &&
            !$message->isGhosted('onlinemeetingexternallink')) {
            $this->url = $message->onlinemeetingexternallink;
        }

        /* Flag that we are initialized */
        $this->initialized = true;
    }

    /**
     * @todo  Do we need to update History here too?
     */
    public function addEASFiles($message)
    {
        $results = array(
            'add' => array(),
            'delete' => array()
        );
        // EAS 16.0
        $supported = true;
        if ($message->getProtocolVersion() < Horde_ActiveSync::VERSION_SIXTEEN ||
            !$this->id) {
            $not_supported = true;
        }

        foreach ($message->airsyncbaseattachments as $atc) {
            switch (get_class($atc)) {
            case 'Horde_ActiveSync_Message_AirSyncBaseAdd':
                if (!$supported) {
                    $results['add'][$atc->clientid] = false;
                    continue 2;
                }
                $info = $this->_addEASFile($atc);
                $results['add'][$atc->clientid] = $this->_getEASFileReference($info['name']);
                break;
            case 'Horde_ActiveSync_Message_AirSyncBaseDelete':
                $file_parts = explode(':', $atc->filereference, 4);
                try {
                    $this->deleteFile($file_parts[3]);
                    $results['delete'][] = $atc->filereference;
                } catch (Kronolith_Exception $e) {
                    Horde::log('Unable to remove VFS file.', 'ERR');
                }
            }
        }

        return $results;
    }

    protected function _getEASFileReference($filename)
    {
        return sprintf('calendar:%s:%s:%s', $this->calendar, $this->uid, $filename);
    }

    protected function _addEASFile(Horde_ActiveSync_Message_AirSyncBaseAdd $add)
    {
        $info = array(
            'name' => empty($add->displayname) ? 'Untitled' : $add->displayname,
            'data' => $add->content
        );
        $this->addFileFromData($info);

        return $info;
    }

    /**
     * Export this event as a MS ActiveSync Message
     *
     * @param array $options  Options:
     *   - protocolversion: (float)  The EAS version to support
     *                      DEFAULT: 2.5
     *   - bodyprefs: (array)  A BODYPREFERENCE array.
     *                DEFAULT: none (No body prefs enforced).
     *   - truncation: (integer)  Truncate event body to this length
     *                 DEFAULT: none (No truncation).
     *
     * @return Horde_ActiveSync_Message_Appointment
     */
    public function toASAppointment(array $options = array())
    {
        global $prefs, $registry;

        // @todo This should be a required option.
        if (empty($options['protocolversion'])) {
            $options['protocolversion'] = 2.5;
        }

        $message = new Horde_ActiveSync_Message_Appointment(
            array(
                'logger' => $GLOBALS['injector']->getInstance('Horde_Log_Logger'),
                'protocolversion' => $options['protocolversion']
            )
        );

        if (!$this->isPrivate()) {
            // Handle body/truncation
            if (!empty($options['bodyprefs'])) {
                if (Horde_String::length($this->description) > 0) {
                    $bp = $options['bodyprefs'];
                    $note = new Horde_ActiveSync_Message_AirSyncBaseBody();
                    // No HTML supported. Always use plaintext.
                    $note->type = Horde_ActiveSync::BODYPREF_TYPE_PLAIN;
                    if (isset($bp[Horde_ActiveSync::BODYPREF_TYPE_PLAIN]['truncationsize'])) {
                        $truncation = $bp[Horde_ActiveSync::BODYPREF_TYPE_PLAIN]['truncationsize'];
                    } elseif (isset($bp[Horde_ActiveSync::BODYPREF_TYPE_HTML])) {
                        $truncation = $bp[Horde_ActiveSync::BODYPREF_TYPE_HTML]['truncationsize'];
                        $this->description = Horde_Text_Filter::filter($this->description, 'Text2html', array('parselevel' => Horde_Text_Filter_Text2html::MICRO));
                    } else {
                        $truncation = false;
                    }
                    if ($truncation && Horde_String::length($this->description) > $truncation) {
                        $note->data = Horde_String::substr($this->description, 0, $truncation);
                        $note->truncated = 1;
                    } else {
                        $note->data = $this->description;
                    }
                    $note->estimateddatasize = Horde_String::length($this->description);
                    $message->airsyncbasebody = $note;
                }
            } else {
                $message->setBody($this->description);
            }
            if ($options['protocolversion'] >= Horde_ActiveSync::VERSION_SIXTEEN && !empty($this->location)) {
                $message->location = new Horde_ActiveSync_Message_AirSyncBaseLocation(
                    array(
                        'logger' => $GLOBALS['injector']->getInstance('Horde_Log_Logger'),
                        'protocolversion' => $options['protocolversion']
                    )
                );
                // @todo - worth it to try to get full city/country etc...
                // from geotagging service if available??
                $message->location->displayname = $this->location;
            } else {
                $message->setLocation($this->location);
            }
        }

        $message->setSubject($this->getTitle());
        $message->alldayevent = $this->isAllDay();
        $st = clone($this->start);
        $et = clone($this->end);
        if ($this->isAllDay()) {
            // EAS requires all day to be from 12:00 to 12:00.
            if ($this->start->hour != 0 || $this->start->min != 0 || $this->start->sec != 0) {
                $st->hour = 0;
                $st->min = 0;
                $st->sec = 0;
            }
            // For end it's a bit trickier. If it's 11:59pm, bump it up to 12:00
            // am of the next day. Otherwise, if it's not 12:00am, make it 12:00
            // am of the same day. This *shouldn't* happen, but protect against
            // issues with EAS just in case.
            if ($this->end->hour != 0 || $this->end->min != 0 || $this->end->sec != 0) {
                if ($this->end->hour == 23 && $this->end->min == 59) {
                    $et->mday++;
                }
                $et->hour = 0;
                $et->min = 0;
                $et->sec = 0;
            }
        }
        $message->starttime = $st;
        $message->endtime = $et;
        $message->setTimezone($this->start);

        // Organizer
        $attendees = $this->attendees;
        $skipOrganizer = null;
        if ($this->organizer) {
            $message->setOrganizer(array('email' => $this->organizer));
        } elseif (count($attendees)) {
            if ($this->creator == $registry->getAuth()) {
                $as_ident = $prefs->getValue('activesync_identity') == 'horde'
                    ? $prefs->getValue('default_identity')
                    : $prefs->getValue('activesync_identity');

                $name = $GLOBALS['injector']
                    ->getInstance('Horde_Core_Factory_Identity')
                    ->create($this->creator)->getValue('fullname', $as_ident);
                $email = $GLOBALS['injector']
                    ->getInstance('Horde_Core_Factory_Identity')
                    ->create($this->creator)->getValue('from_addr', $as_ident);
            } else {
                $name = Kronolith::getUserName($this->creator);
                $email = Kronolith::getUserEmail($this->creator);
            }
            $message->setOrganizer(array(
                'name' => $name,
                'email' => $email)
            );
            $skipOrganizer = $email;
        }

        // Privacy
        $message->setSensitivity($this->private ?
            Horde_ActiveSync_Message_Appointment::SENSITIVITY_PRIVATE :
            Horde_ActiveSync_Message_Appointment::SENSITIVITY_NORMAL);

        // Busy Status
        // This is the *busy* status of the time for this meeting. This is NOT
        // the Kronolith_Event::status or the attendance response for this
        // meeting. Kronolith does not (yet) support sepcifying the busy status
        // of the event time separate from the STATUS_FREE value of the
        // Kronolith_Event::status field, so for now we map these values the
        // best we can by assuming that STATUS_CONFIRMED meetings should always
        // show as BUSYSTATUS_BUSY etc...
        switch ($this->status) {
        case Kronolith::STATUS_CANCELLED:
        case Kronolith::STATUS_FREE:
        case Kronolith::STATUS_NONE:
            $status = Horde_ActiveSync_Message_Appointment::BUSYSTATUS_FREE;
            break;
        case Kronolith::STATUS_CONFIRMED:
            $status = Horde_ActiveSync_Message_Appointment::BUSYSTATUS_BUSY;
            break;
        case Kronolith::STATUS_TENTATIVE:
            $status = Horde_ActiveSync_Message_Appointment::BUSYSTATUS_TENTATIVE;
        }
        $message->setBusyStatus($status);

        // DTStamp
        $message->setDTStamp($_SERVER['REQUEST_TIME']);

        // Recurrence
        if ($this->recurs()) {
            $message->setRecurrence($this->recurrence, $GLOBALS['prefs']->getValue('week_start_monday'));

            /* Exceptions are tricky. Exceptions, even those that represent
             * deleted instances of a recurring event, must be added. To do this
             * we query the storage for all the events that represent exceptions
             * (those with the baseid == $this->uid) and then remove the
             * exceptionoriginaldate from the list of exceptions we know about.
             * Any dates left in this list when we are done, must represent
             * deleted instances of this recurring event.*/
            if (!empty($this->recurrence) && $exceptions = $this->recurrence->getExceptions()) {
                $results = $this->boundExceptions();
                foreach ($results as $exception) {
                    $e = new Horde_ActiveSync_Message_Exception(array(
                        'protocolversion' => $options['protocolversion']));
                    $e->setDateTime(array(
                        'start' => $exception->start,
                        'end' => $exception->end,
                        'allday' => $exception->isAllDay()));

                    // The start time of the *original* recurring event.
                    // EAS < 16.0 uses 'exceptionstarttime'. Otherwise it's
                    // 'instanceid'.
                    if ($options['protocolversion'] < Horde_ActiveSync::VERSION_SIXTEEN) {
                        $e->setExceptionStartTime($exception->exceptionoriginaldate);
                    } else {
                        $e->instanceid = $exception->exceptionoriginaldate;
                    }
                    $originaldate = $exception->exceptionoriginaldate->format('Ymd');
                    $key = array_search($originaldate, $exceptions);
                    if ($key !== false) {
                        unset($exceptions[$key]);
                    }

                    // Remaining properties that could be different
                    $e->setSubject($exception->getTitle());
                    if (!$exception->isPrivate()) {
                        $e->setLocation($exception->location);
                        $e->setBody($exception->description);
                    }

                    $e->setSensitivity($exception->private ?
                        Horde_ActiveSync_Message_Appointment::SENSITIVITY_PRIVATE :
                        Horde_ActiveSync_Message_Appointment::SENSITIVITY_NORMAL);
                    $e->setReminder($exception->alarm);
                    $e->setDTStamp($_SERVER['REQUEST_TIME']);

                    if ($options['protocolversion'] > Horde_ActiveSync::VERSION_TWELVEONE) {
                        switch ($exception->status) {
                        case Kronolith::STATUS_TENTATIVE;
                            $e->responsetype = Horde_ActiveSync_Message_Appointment::RESPONSE_TENTATIVE;
                            break;
                        case Kronolith::STATUS_NONE:
                            $e->responsetype = Horde_ActiveSync_Message_Appointment::RESPONSE_NORESPONSE;
                            break;
                        case Kronolith::STATUS_CONFIRMED:
                            $e->responsetype = Horde_ActiveSync_Message_Appointment::RESPONSE_ACCEPTED;
                            break;
                        default:
                            $e->responsetype = Horde_ActiveSync_Message_Appointment::RESPONSE_NONE;
                        }
                    }

                    // Tags/Categories
                    if (!$exception->isPrivate()) {
                        foreach ($exception->tags as $tag) {
                            $e->addCategory($tag);
                        }
                    }

                    $message->addexception($e);
                }

                // Any dates left in $exceptions must be deleted exceptions
                foreach ($exceptions as $deleted) {
                    $e = new Horde_ActiveSync_Message_Exception(array(
                        'protocolversion' => $options['protocolversion']));
                    // Kronolith stores the date only, but some AS clients need
                    // the datetime.
                    list($year, $month, $mday) = sscanf($deleted, '%04d%02d%02d');
                    $st = clone $this->start;
                    $st->year = $year;
                    $st->month = $month;
                    $st->mday = $mday;
                    if ($options['protocolversion'] < Horde_ActiveSync::VERSION_SIXTEEN) {
                        $e->setExceptionStartTime($st);
                    } else {
                        $e->instanceid = $st;
                    }
                    $e->deleted = true;
                    $message->addException($e);
                }
            }
        }

        // Attendees
        if (!$this->isPrivate() && count($attendees)) {
            $message->setMeetingStatus(
                $this->status == Kronolith::STATUS_CANCELLED
                    ? Horde_ActiveSync_Message_Appointment::MEETING_CANCELLED
                    : Horde_ActiveSync_Message_Appointment::MEETING_IS_MEETING
            );
            foreach ($attendees as $attendee) {
                if ($skipOrganizer && $attendee->email == $skipOrganizer) {
                    continue;
                }
                $attendeeAS = new Horde_ActiveSync_Message_Attendee(array(
                    'protocolversion' => $options['protocolversion']));
                $attendeeAS->name = $attendee->addressObject->label;
                $attendeeAS->email = $attendee->addressObject->bare_address;

                // AS only has required or optional, and only EAS Version > 2.5
                if ($options['protocolversion'] > Horde_ActiveSync::VERSION_TWOFIVE) {
                    $attendeeAS->type = ($attendee->role !== Kronolith::PART_REQUIRED
                        ? Horde_ActiveSync_Message_Attendee::TYPE_OPTIONAL
                        : Horde_ActiveSync_Message_Attendee::TYPE_REQUIRED);

                    switch ($attendee->response) {
                    case Kronolith::RESPONSE_NONE:
                        $attendeeAS->status = Horde_ActiveSync_Message_Attendee::STATUS_NORESPONSE;
                        break;
                    case Kronolith::RESPONSE_ACCEPTED:
                        $attendeeAS->status = Horde_ActiveSync_Message_Attendee::STATUS_ACCEPT;
                        break;
                    case Kronolith::RESPONSE_DECLINED:
                        $attendeeAS->status = Horde_ActiveSync_Message_Attendee::STATUS_DECLINE;
                        break;
                    case Kronolith::RESPONSE_TENTATIVE:
                        $attendeeAS->status = Horde_ActiveSync_Message_Attendee::STATUS_TENTATIVE;
                        break;
                    default:
                        $attendeeAS->status = Horde_ActiveSync_Message_Attendee::STATUS_UNKNOWN;
                    }
                }

                $message->addAttendee($attendeeAS);
            }
        } elseif ($this->status == Kronolith::STATUS_CANCELLED) {
            $message->setMeetingStatus(Horde_ActiveSync_Message_Appointment::MEETING_CANCELLED);
        } else {
            $message->setMeetingStatus(Horde_ActiveSync_Message_Appointment::MEETING_NOT_MEETING);
        }

        // Resources
        if ($options['protocolversion'] > Horde_ActiveSync::VERSION_TWOFIVE) {
            $r = $this->getResources();
            foreach ($r as $id => $data) {
                $resource = Kronolith::getDriver('Resource')->getResource($id);
                // EAS *REQUIRES* an email field for Resources. If it is missing
                // a number of clients will fail, losing push.
                if ($resource->get('email')) {
                    $attendeeAS = new Horde_ActiveSync_Message_Attendee(array(
                        'protocolversion' => $options['protocolversion']));
                    $attendeeAS->email = $resource->get('email');
                    $attendeeAS->type = Horde_ActiveSync_Message_Attendee::TYPE_RESOURCE;
                    $attendeeAS->name = $data['name'];
                    $attendeeAS->status = $data['response'];
                    $message->addAttendee($attendeeAS);
                }
           }
        }

        // Reminder
        if ($this->alarm) {
            $message->setReminder($this->alarm);
        }

        // Categories (tags)
        if (!$this->isPrivate()) {
            foreach ($this->tags as $tag) {
                $message->addCategory($tag);
            }
        }

        // EAS 14, and only if it is a meeting.
        if ($options['protocolversion'] > Horde_ActiveSync::VERSION_TWELVEONE &&
            $message->getMeetingStatus() == Horde_ActiveSync_Message_Appointment::MEETING_IS_MEETING) {

            // Are we the
            if (empty($this->organizer) && $this->creator == $registry->getAuth()) {
                $message->responsetype = Horde_ActiveSync_Message_Appointment::RESPONSE_ORGANIZER;
            }

            // We don't track the actual responses we sent to other's invitations.
            // Set this based on the status flag.
            switch ($this->status) {
            case Kronolith::STATUS_TENTATIVE;
                $message->responsetype = Horde_ActiveSync_Message_Appointment::RESPONSE_TENTATIVE;
                break;
            case Kronolith::STATUS_NONE:
                $message->responsetype = Horde_ActiveSync_Message_Appointment::RESPONSE_NORESPONSE;
                break;
            case Kronolith::STATUS_CONFIRMED:
                $message->responsetype = Horde_ActiveSync_Message_Appointment::RESPONSE_ACCEPTED;
                break;
            default:
                $message->responsetype = Horde_ActiveSync_Message_Appointment::RESPONSE_NONE;
            }
        }

        // 14.1
        if ($options['protocolversion'] >= Horde_ActiveSync::VERSION_FOURTEENONE) {
            $message->onlinemeetingexternallink = $this->url;
        }

        // 16.0
        if ($options['protocolversion'] >= Horde_ActiveSync::VERSION_SIXTEEN) {
            $files = $this->listFiles();
            if (count($files)) {
                foreach ($files as $file) {
                    $atc = new Horde_ActiveSync_Message_AirSyncBaseAttachment(
                        array(
                            'logger' => $GLOBALS['injector']->getInstance('Horde_Log_Logger'),
                            'protocolversion' => $options['protocolversion']
                        )
                    );
                    $atc->displayname = $file['name'];
                    $atc->attname = $this->_getEASFileReference($file['name']);
                    $atc->attmethod = Horde_ActiveSync_Message_AirSyncBaseAttachment::ATT_TYPE_NORMAL;
                    $atc->attsize = $file['size'];
                    $message->addAttachment($atc);
                }
            }
        }

        return $message;
    }

    /**
     * Exports the values for this event to an array of values.
     *
     * @return array  Array containing all the values.
     * @throws Kronolith_Exception
     */
    public function toHash()
    {
        $attendees = array();
        foreach ($this->attendees as $id => $attendee) {
            $attendees[$id] = $attendee->toHash();
        }

        $files = array();
        if ($vfs = $this->vfsInit()) {
            foreach ($this->listFiles() as $file) {
                try {
                    $data = $vfs->read(
                        Kronolith::VFS_PATH . '/' . $this->getVfsUid(),
                        $file['name']
                    );
                } catch (Horde_Vfs_Exception $e) {
                    Horde::log($e->getMessage, 'ERR');
                }
                if ($data) {
                    $file['data'] = base64_encode($data);
                    $files[] = $file;
                }
            }
        }

        return array(
            'title'         => $this->title,
            'start_date'    => $this->start->format('Y-m-d'),
            'start_time'    => $this->start->format('H:i:s'),
            'timezone'      => $this->timezone,
            'end_date'      => $this->end->format('Y-m-d'),
            'end_time'      => $this->end->format('H:i:s'),
            'alarm'         => $this->alarm,
            'allday'        => $this->allday,
            'attendees'     => $attendees,
            'baseid'        => $this->baseid,
            'calendar'      => $this->calendar,
            'creator'       => $this->_creator,
            'description'   => $this->description,
            'geo_location'  => $this->geoLocation,
            'location'      => $this->location,
            'methods'       => $this->methods,
            'organizer'     => $this->organizer,
            'original_date' => (string)$this->exceptionoriginaldate,
            'private'       => $this->private,
            'recurrence'    => $this->recurrence ? $this->recurrence->toHash() : null,
            'resources'     => $this->_resources,
            'sequence'      => $this->sequence,
            'status'        => $this->status,
            'tags'          => $this->tags,
            'uid'           => $this->uid,
            'url'           => $this->url,
            'files'         => $files,
        );
    }

    /**
     * Imports the values for this event from an array of values.
     *
     * @param array $hash  Array containing all the values.
     *
     * @throws Kronolith_Exception
     */
    public function fromHash($hash)
    {
        // See if it's a new event.
        if ($this->id === null) {
            $this->creator = $GLOBALS['registry']->getAuth();
        }

        if (!empty($hash['title'])) {
            $this->title = $hash['title'];
        } else {
            throw new Kronolith_Exception(_("Events must have a title."));
        }

        $this->start = null;
        if (!empty($hash['start_date'])) {
            $date = array_map('intval', explode('-', $hash['start_date']));
            if (empty($hash['start_time'])) {
                $time = array(0, 0, 0);
            } else {
                $time = array_map('intval', explode(':', $hash['start_time']));
                if (count($time) == 2) {
                    $time[2] = 0;
                }
            }
            if (count($time) == 3 && count($date) == 3 &&
                !empty($date[1]) && !empty($date[2])) {
                if ($date[0] < 100) {
                    $date[0] += (date('Y') / 100 | 0) * 100;
                }
                $this->start = new Horde_Date(
                    array(
                        'year'  => $date[0],
                        'month' => $date[1],
                        'mday'  => $date[2],
                        'hour'  => $time[0],
                        'min'   => $time[1],
                        'sec'   => $time[2]
                    ),
                    isset($hash['timezone']) ? $hash['timezone'] : null
                );
            }
        }
        if (!isset($this->start)) {
            throw new Kronolith_Exception(_("Events must have a start date."));
        }

        if (empty($hash['duration'])) {
            if (empty($hash['end_date'])) {
                $hash['end_date'] = $hash['start_date'];
            }
            if (empty($hash['end_time'])) {
                $hash['end_time'] = $hash['start_time'];
            }
        } else {
            $weeks = str_replace('W', '', $hash['duration'][1]);
            $days = str_replace('D', '', $hash['duration'][2]);
            $hours = str_replace('H', '', $hash['duration'][4]);
            $minutes = isset($hash['duration'][5]) ? str_replace('M', '', $hash['duration'][5]) : 0;
            $seconds = isset($hash['duration'][6]) ? str_replace('S', '', $hash['duration'][6]) : 0;
            $hash['duration'] = ($weeks * 60 * 60 * 24 * 7) + ($days * 60 * 60 * 24) + ($hours * 60 * 60) + ($minutes * 60) + $seconds;
            $this->end = new Horde_Date($this->start);
            $this->end->sec += $hash['duration'];
        }
        if (!empty($hash['end_date'])) {
            $date = array_map('intval', explode('-', $hash['end_date']));
            if (empty($hash['end_time'])) {
                $time = array(0, 0, 0);
            } else {
                $time = array_map('intval', explode(':', $hash['end_time']));
                if (count($time) == 2) {
                    $time[2] = 0;
                }
            }
            if (count($time) == 3 && count($date) == 3 &&
                !empty($date[1]) && !empty($date[2])) {
                if ($date[0] < 100) {
                    $date[0] += (date('Y') / 100 | 0) * 100;
                }
                $this->end = new Horde_Date(
                    array(
                        'year'  => $date[0],
                        'month' => $date[1],
                        'mday'  => $date[2],
                        'hour'  => $time[0],
                        'min'   => $time[1],
                        'sec'   => $time[2]
                    ),
                    isset($hash['timezone']) ? $hash['timezone'] : null
                );
            }
        }

        if (!empty($hash['alarm'])) {
            $this->alarm = (int)$hash['alarm'];
        } elseif (!empty($hash['alarm_date']) &&
                  !empty($hash['alarm_time'])) {
            $date = array_map('intval', explode('-', $hash['alarm_date']));
            $time = array_map('intval', explode(':', $hash['alarm_time']));
            if (count($time) == 2) {
                $time[2] = 0;
            }
            if (count($time) == 3 && count($date) == 3 &&
                !empty($date[1]) && !empty($date[2])) {
                $alarm = new Horde_Date(
                    array(
                        'year'  => $date[0],
                        'month' => $date[1],
                        'mday'  => $date[2],
                        'hour'  => $time[0],
                        'min'   => $time[1],
                        'sec'   => $time[2]
                    ),
                    isset($hash['timezone']) ? $hash['timezone'] : null
                );
                $this->alarm = ($this->start->timestamp() - $alarm->timestamp()) / 60;
            }
        }

        $this->allday = !empty($hash['allday']);

        if (!empty($hash['description'])) {
            $this->description = $hash['description'];
        }

        if (!empty($hash['location'])) {
            $this->location = $hash['location'];
        }

        if (!empty($hash['organizer'])) {
            $this->organizer = $hash['organizer'];
        }

        if (!empty($hash['private'])) {
            $this->private = true;
        }

        if (!empty($hash['recur_type'])) {
            $this->recurrence = new Horde_Date_Recurrence($this->start);
            $this->recurrence->setRecurType($hash['recur_type']);
            if (!empty($hash['recur_count'])) {
                $this->recurrence->setRecurCount($hash['recur_count']);
            } elseif (!empty($hash['recur_end_date'])) {
                $date = array_map('intval', explode('-', $hash['recur_end_date']));
                if (count($date) == 3 && !empty($date[1]) && !empty($date[2])) {
                    $this->recurrence->setRecurEnd(
                        new Horde_Date(array(
                            'year'  => $date[0],
                            'month' => $date[1],
                            'mday'  => $date[2]
                        ))
                    );
                }
            }
            if (!empty($hash['recur_interval'])) {
                $this->recurrence->setRecurInterval($hash['recur_interval']);
            }
            if (!empty($hash['recur_data'])) {
                $this->recurrence->setRecurOnDay($hash['recur_data']);
            }
            if (!empty($hash['recur_exceptions'])) {
                foreach ($hash['recur_exceptions'] as $exception) {
                    $parts = explode('-', $exception);
                    if (count($parts) == 3) {
                        $this->recurrence->addException($parts[0], $parts[1], $parts[2]);
                    }
                }
            }
        }

        if (isset($hash['sequence'])) {
            $this->sequence = $hash['sequence'];
        }

        if (!empty($hash['tags'])) {
            $this->tags = $hash['tags'];
        }

        if (!empty($hash['timezone'])) {
            $this->timezone = $hash['timezone'];
        }

        if (!empty($hash['uid'])) {
            $this->uid = $hash['uid'];
        }

        $this->initialized = true;
    }

    /**
     * Returns an alarm hash of this event suitable for Horde_Alarm.
     *
     * @param Horde_Date $time  Time of alarm.
     * @param string $user      The user to return alarms for.
     * @param Prefs $prefs      A Prefs instance.
     *
     * @return array  Alarm hash or null.
     */
    public function toAlarm($time, $user = null, $prefs = null)
    {
        if (!$this->alarm || $this->status == Kronolith::STATUS_CANCELLED) {
            return;
        }

        if ($this->recurs()) {
            $eventDate = $this->recurrence->nextRecurrence($time);
            if (!$eventDate || ($eventDate && $this->recurrence->hasException($eventDate->year, $eventDate->month, $eventDate->mday))) {
                return;
            }
            $start = clone $eventDate;
            $diff = $this->start->diff($this->end);
            $end = new Horde_Date(array(
                'year' => $start->year,
                'month' => $start->month,
                'mday' => $start->mday + $diff,
                'hour' => $this->end->hour,
                'min' => $this->end->min,
                'sec' => $this->end->sec)
            );
        } else {
            $start = clone $this->start;
            $end = clone $this->end;
        }

        $serverName = $_SERVER['SERVER_NAME'];
        $serverConf = $GLOBALS['conf']['server']['name'];
        if (!empty($GLOBALS['conf']['reminder']['server_name'])) {
            $_SERVER['SERVER_NAME'] = $GLOBALS['conf']['server']['name'] = $GLOBALS['conf']['reminder']['server_name'];
        }

        if (empty($user)) {
            $user = $GLOBALS['registry']->getAuth();
        }
        if (empty($prefs)) {
            $prefs = $GLOBALS['prefs'];
        }

        $methods = !empty($this->methods) ? $this->methods : @unserialize($prefs->getValue('event_alarms'));
        if (isset($methods['notify'])) {
            $methods['notify']['show'] = array(
                '__app' => $GLOBALS['registry']->getApp(),
                'event' => $this->id,
                'calendar' => $this->calendar);
            $methods['notify']['ajax'] = 'event:' . $this->calendarType . '|' . $this->calendar . ':' . $this->id . ':' . $start->dateString();
            if (!empty($methods['notify']['sound'])) {
                if ($methods['notify']['sound'] == 'on') {
                    // Handle boolean sound preferences.
                    $methods['notify']['sound'] = (string)Horde_Themes::sound('theetone.wav');
                } else {
                    // Else we know we have a sound name that can be
                    // served from Horde.
                    $methods['notify']['sound'] = (string)Horde_Themes::sound($methods['notify']['sound']);
                }
            }
            if ($this->isAllDay()) {
                if ($start->compareDate($end) == 0) {
                    $methods['notify']['subtitle'] = sprintf(_("On %s"), '<strong>' . $start->strftime($prefs->getValue('date_format')) . '</strong>');
                } else {
                    $methods['notify']['subtitle'] = sprintf(_("From %s to %s"), '<strong>' . $start->strftime($prefs->getValue('date_format')) . '</strong>', '<strong>' . $end->strftime($prefs->getValue('date_format')) . '</strong>');
                }
            } else {
                $methods['notify']['subtitle'] = sprintf(_("From %s at %s to %s at %s"), '<strong>' . $start->strftime($prefs->getValue('date_format')), $start->format($prefs->getValue('twentyFour') ? 'H:i' : 'h:ia') . '</strong>', '<strong>' . $end->strftime($prefs->getValue('date_format')), $this->end->format($prefs->getValue('twentyFour') ? 'H:i' : 'h:ia') . '</strong>');
            }
        }
        if (isset($methods['mail'])) {
            $image = Kronolith::getImagePart('big_alarm.png');

            $view = new Horde_View(array('templatePath' => KRONOLITH_TEMPLATES . '/alarm', 'encoding' => 'UTF-8'));
            new Horde_View_Helper_Text($view);
            $view->event = $this;
            $view->imageId = $image->getContentId();
            $view->user = $user;
            $view->dateFormat = $prefs->getValue('date_format');
            $view->timeFormat = $prefs->getValue('twentyFour') ? 'H:i' : 'h:ia';
            $view->start = $start;
            if (!$prefs->isLocked('event_reminder')) {
                $view->prefsUrl = Horde::url($GLOBALS['registry']->getServiceLink('prefs', 'kronolith'), true)->remove(session_name());
            }
            $view->attendees = $this->attendees;

            $methods['mail']['mimepart'] = Kronolith::buildMimeMessage($view, 'mail', $image);
        }
        if (isset($methods['desktop'])) {
            if ($this->isAllDay()) {
                if ($this->start->compareDate($this->end) == 0) {
                    $methods['desktop']['subtitle'] = sprintf(_("On %s"), $start->strftime($prefs->getValue('date_format')));
                } else {
                    $methods['desktop']['subtitle'] = sprintf(_("From %s to %s"), $start->strftime($prefs->getValue('date_format')), $end->strftime($prefs->getValue('date_format')));
                }
            } else {
                $methods['desktop']['subtitle'] = sprintf(_("From %s at %s to %s at %s"), $start->strftime($prefs->getValue('date_format')), $start->format($prefs->getValue('twentyFour') ? 'H:i' : 'h:ia'), $end->strftime($prefs->getValue('date_format')), $this->end->format($prefs->getValue('twentyFour') ? 'H:i' : 'h:ia'));
            }
            $methods['desktop']['url'] = strval($this->getViewUrl(array(), true, false));
        }

        $alarmStart = clone $start;
        $alarmStart->min -= $this->alarm;
        $alarm = array(
            'id' => $this->uid,
            'user' => $user,
            'start' => $alarmStart,
            'end' => $end,
            'methods' => array_keys($methods),
            'params' => $methods,
            'title' => $this->getTitle($user),
            'text' => $this->description,
            'instanceid' => $this->recurs() ? $eventDate->dateString() : null);

        $_SERVER['SERVER_NAME'] = $serverName;
        $GLOBALS['conf']['server']['name'] = $serverConf;

        return $alarm;
    }

    /**
     * Returns a simple object suitable for json transport representing this
     * event.
     *
     * Possible properties are:
     * - t: title
     * - d: description
     * - c: calendar id
     * - s: start date
     * - e: end date
     * - fi: first day of a multi-day event
     * - la: last day of a multi-day event
     * - x: status (Kronolith::STATUS_* constant)
     * - al: all-day?
     * - bg: background color
     * - fg: foreground color
     * - pe: edit permissions?
     * - pd: delete permissions?
     * - vl: variable, i.e. editable length?
     * - a: alarm text or minutes
     * - r: recurrence type (Horde_Date_Recurrence::RECUR_* constant)
     * - bid: The baseid for an event representing an exception
     * - eod: The original date that an exception is replacing
     * - ic: icon
     * - ln: link
     * - aj: ajax link
     * - id: event id
     * - ty: calendar type (driver)
     * - l: location
     * - u: url
     * - sd: formatted start date
     * - st: formatted start time
     * - ed: formatted end date
     * - et: formatted end time
     * - at: attendees
     * - rs: resources
     * - tg: tag list
     * - mt: meeting (Boolean true if event has attendees, false otherwise).
     * - cb: created by (string describing when and who created the event).
     * - mb: modified by (string describing when and who last modified event).
     * - o: organizer (if known)
     * - oy: organizer you
     * - cr: creator's attendance response
     * - fs: Array of attached files.
     *
     * @param array $options  An array of options:
     *
     *  - all_day: (boolean)    If not null, overrides whether the event is an
     *                          all-day event.
     *                          DEFAULT: null (Do not override).
     *  - full: (boolean)       Whether to return all event details.
     *                          DEFAULT: false (Do not return all details).
     *  - time_format: (string) The date() format to use for time formatting.
     *                          DEFAULT: 'H:i'
     *  - history: (boolean)    If true, ensures that this event's history is
     *                          loaded from the History backend.
     *                          DEFAULT: false (Do not ensure history is loaded).
     *
     * @return stdClass  A simple object.
     */
    public function toJson(array $options = array())
    {
        $options = array_merge(array(
            'all_day' => null,
            'full' => false,
            'time_format' => 'H:i',
            'history' => false),
            $options
        );

        $json = new stdClass;
        $json->uid = $this->uid;
        $json->t = $this->getTitle();
        $json->c = $this->calendar;
        $json->s = $this->start->toJson();
        $json->e = $this->end->toJson();
        $json->fi = $this->first;
        $json->la = $this->last;
        $json->x = (int)$this->status;
        $json->al = is_null($options['all_day']) ? $this->isAllDay() : $options['all_day'];
        $json->pe = $this->hasPermission(Horde_Perms::EDIT);
        $json->pd = $this->hasPermission(Horde_Perms::DELETE);
        $json->l = $this->getLocation();
        $json->mt = (bool)count($this->attendees);
        $json->sort = sprintf(
            '%010s%06s',
            $this->originalStart->timestamp(),
            240000 - $this->end->format('His')
        );

        if ($this->icon) {
            $json->ic = $this->icon;
        }
        if ($this->alarm) {
            if ($this->alarm % 10080 == 0) {
                $alarm_value = $this->alarm / 10080;
                $json->a = sprintf(ngettext("%d week", "%d weeks", $alarm_value), $alarm_value);
            } elseif ($this->alarm % 1440 == 0) {
                $alarm_value = $this->alarm / 1440;
                $json->a = sprintf(ngettext("%d day", "%d days", $alarm_value), $alarm_value);
            } elseif ($this->alarm % 60 == 0) {
                $alarm_value = $this->alarm / 60;
                $json->a = sprintf(ngettext("%d hour", "%d hours", $alarm_value), $alarm_value);
            } else {
                $alarm_value = $this->alarm;
                $json->a = sprintf(ngettext("%d minute", "%d minutes", $alarm_value), $alarm_value);
            }
        }
        if ($this->recurs()) {
            $json->r = $this->recurrence->getRecurType();
        } elseif ($this->baseid) {
            $json->bid = $this->baseid;
            if ($this->exceptionoriginaldate) {
                $json->eod = sprintf(_("%s at %s"), $this->exceptionoriginaldate->strftime($GLOBALS['prefs']->getValue('date_format')), $this->exceptionoriginaldate->strftime(($GLOBALS['prefs']->getValue('twentyFour') ? '%H:%M' : '%I:%M %p')));
            }
        }
        if ($this->_resources) {
            $json->rs = $this->_resources;
        }

        if ($options['history']) {
            $this->loadHistory();
            if (!empty($this->created)) {
                $json->cb = sprintf(
                    '%s %s %s',
                    $this->created->strftime($GLOBALS['prefs']->getValue('date_format')),
                    $this->created->strftime(($GLOBALS['prefs']->getValue('twentyFour') ? '%H:%M' : '%I:%M %p')),
                    $this->createdby);
            } else {
                $json->cb = '';
            }
            if (!empty($this->modified)) {
                $json->mb = sprintf(
                    '%s %s %s',
                    $this->modified->strftime($GLOBALS['prefs']->getValue('date_format')),
                    $this->modified->strftime(($GLOBALS['prefs']->getValue('twentyFour') ? '%H:%M' : '%I:%M %p')),
                    $this->modifiedby);
            } else {
                $json->mb = '';
            }
        }
        if ($this->organizer) {
            $json->o = $this->organizer;
            $json->oy = Kronolith::isUserEmail($this->creator, $this->organizer);
        } else {
            $json->oy = true;
        }
        if ($options['full']) {
            $json->id = $this->id;
            $json->ty = $this->calendarType;
            $json->sd = $this->start->strftime('%x');
            $json->st = $this->start->format($options['time_format']);
            $json->ed = $this->end->strftime('%x');
            $json->et = $this->end->format($options['time_format']);
            $json->tz = $this->timezone;
            $json->a = $this->alarm;
            $json->pv = $this->private;
            if ($this->recurs()) {
                $json->r = $this->recurrence->toJson();
            }
            if (!$this->isPrivate()) {
                $json->d = $this->description;
                $json->u =  htmlentities($this->url);
                $json->uhl = $GLOBALS['injector']->getInstance('Horde_Core_Factory_TextFilter')->filter(
                    $GLOBALS['injector']->getInstance('Horde_Core_Factory_TextFilter')->filter($this->url, 'linkurls'),
                    'Xss'
                );
                $json->tg = array_values($this->tags);
                $json->gl = $this->geoLocation;
                if (count($this->attendees)) {
                    $attendees = array();
                    foreach ($this->attendees as $attendee) {
                        if (Kronolith::isUserEmail($this->creator, $attendee->email)) {
                            $json->cr = intval($attendee->response);
                        }
                        $attendeeJson = $attendee->toJson();
                        $attendeeJson->o =
                            ($json->oy &&
                             Kronolith::isUserEmail(
                                 $this->creator,
                                 $attendee->addressObject->bare_address
                             )) ||
                            (!empty($this->organizer) &&
                             $this->organizer == $attendee->addressObject->bare_address);
                        $attendees[] = $attendeeJson;
                    }
                    $json->at = $attendees;
                }
            }

            if ($this->methods) {
                $json->m = $this->methods;
            }

            if ($this->vfsInit()) {
                $files = $this->listFiles();
                $json->fs = count($files)
                    ? $files
                    : false;
            }
        }

        return $json;
    }

    /**
     * Checks if the current event is already present in the calendar.
     *
     * Does the check based on the uid.
     *
     * @return boolean  True if event exists, false otherwise.
     */
    public function exists()
    {
        if (!isset($this->uid) || !isset($this->calendar)) {
            return false;
        }
        try {
            $eventID = $this->getDriver()->exists($this->uid, $this->calendar);
            if (!$eventID) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        $this->id = $eventID;
        return true;
    }

    /**
     * Converts this event between the event's and the local timezone.
     *
     * @param boolean $to_orginal  If true converts to the event's timezone.
     */
    public function setTimezone($to_original)
    {
        if (!$this->timezone || !$this->getDriver()->supportsTimezones()) {
            return;
        }
        $timezone = $to_original ? $this->timezone : date_default_timezone_get();
        $this->start->setTimezone($timezone);
        $this->end->setTimezone($timezone);
        if ($this->recurs() && $this->recurrence->hasRecurEnd()) {
            /* @todo Check if have to go through all recurrence
               exceptions too. */
            $this->recurrence->start->setTimezone($timezone);
            $this->recurrence->recurEnd->setTimezone($timezone);
        }
    }

    public function getDuration()
    {
        if (isset($this->_duration)) {
            return $this->_duration;
        }

        if ($this->start && $this->end) {
            $dur_day_match = $this->start->diff($this->end);
            $dur_hour_match = $this->end->hour - $this->start->hour;
            $dur_min_match = $this->end->min - $this->start->min;
            while ($dur_min_match < 0) {
                $dur_min_match += 60;
                --$dur_hour_match;
            }
            while ($dur_hour_match < 0) {
                $dur_hour_match += 24;
                --$dur_day_match;
            }
        } else {
            $dur_day_match = 0;
            $dur_hour_match = 1;
            $dur_min_match = 0;
        }

        $this->_duration = new stdClass;
        $this->_duration->day = $dur_day_match;
        $this->_duration->hour = $dur_hour_match;
        $this->_duration->min = $dur_min_match;
        $this->_duration->wholeDay = $this->isAllDay();

        return $this->_duration;
    }

    /**
     * Returns whether this event is a recurring event.
     *
     * @return boolean  True if this is a recurring event.
     */
    public function recurs()
    {
        return isset($this->recurrence) &&
            !$this->recurrence->hasRecurType(Horde_Date_Recurrence::RECUR_NONE) &&
            empty($this->baseid);
    }

    /**
     * Returns a description of this event's recurring type.
     *
     * @return string  Human readable recurring type.
     */
    public function getRecurName()
    {
        if (empty($this->baseid)) {
            return $this->recurs()
                ? $this->recurrence->getRecurName()
                : _("No recurrence");
        } else {
            return _("Exception");
        }
    }

    /**
     * Returns a correcty formatted exception date for recurring events and a
     * link to delete this exception.
     *
     * @param string $date  Exception in the format Ymd.
     *
     * @return string  The formatted date and delete link.
     */
    public function exceptionLink($date)
    {
        if (!preg_match('/(\d{4})(\d{2})(\d{2})/', $date, $match)) {
            return '';
        }
        $horde_date = new Horde_Date(array('year' => $match[1],
                                           'month' => $match[2],
                                           'mday' => $match[3]));
        $formatted = $horde_date->strftime($GLOBALS['prefs']->getValue('date_format'));
        return $formatted
            . Horde::url('edit.php')
            ->add(array('calendar' => $this->calendarType . '_' .$this->calendar,
                        'eventID' => $this->id,
                        'del_exception' => $date,
                        'url' => Horde_Util::getFormData('url')))
            ->link(array('title' => sprintf(_("Delete exception on %s"), $formatted)))
            . Horde::img('delete-small.png', _("Delete"))
            . '</a>';
    }

    /**
     * Returns a list of exception dates for recurring events including links
     * to delete them.
     *
     * @return string  List of exception dates and delete links.
     */
    public function exceptionsList()
    {
        $exceptions = $this->recurrence->getExceptions();
        asort($exceptions);
        return implode(', ', array_map(array($this, 'exceptionLink'), $exceptions));
    }

    /**
     * Returns a list of events that represent exceptions to this event's
     * recurrence series, if any. If this event does not recur, an empty array
     * is returned.
     *
     * @param boolean $flat  If true (the default), returns a flat array
     *                       containing Kronolith_Event objects. If false,
     *                       results are in the format of listEvents calls. @see
     *                       Kronolith::listEvents().
     *
     * @return array  An array of Kronolith_Event objects whose baseid property
     *                is equal to this event's uid. I.e., it is a bound
     *                exception.
     *
     * @since 4.2.2
     */
    public function boundExceptions($flat = true)
    {
        if (!$this->recurrence || !$this->uid) {
            return array();
        }
        $return = array();
        $search = new stdClass();
        $search->baseid = $this->uid;
        $results = $this->getDriver()->search($search);
        if (!$flat) {
            return $results;
        }
        foreach ($results as $days) {
            foreach ($days as $exception) {
                $return[] = $exception;
            }
        }

        return $return;
    }

    /**
     * Returns whether the event should be considered private.
     *
     * @param string $user  The current user. If omitted, uses the current user.
     *
     * @return boolean  Whether to consider the event as private.
     */
    public function isPrivate($user = null)
    {
        global $registry;

        if ($user === null) {
            $user = $registry->getAuth();
        }

        // Never private if private is not true or if the current user is the
        // event creator.
        if ((!$this->private || $this->creator == $user) &&
            $this->hasPermission(Horde_Perms::READ, $user)) {
            return false;
        }

        return true;
    }

    /**
     * Returns the title of this event, considering private flags.
     *
     * @param string $user  The current user.
     *
     * @return string  The title of this event.
     */
    public function getTitle($user = null)
    {
        if (!$this->initialized) {
            return '';
        }

        return $this->isPrivate($user)
            ? _("busy")
            : (strlen($this->title) ? $this->title : _("[Unnamed event]"));
    }

    /**
     * Returns the location of this event, considering private flags.
     *
     * @param string $user  The current user.
     *
     * @return string  The location of this event.
     */
    public function getLocation($user = null)
    {
        return $this->isPrivate($user) ? '' : $this->location;
    }

    /**
     * Checks to see whether the specified attendee is associated with the
     * current event.
     *
     * @param string $email            The email address of the attendee.
     * @param boolean $case_sensitive  Match in a case sensitive manner.
     *                                 @since 4.3.0
     * @param array $attendees         Search that attendee list instead of
     *                                 this event's. @since 4.3.0
     *
     * @return boolean  True if the specified attendee is present for this
     *                  event.
     */
    public function hasAttendee(
        $email, $case_sensitive = false, $attendees = null
    )
    {
        if (is_null($attendees)) {
            $attendees = $this->attendees;
        }
        foreach ($attendees as $attendee) {
            if ($attendee->matchesEmail($email, $case_sensitive)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a new attendee to the current event.
     *
     * This will overwrite an existing attendee if one exists with the same
     * email address.
     *
     * @param string $email              The email address of the attendee.
     * @param integer $role              The role code of the attendee.
     * @param integer|boolean $response  The response code of the attendee.
     * @param string $name               The name of the attendee.
     */
    public function addAttendee($email, $role, $response, $name = null)
    {
        $found = false;
        foreach ($this->attendees as $key => &$attendee) {
            if ($attendee->email == $email) {
                $found = true;
                unset($this->attendees[$key]);
                break;
            }
        }
        if ($found) {
            if ($role != Kronolith::PART_IGNORE) {
                $attendee->role = $role;
            }
        } else {
            if ($role == Kronolith::PART_IGNORE) {
                $role = Kronolith::PART_REQUIRED;
            }
            $attendee = new Kronolith_Attendee(
                array('email' => $email, 'role' => $role)
            );
        }

        if ($response !== false) {
            $attendee->response = $response;
        }
        if (strlen($name)) {
            $attendee->name = $name;
        }

        $this->attendees->add($attendee);
    }

    /**
     * Adds a single resource to this event.
     *
     * No validation or acceptence/denial is done here...it should be done
     * when saving the event.
     *
     * @param Kronolith_Resource $resource  The resource to add.
     */
    public function addResource($resource, $response)
    {
        $this->_resources[$resource->getId()] = array(
            'attendance' => Kronolith::PART_REQUIRED,
            'response' => $response,
            'name' => $resource->get('name'),
            'calendar' => $resource->get('calendar')
        );
    }

    /**
     * Removes a resource from this event.
     *
     * @param Kronolith_Resource $resource  The resource to remove.
     */
    public function removeResource($resource)
    {
        if (isset($this->_resources[$resource->getId()])) {
            unset($this->_resources[$resource->getId()]);
        }
    }

    /**
     * Returns all resources.
     *
     * @return array  A copy of the resources array.
     */
    public function getResources()
    {
        return $this->_resources;
    }

    /**
     * Set the entire resource array. Only used when copying an Event.
     *
     * @param array  $resources  The resource array.
     * @since 4.2.6
     */
    public function setResources(array $resources)
    {
        $this->_resources = $resources;
    }

    public function isAllDay()
    {
        return $this->allday ||
            ($this->start->hour == 0 && $this->start->min == 0 && $this->start->sec == 0 &&
             (($this->end->hour == 23 && $this->end->min == 59) ||
              ($this->end->hour == 0 && $this->end->min == 0 && $this->end->sec == 0 &&
               ($this->end->mday > $this->start->mday ||
                $this->end->month > $this->start->month ||
                $this->end->year > $this->start->year))));
    }

    /**
     * Syncronizes tags from the tagging backend with the task storage backend,
     * if necessary.
     *
     * @param array $tags  Tags from the tagging backend.
     */
    public function synchronizeTags($tags)
    {
        if (isset($this->_internaltags)) {
            $lower_internaltags = array_map('Horde_String::lower', $this->_internaltags);
            $lower_tags = array_map('Horde_String::lower', $tags);
            usort($lower_tags, 'strcoll');

            if (array_diff($lower_internaltags, $lower_tags)) {
                Kronolith::getTagger()->replaceTags(
                    $this->uid,
                    $this->_internaltags,
                    $this->_creator,
                    Kronolith_Tagger::TYPE_EVENT
                );
            }
            $this->_tags = $this->_internaltags;
        } else {
            $this->_tags = $tags;
        }
    }

    /**
     * Reads form/post data and updates this event's properties.
     *
     * @param  Kronolith_Event|null $existing  If this is an exception event
     *                                         this is taken as the base event.
     *                                         @since 4.2.6
     *
     */
    public function readForm(Kronolith_Event $existing = null)
    {
        global $notification, $prefs, $registry, $session;

        // Event owner.
        $targetcalendar = Horde_Util::getFormData('targetcalendar');
        if (strpos($targetcalendar, '\\')) {
            list(, $this->creator) = explode('\\', $targetcalendar, 2);
        } elseif (!isset($this->_id)) {
            $this->creator = $registry->getAuth();
        }

        // Basic fields.
        $this->title = Horde_Util::getFormData('title', $this->title);
        $this->description = Horde_Util::getFormData('description', $this->description);
        $this->location = Horde_Util::getFormData('location', $this->location);
        $this->timezone = Horde_Util::getFormData('timezone', $this->timezone);
        $this->private = (bool)Horde_Util::getFormData('private');

        // if the field is empty you are the organizer (and so organizer should be null)
        $this->organizer = Horde_Util::getFormData('organizer', $this->organizer) ?: null;

        // URL.
        $url = Horde_Util::getFormData('eventurl', $this->url);
        if (strlen($url)) {
            // Analyze and re-construct.
            $url = @parse_url($url);
            if ($url) {
                if (function_exists('http_build_url')) {
                    if (empty($url['path'])) {
                        $url['path'] = '/';
                    }
                    $url = http_build_url($url);
                } else {
                    $new_url = '';
                    if (isset($url['scheme'])) {
                        $new_url .= $url['scheme'] . '://';
                    }
                    if (isset($url['user'])) {
                        $new_url .= $url['user'];
                        if (isset($url['pass'])) {
                            $new_url .= ':' . $url['pass'];
                        }
                        $new_url .= '@';
                    }
                    if (isset($url['host'])) {
                        // Convert IDN hosts to ASCII.
                        if (function_exists('idn_to_ascii')) {
                            $url['host'] = @idn_to_ascii($url['host']);
                        } elseif (Horde_Mime::is8bit($url['host'])) {
                            //throw new Kronolith_Exception(_("Invalid character in URL."));
                            $url['host'] = '';
                        }
                        $new_url .= $url['host'];
                    }
                    if (isset($url['path'])) {
                        $new_url .= $url['path'];
                    }
                    if (isset($url['query'])) {
                        $new_url .= '?' . $url['query'];
                    }
                    if (isset($url['fragment'])) {
                        $new_url .= '#' . $url['fragment'];
                    }
                    $url = $new_url;
                }
            }
        }
        $this->url = $url;

        // Status.
        $this->status = Horde_Util::getFormData('status', $this->status);

        // Attendees.
        $attendees = $session->get('kronolith', 'attendees');
        if (!$attendees) {
            $attendees = new Kronolith_Attendee_List();
        }
        $newattendees = Horde_Util::getFormData('attendees');
        $userattendees = Horde_Util::getFormData('users');
        if (!is_null($newattendees) || !is_null($userattendees)) {
            if ($newattendees) {
                $newattendees = Kronolith_Attendee_List::parse(
                    trim($newattendees), $notification
                );
            } else {
                $newattendees = new Kronolith_Attendee_List();
            }
            if ($userattendees) {
                foreach (explode(',', $userattendees) as $user) {
                    if (!$newUser = Kronolith::validateUserAttendee($user)) {
                        $notification->push(sprintf(_("The user \"%s\" does not exist."), $user), 'horde.error');
                    } else {
                        $newattendees->add($newUser);
                    }
                }
            }

            // First add new attendees missing in the current list.
            foreach ($newattendees as $attendee) {
                if (!$attendees->has($attendee)) {
                    $attendees->add($attendee);
                }
            }
            // Now check for attendees in the current list that don't exist in
            // the new attendee list anymore.
            $finalAttendees = new Kronolith_Attendee_List();
            foreach ($attendees as $attendee) {
                if (!$newattendees->has($attendee)) {
                    continue;
                }
                if (Kronolith::isUserEmail($this->creator, $attendee->email)) {
                    $attendee->response = Horde_Util::getFormData('attendance');
                }
                $finalAttendees->add($attendee);
            }
            $attendees = $finalAttendees;
        }
        $this->attendees = $attendees;

        // Event start.
        $allDay = Horde_Util::getFormData('whole_day');
        if ($start_date = Horde_Util::getFormData('start_date')) {
            // From ajax interface.
            $this->start = Kronolith::parseDate($start_date . ' ' . Horde_Util::getFormData('start_time'), true, $this->timezone);
            if ($allDay) {
                $this->start->hour = $this->start->min = $this->start->sec = 0;
            }
        } elseif ($start = Horde_Util::getFormData('start')) {
            // From traditional interface.
            $start_year = $start['year'];
            $start_month = $start['month'];
            $start_day = $start['day'];
            $start_hour = Horde_Util::getFormData('start_hour');
            $start_min = Horde_Util::getFormData('start_min');
            $am_pm = Horde_Util::getFormData('am_pm');

            if (!$prefs->getValue('twentyFour')) {
                if ($am_pm == 'PM') {
                    if ($start_hour != 12) {
                        $start_hour += 12;
                    }
                } elseif ($start_hour == 12) {
                    $start_hour = 0;
                }
            }

            if (Horde_Util::getFormData('end_or_dur') == 1) {
                if ($allDay) {
                    $start_hour = 0;
                    $start_min = 0;
                    $dur_day = 0;
                    $dur_hour = 24;
                    $dur_min = 0;
                } else {
                    $dur_day = (int)Horde_Util::getFormData('dur_day');
                    $dur_hour = (int)Horde_Util::getFormData('dur_hour');
                    $dur_min = (int)Horde_Util::getFormData('dur_min');
                }
            }

            $this->start = new Horde_Date(array('hour' => $start_hour,
                                                'min' => $start_min,
                                                'month' => $start_month,
                                                'mday' => $start_day,
                                                'year' => $start_year),
                                          $this->timezone);
        }

        // Event end.
        if ($end_date = Horde_Util::getFormData('end_date')) {
            // From ajax interface.
            $this->end = Kronolith::parseDate($end_date . ' ' . Horde_Util::getFormData('end_time'), true, $this->timezone);
            if ($allDay) {
                $this->end->hour = $this->end->min = $this->end->sec = 0;
                $this->end->mday++;
            }
        } elseif (Horde_Util::getFormData('end_or_dur') == 1) {
            // Event duration from traditional interface.
            $this->end = new Horde_Date(array('hour' => $start_hour + $dur_hour,
                                              'min' => $start_min + $dur_min,
                                              'month' => $start_month,
                                              'mday' => $start_day + $dur_day,
                                              'year' => $start_year));
        } elseif ($end = Horde_Util::getFormData('end')) {
            // From traditional interface.
            $end_year = $end['year'];
            $end_month = $end['month'];
            $end_day = $end['day'];
            $end_hour = Horde_Util::getFormData('end_hour');
            $end_min = Horde_Util::getFormData('end_min');
            $end_am_pm = Horde_Util::getFormData('end_am_pm');

            if (!$prefs->getValue('twentyFour')) {
                if ($end_am_pm == 'PM') {
                    if ($end_hour != 12) {
                        $end_hour += 12;
                    }
                } elseif ($end_hour == 12) {
                    $end_hour = 0;
                }
            }

            $this->end = new Horde_Date(array('hour' => $end_hour,
                                              'min' => $end_min,
                                              'month' => $end_month,
                                              'mday' => $end_day,
                                              'year' => $end_year),
                                        $this->timezone);
            if ($this->end->compareDateTime($this->start) < 0) {
                $this->end = new Horde_Date($this->start);
            }
        }

        $this->allday = false;

        // Alarm.
        if (!is_null($alarm = Horde_Util::getFormData('alarm'))) {
            if ($alarm) {
                $value = Horde_Util::getFormData('alarm_value');
                $unit = Horde_Util::getFormData('alarm_unit');
                if ($value == 0) {
                    $value = $unit = 1;
                }
                $this->alarm = $value * $unit;
                // Notification.
                if (Horde_Util::getFormData('alarm_change_method')) {
                    $types = Horde_Util::getFormData('event_alarms');
                    $methods = array();
                    if (!empty($types)) {
                        foreach ($types as $type) {
                            $methods[$type] = array();
                            switch ($type){
                            case 'notify':
                                $methods[$type]['sound'] = Horde_Util::getFormData('event_alarms_sound');
                                break;
                            case 'mail':
                                $methods[$type]['email'] = Horde_Util::getFormData('event_alarms_email');
                                break;
                            case 'popup':
                                break;
                            }
                        }
                    }
                    $this->methods = $methods;
                } else {
                    $this->methods = array();
                }
            } else {
                $this->alarm = 0;
                $this->methods = array();
            }
        }

        // Recurrence.
        $this->recurrence = $this->readRecurrenceForm(
            $this->start, $this->timezone, $this->recurrence);

        // Convert to local timezone.
        $this->setTimezone(false);

        $this->_handleResources($existing);

        // Tags.
        $this->tags = Horde_Util::getFormData('tags', $this->tags);

        // Geolocation
        if (Horde_Util::getFormData('lat') && Horde_Util::getFormData('lon')) {
            $this->geoLocation = array('lat' => Horde_Util::getFormData('lat'),
                                       'lon' => Horde_Util::getFormData('lon'),
                                       'zoom' => Horde_Util::getFormData('zoom'));
        }

        $this->initialized = true;
    }

    public static function readRecurrenceForm($start, $timezone,
                                              $recurrence = null)
    {
        $recur = Horde_Util::getFormData('recur');
        if (!strlen($recur)) {
            return $recurrence;
        }
        if (!isset($recurrence)) {
            $recurrence = new Horde_Date_Recurrence($start);
        } else {
            $recurrence->setRecurStart($start);
        }
        if (Horde_Util::getFormData('recur_end_type') == 'date') {
            $end_date = Horde_Util::getFormData('recur_end_date', false);
            if ($end_date !== false) {
                // From ajax interface.
                if (empty($end_date)) {
                    throw new Kronolith_Exception("Missing required end date of recurrence.");
                }
                $date_ob = Kronolith::parseDate($end_date, false);
                $recur_enddate = array(
                    'year'  => $date_ob->year,
                    'month' => $date_ob->month,
                    'day'  => $date_ob->mday);
            } else {
                // From traditional interface.
                $recur_enddate = Horde_Util::getFormData('recur_end');
            }
            if ($recurrence->hasRecurEnd()) {
                $recurEnd = $recurrence->recurEnd;
                $recurEnd->month = $recur_enddate['month'];
                $recurEnd->mday = $recur_enddate['day'];
                $recurEnd->year = $recur_enddate['year'];
            } else {
                $recurEnd = new Horde_Date(
                    array('hour' => 23,
                          'min' => 59,
                          'sec' => 59,
                          'month' => $recur_enddate['month'],
                          'mday' => $recur_enddate['day'],
                          'year' => $recur_enddate['year']),
                    $timezone);
            }
            $recurrence->setRecurEnd($recurEnd);
        } elseif (Horde_Util::getFormData('recur_end_type') == 'count') {
            $recurrence->setRecurCount(Horde_Util::getFormData('recur_count'));
        } elseif (Horde_Util::getFormData('recur_end_type') == 'none') {
            $recurrence->setRecurCount(0);
            $recurrence->setRecurEnd(null);
        }

        $recurrence->setRecurType($recur);
        switch ($recur) {
        case Horde_Date_Recurrence::RECUR_DAILY:
            $recurrence->setRecurInterval(Horde_Util::getFormData('recur_daily_interval', 1));
            break;

        case Horde_Date_Recurrence::RECUR_WEEKLY:
            $weekly = Horde_Util::getFormData('weekly');
            $weekdays = 0;
            if (is_array($weekly)) {
                foreach ($weekly as $day) {
                    $weekdays |= $day;
                }
            }

            if ($weekdays == 0) {
                // Sunday starts at 0.
                switch ($start->dayOfWeek()) {
                case 0: $weekdays |= Horde_Date::MASK_SUNDAY; break;
                case 1: $weekdays |= Horde_Date::MASK_MONDAY; break;
                case 2: $weekdays |= Horde_Date::MASK_TUESDAY; break;
                case 3: $weekdays |= Horde_Date::MASK_WEDNESDAY; break;
                case 4: $weekdays |= Horde_Date::MASK_THURSDAY; break;
                case 5: $weekdays |= Horde_Date::MASK_FRIDAY; break;
                case 6: $weekdays |= Horde_Date::MASK_SATURDAY; break;
                }
            }

            $recurrence->setRecurInterval(Horde_Util::getFormData('recur_weekly_interval', 1));
            $recurrence->setRecurOnDay($weekdays);
            break;

        case Horde_Date_Recurrence::RECUR_MONTHLY_DATE:
            switch (Horde_Util::getFormData('recur_monthly_scheme')) {
            case Horde_Date_Recurrence::RECUR_MONTHLY_WEEKDAY:
            case Horde_Date_Recurrence::RECUR_MONTHLY_LAST_WEEKDAY:
                $recurrence->setRecurType(Horde_Util::getFormData('recur_monthly_scheme'));
            case Horde_Date_Recurrence::RECUR_MONTHLY_DATE:
                $recurrence->setRecurInterval(
                    Horde_Util::getFormData('recur_monthly')
                        ? 1
                        : Horde_Util::getFormData('recur_monthly_interval', 1)
                );
                break;
            default:
                $recurrence->setRecurInterval(Horde_Util::getFormData('recur_day_of_month_interval', 1));
                break;
            }
            break;

        case Horde_Date_Recurrence::RECUR_MONTHLY_WEEKDAY:
            $recurrence->setRecurInterval(Horde_Util::getFormData('recur_week_of_month_interval', 1));
            break;

        case Horde_Date_Recurrence::RECUR_MONTHLY_LAST_WEEKDAY:
            $recurrence->setRecurInterval(Horde_Util::getFormData('recur_last_week_of_month_interval', 1));
            break;

        case Horde_Date_Recurrence::RECUR_YEARLY_DATE:
            switch (Horde_Util::getFormData('recur_yearly_scheme')) {
            case Horde_Date_Recurrence::RECUR_YEARLY_WEEKDAY:
            case Horde_Date_Recurrence::RECUR_YEARLY_DAY:
                $recurrence->setRecurType(Horde_Util::getFormData('recur_yearly_scheme'));
            case Horde_Date_Recurrence::RECUR_YEARLY_DATE:
                $recurrence->setRecurInterval(
                    Horde_Util::getFormData('recur_yearly')
                        ? 1
                        : Horde_Util::getFormData('recur_yearly_interval', 1)
                );
                break;
            default:
                $recurrence->setRecurInterval(Horde_Util::getFormData('recur_yearly_interval', 1));
                break;
            }
            break;

        case Horde_Date_Recurrence::RECUR_YEARLY_DAY:
            $recurrence->setRecurInterval(Horde_Util::getFormData('recur_yearly_day_interval', $yearly_interval));
            break;

        case Horde_Date_Recurrence::RECUR_YEARLY_WEEKDAY:
            $recurrence->setRecurInterval(Horde_Util::getFormData('recur_yearly_weekday_interval', $yearly_interval));
            break;
        }

        foreach (array('exceptions', 'completions') as $what) {
            if ($data = Horde_Util::getFormData($what)) {
                if (!is_array($data)) {
                    $data = explode(',', $data);
                }
                foreach ($data as $date) {
                    list($year, $month, $mday) = sscanf($date, '%04d%02d%02d');
                    if ($what == 'exceptions') {
                        $recurrence->addException($year, $month, $mday);
                    } else {
                        $recurrence->addCompletion($year, $month, $mday);
                    }
                }
            }
        }

        return $recurrence;
    }

    /**
     * Handles updating/saving this event's resources. Unless this event recurs,
     * this will delete this event from any resource calendars that are no
     * longer needed (as when a resource is removed from an existing event). If
     * this event is an exception, i.e., contains a baseid, AND $existing is
     * provided, the resources from the original event are used for purposes
     * of determining any resources that need to be removed.
     *
     *
     * @param  Kronolith_Event|null $existing  An existing base event.
     * @since 4.2.6
     */
    protected function _handleResources(Kronolith_Event $existing = null)
    {
        global $notification, $session;

        if (Horde_Util::getFormData('isajax', false)) {
            $resources = array();
        } else {
            $resources = $session->get('kronolith', 'resources', Horde_Session::TYPE_ARRAY);
        }

        $existingResources = $this->_resources;
        $newresources = Horde_Util::getFormData('resources');
        if (!empty($newresources)) {
            foreach (explode(',', $newresources) as $id) {
                try {
                    $resource = Kronolith::getDriver('Resource')->getResource($id);
                } catch (Kronolith_Exception $e) {
                    $notification->push($e->getMessage(), 'horde.error');
                    continue;
                }
                if (!($resource instanceof Kronolith_Resource_Group) ||
                    $resource->isFree($this)) {
                    $resources[$resource->getId()] = array(
                        'attendance' => Kronolith::PART_REQUIRED,
                        'response'   => Kronolith::RESPONSE_NONE,
                        'name'       => $resource->get('name')
                    );
                } else {
                    $notification->push(_("No resources from this group were available"), 'horde.error');
                }
            }
        }
        $this->_resources = $resources;


        // Have the base event, and this is an exception so we must
        // match the recurrence in the resource's copy of the base event.
        if (!empty($existing) && $existing->recurs() && !$this->recurs()) {
            foreach ($existing->getResources() as $rid => $data) {
                $resource = Kronolith::getDriver('Resource')->getResource($key);
                $r_event = Kronolith::getDriver('Resource')->getByUID($existing->uid, $resource->calendar);
                $r_event->recurrence = $event->recurrence;
                $r_event->save();
            }
        }

        // If we don't recur, check for removal of any resources so we can
        // update those resources' calendars.
        if (!$this->recurs()) {
            $merged = $existingResources + $this->_resources;
            $delete = array_diff(array_keys($existingResources), array_keys($this->_resources));
            foreach ($delete as $key) {
                // Resource might be declined, in which case it won't have the event
                // on it's calendar.
                if ($merged[$key]['response'] != Kronolith::RESPONSE_DECLINED) {
                    try {
                        Kronolith::getDriver('Resource')
                            ->getResource($key)
                            ->removeEvent($this);
                    } catch (Kronolith_Exception $e) {
                        $notification->push('foo', 'horde.error');
                    }
                }
            }
        }
    }

    public function html($property)
    {
        global $prefs;

        $options = array();
        $attributes = '';
        $sel = false;
        $label = '';

        switch ($property) {
        case 'start[year]':
            return  '<label for="' . $this->_formIDEncode($property) . '" class="hidden">' . _("Start Year") . '</label>' .
                '<input name="' . $property . '" value="' . $this->start->year .
                '" type="text"' .
                ' id="' . $this->_formIDEncode($property) . '" size="4" maxlength="4" />';

        case 'start[month]':
            $sel = $this->start->month;
            for ($i = 1; $i < 13; ++$i) {
                $options[$i] = strftime('%b', mktime(1, 1, 1, $i, 1));
            }
            $label = _("Start Month");
            break;

        case 'start[day]':
            $sel = $this->start->mday;
            for ($i = 1; $i < 32; ++$i) {
                $options[$i] = $i;
            }
            $label = _("Start Day");
            break;

        case 'start_hour':
            $sel = $this->start->format($prefs->getValue('twentyFour') ? 'G' : 'g');
            $hour_min = $prefs->getValue('twentyFour') ? 0 : 1;
            $hour_max = $prefs->getValue('twentyFour') ? 24 : 13;
            for ($i = $hour_min; $i < $hour_max; ++$i) {
                $options[$i] = $i;
            }
            $label = _("Start Hour");
            break;

        case 'start_min':
            $sel = sprintf('%02d', $this->start->min);
            for ($i = 0; $i < 12; ++$i) {
                $min = sprintf('%02d', $i * 5);
                $options[$min] = $min;
            }
            $label = _("Start Minute");
            break;

        case 'end[year]':
            return  '<label for="' . $this->_formIDEncode($property) . '" class="hidden">' . _("End Year") . '</label>' .
                '<input name="' . $property . '" value="' . $this->end->year .
                '" type="text"' .
                ' id="' . $this->_formIDEncode($property) . '" size="4" maxlength="4" />';

        case 'end[month]':
            $sel = $this->end ? $this->end->month : $this->start->month;
            for ($i = 1; $i < 13; ++$i) {
                $options[$i] = strftime('%b', mktime(1, 1, 1, $i, 1));
            }
            $label = _("End Month");
            break;

        case 'end[day]':
            $sel = $this->end ? $this->end->mday : $this->start->mday;
            for ($i = 1; $i < 32; ++$i) {
                $options[$i] = $i;
            }
            $label = _("End Day");
            break;

        case 'end_hour':
            $sel = $this->end
                ? $this->end->format($prefs->getValue('twentyFour') ? 'G' : 'g')
                : $this->start->format($prefs->getValue('twentyFour') ? 'G' : 'g') + 1;
            $hour_min = $prefs->getValue('twentyFour') ? 0 : 1;
            $hour_max = $prefs->getValue('twentyFour') ? 24 : 13;
            for ($i = $hour_min; $i < $hour_max; ++$i) {
                $options[$i] = $i;
            }
            $label = _("End Hour");
            break;

        case 'end_min':
            $sel = $this->end ? $this->end->min : $this->start->min;
            $sel = sprintf('%02d', $sel);
            for ($i = 0; $i < 12; ++$i) {
                $min = sprintf('%02d', $i * 5);
                $options[$min] = $min;
            }
            $label = _("End Minute");
            break;

        case 'dur_day':
            $dur = $this->getDuration();
            return  '<label for="' . $property . '" class="hidden">' . _("Duration Day") . '</label>' .
                '<input name="' . $property . '" value="' . $dur->day .
                '" type="text"' .
                ' id="' . $property . '" size="4" maxlength="4" />';

        case 'dur_hour':
            $dur = $this->getDuration();
            $sel = $dur->hour;
            for ($i = 0; $i < 24; ++$i) {
                $options[$i] = $i;
            }
            $label = _("Duration Hour");
            break;

        case 'dur_min':
            $dur = $this->getDuration();
            $sel = $dur->min;
            for ($i = 0; $i < 13; ++$i) {
                $min = sprintf('%02d', $i * 5);
                $options[$min] = $min;
            }
            $label = _("Duration Minute");
            break;

        case 'recur_end[year]':
            if ($this->end) {
                $end = ($this->recurs() && $this->recurrence->hasRecurEnd())
                        ? $this->recurrence->recurEnd->year
                        : $this->end->year;
            } else {
                $end = $this->start->year;
            }
            return  '<label for="' . $this->_formIDEncode($property) . '" class="hidden">' . _("Recurrence End Year") . '</label>' .
                '<input name="' . $property . '" value="' . $end .
                '" type="text"' .
                ' id="' . $this->_formIDEncode($property) . '" size="4" maxlength="4" />';

        case 'recur_end[month]':
            if ($this->end) {
                $sel = ($this->recurs() && $this->recurrence->hasRecurEnd())
                    ? $this->recurrence->recurEnd->month
                    : $this->end->month;
            } else {
                $sel = $this->start->month;
            }
            for ($i = 1; $i < 13; ++$i) {
                $options[$i] = strftime('%b', mktime(1, 1, 1, $i, 1));
            }
            $label = _("Recurrence End Month");
            break;

        case 'recur_end[day]':
            if ($this->end) {
                $sel = ($this->recurs() && $this->recurrence->hasRecurEnd())
                    ? $this->recurrence->recurEnd->mday
                    : $this->end->mday;
            } else {
                $sel = $this->start->mday;
            }
            for ($i = 1; $i < 32; ++$i) {
                $options[$i] = $i;
            }
            $label = _("Recurrence End Day");
            break;
        }

        if (!$this->_varRenderer) {
            $this->_varRenderer = Horde_Core_Ui_VarRenderer::factory('Html');
        }

        return '<label for="' . $this->_formIDEncode($property) . '" class="hidden">' . $label . '</label>' .
            '<select name="' . $property . '"' . $attributes . ' id="' . $this->_formIDEncode($property) . '">' .
            $this->_varRenderer->selectOptions($options, $sel) .
            '</select>';
    }

    /**
     * @param array $params
     *
     * @return Horde_Url
     */
    public function getViewUrl($params = array(), $full = false, $encoded = true)
    {
        $params['eventID'] = $this->id;
        $params['calendar'] = $this->calendar;
        $params['type'] = $this->calendarType;

        return Horde::url('event.php', $full)->setRaw(!$encoded)->add($params);
    }

    /**
     * @param array $params
     *
     * @return Horde_Url
     */
    public function getEditUrl($params = array(), $full = false)
    {
        $params['view'] = 'EditEvent';
        $params['eventID'] = $this->id;
        $params['calendar'] = $this->calendar;
        $params['type'] = $this->calendarType;

        return Horde::url('event.php', $full)->add($params);
    }

    /**
     * @param array $params
     *
     * @return Horde_Url
     */
    public function getDeleteUrl($params = array(), $full = false)
    {
        $params['view'] = 'DeleteEvent';
        $params['eventID'] = $this->id;
        $params['calendar'] = $this->calendar;
        $params['type'] = $this->calendarType;

        return Horde::url('event.php', $full)->add($params);
    }

    /**
     * @param array $params
     *
     * @return Horde_Url
     */
    public function getExportUrl($params = array(), $full = false)
    {
        $params['view'] = 'ExportEvent';
        $params['eventID'] = $this->id;
        $params['calendar'] = $this->calendar;
        $params['type'] = $this->calendarType;

        return Horde::url('event.php', $full)->add($params);
    }

    public function getLink($datetime = null, $icons = true, $from_url = null,
                            $full = false, $encoded = true)
    {
        global $prefs;

        if (is_null($datetime)) {
            $datetime = $this->start;
        }
        if (is_null($from_url)) {
            $from_url = Horde::selfUrl(true, false, true);
        }

        $event_title = $this->getTitle();
        $view_url = $this->getViewUrl(array('datetime' => $datetime->strftime('%Y%m%d%H%M%S'), 'url' => $from_url), $full, $encoded);
        $read_permission = $this->hasPermission(Horde_Perms::READ);

        $link = '<span' . $this->getCSSColors() . '>';
        if ($read_permission && $view_url) {
            $link .= Horde::linkTooltip($view_url,
                                       $event_title,
                                       $this->getStatusClass(),
                                       '',
                                       '',
                                       $this->getTooltip(),
                                       '',
                                       array('style' => $this->getCSSColors(false)));
        }
        $link .= htmlspecialchars($event_title);
        if ($read_permission && $view_url) {
            $link .= '</a>';
        }

        if ($icons && $prefs->getValue('show_icons')) {
            $icon_color = $this->_foregroundColor == '#000' ? '000' : 'fff';
            $status = '';
            if ($this->alarm) {
                if ($this->alarm % 10080 == 0) {
                    $alarm_value = $this->alarm / 10080;
                    $title = sprintf(ngettext("Alarm %d week before", "Alarm %d weeks before", $alarm_value), $alarm_value);
                } elseif ($this->alarm % 1440 == 0) {
                    $alarm_value = $this->alarm / 1440;
                    $title = sprintf(ngettext("Alarm %d day before", "Alarm %d days before", $alarm_value), $alarm_value);
                } elseif ($this->alarm % 60 == 0) {
                    $alarm_value = $this->alarm / 60;
                    $title = sprintf(ngettext("Alarm %d hour before", "Alarm %d hours before", $alarm_value), $alarm_value);
                } else {
                    $alarm_value = $this->alarm;
                    $title = sprintf(ngettext("Alarm %d minute before", "Alarm %d minutes before", $alarm_value), $alarm_value);
                }
                $status .= Horde::fullSrcImg('alarm-' . $icon_color . '.png', array('attr' => array('alt' => $title, 'title' => $title, 'class' => 'iconAlarm')));
            }

            if ($this->recurs()) {
                $title = Kronolith::recurToString($this->recurrence->getRecurType());
                $status .= Horde::fullSrcImg('recur-' . $icon_color . '.png', array('attr' => array('alt' => $title, 'title' => $title, 'class' => 'iconRecur')));
            } elseif ($this->baseid) {
                $title = _("Exception");
                $status .= Horde::fullSrcImg('exception-' . $icon_color . '.png', array('attr' => array('alt' => $title, 'title' => $title, 'class' => 'iconRecur')));
            }

            if ($this->private) {
                $title = _("Private event");
                $status .= Horde::fullSrcImg('private-' . $icon_color . '.png', array('attr' => array('alt' => $title, 'title' => $title, 'class' => 'iconPrivate')));
            }

            if (count($this->attendees)) {
                $status .= Horde::fullSrcImg('attendees-' . $icon_color . '.png', array('attr' => array('alt' => _("Meeting"), 'title' => _("Meeting"), 'class' => 'iconPeople')));
            }

            $space = ' ';
            if (!empty($this->icon)) {
                $link = $status . ' <img class="kronolithEventIcon" src="' . $this->icon . '" /> ' . $link;
            } elseif (!empty($status)) {
                $link .= ' ' . $status;
                $space = '';
            }

            if ((!$this->private ||
                 $this->creator == $GLOBALS['registry']->getAuth()) &&
                Kronolith::getDefaultCalendar(Horde_Perms::EDIT)) {
                $url = $this->getEditUrl(
                    array('datetime' => $datetime->strftime('%Y%m%d%H%M%S'),
                          'url' => $from_url),
                    $full);
                if ($url) {
                    $link .= $space
                        . $url->link(array('title' => sprintf(_("Edit %s"), $event_title),
                                           'class' => 'iconEdit'))
                        . Horde::fullSrcImg('edit-' . $icon_color . '.png',
                                            array('attr' => array('alt' => _("Edit"))))
                        . '</a>';
                    $space = '';
                }
            }
            if ($this->hasPermission(Horde_Perms::DELETE)) {
                $url = $this->getDeleteUrl(
                    array('datetime' => $datetime->strftime('%Y%m%d%H%M%S'),
                          'url' => $from_url),
                    $full);
                if ($url) {
                    $link .= $space
                        . $url->link(array('title' => sprintf(_("Delete %s"), $event_title),
                                           'class' => 'iconDelete'))
                        . Horde::fullSrcImg('delete-' . $icon_color . '.png',
                                            array('attr' => array('alt' => _("Delete"))))
                        . '</a>';
                }
            }
        }

        return $link . '</span>';
    }

    /**
     * Returns the CSS color definition for this event.
     *
     * @param boolean $with_attribute  Whether to wrap the colors inside a
     *                                 "style" attribute.
     *
     * @return string  A CSS string with color definitions.
     */
    public function getCSSColors($with_attribute = true)
    {
        $css = 'background-color:' . $this->_backgroundColor . ';color:' . $this->_foregroundColor;
        if ($with_attribute) {
            $css = ' style="' . $css . '"';
        }
        return $css;
    }

    /**
     * @return string  A tooltip for quick descriptions of this event.
     */
    public function getTooltip()
    {
        $tooltip = $this->getTimeRange()
            . "\n" . sprintf(_("Owner: %s"), ($this->creator == $GLOBALS['registry']->getAuth() ?
                                              _("Me") : Kronolith::getUserName($this->creator)));

        if (!$this->isPrivate()) {
            if ($this->location) {
                $tooltip .= "\n" . _("Location") . ': ' . $this->location;
            }

            if ($this->description) {
                $tooltip .= "\n\n" . Horde_String::wrap($this->description);
            }
        }

        return $tooltip;
    }

    /**
     * @return string  The time range of the event ("All Day", "1:00pm-3:00pm",
     *                 "08:00-22:00").
     */
    public function getTimeRange()
    {
        if ($this->isAllDay()) {
            return _("All day");
        } elseif (($cmp = $this->start->compareDate($this->end)) > 0) {
            $df = $GLOBALS['prefs']->getValue('date_format');
            if ($cmp > 0) {
                return $this->end->strftime($df) . '-'
                    . $this->start->strftime($df);
            } else {
                return $this->start->strftime($df) . '-'
                    . $this->end->strftime($df);
            }
        } else {
            $twentyFour = $GLOBALS['prefs']->getValue('twentyFour');
            return $this->start->format($twentyFour ? 'G:i' : 'g:ia')
                . '-'
                . $this->end->format($twentyFour ? 'G:i' : 'g:ia');
        }
    }

    /**
     * @return string  The CSS class for the event based on its status.
     */
    public function getStatusClass()
    {
        switch ($this->status) {
        case Kronolith::STATUS_CANCELLED:
            return 'kronolith-event-cancelled';

        case Kronolith::STATUS_TENTATIVE:
        case Kronolith::STATUS_FREE:
            return 'kronolith-event-tentative';
        }
    }

    protected function _formIDEncode($id)
    {
        return str_replace(array('[', ']'),
                           array('_', ''),
                           $id);
    }

    /**
     * Loads the VFS configuration and initializes the VFS backend.
     *
     * @return Horde_Vfs  A VFS object.
     * @throws Kronolith_Exception
     */
    public function vfsInit()
    {
        if (!isset($this->_vfs)) {
            try {
                $this->_vfs = $GLOBALS['injector']
                    ->getInstance('Horde_Core_Factory_Vfs')
                    ->create('documents');
            } catch (Horde_Exception $e) {
                throw new Kronolith_Exception($e);
            }
        }

        return $this->_vfs;
    }

    /**
     * Return a unique id suitable for identifying this event in the VFS. Takes
     * into account there may be multiple users with access to the same UID in
     * different calendars.
     *
     * @return string  The unique id.
     */
    public function getVfsUid()
    {
        return $this->calendar . ':' . $this->uid;
    }

    /**
     * Saves a file into the VFS backend associated with this event.
     *
     * @param array $info  A hash with the file information as returned from a
     *                     Horde_Form_Type_file.
     *
     * @throws Kronolith_Exception
     */
    public function addFile(array $info)
    {
        $this->_addFile($info);
    }

    /**
     * Saves a file into the VFS backend associated with this event.
     *
     * @param array $info  A hash with the file information and the file
     *                     contents in 'data'.
     *
     * @throws Kronolith_Exception
     */
    public function addFileFromData($info)
    {
        $this->_addFile($info, true);
    }

    /**
     * Saves a file into the VFS backend associated with this event.
     *
     * @param array $info    A hash with the file information.
     * @param boolean $data  Whether the file contents is in $info['data'].
     *
     * @throws Kronolith_Exception
     */
    protected function _addFile($info, $data = false)
    {
        if (empty($this->uid)) {
            throw new Kronolith_Exception("VFS not supported until object saved");
        }

        $vfs = $this->vfsInit();
        $dir = Kronolith::VFS_PATH . '/' . $this->getVfsUid();
        $file = $info['name'];
        while ($vfs->exists($dir, $file)) {
            if (preg_match('/(.*)\[(\d+)\](\.[^.]*)?$/', $file, $match)) {
                $file = $match[1] . '[' . ++$match[2] . ']' . $match[3];
            } else {
                $dot = strrpos($file, '.');
                if ($dot === false) {
                    $file .= '[1]';
                } else {
                    $file = substr($file, 0, $dot) . '[1]' . substr($file, $dot);
                }
            }
        }
        try {
            if ($data) {
                $vfs->writeData($dir, $file, $info['data'], true);
            } else {
                $vfs->write($dir, $file, $info['tmp_name'], true);
            }
        } catch (Horde_Vfs_Exception $e) {
            throw new Kronolith_Exception($e);
        }
    }

    /**
     * Deletes a file from the VFS backend associated with this event.
     *
     * @param string $file  The file name.
     * @throws Kronolith_Exception
     */
    public function deleteFile($file)
    {
        if (empty($this->uid)) {
            throw new Kronolith_Exception('VFS not supported for this object.');
        }

        try {
            $this->vfsInit()->deleteFile(Kronolith::VFS_PATH . '/' . $this->getVfsUid(), $file);
        } catch (Horde_Vfs_Exception $e) {
            throw new Kronolith_Exception($e);
        }
    }

    /**
     * Deletes all files from the VFS backend associated with this event.
     *
     * @throws Kronolith_Exception
     */
    public function deleteFiles()
    {
        if (empty($this->uid)) {
            throw new Kronolith_Exception('VFS not supported for this object.');
        }

        $vfs = $this->vfsInit();

        if ($vfs->exists(Kronolith::VFS_PATH, $this->getVfsUid())) {
            try {
                $vfs->deleteFolder(Kronolith::VFS_PATH, $this->getVfsUid(), true);
            } catch (Horde_Vfs_Exception $e) {
                throw new Kronolith_Exception($e);
            }
        }
    }

    /**
     * Returns all files from the VFS backend associated with this event.
     *
     * @return array  A list of hashes with file informations.
     */
    public function listFiles()
    {
        if ($this->uid) {
            try {
                $vfs = $this->vfsInit();
                if ($vfs->exists(Kronolith::VFS_PATH, $this->getVfsUid())) {
                    return $vfs->listFolder(Kronolith::VFS_PATH . '/' . $this->getVfsUid());
                }
            } catch (Kronolith_Exception $e) {
            }
        }

        return array();
    }

    /**
     * Returns a link to display and download a file from the VFS backend
     * associated with this object.
     *
     * @param array $file  The file information hash as returned from self::listFiles.
     *
     * @return string  The HTML code of the generated link.
     */
    public function vfsDisplayUrl($file)
    {
        global $registry;

        $mime_part = new Horde_Mime_Part();
        $mime_part->setType(Horde_Mime_Magic::extToMime($file['type']));
        $viewer = $GLOBALS['injector']->getInstance('Horde_Core_Factory_MimeViewer')->create($mime_part);

        // We can always download files.
        $url_params = array(
            'actionID' => 'download_file',
            'file' => $file['name'],
            'type' => $file['type'],
            'source' => $this->calendarType . '|' . $this->calendar,
            'key' => $this->_id
        );
        $dl = Horde::link($registry->downloadUrl($file['name'], $url_params), $file['name']) . Horde_Themes_Image::tag('download.png', array('alt' => _("Download"))) . '</a>';

        // Let's see if we can view this one, too.
        if ($viewer && !($viewer instanceof Horde_Mime_Viewer_Default)) {
            $url = Horde::url('viewer.php')
                ->add($url_params)
                ->add('actionID', 'view_file');
            $link = Horde::link($url, $file['name'], null, '_blank') . $file['name'] . '</a>';
        } else {
            $link = $file['name'];
        }

        return $link . ' ' . $dl;
    }

    /**
     * Returns a link to display, download, and delete a file from the VFS
     * backend associated with this object.
     *
     * @param array $file  The file information hash as returned from self::listFiles.
     *
     * @return string  The HTML code of the generated link.
     */
    public function vfsEditUrl($file)
    {
        $delform = '<form action="' .
            Horde::url('deletefile.php') .
            '" style="display:inline" method="post">' .
            Horde_Util::formInput() .
            '<input type="hidden" name="file" value="' . htmlspecialchars($file['name']) . '" />' .
            '<input type="hidden" name="source" value="' . htmlspecialchars($this->calendar) . '" />' .
            '<input type="hidden" name="key" value="' . htmlspecialchars($this->_id) . '" />' .
            '<input type="image" class="img" src="' . Horde_Themes::img('delete.png') . '" />' .
            '</form>';

        return $this->vfsDisplayUrl($file) . ' ' . $delform;
    }

    /**
     * Ensure the given string is valid UTF-8.
     *
     * @param string $text  The string to ensure contains no invalid UTF-8 sequences.
     *
     * @return string|boolean  The valid UTF-8 string, possibly with illegal sequences removed.
     */
    protected function _ensureUtf8($text)
    {
        if (Horde_String::validUtf8($text)) {
            return $text;
        }

        return preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $text);
    }
}
