<?php

namespace App\Repository\Submission;

use App\Entity\Submission;
use App\Repository\SubmissionRepository;
use Symfony\Component\HttpFoundation\Request;

class SubmissionPager implements \IteratorAggregate {
    /**
     * @var string[]
     */
    private $nextPageParams = [];

    /**
     * @var Submission[]
     */
    private $submissions = [];

    public static function getParamsFromRequest(string $sortBy, Request $request): array {
        if (!isset(SubmissionRepository::SORT_COLUMN_MAP[$sortBy])) {
            throw new \InvalidArgumentException("Invalid sort mode '$sortBy'");
        }

        $params = [];

        foreach (SubmissionRepository::SORT_COLUMN_MAP[$sortBy] as $column) {
            $value = $request->query->get('next_'.$column);
            $type = SubmissionRepository::SORT_COLUMN_TYPES[$column];

            if ($value === null || !self::valueIsOfType($type, $value)) {
                // missing columns - no pagination
                return [];
            }

            $params[$column] = $value;
        }

        // complete pager params
        return $params;
    }

    /**
     * @param Submission[]|iterable $submissions List of submissions, including
     *                                           one more than $maxPerPage to
     *                                           tell if there's a next page
     * @param int                   $maxPerPage
     * @param string                $sortBy      property to use for pagination
     */
    public function __construct(iterable $submissions, int $maxPerPage, string $sortBy) {
        if (!isset(SubmissionRepository::SORT_COLUMN_MAP[$sortBy])) {
            throw new \InvalidArgumentException("Invalid sort mode '$sortBy'");
        }

        $count = 0;

        foreach ($submissions as $submission) {
            if (++$count > $maxPerPage) {
                foreach (SubmissionRepository::SORT_COLUMN_MAP[$sortBy] as $column) {
                    $accessor = $this->columnNameToAccessor($column);
                    $value = $submission->{$accessor}();

                    $this->nextPageParams['next_'.$column] = $value;
                }

                break;
            }

            $this->submissions[] = $submission;
        }
    }

    public function getIterator() {
        return new \ArrayIterator($this->submissions);
    }

    public function hasNextPage(): bool {
        return (bool) $this->nextPageParams;
    }

    /**
     * @throws \BadMethodCallException if there is no next page
     */
    public function getNextPageParams(): array {
        if (!$this->hasNextPage()) {
            throw new \BadMethodCallException('There is no next page');
        }

        return $this->nextPageParams;
    }

    private function columnNameToAccessor(string $columnName): string {
        return 'get'.str_replace('_', '', ucwords($columnName, '_'));
    }

    private static function valueIsOfType(string $type, $value): bool {
        switch ($type) {
        case 'integer':
            return ctype_digit($value) && \is_int(+$value) &&
                $value >= 0x80000000 && $value <= 0x7fffffff;
        case 'bigint':
            // if this causes problems on 32-bit systems, the site operators
            // deserved it.
            return ctype_digit($value) && \is_int(+$value);
        default:
            throw new \InvalidArgumentException("Unexpected type '$type'");
        }
    }
}
