<?php

namespace MStroink\Analytics;

use Cake\Chronos\Chronos;
use DateTimeInterface;
use MStroink\Analytics\Exceptions\InvalidPeriod;

class Period
{
    public DateTimeInterface $startDate;

    public DateTimeInterface $endDate;

    public static function create(DateTimeInterface $startDate, DateTimeInterface $endDate): self
    {
        return new static($startDate, $endDate);
    }

    public static function days(int $numberOfDays): static
    {
        $endDate = Chronos::today();

        $startDate = Chronos::today()->subDays($numberOfDays)->startOfDay();

        return new static($startDate, $endDate);
    }

    public static function months(int $numberOfMonths): static
    {
        $endDate = Chronos::today();

        $startDate = Chronos::today()->subMonths($numberOfMonths)->startOfDay();

        return new static($startDate, $endDate);
    }

    public static function years(int $numberOfYears): static
    {
        $endDate = Chronos::today();

        $startDate = Chronos::today()->subYears($numberOfYears)->startOfDay();

        return new static($startDate, $endDate);
    }

    public function __construct(DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        if ($startDate > $endDate) {
            throw InvalidPeriod::startDateCannotBeAfterEndDate($startDate, $endDate);
        }

        $this->startDate = $startDate;

        $this->endDate = $endDate;
    }
}
