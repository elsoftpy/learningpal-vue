<?php

namespace App\Services\Authorization;

use App\Models\Course;
use App\Models\User;
use App\Services\Academics\Settings\TeacherService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CourseVisibilityService
{
    public function visibleCourseIdsForUser(?User $user): ?array
    {
        if ($user?->can('view all students')) {
            return null;
        }

        if ($user?->profile?->teacher) {
            return (new TeacherService())->assignedCoursesArray($user->profile->teacher);
        }

        if ($user?->profile?->student) {
            return $user->profile->student->courses()->pluck('courses.id')->all();
        }

        return null;
    }

    public function visibleLanguageLevelIdsForUser(?User $user): ?array
    {
        $visibleCourseIds = $this->visibleCourseIdsForUser($user);

        if ($visibleCourseIds === null) {
            return null;
        }

        return Course::query()
            ->whereIn('id', $visibleCourseIds)
            ->whereNotNull('language_level_id')
            ->pluck('language_level_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function applyCourseScope(Builder $query, ?User $user, string $column = 'course_id'): Builder
    {
        $visibleCourseIds = $this->visibleCourseIdsForUser($user);

        if ($visibleCourseIds === null) {
            return $query;
        }

        return $query->whereIn($column, $visibleCourseIds);
    }

    public function applyCourseRelationScope(
        Builder $query,
        ?User $user,
        string $relation,
        string $column = 'id'
    ): Builder {
        $visibleCourseIds = $this->visibleCourseIdsForUser($user);

        if ($visibleCourseIds === null) {
            return $query;
        }

        return $query->whereHas($relation, function (Builder $relationQuery) use ($visibleCourseIds, $column) {
            $relationQuery->whereIn($column, $visibleCourseIds);
        });
    }

    public function applyLanguageLevelScope(Builder $query, ?User $user, string $column = 'language_level_id'): Builder
    {
        $visibleLanguageLevelIds = $this->visibleLanguageLevelIdsForUser($user);

        if ($visibleLanguageLevelIds === null) {
            return $query;
        }

        return $query->whereIn($column, $visibleLanguageLevelIds);
    }

    public function canAccessCourseId(?User $user, ?int $courseId): bool
    {
        if ($courseId === null) {
            return false;
        }

        $visibleCourseIds = $this->visibleCourseIdsForUser($user);

        return $visibleCourseIds === null || in_array($courseId, $visibleCourseIds, true);
    }

    public function canAccessLanguageLevelId(?User $user, ?int $languageLevelId): bool
    {
        if ($languageLevelId === null) {
            return false;
        }

        $visibleLanguageLevelIds = $this->visibleLanguageLevelIdsForUser($user);

        return $visibleLanguageLevelIds === null || in_array($languageLevelId, $visibleLanguageLevelIds, true);
    }

    public function authorizeCourseId(?User $user, ?int $courseId, string $message = 'This action is unauthorized.'): void
    {
        if (! $this->canAccessCourseId($user, $courseId)) {
            throw new HttpException(403, __($message));
        }
    }

    public function authorizeLanguageLevelId(?User $user, ?int $languageLevelId, string $message = 'This action is unauthorized.'): void
    {
        if (! $this->canAccessLanguageLevelId($user, $languageLevelId)) {
            throw new HttpException(403, __($message));
        }
    }

    public function authorizeAnyVisibleCourse(?User $user, Collection|array $courseIds, string $message = 'This action is unauthorized.'): void
    {
        $candidateIds = collect($courseIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $visibleCourseIds = $this->visibleCourseIdsForUser($user);

        if ($visibleCourseIds === null) {
            return;
        }

        if ($candidateIds->isEmpty() || $candidateIds->intersect($visibleCourseIds)->isEmpty()) {
            throw new HttpException(403, __($message));
        }
    }
}
