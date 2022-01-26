<?php
/**
 * Class to handle creating ICS (iCalendar) feeds.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion\Syndication\Formats;


/**
 * ICS Feed class.
 * @package glfusion
 */
class ICS extends \glFusion\Syndication\Feed
{
    public static $versions = array('1.0');

    /**
     * Generate and write the feed file.
     *
     * @return  bool    True on success, False on error
     */
    protected function _generate() : bool
    {
        global $_CONF, $_SYND_DEBUG;

        $vCalendar = new \Eluceo\iCal\Component\Calendar(
            $_CONF['site_url'] . '//NONSGML ' . $this->title . '//' . strtoupper($_CONF['iso_lang'])
        );

        $vCalendar->setMethod('PUBLISH');
        if (!empty($this->title)) {
            $vCalendar->setName($this->title);
        }
        if (!empty($this->description)) {
            $vCalendar->setDescription($this->description);
        }
        if (empty($this->filename)) {
            $this->filename = 'glfusion.rss';
        }

        $content = PLG_getFeedContent($this->type, $this->fid, $link, $data, $this->format, $this->format_version);
        //var_dump($content);die;
        if ( is_array($content) ) {
            foreach ( $content AS $feedItem ) {
                if (!isset($feedItem['guid'])) {
                    $feedItem['guid'] = $feedItem['link'];
                }
                $vEvent = new \Eluceo\iCal\Component\Event();
                foreach($feedItem as $var => $value) {
                    switch ($var) {
                        case 'date' :
                            $date = is_numeric($value) ? date('c', $value) : $value;
                            $vEvent->setCreated(new \DateTime($date));
                            break;

                        case 'modified' :
                            $date = is_numeric($value) ? date('c', $value) : $value;
                            $vEvent->setModified(new \DateTime($date));
                            break;

                        case 'title' :
                            $vEvent->setSummary($value);
                
                        case 'summary' :
                            $vEvent->setDescription($value);
                            break;

                        case 'guid' :
                            $vEvent->setUniqueId($value);
                            break;

                        case 'link' :
                            $vEvent->setUrl($value);
                            break;

                        case 'dtstart' :
                            $vEvent->setDtStart(new \DateTime($value));
                            break;

                        case 'dtend' :
                            $vEvent->setDtEnd(new \DateTime($value));
                            break;

                        case 'location' :
                            $vEvent->setLocation($value);
                            break;

                        case 'allday' :
                            $vEvent->setNoTime($value);
                            break;

                        case 'status' :
                            $vEvent->setStatus($value);
                            break;

                        case 'sequence':
                            $vEvent->setSequence($value);
                            break;

                        case 'rrule' :
                            if ($value !== null && $value !== '') {
                                $rrule = new \Eluceo\iCal\Property\Event\RecurrenceRule();
                                $ruleArray = explode(';',$value);
                                $rules = array();
                                foreach ( $ruleArray AS $element ) {
                                    $rule = explode('=',$element);
                                    if ( $rule[0] != '' ) {
                                        $rules[$rule[0]] = $rule[1];
                                    }
                                }
                                foreach ($rules AS $type => $var) {
                                    switch ($type) {
                                        case 'FREQ' : 
                                            $rrule->setFreq($var);
                                            break;
                                        case 'INTERVAL' :
                                            $rrule->setInterval($var);
                                            break;
                                        case 'BYSETPOS' :
                                            $rrule->setBySetPos($var);
                                            break;
                                        case 'BYDAY' :
                                            $rrule->setByDay($var);
                                            break;
                                        case 'BYMONTHDAY' :
                                            $rrule->setByMonthDay((int)$var);
                                            break;
                                        case 'BYMONTH' :
                                            $rrule->setByMonth( (int) $var);
                                            break;
                                        case 'DTSTART' :
                                            $vEvent->setDtStart(new \DateTime($var));
                                            break;
                                        case 'COUNT' :
                                            $rrule->setCount($var);
                                            break;
                                        default :
                                            Log::write('system',Log::ERROR,"SYND: RRULE unknown: " . $type);
                                            break;
                                    }
                                }
                                $vEvent->setRecurrenceRule($rrule);
                            }
                            break;
                    }
                }
                $vCalendar->addComponent($vEvent);
            }
        }
        if (empty($link)) {
            $link = $_CONF['site_url'];
        }
        $feedData = $vCalendar->render();
        $this->setUpdateData($data);
        return $this->writeFile($feedData);
    }

}

