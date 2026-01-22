<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

use App\Ldap\User as LdapUser;
use App\Models\User as DbUser;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Sync semua dosen dari LDAP ke database lokal (tabel users).
 *
 * Test dulu:
 *   php artisan ldap:sync-dosen --dry-run --limit=5
 *
 * Jalankan beneran:
 *   php artisan ldap:sync-dosen
 *
 * Opsional:
 *   php artisan ldap:sync-dosen --base-dn="ou=Dosen,dc=example,dc=ac,dc=id"
 *   php artisan ldap:sync-dosen --filter="(&(objectClass=inetOrgPerson)(uid=*))"
 */
Artisan::command('ldap:sync-dosen
    {--base-dn= : Base DN khusus dosen (kalau ada OU dosen)}
    {--filter= : LDAP raw filter}
    {--page=500 : Page size untuk chunk}
    {--limit=0 : Batasi jumlah (buat test)}
    {--dry-run : Tidak menulis DB}
    {--role=pengaju : Role default untuk user hasil sync}
', function () {

    $baseDn = $this->option('base-dn')
        ?: env('LDAP_DOSEN_BASE_DN', config('ldap.connections.default.base_dn'));

    $filter = $this->option('filter')
        ?: env('LDAP_DOSEN_FILTER', '(uid=*)');

    $pageSize = (int) ($this->option('page') ?: 500);
    $limit    = (int) ($this->option('limit') ?: 0);
    $dryRun   = (bool) $this->option('dry-run');
    $role     = (string) ($this->option('role') ?: 'pengaju');

    $this->info("LDAP Sync Dosen");
    $this->line("Base DN : {$baseDn}");
    $this->line("Filter  : {$filter}");
    $this->line("Page    : {$pageSize}");
    $this->line("Limit   : " . ($limit > 0 ? $limit : 'no limit'));
    $this->line("Mode    : " . ($dryRun ? 'DRY RUN' : 'WRITE DB'));
    $this->newLine();

    $stats = [
        'processed' => 0,
        'created'   => 0,
        'updated'   => 0,
        'skipped_admin' => 0,
        'no_username'   => 0,
    ];

    $counter = 0;

    $query = LdapUser::query()
        ->in($baseDn)
        ->rawFilter($filter)
        ->select([
            'uid', 'cn', 'displayname', 'mail',
            'description', 'department', 'title', 'physicaldeliveryofficename',
            'samaccountname', 'userprincipalname', 'entryuuid', 'objectguid',
        ]);

    $query->chunk($pageSize, function ($results) use (&$stats, &$counter, $limit, $dryRun, $role) {
        foreach ($results as $ldap) {

            if ($limit > 0 && $counter >= $limit) {
                return false;
            }
            $counter++;

            // Ambil username dari beberapa kemungkinan atribut
            $username =
                $ldap->getFirstAttribute('uid')
                ?: $ldap->getFirstAttribute('samaccountname')
                ?: (function () use ($ldap) {
                    $upn = $ldap->getFirstAttribute('userprincipalname');
                    return $upn ? explode('@', $upn)[0] : null;
                })();

            if (!$username) {
                $stats['no_username']++;
                continue;
            }

            $name =
                $ldap->getFirstAttribute('displayname')
                ?: $ldap->getFirstAttribute('cn')
                ?: $username;

            $email = $ldap->getFirstAttribute('mail');

            $payload = [
                'name'          => $name,
                'email'         => $email,
                // Mapping sesuai login kamu yang sekarang:
                'nidn'          => $ldap->getFirstAttribute('description'),
                'fakultas'      => $ldap->getFirstAttribute('department'),
                'program_studi' => $ldap->getFirstAttribute('title'),
                'jabatan'       => $ldap->getFirstAttribute('physicaldeliveryofficename'),
                'guid'          => method_exists($ldap, 'getConvertedGuid') ? ($ldap->getConvertedGuid() ?: $ldap->getFirstAttribute('entryuuid')) : $ldap->getFirstAttribute('entryuuid'),
                'domain'        => 'default',
                'role'          => $role,
            ];

            $existing = DbUser::where('username', $username)->first();

            // Jangan timpa admin lokal
            if ($existing && $existing->role === 'admin') {
                $stats['skipped_admin']++;
                continue;
            }

            if ($dryRun) {
                $stats['processed']++;
                continue;
            }

            if ($existing) {
                $existing->fill($payload);
                $existing->save();
                $stats['updated']++;
            } else {
                DbUser::create(array_merge($payload, [
                    'username' => $username,
                    // password random biar tidak dipakai login lokal sembarangan
                    'password' => bcrypt(Str::random(32)),
                ]));
                $stats['created']++;
            }

            $stats['processed']++;
        }

        return true;
    }, false, true);

    $this->newLine();
    $this->info("DONE. Statistik:");
    foreach ($stats as $k => $v) {
        $this->line("- {$k}: {$v}");
    }
})->purpose('Sync semua dosen dari LDAP ke database lokal');
