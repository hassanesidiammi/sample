<?php


class DateUtilities
{
    public function notEmptyDays($period='') {
        $date = @DateTime::createFromFormat('Ymd', $period.'01');

        $lastDay = $date->format('t');
        $days    = [];
        foreach (range(1, $lastDay) as $day) {
            $date = @DateTime::createFromFormat('Ymd', $period.str_pad($day, 2, '0', STR_PAD_LEFT));
            if (!in_array($date->format('w'), [0, 6])){
                $days[] = $date->format('Ymd');
            }
        }

        return $days;
    }

    public function countNotEmptyDays($period)
    {
        return count($this->notEmptyDays($period));
    }

}