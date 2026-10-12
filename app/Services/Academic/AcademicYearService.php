<?php

namespace App\Services\Academic;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcademicYearService
{
    public function create(array $data): AcademicYear
    {
        return DB::transaction(function () use ($data) {

            $this->ensureValidYearDates($data);

            $this->ensureNoOverlappingAcademicYear(
                $data['start_date'],
                $data['end_date']
            );

            $this->ensureValidTerms(
                $data['start_date'],
                $data['end_date'],
                $data['term_1'],
                $data['term_2']
            );

            $academicYear = AcademicYear::create([
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => false,
            ]);

            $academicYear->terms()->createMany([
                [
                    'term_number' => 1,
                    'name' => $data['term_1']['name'],
                    'start_date' => $data['term_1']['start_date'],
                    'end_date' => $data['term_1']['end_date'],
                ],
                [
                    'term_number' => 2,
                    'name' => $data['term_2']['name'],
                    'start_date' => $data['term_2']['start_date'],
                    'end_date' => $data['term_2']['end_date'],
                ],
            ]);

            return $academicYear->load('terms');
        });
    }

    public function update(int $id, array $data): AcademicYear
    {
        return DB::transaction(function () use ($id, $data) {

            $academicYear = AcademicYear::with('terms')->findOrFail($id);

            $hasRelatedData =
                $academicYear->classSchedules()->exists() ||
                $academicYear->examSchedules()->exists();



            if ($hasRelatedData) {

                $forbiddenFields = [
                    'start_date',
                    'end_date',
                    'term_1',
                    'term_2',
                ];

                foreach ($forbiddenFields as $field) {
                    if (array_key_exists($field, $data)) {
                        throw ValidationException::withMessages([
                            'academic_year' =>
                            'لا يمكن تعديل تواريخ السنة الدراسية أو فصولها بعد إنشاء جداول مرتبطة بها.',
                        ]);
                    }
                }

                if (array_key_exists('name', $data)) {
                    $academicYear->update([
                        'name' => $data['name'],
                    ]);
                }

                return $academicYear->fresh('terms');
            }


            $yearData = [
                'name' => $data['name'] ?? $academicYear->name,
                'start_date' => $data['start_date']
                    ?? $academicYear->start_date->format('Y-m-d'),
                'end_date' => $data['end_date']
                    ?? $academicYear->end_date->format('Y-m-d'),
            ];

            $term1 = $academicYear->terms
                ->firstWhere('term_number', 1);

            $term2 = $academicYear->terms
                ->firstWhere('term_number', 2);

            if (! $term1 || ! $term2) {
                throw ValidationException::withMessages([
                    'academic_year' =>
                    'السنة الدراسية يجب أن تحتوي على فصلين دراسيين.',
                ]);
            }

            $term1Data = [
                'name' => $data['term_1']['name']
                    ?? $term1->name,

                'start_date' => $data['term_1']['start_date']
                    ?? $term1->start_date->format('Y-m-d'),

                'end_date' => $data['term_1']['end_date']
                    ?? $term1->end_date->format('Y-m-d'),
            ];

            $term2Data = [
                'name' => $data['term_2']['name']
                    ?? $term2->name,

                'start_date' => $data['term_2']['start_date']
                    ?? $term2->start_date->format('Y-m-d'),

                'end_date' => $data['term_2']['end_date']
                    ?? $term2->end_date->format('Y-m-d'),
            ];


            $this->ensureValidYearDates($yearData);

            $this->ensureNoOverlappingAcademicYear(
                $yearData['start_date'],
                $yearData['end_date'],
                $id
            );

            $this->ensureValidTerms(
                $yearData['start_date'],
                $yearData['end_date'],
                $term1Data,
                $term2Data
            );

            $academicYear->update($yearData);

            $term1->update($term1Data);

            $term2->update($term2Data);

            return $academicYear->fresh('terms');
        });
    }
    public function activate(int $id): AcademicYear
    {
        return DB::transaction(function () use ($id) {

            $academicYear = AcademicYear::with('terms')->findOrFail($id);

            $this->ensureYearHasValidTerms($academicYear);

            AcademicYear::query()
                ->where('id', '!=', $academicYear->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                ]);

            $academicYear->update([
                'is_active' => true,
            ]);

            return $academicYear->fresh('terms');
        });
    }

    public function deactivate(int $id): AcademicYear
    {
        return DB::transaction(function () use ($id) {

            $academicYear = AcademicYear::findOrFail($id);

            $academicYear->update([
                'is_active' => false,
            ]);

            return $academicYear->fresh('terms');
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {

            $academicYear = AcademicYear::findOrFail($id);

            if ($academicYear->is_active) {
                throw ValidationException::withMessages([
                    'academic_year' =>
                    'لا يمكن حذف السنة الدراسية النشطة.',
                ]);
            }

            if ($academicYear->classSchedules()->exists()) {
                throw ValidationException::withMessages([
                    'academic_year' =>
                    'لا يمكن حذف السنة الدراسية لوجود جداول دراسية مرتبطة بها.',
                ]);
            }

            if ($academicYear->examSchedules()->exists()) {
                throw ValidationException::withMessages([
                    'academic_year' =>
                    'لا يمكن حذف السنة الدراسية لوجود جداول امتحانات مرتبطة بها.',
                ]);
            }

            $academicYear->delete();
        });
    }

    public function getActive(): AcademicYear
    {
        $academicYear = AcademicYear::query()
            ->with('terms')
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            throw ValidationException::withMessages([
                'academic_year_id' =>
                'لا توجد سنة دراسية نشطة حالياً في النظام.',
            ]);
        }

        return $academicYear;
    }

    public function getCurrentTerm(): ?AcademicTerm
    {
        $academicYear = $this->getActive();

        return $academicYear->terms()
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->first();
    }

    private function ensureValidYearDates(array $data): void
    {
        if ($data['start_date'] >= $data['end_date']) {
            throw ValidationException::withMessages([
                'end_date' =>
                'يجب أن يكون تاريخ نهاية السنة الدراسية بعد تاريخ بدايتها.',
            ]);
        }
    }

    private function ensureNoOverlappingAcademicYear(
        string $startDate,
        string $endDate,
        ?int $ignoreId = null
    ): void {
        $exists = AcademicYear::query()
            ->where(function ($query) use ($startDate, $endDate) {
                $query
                    ->whereDate('start_date', '<=', $endDate)
                    ->whereDate('end_date', '>=', $startDate);
            })
            ->when(
                $ignoreId,
                fn($query) => $query->where('id', '!=', $ignoreId)
            )
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'start_date' =>
                'توجد سنة دراسية أخرى تتداخل مع الفترة المحددة.',
            ]);
        }
    }

    private function ensureValidTerms(
        string $yearStart,
        string $yearEnd,
        array $term1,
        array $term2
    ): void {
        if ($term1['start_date'] < $yearStart) {
            throw ValidationException::withMessages([
                'term_1.start_date' =>
                'يجب أن يبدأ الفصل الأول ضمن فترة السنة الدراسية.',
            ]);
        }

        if ($term1['end_date'] > $yearEnd) {
            throw ValidationException::withMessages([
                'term_1.end_date' =>
                'يجب أن ينتهي الفصل الأول ضمن فترة السنة الدراسية.',
            ]);
        }

        if ($term2['start_date'] < $yearStart) {
            throw ValidationException::withMessages([
                'term_2.start_date' =>
                'يجب أن يبدأ الفصل الثاني ضمن فترة السنة الدراسية.',
            ]);
        }

        if ($term2['end_date'] > $yearEnd) {
            throw ValidationException::withMessages([
                'term_2.end_date' =>
                'يجب أن ينتهي الفصل الثاني ضمن فترة السنة الدراسية.',
            ]);
        }

        if ($term1['start_date'] >= $term1['end_date']) {
            throw ValidationException::withMessages([
                'term_1.end_date' =>
                'يجب أن يكون تاريخ نهاية الفصل الأول بعد تاريخ بدايته.',
            ]);
        }

        if ($term2['start_date'] >= $term2['end_date']) {
            throw ValidationException::withMessages([
                'term_2.end_date' =>
                'يجب أن يكون تاريخ نهاية الفصل الثاني بعد تاريخ بدايته.',
            ]);
        }

        if ($term1['end_date'] >= $term2['start_date']) {
            throw ValidationException::withMessages([
                'term_2.start_date' =>
                'لا يجوز أن يتداخل الفصل الثاني مع الفصل الأول.',
            ]);
        }
    }

    private function ensureYearHasValidTerms(
        AcademicYear $academicYear
    ): void {
        if ($academicYear->terms->count() !== 2) {
            throw ValidationException::withMessages([
                'academic_year' =>
                'لا يمكن تفعيل السنة الدراسية إلا إذا كانت تحتوي على فصلين دراسيين.',
            ]);
        }

        $term1 = $academicYear->terms
            ->firstWhere('term_number', 1);

        $term2 = $academicYear->terms
            ->firstWhere('term_number', 2);

        if (! $term1 || ! $term2) {
            throw ValidationException::withMessages([
                'academic_year' =>
                'يجب أن تحتوي السنة الدراسية على الفصل الأول والفصل الثاني.',
            ]);
        }

        $this->ensureValidTerms(
            $academicYear->start_date->format('Y-m-d'),
            $academicYear->end_date->format('Y-m-d'),
            [
                'start_date' => $term1->start_date->format('Y-m-d'),
                'end_date' => $term1->end_date->format('Y-m-d'),
            ],
            [
                'start_date' => $term2->start_date->format('Y-m-d'),
                'end_date' => $term2->end_date->format('Y-m-d'),
            ]
        );
    }
}
