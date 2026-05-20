<?php

namespace App\Support;

final class OperationalMetrics
{
    /**
     * @return array<int, int|float>
     */
    public static function emptySparkline(): array
    {
        return [];
    }

    /**
     * @param  list<array{label: string, value: float|int}>  $rows
     * @return array<int, float>
     */
    public static function sparkFromSeries(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        return array_map(fn (array $row) => (float) $row['value'], $rows);
    }

    /**
     * Drop leading zero-only buckets so flat empty history does not render as a fake chart.
     *
     * @param  list<int|float>  $series
     * @return list<int|float>
     */
    public static function normalizeSparkline(array $series): array
    {
        if ($series === []) {
            return [];
        }

        $firstMeaningful = null;
        foreach ($series as $index => $value) {
            if ((float) $value > 0) {
                $firstMeaningful = $index;
                break;
            }
        }

        if ($firstMeaningful === null) {
            return [];
        }

        return array_values(array_slice($series, $firstMeaningful));
    }
}
