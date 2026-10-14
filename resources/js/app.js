import { initSidebar } from './modules/sidebar.js';
import { initModals } from './modules/modals.js';
import { initJadwalMonitoring } from './pages/jadwal-monitoring.js';
import { initJadwalPiket } from './pages/jadwal-piket.js';
import { initJamPulangAutoSave } from './pages/jam-pulang.js';
import { initSpa } from './modules/spa.js';
import { initIdleTracker } from './modules/idleTracker.js';

document.addEventListener('DOMContentLoaded', function () {
    initSidebar();
    initModals();
    initJadwalMonitoring();
    initJadwalPiket();
    initJamPulangAutoSave();
    initSpa();
    initIdleTracker();
});
