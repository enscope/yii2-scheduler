# yii2-scheduler
Yet another Yii2 Cron-like extension, which was created because I felt, I needed something else, that was available at the time.
In case you like it, feel free to use it in your projects. Currently, it is running in production successfully for multiple months.

## Basic Usage

Add `ConsoleController` to `controllerMap` in your `config.php`:

    'controllerMap' => [
        ...
        'scheduler' => [
            'class' => \enscope\Scheduler\ConsoleController::className(),
            'tasks' => require dirname(__FILE__) . '/schedule.php',
        ],
        ...
    ],

Tasks can be loaded from external file or in-lined in `config.php`.
Externalizing the tasks is recommended, then `schedule.php` can be like:

    return [
        [
            'schedule' => '0 2 * * 1-5',
            'class' => 'Main\Notifications\ScheduledTask\NotificationsDigestTask',
        ],
        [
            'schedule' => '* * * * *',
            'class' => 'enscope\Scheduler\ScheduledTask\RunActionScheduledTask',
            'action' => 'mailer/send',
            'params' => [
                'quiet' => true,
            ],
        ],
    ];

This scheduler configuration contains two tasks, one that is run every work-day at 2am and one that is run each minute.
As you can see, you can either reference class, sub-classing `BaseScheduledTask` (in principe, the task must implement `ScheduledTaskInteface`,
allowing customization of the schedule configuration, but it is recommended to extend `BaseScheduledTask` and use
default CRON-like configuration) or call any available action from another controller.

Tasks are executed in order of definition, as are present in `tasks` array.

**The scheduler itself should be run using CRON or other task scheduler and to allow for the most granularity, it should be run each minute.**

# Scheduled Task Configuration

Configuration of scheduled tasks closely follows CRON-like principles.
The schedule configuration string is 5 part definition in following order:

    * * * * *
    ^ ^ ^ ^ ^ day of week (0-6)
    | | | | month (1-12)
    | | | day of month (1-31)
    | | hour (0-23)
    | minute (0-59)

You can define intervals using hyphen, e.g. `0 2 * * 1-5` to run the task 2AM on weekdays,
you can define cycles using slash, e.g. `*/5 * * * *` to run the task "every five minutes",
you can specify multiple values separated by comma, e.g. `* 6,12,18 * * *` to run the task every 6 hours except midnight.

# Implementing Custom Tasks

All scheduled tasks must implement `ScheduledTaskInterface`, but this interface does not provide actual evaluation of correct time to run the task
and will run the task every time, the scheduler is run. 

This basic interface is simple:

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

The comment says it all. To implement a scheduled task, you need to implement `execute(DateTime, boolean = false)` method.
The task itself has no connection to task scheduler, it should have the configuration of the schedule included in itself.
Simplicity of the interface is by design, to allow for more advanced scheduled task configuration, if ever needed.
For most use-cases, it is sufficient to extend abstract `BaseScheduledTask`, which contains single abstract method
`scheduledTask()`, that needs to be implemented. The class implements `execute(..)` method and evaluates satisfaction
of the `schedule` automatically, running the task only when the conditions are satisfied (and the task is not forced).

# Exceptions and Error Handling

Task can throw any Exception and the Scheduler will handle it, reporting error on error output along with scheduled task name
and exception message. After reporting the error, Scheduler will continue running other tasks in order as defined. If it is
necessary to terminate `Scheduler` execution immediatelly, it is possible to use `TerminateScheduleException`, which will
force `Scheduler` to terminate instantly with appropriate error message. Of course, another Scheduler execution will
try to run tasks again.
