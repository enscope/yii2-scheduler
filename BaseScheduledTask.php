<?php
/**
 * @package   yii2-scheduler
 * @author    Miro Hudak <mhudak@dev.enscope.com>
 * @copyright Copyright &copy; Miro Hudak, enscope.com, 2016
 * @version   1.0
 */

namespace enscope\Scheduler
{
    use yii\base\InvalidConfigException;
    use yii\base\Object;

    abstract class BaseScheduledTask
        extends Object
        implements ScheduledTaskInterface
    {
        /** @var string Cron-like schedule specification */
        public $schedule = false;

        /**
         * Schedule task definition, which is invoked only when the internal
         * schedule is satisfied or the execution is forced.
         */
        abstract public function scheduledTask();

        /**
         * Executes scheduled task, but task must determine, if it will
         * perform the job or not, based on internal scheduling mechanism.
         *
         * @param \DateTime $time             Time of the scheduler invocation, that may be different
         *                                    from the current time, but all schedules must relate
         *                                    to this time when checking if schedule is satisfied
         * @param bool      $force            If true, task should run regardless of schedule
         *
         * @return bool TRUE, if the task was actually executed
         */
        public function execute(\DateTime $time, $force = false)
        {
            if ($this->isScheduleSatisfied($time)
                || $force
            )
            {
                $this->scheduledTask();

                return (true);
            }

            return (false);
        }

        protected function isScheduleSatisfied(\DateTime $time)
        {
            // the loop will iterate thru all elements to avoid hidden errors
            // in schedule definition; if it would return false in the instant
            // the schedule is not satisfied, errors in later schedule def parts
            // would be hidden until the preceding schedule part is satisfied;
            // therefore the optimization would be error-prone and is not used
            $satisfied = true;
            foreach (explode(' ', $this->schedule) as $index => $def)
            {
                switch ($index)
                {
                    case 0:
                        $satisfied &= $this->isSchedulePartSatisfied($def, $time->format('i'));
                        break;
                    case 1:
                        $satisfied &= $this->isSchedulePartSatisfied($def, $time->format('H'));
                        break;
                    case 2:
                        $satisfied &= $this->isSchedulePartSatisfied($def, $time->format('j'));
                        break;
                    case 3:
                        $satisfied &= $this->isSchedulePartSatisfied($def, $time->format('n'));
                        break;
                    case 4:
                        $satisfied &= $this->isSchedulePartSatisfied($def, $time->format('w'));
                        break;
                    default:
                        $satisfied &= false;
                        break;
                }
            }

            return ($satisfied);
        }

        protected function terminateScheduledTask($reason = null)
        {
            throw new TerminateScheduleException($reason);
        }

        private function isSchedulePartSatisfied($def, $value)
        {
            if ($def == '*')
            {
                return (true);
            }

            foreach (explode(',', $def) as $defItem)
            {
                if (is_numeric($defItem))
                {
                    if ($defItem == $value)
                    {
                        return (true);
                    }
                }
                elseif (strpos($defItem, '/') !== false)
                {
                    list($star, $div) = explode('/', $defItem);
                    if ($star != '*')
                    {
                        throw new InvalidConfigException("Invalid definition of schedule '{$this->schedule}'.");
                    }
                    elseif (($value % $div) === 0)
                    {
                        return (true);
                    }
                }
                elseif (strpos($defItem, '-') !== false)
                {
                    list($from, $to) = explode('-', $defItem);
                    if (($from <= $value)
                        && ($value <= $to)
                    )
                    {
                        return (true);
                    }
                }
                else
                {
                    throw new InvalidConfigException("Invalid definition of schedule '{$this->schedule}'.");
                }
            }

            return (false);
        }

        public function __toString()
        {
            return (new \ReflectionClass($this))->getShortName();
        }
    }
}
