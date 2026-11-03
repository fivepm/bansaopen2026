<?php
declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\CommitteeController;
use App\Controllers\Admin\ContactController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\GroupController;
use App\Controllers\Admin\LivePanelController;
use App\Controllers\Admin\MatchController as AdminMatch;
use App\Controllers\Admin\ScheduleController;
use App\Controllers\Admin\SettingController;
use App\Controllers\Admin\SportController;
use App\Controllers\Admin\SponsorController;
use App\Controllers\Admin\TeamController;
use App\Controllers\Admin\UserController;
use App\Controllers\PublicController;
use App\Middleware\AuthMiddleware;
use App\Middleware\SuperAdminMiddleware;

/** @var App\Core\Router $router */

// Catatan urutan: rute tetap didaftarkan lebih dulu daripada rute berparameter ({sport}).

// ---------- Admin: Auth (URL login rahasia, dari .env ADMIN_LOGIN_PATH) ----------
$login = '/' . admin_login_path();
$router->get($login, [AuthController::class, 'showLogin']);
$router->post($login, [AuthController::class, 'login']);
$router->post('/admin/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);

// ---------- Admin: Live Panel (Dapat diakses oleh Super Admin & Operator Wasit) ----------
$mwAuth = [AuthMiddleware::class];

$router->get('/admin/live-panel', [LivePanelController::class, 'index'], $mwAuth);
$router->post('/admin/live-panel/{id}/start-set', [LivePanelController::class, 'startSet'], $mwAuth);
$router->post('/admin/live-panel/{id}/finish-set', [LivePanelController::class, 'finishSet'], $mwAuth);
$router->post('/admin/live-panel/{id}/reopen-set', [LivePanelController::class, 'reopenSet'], $mwAuth);
$router->post('/admin/live-panel/{id}/point', [LivePanelController::class, 'point'], $mwAuth);
$router->post('/admin/live-panel/{id}/finish', [LivePanelController::class, 'finish'], $mwAuth);
$router->post('/admin/live-panel/{id}/reset-status', [LivePanelController::class, 'resetStatus'], $mwAuth);

// ---------- Super Admin (Hanya Super Admin, Operator ditolak dan dialihkan ke Live Panel) ----------
$mwSuper = [SuperAdminMiddleware::class];

$router->get('/admin', [DashboardController::class, 'index'], $mwSuper);

// Manajemen Logo & Pengaturan
$router->get('/admin/settings', [SettingController::class, 'index'], $mwSuper);
$router->post('/admin/settings/logo', [SettingController::class, 'updateLogo'], $mwSuper);
$router->post('/admin/settings/logo/delete', [SettingController::class, 'deleteLogo'], $mwSuper);

// Pengaturan Jadwal & Lokasi Turnamen
$router->get('/admin/schedules', [ScheduleController::class, 'index'], $mwSuper);
$router->post('/admin/schedules', [ScheduleController::class, 'save'], $mwSuper);

// Pengaturan Kontak & Sosial Media (Multi-Akun)
$router->get('/admin/contacts', [ContactController::class, 'index'], $mwSuper);
$router->get('/admin/contacts/create', [ContactController::class, 'create'], $mwSuper);
$router->post('/admin/contacts', [ContactController::class, 'store'], $mwSuper);
$router->get('/admin/contacts/{id}/edit', [ContactController::class, 'edit'], $mwSuper);
$router->post('/admin/contacts/{id}/update', [ContactController::class, 'update'], $mwSuper);
$router->post('/admin/contacts/{id}/delete', [ContactController::class, 'destroy'], $mwSuper);

// Manajemen Susunan Kepanitiaan (Many-to-Many)
$router->get('/admin/committees', [CommitteeController::class, 'index'], $mwSuper);
$router->get('/admin/committees/create', [CommitteeController::class, 'create'], $mwSuper);
$router->post('/admin/committees', [CommitteeController::class, 'store'], $mwSuper);
$router->get('/admin/committees/{id}/edit', [CommitteeController::class, 'edit'], $mwSuper);
$router->post('/admin/committees/{id}/update', [CommitteeController::class, 'update'], $mwSuper);
$router->post('/admin/committees/{id}/delete', [CommitteeController::class, 'destroy'], $mwSuper);

// Manajemen Sponsor
$router->get('/admin/sponsors', [SponsorController::class, 'index'], $mwSuper);
$router->get('/admin/sponsors/create', [SponsorController::class, 'create'], $mwSuper);
$router->post('/admin/sponsors', [SponsorController::class, 'store'], $mwSuper);
$router->get('/admin/sponsors/batch', [SponsorController::class, 'batch'], $mwSuper);
$router->post('/admin/sponsors/batch', [SponsorController::class, 'storeBatch'], $mwSuper);
$router->get('/admin/sponsors/{id}/edit', [SponsorController::class, 'edit'], $mwSuper);
$router->post('/admin/sponsors/{id}/update', [SponsorController::class, 'update'], $mwSuper);
$router->post('/admin/sponsors/{id}/toggle', [SponsorController::class, 'toggle'], $mwSuper);
$router->post('/admin/sponsors/{id}/delete', [SponsorController::class, 'destroy'], $mwSuper);

// Manajemen Pengguna & Wasit (RBAC)
$router->get('/admin/users', [UserController::class, 'index'], $mwSuper);
$router->get('/admin/users/create', [UserController::class, 'create'], $mwSuper);
$router->post('/admin/users', [UserController::class, 'store'], $mwSuper);
$router->get('/admin/users/{id}/edit', [UserController::class, 'edit'], $mwSuper);
$router->post('/admin/users/{id}/update', [UserController::class, 'update'], $mwSuper);
$router->post('/admin/users/{id}/delete', [UserController::class, 'destroy'], $mwSuper);

// ---------- Super Admin per cabang olahraga: /admin/{sport}/... ----------
$router->get('/admin/{sport}', [SportController::class, 'hub'], $mwSuper);
$router->post('/admin/{sport}/system', [SportController::class, 'setSystem'], $mwSuper);

// Tim
$router->get('/admin/{sport}/teams', [TeamController::class, 'index'], $mwSuper);
$router->get('/admin/{sport}/teams/create', [TeamController::class, 'create'], $mwSuper);
$router->post('/admin/{sport}/teams', [TeamController::class, 'store'], $mwSuper);
$router->get('/admin/{sport}/teams/{id}/edit', [TeamController::class, 'edit'], $mwSuper);
$router->post('/admin/{sport}/teams/{id}/update', [TeamController::class, 'update'], $mwSuper);
$router->post('/admin/{sport}/teams/{id}/delete', [TeamController::class, 'destroy'], $mwSuper);

// Bracket & Pertandingan
$router->get('/admin/{sport}/matches', [AdminMatch::class, 'index'], $mwSuper);
$router->post('/admin/{sport}/matches/generate', [AdminMatch::class, 'generate'], $mwSuper);
$router->post('/admin/{sport}/matches/reset', [AdminMatch::class, 'reset'], $mwSuper);
$router->post('/admin/{sport}/matches/{id}/status-system', [AdminMatch::class, 'updateStatusAndSystem'], $mwSuper);
$router->post('/admin/{sport}/matches/{id}/teams', [AdminMatch::class, 'updateTeams'], $mwSuper);
$router->get('/admin/{sport}/matches/{id}/edit', [AdminMatch::class, 'edit'], $mwSuper);
$router->post('/admin/{sport}/matches/{id}/update', [AdminMatch::class, 'update'], $mwSuper);
$router->post('/admin/{sport}/matches/{id}/set-live', [AdminMatch::class, 'setLive'], $mwSuper);

// Grup (Liga)
$router->get('/admin/{sport}/groups', [GroupController::class, 'index'], $mwSuper);
$router->post('/admin/{sport}/groups/create', [GroupController::class, 'create'], $mwSuper);
$router->post('/admin/{sport}/groups/assign', [GroupController::class, 'assign'], $mwSuper);
$router->post('/admin/{sport}/groups/auto', [GroupController::class, 'auto'], $mwSuper);
$router->post('/admin/{sport}/groups/fixtures', [GroupController::class, 'fixtures'], $mwSuper);
$router->post('/admin/{sport}/groups/reset', [GroupController::class, 'reset'], $mwSuper);

// ---------- Publik ----------
$router->get('/', [PublicController::class, 'home']);
$router->get('/kepanitiaan', [PublicController::class, 'committees']);
$router->get('/{sport}', [PublicController::class, 'sport']);
$router->get('/{sport}/live-data', [PublicController::class, 'liveData']);
$router->get('/{sport}/bracket', [PublicController::class, 'bracket']);
$router->get('/{sport}/klasemen', [PublicController::class, 'standings']);
$router->get('/{sport}/live', [PublicController::class, 'live']);
$router->get('/{sport}/hasil', [PublicController::class, 'results']);
