<?php
/**
 * @package   yii2-scheduler
 * @author    Miro Hudak <mhudak@dev.enscope.com>
 * @copyright Copyright &copy; Miro Hudak, enscope.com, 2016
 * @version   1.0
 */

namespace enscope\Scheduler
{
    interface ScheduledTaskInterface
    {
        /**
         * Executes scheduled task, but task must determine, if it will
         * perform the job or not, based on internal scheduling mechanism.
         *
         * @param \DateTime        $time      Time of the scheduler invocation, that may be different
         *                                    from the current time, but all schedules must relate
         *                                    to this time when checking if schedule is satisfied
         * @param bool             $force     If true, task should run regardless of schedule
         *
         * @return bool TRUE, if the task was actually executed
         */
        public function execute(\DateTime $time, $force = false);
    }
}
