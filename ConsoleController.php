<?php
/**
 * @package   yii2-scheduler
 * @author    Miro Hudak <mhudak@dev.enscope.com>
 * @copyright Copyright &copy; Miro Hudak, enscope.com, 2016
 * @version   1.0
 */

namespace enscope\Scheduler
{
    use yii\base\Exception;
    use yii\base\InvalidConfigException;
    use yii\console\Controller;

    class ConsoleController
        extends Controller
    {
        public $defaultAction = 'run-tasks';

        /**
         * @var bool Suppress stdout() messages
         */
        public $quiet = false;

        /**
         * @var bool Allow verbose stdout(string, true) messages
         */
        public $verbose = false;

        /**
         * Definition of scheduled tasks, which must be an array
         * or configurations or instances of ScheduledTaskInterface.
         *
         * @var ScheduledTaskInterface[]
         */
        public $tasks = [];

        /**
         * @var bool If set, all tasks are forced to execute regardless of schedule
         */
        public $force = false;

        /**
         * Returns the names of valid options for the action (id)
         * An option requires the existence of a public member variable whose
         * name is the option name.
         * Child classes may override this method to specify possible options.
         *
         * Note that the values setting via options are not available
         * until [[beforeAction()]] is being called.
         *
         * @param string $actionID the action id of the current request
         *
         * @return array the names of the options valid for the action
         */
        public function options($actionID)
        {
            switch ($actionID)
            {
                case 'run-tasks':
                    return ['force', 'verbose', 'quiet'];
            }

            return [];
        }

        /**
         * Initializes the object.
         * This method is invoked at the end of the constructor after the object is initialized with the
         * given configuration.
         */
        public function init()
        {
            parent::init();
            $this->initializeTasks();
        }

        /**
         * Executes scheduled tasks which are scheduled for current date and time.
         */
        public function actionRunTasks()
        {
            // this time is passed to scheduled tasks
            // and all tasks should base schedule satisfaction
            // on this time
            $taskTime = date_create();
            $this->stdout("Executing scheduled tasks...\n", true, true);
            foreach ($this->tasks as $scheduledTask)
            {
                try
                {
                    if ($scheduledTask->execute($taskTime, $this->force))
                    {
                        $this->stdout("{$scheduledTask}: Task executed.\n", true, true);
                    }
                }
                catch (TerminateScheduleException $tse)
                {
                    // this is the only schedule exception, that will terminate the scheduler
                    $this->stderr("SCHEDULER TERMINATED: {$scheduledTask}: {$tse->getMessage()}\n");
                    break;
                }
                catch (Exception $ex)
                {
                    $this->stderr("ERROR: {$scheduledTask}: {$ex->getMessage()}\n");
                }
            }
            $this->stdout("Scheduled tasks completed.\n", true, true);
        }

        protected function initializeTasks()
        {
            foreach ($this->tasks as $index => $config)
            {
                if ($config instanceof ScheduledTaskInterface)
                {
                    // skip already initialized tasks
                    continue;
                }

                if (!(($this->tasks[$index] = \Yii::createObject($config)) instanceof ScheduledTaskInterface))
                {
                    throw new InvalidConfigException('Scheduled task must implement ScheduledTaskInterface.');
                }
            }
        }

        /**
         * Overridden method honors $quiet option to disable
         * all non-error output.
         *
         * @param string      $string
         * @param bool        $verbose   If TRUE, message is considered as verbose
         * @param string|bool $timestamp If set, messages are timestamped by specified format or default format
         *
         * @return bool|int|string
         */
        public function stdout($string, $verbose = false, $timestamp = false)
        {
            if (!$verbose
                || $this->verbose
            )
            {
                if ($timestamp)
                {
                    $timestamp = is_string($timestamp) ? $timestamp : 'd-m-Y H:i:s';
                    $currentTime = date_create();
                    $string = "[{$currentTime->format($timestamp)}] {$string}";
                }

                return !$this->quiet ? parent::stdout($string) : '';
            }

            return ('');
        }
    }
}
