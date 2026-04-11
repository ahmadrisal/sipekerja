<?php

namespace App\Console\Commands;

use App\Models\Rating;
use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class SipekerjaMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sipekerja:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from old Sipekerja SQLite to new database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking old database connection...');
        
        try {
            DB::connection('sqlite_old')->getPdo();
        } catch (\Exception $e) {
            $this->error('Could not connect to old database: ' . $e->getMessage());
            return 1;
        }

        $this->info('Starting migration...');

        // 1. Migrate Roles
        $oldRoles = DB::connection('sqlite_old')->table('Role')->get();
        foreach ($oldRoles as $oldRole) {
            Role::firstOrCreate(['name' => $oldRole->roleName, 'guard_name' => 'web']);
        }
        $this->info('Roles migrated.');

        // 2. Migrate Users & UserRoles
        $oldUsers = DB::connection('sqlite_old')->table('User')->get();
        foreach ($oldUsers as $oldUser) {
            DB::table('users')->updateOrInsert(
                ['id' => $oldUser->id],
                [
                    'nip' => $oldUser->nip,
                    'username' => $oldUser->username,
                    'name' => $oldUser->name,
                    'email' => $oldUser->email,
                    'password' => $oldUser->password,
                    'created_at' => $oldUser->createdAt,
                    'updated_at' => $oldUser->updatedAt,
                ]
            );

            // Fetch old user roles
            $oldUserRoles = DB::connection('sqlite_old')->table('UserRole')
                ->where('userId', $oldUser->id)
                ->join('Role', 'UserRole.roleId', '=', 'Role.id')
                ->pluck('roleName')
                ->toArray();

            $user = User::find($oldUser->id);
            if ($user && !empty($oldUserRoles)) {
                $user->syncRoles($oldUserRoles);
            }
        }
        $this->info('Users and RBAC migrated.');

        // 3. Migrate Teams
        $oldTeams = DB::connection('sqlite_old')->table('Team')->get();
        foreach ($oldTeams as $oldTeam) {
            DB::table('teams')->updateOrInsert(
                ['id' => $oldTeam->id],
                [
                    'team_name' => $oldTeam->teamName,
                    'leader_id' => $oldTeam->leaderId,
                    'created_at' => $oldTeam->createdAt,
                    'updated_at' => $oldTeam->updatedAt,
                ]
            );
        }
        $this->info('Teams migrated.');

        // 4. Migrate Team Members
        $oldMembers = DB::connection('sqlite_old')->table('TeamMember')->get();
        foreach ($oldMembers as $oldMember) {
            DB::table('team_members')->updateOrInsert(
                ['team_id' => $oldMember->teamId, 'user_id' => $oldMember->userId],
                []
            );
        }
        $this->info('Team members migrated.');

        // 5. Migrate Ratings
        $oldRatings = DB::connection('sqlite_old')->table('Rating')->get();
        foreach ($oldRatings as $oldRating) {
            // Recalculate score based on new 80/10/10 rule as requested
            $finalScore = $this->calculateNewFinalScore($oldRating->score, $oldRating->volumeWork, $oldRating->qualityWork);

            DB::table('ratings')->updateOrInsert(
                ['id' => $oldRating->id],
                [
                    'evaluator_id' => $oldRating->evaluatorId,
                    'target_user_id' => $oldRating->targetUserId,
                    'team_id' => $oldRating->teamId,
                    'score' => $oldRating->score,
                    'notes' => $oldRating->notes,
                    'volume_work' => $oldRating->volumeWork,
                    'quality_work' => $oldRating->qualityWork,
                    'final_score' => $finalScore, // Use recalculated score
                    'period_month' => $oldRating->periodMonth,
                    'period_year' => $oldRating->periodYear,
                    'created_at' => $oldRating->createdAt,
                    'updated_at' => $oldRating->createdAt,
                ]
            );
        }
        $this->info('Ratings migrated with new 80/10/10 weighting.');

        $this->info('All data migrated successfully!');
        return 0;
    }

    private function calculateNewFinalScore($score, $volumeWork, $qualityWork)
    {
        $volScore = 80;
        if ($volumeWork === 'Ringan') $volScore = 60;
        else if ($volumeWork === 'Berat') $volScore = 100;

        $qualScore = 75;
        if ($qualityWork === 'Kurang') $qualScore = 50;
        else if ($qualityWork === 'Baik') $qualScore = 90;
        else if ($qualityWork === 'Sangat Baik') $qualScore = 100;

        $final = ($score * 0.8) + ($volScore * 0.1) + ($qualScore * 0.1);
        return round($final, 2);
    }
}
