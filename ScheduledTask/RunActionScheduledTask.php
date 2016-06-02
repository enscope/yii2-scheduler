<?php
/**
 * @package   yii2-scheduler
 * @author    Miro Hudak <mhudak@dev.enscope.com>
 * @copyright Copyright &copy; Miro Hudak, enscope.com, 2016
 * @version   1.0
 */

namespace enscope\Scheduler\ScheduledTask
{
    use enscope\Scheduler\BaseScheduledTask;

    class RunActionScheduledTask
        extends BaseScheduledTask
    {
        /** @var string|null */
        public $action = null;
        /** @var array */
        public $params = [];

        /**
         * Schedule task definition, which is invoked only when the internal
         * schedule is satisfied or the execution is forced.
         */
        public function scheduledTask()
        {
            \Yii::$app->runAction($this->action, $this->params);
        }

        public function __toString()
        {
            return sprintf('%s: action="%s"', parent::__toString(), $this->action);
        }
    }
}
