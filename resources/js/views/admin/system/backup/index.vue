<template>
    <div class="database-backup-page pb-5">
        <!-- Header & Top Actions -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="fas fa-database text-primary"></i>
                    Full Database Backup & Restore
                </h4>
                <p class="text-muted mb-0 small">
                    সম্পূর্ণ ডাটাবেজ ব্যাকআপ (.sql) ডাউনলোড করুন অথবা পূর্বের ব্যাকআপ ফাইল আপলোড করে ডাটাবেজ রিস্টোর করুন।
                </p>
            </div>
            <div class="d-flex gap-2">
                <router-link :to="{ name: 'softwareupdate.index' }" class="btn btn-outline-primary btn-sm px-3 d-flex align-items-center gap-2 shadow-sm">
                    <i class="fas fa-sync-alt"></i>
                    <span>Software Update</span>
                </router-link>
                <button type="button" class="btn btn-outline-secondary btn-sm px-3 d-flex align-items-center gap-2 shadow-sm" :disabled="loading || processing" @click="fetchInfo">
                    <i class="fas fa-redo" :class="{ 'fa-spin': loading }"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        <!-- System & Database Overview Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-server fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Active Database</small>
                            <h6 class="fw-bold mb-0 text-dark font-monospace">{{ info.database_name || 'Loading...' }}</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info">
                            <i class="fas fa-table fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Total Tables</small>
                            <h6 class="fw-bold mb-0 text-dark">{{ info.table_count || 0 }} Tables</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success">
                            <i class="fas fa-hard-drive fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Estimated DB Size</small>
                            <h6 class="fw-bold mb-0 text-dark">{{ info.size_mb || 0 }} MB</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-clock-rotate-left fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Server Backups</small>
                            <h6 class="fw-bold mb-0 text-dark">{{ info.total_backups_count || 0 }} Saved Files</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Backup & Restore Action Grid -->
        <div class="row g-4 mb-4">
            <!-- Left Column: Backup Database Card -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden d-flex flex-column">
                    <div class="card-header bg-primary bg-opacity-10 border-primary border-opacity-25 py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-cloud-arrow-down text-primary fa-lg"></i>
                            <h6 class="fw-bold mb-0 text-dark">Download Database Backup</h6>
                        </div>
                        <span class="badge bg-primary text-white fw-bold px-2 py-1">One-Click SQL</span>
                    </div>

                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <p class="text-secondary mb-3">
                                আপনার বর্তমান অর্গানাইজেশন ডাটাবেজের (<strong>{{ info.database_name }}</strong>) সকল টেবিল, স্কিমা ও ডেটার পূর্ণাঙ্গ ব্যাকআপ ডাউনলোড করুন।
                            </p>

                            <div class="bg-light rounded-3 p-3 mb-4 border">
                                <h6 class="fw-bold text-dark small mb-2 d-flex align-items-center gap-2">
                                    <i class="fas fa-circle-check text-success"></i>
                                    ব্যাকআপে যা অন্তর্ভুক্ত থাকবে:
                                </h6>
                                <ul class="list-unstyled mb-0 small text-muted">
                                    <li class="mb-1"><i class="fas fa-check text-primary me-2"></i>সকল টেবিল কাঠামো (Table Structures & DDL)</li>
                                    <li class="mb-1"><i class="fas fa-check text-primary me-2"></i>সকল রেকর্ড ও ডেটা (Full Rows & Data)</li>
                                    <li class="mb-1"><i class="fas fa-check text-primary me-2"></i>প্রাইমারি কি, ইনডেক্স ও ফরেন কি রুলস</li>
                                    <li><i class="fas fa-check text-primary me-2"></i>UTF-8 (utf8mb4) মাল্টি-ল্যাঙ্গুয়েজ সাপোর্ট</li>
                                </ul>
                            </div>
                        </div>

                        <div>
                            <div class="row g-2">
                                <div class="col-sm-7 col-12">
                                    <button type="button" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow d-flex align-items-center justify-content-center gap-2 action-btn" :disabled="processing || downloading" @click="downloadBackupFile">
                                        <i class="fas" :class="downloading ? 'fa-spinner fa-spin' : 'fa-file-arrow-down'"></i>
                                        <span>{{ downloading ? 'Generating Backup...' : 'Download .sql Backup Now' }}</span>
                                    </button>
                                </div>
                                <div class="col-sm-5 col-12">
                                    <button type="button" class="btn btn-outline-dark btn-lg w-100 fw-bold py-3 shadow-sm d-flex align-items-center justify-content-center gap-2" :disabled="processing || savingServerBackup" @click="saveBackupOnServer">
                                        <i class="fas" :class="savingServerBackup ? 'fa-spinner fa-spin' : 'fa-floppy-disk'"></i>
                                        <span style="font-size: 13.5px;">Save on Server</span>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2 text-center">
                                <small class="text-muted" style="font-size: 11px;">
                                    <i class="fas fa-shield-halved text-success me-1"></i>
                                    Engine: {{ info.has_mysqldump ? 'High-Speed mysqldump CLI' : 'Pure PHP PDO Stream Engine' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Restore Database Card -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden d-flex flex-column">
                    <div class="card-header bg-danger bg-opacity-10 border-danger border-opacity-25 py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-cloud-arrow-up text-danger fa-lg"></i>
                            <h6 class="fw-bold mb-0 text-dark">Restore Database (.sql File)</h6>
                        </div>
                        <span class="badge bg-danger text-white fw-bold px-2 py-1">Replace Active DB</span>
                    </div>

                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Dropzone File Upload -->
                            <div class="dropzone-box border-2 border-dashed rounded-3 p-4 text-center mb-3 cursor-pointer" :class="{ 'border-primary bg-primary bg-opacity-10': isDragging, 'border-secondary': !isDragging }" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="handleFileDrop" @click="triggerFileInput">
                                <input type="file" ref="fileInput" class="d-none" accept=".sql" @change="handleFileSelected" />
                                
                                <div v-if="!selectedFile">
                                    <div class="upload-icon-box mx-auto mb-2 rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger">
                                        <i class="fas fa-file-arrow-up fa-2x"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Click to browse or Drag & Drop .sql file</h6>
                                    <small class="text-muted">Supports standard MySQL .sql dump files</small>
                                </div>

                                <div v-else class="selected-file-info d-flex align-items-center justify-content-between bg-white border rounded-3 p-3 shadow-sm">
                                    <div class="d-flex align-items-center gap-3 text-start">
                                        <div class="file-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary">
                                            <i class="fas fa-file-code fa-lg"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 260px;">{{ selectedFile.name }}</div>
                                            <small class="text-muted font-monospace">{{ formatBytes(selectedFile.size) }}</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" title="Remove file" @click.stop="clearSelectedFile">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Safety Snapshot Checkbox -->
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="safetySnapshotSwitch" v-model="createSafetySnapshot" />
                                <label class="form-check-label small fw-semibold text-dark cursor-pointer" for="safetySnapshotSwitch">
                                    <i class="fas fa-shield-heart text-success me-1"></i>
                                    রিস্টোর করার আগে বর্তমান ডাটাবেজের একটি স্বয়ংক্রিয় সেফটি স্ন্যাপশট ব্যাকআপ রাখুন (প্রস্তাবিত)
                                </label>
                            </div>

                            <!-- Danger Warning -->
                            <div class="alert alert-warning border-warning border-opacity-50 py-2 px-3 mb-3 d-flex align-items-center gap-2 small">
                                <i class="fas fa-triangle-exclamation text-danger fa-lg flex-shrink-0"></i>
                                <span><strong>সতর্কতা:</strong> ব্যাকআপ ফাইল রিস্টোর করলে বর্তমান ডাটাবেজ ফাইলের ডেটা দ্বারা সম্পূর্ণ <strong>Replace</strong> হয়ে যাবে।</span>
                            </div>
                        </div>

                        <div>
                            <button type="button" class="btn btn-danger btn-lg w-100 fw-bold py-3 shadow d-flex align-items-center justify-content-center gap-2 action-btn" :disabled="!selectedFile || processing || restoring" @click="confirmRestoreModal">
                                <i class="fas" :class="restoring ? 'fa-spinner fa-spin' : 'fa-arrows-rotate'"></i>
                                <span>{{ restoring ? 'Restoring Database... Please wait' : 'Restore & Overwrite Database' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Grid: Stored Backups & Console Logs -->
        <div class="row g-4">
            <!-- Left: Stored Backups Table -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-folder-open text-primary"></i>
                            <h6 class="fw-bold mb-0 text-dark">Stored Backups & Safety Snapshots</h6>
                        </div>
                        <span class="badge bg-light text-dark border font-monospace">{{ info.stored_backups?.length || 0 }} Files</span>
                    </div>

                    <div class="card-body p-0">
                        <div v-if="!info.stored_backups || info.stored_backups.length === 0" class="p-5 text-center text-muted">
                            <i class="fas fa-folder-empty fa-3x mb-3 text-secondary opacity-50"></i>
                            <p class="mb-0">No server backup files found.</p>
                            <small>Use the "Save on Server" button or upload a backup to create one.</small>
                        </div>

                        <div v-else class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4">File Name</th>
                                        <th>Size</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(file, idx) in info.stored_backups" :key="`bk_${idx}`">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-file-code text-primary"></i>
                                                <span class="fw-semibold text-dark font-monospace small" :title="file.file_name">
                                                    {{ truncateString(file.file_name, 28) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="font-monospace small text-muted">{{ file.file_size_human }}</td>
                                        <td class="small text-muted">{{ file.created_at }}</td>
                                        <td>
                                            <span v-if="file.is_snapshot" class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25" style="font-size: 10.5px;">
                                                Safety Snapshot
                                            </span>
                                            <span v-else class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 10.5px;">
                                                Manual Backup
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-primary" title="Download .sql" @click="downloadStoredFile(file.file_name)">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Restore this file" :disabled="processing" @click="promptRestoreStoredFile(file)">
                                                    <i class="fas fa-rotate"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Delete" :disabled="processing" @click="deleteStoredFile(file.file_name)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Terminal Console Box -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                    <div class="card-header bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="terminal-dots d-flex gap-1">
                                <span class="dot bg-danger"></span>
                                <span class="dot bg-warning"></span>
                                <span class="dot bg-success"></span>
                            </div>
                            <span class="small fw-semibold ms-2 font-monospace">Backup & Restore Activity Log</span>
                        </div>
                        <button v-if="consoleLogs" type="button" class="btn btn-sm btn-link text-white-50 text-decoration-none p-0" @click="consoleLogs = ''">
                            Clear
                        </button>
                    </div>
                    <div class="card-body p-3 bg-dark text-light font-monospace d-flex flex-column" style="min-height: 380px;">
                        <div class="console-box flex-grow-1 p-2 overflow-auto" style="max-height: 340px; font-size: 12px; line-height: 1.6;">
                            <div class="text-white-50 mb-2">// System ready. Ready for Backup & Restore operations.</div>
                            <div v-if="lastActionTime" class="text-info mb-2">[{{ lastActionTime }}] Console active.</div>
                            <pre class="m-0 text-success" style="white-space: pre-wrap; font-family: inherit;">{{ consoleLogs || '> Awaiting backup or restore command...' }}</pre>
                        </div>
                        <div class="border-top border-secondary pt-2 mt-2 d-flex justify-content-between align-items-center text-white-50 small" style="font-size: 11px;">
                            <span>Status: {{ processing ? 'Executing...' : 'Idle / Ready' }}</span>
                            <span>PHP {{ info.php_version }} | MySQL {{ info.mysql_version }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal for Restore -->
        <div v-if="showConfirmModal" class="modal-backdrop fade show"></div>
        <div v-if="showConfirmModal" class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header bg-danger text-white py-3 px-4">
                        <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                            <i class="fas fa-triangle-exclamation"></i>
                            Confirm Database Restore
                        </h5>
                        <button type="button" class="btn-close btn-close-white" :disabled="processing" @click="showConfirmModal = false"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="text-center mb-3">
                            <div class="warning-icon-box mx-auto mb-2 rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-database fa-2x"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Are you absolutely sure?</h5>
                            <p class="text-muted small mb-0">
                                This action will replace all existing tables and records in <strong>{{ info.database_name }}</strong> with the contents of the backup file.
                            </p>
                        </div>

                        <div class="bg-light border rounded-3 p-3 mb-3 small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Target Database:</span>
                                <span class="fw-bold font-monospace text-dark">{{ info.database_name }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Backup File:</span>
                                <span class="fw-bold font-monospace text-primary text-truncate" style="max-width: 220px;">
                                    {{ restoreTargetFileName }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Safety Snapshot:</span>
                                <span :class="createSafetySnapshot ? 'text-success fw-bold' : 'text-danger fw-bold'">
                                    {{ createSafetySnapshot ? 'Yes (Automatic Backup)' : 'Disabled' }}
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label small fw-semibold text-dark">
                                Type <strong>RESTORE</strong> below to confirm:
                            </label>
                            <input type="text" class="form-control form-control-lg text-center fw-bold font-monospace" placeholder="RESTORE" v-model="confirmText" />
                        </div>
                    </div>

                    <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" :disabled="processing" @click="showConfirmModal = false">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger px-4 fw-bold d-flex align-items-center gap-2 shadow" :disabled="confirmText.trim().toUpperCase() !== 'RESTORE' || processing" @click="executeRestore">
                            <i class="fas" :class="restoring ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                            <span>{{ restoring ? 'Restoring...' : 'Yes, Replace & Restore' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "DatabaseBackupIndex",
    data() {
        return {
            loading: false,
            downloading: false,
            savingServerBackup: false,
            restoring: false,
            isDragging: false,
            selectedFile: null,
            createSafetySnapshot: true,
            showConfirmModal: false,
            confirmText: "",
            restoreTargetType: "upload", // 'upload' or 'stored'
            restoreTargetFileName: "",
            storedFileToRestore: null,
            consoleLogs: "",
            lastActionTime: "",
            info: {
                database_name: "",
                table_count: 0,
                size_mb: 0,
                has_mysqldump: false,
                php_version: "",
                mysql_version: "",
                server_os: "",
                stored_backups: [],
                total_backups_count: 0,
            },
        };
    },
    computed: {
        processing() {
            return this.downloading || this.savingServerBackup || this.restoring;
        },
    },
    created() {
        this.fetchInfo();
    },
    methods: {
        async fetchInfo() {
            this.loading = true;
            try {
                const res = await this.callApi("get", "database-backup/info");
                if (res.status === 200) {
                    this.info = res.data;
                    this.lastActionTime = new Date().toLocaleTimeString();
                }
            } catch (err) {
                this.$toast("Failed to load database information.", "error");
            } finally {
                this.loading = false;
            }
        },

        // Trigger direct browser download of generated .sql file
        downloadBackupFile() {
            this.downloading = true;
            this.lastActionTime = new Date().toLocaleTimeString();
            this.consoleLogs = `> [${this.lastActionTime}] Generating full database backup for '${this.info.database_name}'...\n> Engine: ${this.info.has_mysqldump ? 'mysqldump CLI' : 'PHP PDO Streaming Engine'}...\n> Preparing file stream for download...`;

            const url = (this.$root.baseurl ? this.$root.baseurl : "") + "/admin/database-backup/download";
            
            // Trigger direct download via hidden link or window location
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", `backup_${this.info.database_name}_${new Date().toISOString().slice(0, 10)}.sql`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            setTimeout(() => {
                this.downloading = false;
                this.consoleLogs += `\n> Backup download dispatched to browser successfully.`;
                this.$toast("Database backup download started!", "success");
                this.fetchInfo();
            }, 1200);
        },

        // Save backup directly on server storage
        async saveBackupOnServer() {
            this.savingServerBackup = true;
            this.lastActionTime = new Date().toLocaleTimeString();
            this.consoleLogs = `> [${this.lastActionTime}] Starting server-side backup generation for '${this.info.database_name}'...\n> Writing .sql backup to server storage...`;

            try {
                const res = await this.callApi("post", "database-backup/create");
                if (res.status === 200 && res.data.success) {
                    const data = res.data.data;
                    this.consoleLogs += `\n> Backup generated successfully!\n> File: ${data.file_name} (${data.file_size_human})\n> Path: ${data.file_path}\n> Method: ${data.method}`;
                    this.$toast(res.data.message || "Backup saved successfully on server!", "success");
                    await this.fetchInfo();
                } else {
                    this.consoleLogs += `\n> Error: ${res.data.message || 'Backup generation failed'}`;
                    this.$toast(res.data.message || "Backup creation failed.", "error");
                }
            } catch (err) {
                this.consoleLogs += `\n> Server exception occurred during backup generation.`;
                this.$toast("Failed to create server backup.", "error");
            } finally {
                this.savingServerBackup = false;
            }
        },

        // File selection handling
        triggerFileInput() {
            this.$refs.fileInput.click();
        },

        handleFileSelected(event) {
            const files = event.target.files;
            if (files && files.length > 0) {
                this.validateAndSetFile(files[0]);
            }
        },

        handleFileDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer.files;
            if (files && files.length > 0) {
                this.validateAndSetFile(files[0]);
            }
        },

        validateAndSetFile(file) {
            if (!file.name.toLowerCase().endsWith(".sql")) {
                this.$toast("Please select a valid .sql backup file.", "warning");
                return;
            }
            this.selectedFile = file;
            this.restoreTargetType = "upload";
            this.restoreTargetFileName = file.name;
        },

        clearSelectedFile() {
            this.selectedFile = null;
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = "";
            }
        },

        confirmRestoreModal() {
            if (!this.selectedFile && this.restoreTargetType === "upload") {
                this.$toast("Please select a .sql file first.", "warning");
                return;
            }
            this.confirmText = "";
            this.showConfirmModal = true;
        },

        promptRestoreStoredFile(file) {
            this.restoreTargetType = "stored";
            this.storedFileToRestore = file;
            this.restoreTargetFileName = file.file_name;
            this.confirmText = "";
            this.showConfirmModal = true;
        },

        // Execute Restore
        async executeRestore() {
            if (this.confirmText.trim().toUpperCase() !== "RESTORE") {
                return;
            }

            this.restoring = true;
            this.lastActionTime = new Date().toLocaleTimeString();
            this.consoleLogs = `> [${this.lastActionTime}] Starting database restoration for '${this.info.database_name}'...\n> Target file: ${this.restoreTargetFileName}\n> Safety snapshot enabled: ${this.createSafetySnapshot ? 'YES' : 'NO'}`;

            try {
                let res;
                if (this.restoreTargetType === "upload") {
                    const formData = new FormData();
                    formData.append("sql_file", this.selectedFile);
                    formData.append("create_safety_snapshot", this.createSafetySnapshot);

                    this.consoleLogs += `\n> Uploading .sql file to server and parsing statements...`;

                    res = await axios.post("/admin/database-backup/restore", formData, {
                        headers: { "Content-Type": "multipart/form-data" },
                    });
                } else {
                    this.consoleLogs += `\n> Reading server file and executing statements with Foreign Keys disabled...`;

                    res = await this.callApi("post", "database-backup/restore", {
                        file_name: this.storedFileToRestore.file_name,
                        create_safety_snapshot: this.createSafetySnapshot,
                    });
                }

                if (res.status === 200 && res.data.success) {
                    const data = res.data.data;
                    this.consoleLogs += `\n> RESTORE COMPLETE SUCCESS!\n> Executed Statements: ${data.executed_statements}\n> Duration: ${data.duration_seconds} seconds`;
                    if (data.snapshot) {
                        this.consoleLogs += `\n> Pre-restore snapshot created at: ${data.snapshot.file_name}`;
                    }
                    this.consoleLogs += `\n> System and application cache cleared automatically.`;

                    this.$toast("Database restored successfully!", "success");
                    this.showConfirmModal = false;
                    this.clearSelectedFile();
                    await this.fetchInfo();
                } else {
                    this.consoleLogs += `\n> Restore Error: ${res.data.message || 'Unknown error'}`;
                    this.$toast(res.data.message || "Database restoration failed.", "error");
                }
            } catch (err) {
                const errMsg = err.response?.data?.message || err.message || "Restore execution error";
                this.consoleLogs += `\n> Exception occurred: ${errMsg}`;
                this.$toast("Database restore failed: " + errMsg, "error");
            } finally {
                this.restoring = false;
            }
        },

        // Download existing stored backup file
        downloadStoredFile(fileName) {
            const url = (this.$root.baseurl ? this.$root.baseurl : "") + `/admin/database-backup/download-file/${encodeURIComponent(fileName)}`;
            window.open(url, "_blank");
        },

        // Delete existing stored backup file
        async deleteStoredFile(fileName) {
            if (!confirm(`Are you sure you want to delete backup file '${fileName}'?`)) {
                return;
            }

            try {
                const res = await this.callApi("delete", `database-backup/delete-file/${encodeURIComponent(fileName)}`);
                if (res.status === 200 && res.data.success) {
                    this.$toast("Backup file deleted successfully.", "success");
                    await this.fetchInfo();
                } else {
                    this.$toast(res.data.message || "Failed to delete backup file.", "error");
                }
            } catch (err) {
                this.$toast("Delete failed.", "error");
            }
        },

        formatBytes(bytes) {
            if (!bytes || bytes <= 0) return "0 B";
            const k = 1024;
            const sizes = ["B", "KB", "MB", "GB"];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
        },

        truncateString(str, num) {
            if (!str) return "";
            if (str.length <= num) return str;
            return str.slice(0, num) + "...";
        },
    },
};
</script>

<style scoped>
.stat-icon {
    width: 48px;
    height: 48px;
    flex-shrink: 0;
}
.upload-icon-box {
    width: 64px;
    height: 64px;
}
.file-icon {
    width: 42px;
    height: 42px;
    flex-shrink: 0;
}
.warning-icon-box {
    width: 68px;
    height: 68px;
}
.action-btn {
    transition: all 0.3s ease;
}
.action-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(13, 110, 253, 0.35) !important;
}
.dropzone-box {
    background-color: #fafbfc;
    transition: all 0.25s ease-in-out;
}
.dropzone-box:hover {
    background-color: #f1f5f9;
    border-color: #0d6efd !important;
}
.cursor-pointer {
    cursor: pointer;
}
.terminal-dots .dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}
.console-box::-webkit-scrollbar {
    width: 6px;
}
.console-box::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
}
</style>
