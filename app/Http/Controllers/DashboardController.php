<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\ExtracurricularCategory;
use App\Models\ExtracurricularMembership;
use App\Models\ExtracurricularSchedule;
use App\Models\Presence;
use App\Models\PresenceDetail;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        return match ($user->role) {
            'kesiswaan' => $this->kesiswaan(),
            'pembina' => $this->pembina(),
            'admin' => $this->admin(),
            default => abort(403, 'Unauthorized action.'),
        };
    }

    protected function kesiswaan()
    {
        $activeAY = AcademicYear::getActiveYear();

        // Stat Cards
        $totalStudents = Student::active()->count();
        $totalExtracurriculars = Extracurricular::where('is_active', true)->count();
        $totalAdvisor = User::where('role', 'pembina')->where('is_active', true)->count();

        // Donut Chart: Pemerataan per Kategori
        $categoryDist = ExtracurricularCategory::withCount([
            'extracurriculars as member_count' => function ($q) use ($activeAY) {
                $q->whereHas('memberships', function ($m) use ($activeAY) {
                    $m->where('status', MembershipStatus::AKTIF->value)->when($activeAY, fn($m) => $m->where('academic_year_id', $activeAY->id));
                });
            },
        ])
            ->get()
            ->map(
                fn($cat) => [
                    'name' => $cat->name,
                    'count' => $cat->member_count,
                ],
            );

        // Bar Chart: Top 10 Ekskul Terfavorit
        $topExtracurriculars = Extracurricular::withCount([
            'memberships as active_member_count' => function ($q) use ($activeAY) {
                $q->where('status', MembershipStatus::AKTIF->value)->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id));
            },
        ])
            ->where('is_active', true)
            ->orderByDesc('active_member_count')
            ->limit(10)
            ->get()
            ->map(
                fn($e) => [
                    'name' => $e->name,
                    'count' => $e->active_member_count,
                ],
            );

        return view('role.kesiswaan.contents.dashboard', compact('activeAY', 'totalStudents', 'totalExtracurriculars', 'totalAdvisor', 'categoryDist', 'topExtracurriculars'));
    }

    protected function pembina()
    {
        $user     = Auth::user();
        $activeAY = AcademicYear::getActiveYear();

        $myExtracurriculars    = $user->extracurricularList()->where('is_active', true)->get();
        $extracurricularIds    = $myExtracurriculars->pluck('id');

        $totalMembers = \App\Models\ExtracurricularMembership::whereIn('extracurricular_id', $extracurricularIds)
            ->where('status', MembershipStatus::AKTIF->value)
            ->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))
            ->count();

        $totalPresences = \App\Models\Presence::whereIn('extracurricular_id', $extracurricularIds)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();

        $memberPerExtracurricular = $myExtracurriculars->map(fn($e) => [
            'name'  => $e->name,
            'count' => $e->memberships()
                ->where('status', MembershipStatus::AKTIF->value)
                ->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))
                ->count(),
        ]);

        return view('role.pembina.contents.dashboard', compact(
            'activeAY',
            'myExtracurriculars',
            'totalMembers',
            'totalPresences',
            'memberPerExtracurricular',
        ));
    }

    protected function admin()
    {
        $activeAY = AcademicYear::getActiveYear();
    
        $totalExtracurriculars = Extracurricular::where('is_active', true)->count();
    
        $totalMembers = ExtracurricularMembership::where('status', MembershipStatus::AKTIF->value)
            ->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))
            ->count();
    
        $todayPresences = Presence::whereDate('date', today())->count();
    
        $monthPresences = Presence::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();
    
        $todayDayName = ucfirst(now()->locale('id')->dayName);
    
        $todaySchedules = ExtracurricularSchedule::with([
                'extracurricular.category',
                'extracurricular.users.user',
            ])
            ->whereHas('extracurricular', fn($q) => $q->where('is_active', true))
            ->where('day', $todayDayName)
            ->get()
            ->unique('extracurricular_id')
            ->map(function ($schedule) use ($activeAY) {
                $presence = Presence::where('extracurricular_id', $schedule->extracurricular_id)
                    ->whereDate('date', today())
                    ->first();
    
                $advisorNames = $schedule->extracurricular
                    ->users
                    ->map(fn($eu) => $eu->user?->name)
                    ->filter()
                    ->join(', ') ?: '-';
    
                return [
                    'extracurricular_id' => $schedule->extracurricular_id,
                    'name'               => $schedule->extracurricular->name,
                    'category'           => $schedule->extracurricular->category?->name ?? '-',
                    'advisors'           => $advisorNames,
                    'has_presence'       => !is_null($presence),
                    'presence_id'        => $presence?->id,
                    'member_count'       => $schedule->extracurricular
                        ->memberships()
                        ->where('status', MembershipStatus::AKTIF->value)
                        ->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))
                        ->count(),
                ];
            })
            ->values();

        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'date'  => $date->format('d M'),
                'count' => Presence::whereDate('date', $date->toDateString())->count(),
            ];
        });
    
        return view('role.admin.contents.dashboard', compact(
            'activeAY',
            'totalExtracurriculars',
            'totalMembers',
            'todayPresences',
            'monthPresences',
            'todaySchedules',
            'last7Days',
        ));
    }
}
