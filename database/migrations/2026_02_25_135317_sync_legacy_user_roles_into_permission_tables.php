<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        $tableNames = config('permission.table_names');

        if (! is_array($tableNames)) {
            return;
        }

        $rolesTable = $tableNames['roles'] ?? null;
        $modelHasRolesTable = $tableNames['model_has_roles'] ?? null;

        if (
            ! is_string($rolesTable)
            || ! is_string($modelHasRolesTable)
            || ! Schema::hasTable($rolesTable)
            || ! Schema::hasTable($modelHasRolesTable)
        ) {
            return;
        }

        $users = DB::table('users')
            ->select(['id', 'role'])
            ->whereNotNull('role')
            ->where('role', '<>', '')
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        $guardName = config('auth.defaults.guard', 'web');
        $resolvedGuardName = is_string($guardName) ? $guardName : 'web';
        $rolePivotKey = config('permission.column_names.role_pivot_key') ?: 'role_id';
        $modelMorphKey = config('permission.column_names.model_morph_key') ?: 'model_id';
        $teamForeignKey = config('permission.column_names.team_foreign_key');
        $usesTeams = (bool) config('permission.teams', false);

        $roleIdsByName = [];

        foreach ($users->pluck('role')->unique() as $legacyRoleName) {
            if (! is_string($legacyRoleName) || $legacyRoleName === '') {
                continue;
            }

            $existingRole = DB::table($rolesTable)
                ->select('id')
                ->where('name', $legacyRoleName)
                ->where('guard_name', $resolvedGuardName)
                ->first();

            if ($existingRole !== null) {
                $roleIdsByName[$legacyRoleName] = $existingRole->id;

                continue;
            }

            $roleIdsByName[$legacyRoleName] = DB::table($rolesTable)->insertGetId([
                'name' => $legacyRoleName,
                'guard_name' => $resolvedGuardName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($users as $user) {
            if (! is_string($user->role) || ! array_key_exists($user->role, $roleIdsByName)) {
                continue;
            }

            $attributes = [
                $rolePivotKey => $roleIdsByName[$user->role],
                $modelMorphKey => $user->id,
                'model_type' => User::class,
            ];

            if ($usesTeams && is_string($teamForeignKey)) {
                $attributes[$teamForeignKey] = null;
            }

            $exists = DB::table($modelHasRolesTable)
                ->where($rolePivotKey, $attributes[$rolePivotKey])
                ->where($modelMorphKey, $attributes[$modelMorphKey])
                ->where('model_type', $attributes['model_type']);

            if ($usesTeams && is_string($teamForeignKey)) {
                $exists->whereNull($teamForeignKey);
            }

            if (! $exists->exists()) {
                DB::table($modelHasRolesTable)->insert($attributes);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
