<div class="settings-modal" id="settingsDeviceResetModal" aria-hidden="true">
    <div class="settings-modal-backdrop" data-device-reset-close></div>
    <div class="settings-modal-card" role="dialog" aria-modal="true" aria-labelledby="settingsDeviceResetTitle">
        <div class="settings-modal-icon"><i class="fas fa-broom"></i></div>
        <h2 class="settings-modal-title" id="settingsDeviceResetTitle">Reset device?</h2>
        <div class="settings-modal-copy">Device akan clear data lokal saat heartbeat berikutnya. Gunakan untuk device yang cache atau data lokalnya bermasalah.</div>
        <div class="settings-modal-actions">
            <button type="button" class="settings-btn is-muted" data-device-reset-close>Batal</button>
            <button type="button" class="settings-btn is-danger" id="settingsDeviceResetConfirm">
                <i class="fas fa-broom"></i> Reset Device
            </button>
        </div>
    </div>
</div>

<div class="settings-modal" id="settingsDeviceDeleteModal" aria-hidden="true">
    <div class="settings-modal-backdrop" data-device-delete-close></div>
    <div class="settings-modal-card" role="dialog" aria-modal="true" aria-labelledby="settingsDeviceDeleteTitle">
        <div class="settings-modal-icon"><i class="fas fa-trash"></i></div>
        <h2 class="settings-modal-title" id="settingsDeviceDeleteTitle">Hapus device?</h2>
        <div class="settings-modal-copy">Device akan dihapus dari monitoring. Jika browser device masih aktif, heartbeat berikutnya bisa mendaftarkannya lagi.</div>
        <div class="settings-modal-actions">
            <button type="button" class="settings-btn is-muted" data-device-delete-close>Batal</button>
            <button type="button" class="settings-btn is-danger" id="settingsDeviceDeleteConfirm">
                <i class="fas fa-trash"></i> Hapus Device
            </button>
        </div>
    </div>
</div>
