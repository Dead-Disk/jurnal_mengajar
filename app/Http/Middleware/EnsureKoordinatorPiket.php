<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batasi akses panel Koordinator Piket.
 *
 * Koordinator Piket BUKAN role tetap di database, melainkan tugas dinamis yang
 * diturunkan dari jadwal piket (koordinator_pagi_user_id / koordinator_siang_user_id)
 * pada hari berjalan. Hanya pengguna yang tercatat sebagai koordinator hari
 * ini yang dapat mengakses portal.
 */
class EnsureKoordinatorPiket
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        abort_unless(
            $user->isKoordinatorToday(),
            403,
            'Akses ditolak. Anda tidak bertugas sebagai Koordinator Piket hari ini.'
        );

        return $next($request);
    }
}