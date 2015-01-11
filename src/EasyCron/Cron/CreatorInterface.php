<?php
/**
 * Created by PhpStorm.
 * User: andres
 * Date: 1/10/2015
 * Time: 6:16 PM
 */
namespace EasyCron\Cron;

interface CreatorInterface
{
    /**
     * Compile given values into a cron string
     *
     * @return string
     */
    public function getLine();

    /**
     * Executing CronJob in regular arbitrary intervals
     *
     * @param int $amount
     * @param string $unit
     */
    public function every($amount, $unit);

    /**
     * Specifying the weekdays (monday-friday) that the cronjob will be executed.
     * Handles string mon-fri or 0-6
     *
     * @param array $weekdays
     */
    public function on(array $weekdays);

    /**
     * Specify the days of the month the cronjob will be executed.
     *
     * @param array $days
     */
    public function onThe(array $days);

    /**
     * Specify times of day that cronjob can be executed. Handles 00-24.
     *
     * @param array $times
     */
    public function at(array $times);

    /**
     * Specifying the months a cronjob is executed in. Handles jan-dec or 1-12
     *
     * @param array $months
     */
    public function in(array $months);

    /**
     * Specify the command to be executed.
     *
     * @param string $command
     */
    public function execute($command);
}