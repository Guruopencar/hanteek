<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Api\ApiController;
use App\Models\Vacancy;
use App\Models\VacancyApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends ApiController
{
    public function index(Vacancy $vacancy): JsonResponse
    {
        if ($vacancy->owner_id !== auth()->id()) {
            return $this->forbidden();
        }

        $applications = $vacancy->applications()
            ->with(['applicant.profile', 'applicant.developerResume'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $applications->map(fn($a) => [
                'id'             => $a->id,
                'status'         => $a->status,
                'cover_letter'   => $a->cover_letter,
                'proposed_rate'  => $a->proposed_rate,
                'created_at'     => $a->created_at->toISOString(),
                'applicant' => [
                    'id'             => $a->applicant->id,
                    'full_name'      => $a->applicant->profile?->full_name,
                    'avatar'         => $a->applicant->profile?->avatar,
                    'average_rating' => $a->applicant->profile?->average_rating,
                    'reviews_count'  => $a->applicant->profile?->reviews_count,
                    'position'       => $a->applicant->developerResume?->position,
                    'experience'     => $a->applicant->developerResume?->experience_years,
                    'hourly_rate'    => $a->applicant->developerResume?->hourly_rate,
                ],
            ]),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page'    => $applications->lastPage(),
                'total'        => $applications->total(),
            ],
        ]);
    }

    public function apply(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        if ($vacancy->status !== 'published') {
            return $this->error('Вакансія недоступна для заявок');
        }

        $exists = VacancyApplication::where('vacancy_id', $vacancy->id)
            ->where('applicant_id', $user->id)
            ->exists();

        if ($exists) {
            return $this->error('Ви вже подали заявку на цю вакансію');
        }

        $data = $request->validate([
            'cover_letter'  => 'nullable|string|max:3000',
            'proposed_rate' => 'nullable|numeric|min:0',
        ]);

        $application = VacancyApplication::create([
            ...$data,
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $user->id,
            'status'       => 'pending',
        ]);

        // Збільшити лічильник заявок
        $vacancy->increment('applications_count');

        return $this->created($application, 'Заявку надіслано');
    }

    public function respond(Request $request, Vacancy $vacancy, VacancyApplication $application): JsonResponse
    {
        if ($vacancy->owner_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'status' => 'required|in:accepted,rejected,viewed',
        ]);

        $application->update([
            'status'       => $data['status'],
            'responded_at' => now(),
        ]);

        return $this->success($application->fresh(), 'Статус оновлено');
    }

    public function myApplications(Request $request): JsonResponse
    {
        $applications = VacancyApplication::with(['vacancy.project', 'vacancy.owner.profile'])
            ->where('applicant_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $applications->map(fn($a) => [
                'id'            => $a->id,
                'status'        => $a->status,
                'cover_letter'  => $a->cover_letter,
                'proposed_rate' => $a->proposed_rate,
                'created_at'    => $a->created_at->toISOString(),
                'vacancy' => [
                    'id'            => $a->vacancy->id,
                    'title'         => $a->vacancy->title,
                    'contract_type' => $a->vacancy->contract_type,
                    'budget'        => $a->vacancy->budget,
                    'hourly_rate'   => $a->vacancy->hourly_rate,
                    'project_title' => $a->vacancy->project?->title,
                    'owner_name'    => $a->vacancy->owner->profile?->full_name,
                    'owner_avatar'  => $a->vacancy->owner->profile?->avatar,
                ],
            ]),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page'    => $applications->lastPage(),
                'total'        => $applications->total(),
            ],
        ]);
    }

    public function withdraw(Request $request, VacancyApplication $application): JsonResponse
    {
        if ($application->applicant_id !== $request->user()->id) {
            return $this->forbidden();
        }

        if ($application->status !== 'pending') {
            return $this->error('Можна відкликати тільки очікуючу заявку');
        }

        $application->update(['status' => 'withdrawn']);
        $application->vacancy->decrement('applications_count');

        return $this->success(null, 'Заявку відкликано');
    }
}
