<?php
/**
 * @package   yii2-scheduler
 * @author    Miro Hudak <mhudak@dev.enscope.com>
 * @copyright Copyright &copy; Miro Hudak, enscope.com, 2016
 * @version   1.0
 */

namespace enscope\Scheduler
{
    use Exception;

    class TerminateScheduleException
        extends \Exception
    {
        public function __construct($message = null, Exception $previous = null)
        {
            $message = $message ?: 'No reason.';
            parent::__construct($message, $previous);
        }
    }
}
